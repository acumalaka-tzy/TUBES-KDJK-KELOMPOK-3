<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\RoomParticipant;
use App\Models\SecurityLog;
use App\Models\UserSession;
use Illuminate\Support\Str;

class RoomService
{
    public function __construct(
        protected RoomIdGeneratorService $idGenerator,
        protected RoomPinValidationService $pinService
    ) {}

    /**
     * Create a new room with configuration and enroll creator as first participant.
     */
    public function createRoom(UserSession $creator, array $data): Room
    {
        $roomId = ! empty($data['room_id']) && $this->idGenerator->isValidFormat($data['room_id'])
            ? trim($data['room_id'])
            : $this->idGenerator->generate($data['id_format'] ?? 'code');

        // Check PIN setup
        $pinHash = null;
        if (! empty($data['pin'])) {
            $pinHash = $this->pinService->hashPin($data['pin']);
        }

        // Dynamic TOTP PIN setup
        $dynamicPinEnabled = (bool) ($data['dynamic_pin_enabled'] ?? false);
        $dynamicPinSecret = null;
        if ($dynamicPinEnabled) {
            $dynamicPinSecret = $this->pinService->generateTotpSecret();
        }

        $pinExpiresAt = ! empty($data['pin_expires_in_minutes'])
            ? now()->addMinutes((int) $data['pin_expires_in_minutes'])
            : null;

        $room = Room::create([
            'room_id' => $roomId,
            'room_name' => trim($data['room_name']),
            'description' => $data['description'] ?? null,
            'creator_id' => $creator->user_id,
            'room_pin_hash' => $pinHash,
            'dynamic_pin_enabled' => $dynamicPinEnabled,
            'dynamic_pin_secret' => $dynamicPinSecret,
            'pin_expires_at' => $pinExpiresAt,
            'max_participants' => max(2, min((int) ($data['max_participants'] ?? 10), 50)),
            'e2ee_enabled' => (bool) ($data['e2ee_enabled'] ?? true),
            'ephemeral_enabled' => (bool) ($data['ephemeral_enabled'] ?? false),
            'default_ephemeral_duration' => (int) ($data['default_ephemeral_duration'] ?? 0),
            'is_locked' => false,
            'allow_invites' => (bool) ($data['allow_invites'] ?? true),
            'allow_reactions' => (bool) ($data['allow_reactions'] ?? true),
            'allow_replies' => (bool) ($data['allow_replies'] ?? true),
            'expires_at' => ! empty($data['expires_in_hours']) ? now()->addHours((int) $data['expires_in_hours']) : null,
        ]);

        // Creator automatically joins as participant
        RoomParticipant::create([
            'participant_id' => 'part_'.Str::random(12),
            'room_id' => $room->room_id,
            'user_id' => $creator->user_id,
            'display_name' => $creator->display_name,
            'role' => 'creator',
            'connection_status' => 'connected',
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        SecurityLog::logEvent(
            eventType: 'room_created',
            roomId: $room->room_id,
            userId: $creator->user_id,
            details: [
                'has_pin' => $room->hasPin(),
                'dynamic_pin' => $dynamicPinEnabled,
                'e2ee' => $room->e2ee_enabled,
                'max_participants' => $room->max_participants,
            ]
        );

        return $room;
    }

    /**
     * Join an existing room with access validation (PIN, lock, capacity, kick-status).
     *
     * @return array{success: bool, room: ?Room, participant: ?RoomParticipant, message: string, code: int}
     */
    public function joinRoom(
        UserSession $user,
        string $roomId,
        ?string $pin = null,
        ?string $inviteToken = null
    ): array {
        $room = Room::where('room_id', trim($roomId))->first();

        if (! $room) {
            return [
                'success' => false,
                'room' => null,
                'participant' => null,
                'message' => 'Room tidak ditemukan. Periksa kembali Room ID.',
                'code' => 404,
            ];
        }

        if ($room->isExpired()) {
            return [
                'success' => false,
                'room' => null,
                'participant' => null,
                'message' => 'Room ini telah kedaluwarsa.',
                'code' => 410,
            ];
        }

        $existingParticipant = $room->getParticipant($user->user_id);

        // Check if user has been kicked
        if ($existingParticipant && $existingParticipant->is_kicked) {
            SecurityLog::logEvent(
                eventType: 'kicked_user_join_blocked',
                roomId: $room->room_id,
                userId: $user->user_id
            );

            return [
                'success' => false,
                'room' => null,
                'participant' => null,
                'message' => 'Anda telah dikeluarkan dari room ini oleh Room Creator.',
                'code' => 403,
            ];
        }

        // Creator always has instant access
        $isCreator = $room->isCreator($user->user_id);

        if (! $isCreator) {
            // Check if room is locked
            if ($room->isLocked() && ! $existingParticipant) {
                return [
                    'success' => false,
                    'room' => null,
                    'participant' => null,
                    'message' => 'Room saat ini sedang dikunci oleh Creator. Akses ditolak.',
                    'code' => 423, // Locked
                ];
            }

            // Check if room is full
            if (! $existingParticipant && $room->isFull()) {
                return [
                    'success' => false,
                    'room' => null,
                    'participant' => null,
                    'message' => 'Kapasitas maksimum peserta room telah tercapai.',
                    'code' => 409,
                ];
            }

            // Check invitation token if provided
            $hasValidInvite = false;
            if ($inviteToken) {
                $invite = $this->validateInvitation($inviteToken, $room->room_id);
                if ($invite) {
                    $invite->recordUsage();
                    $hasValidInvite = true;
                }
            }

            // Validate PIN if room requires PIN and user is not yet an active member
            if ($room->hasPin() && ! $existingParticipant) {
                $pinValidation = $this->pinService->validatePin($room, $pin, $user->user_id);
                if (! $pinValidation['valid']) {
                    return [
                        'success' => false,
                        'room' => null,
                        'participant' => null,
                        'message' => $pinValidation['message'],
                        'code' => 401,
                    ];
                }
            }
        }

        // Add or update participant
        if ($existingParticipant) {
            $existingParticipant->update([
                'display_name' => $user->display_name,
                'connection_status' => 'connected',
                'last_seen_at' => now(),
            ]);
            $participant = $existingParticipant;
        } else {
            $participant = RoomParticipant::create([
                'participant_id' => 'part_'.Str::random(12),
                'room_id' => $room->room_id,
                'user_id' => $user->user_id,
                'display_name' => $user->display_name,
                'role' => $isCreator ? 'creator' : 'participant',
                'connection_status' => 'connected',
                'joined_at' => now(),
                'last_seen_at' => now(),
            ]);
        }

        SecurityLog::logEvent(
            eventType: 'user_joined',
            roomId: $room->room_id,
            userId: $user->user_id,
            details: ['role' => $participant->role]
        );

        return [
            'success' => true,
            'room' => $room->fresh(),
            'participant' => $participant,
            'message' => 'Berhasil bergabung ke room.',
            'code' => 200,
        ];
    }

    /**
     * Leave room and update participant connection status.
     */
    public function leaveRoom(UserSession $user, Room $room): void
    {
        $participant = $room->getParticipant($user->user_id);
        if ($participant) {
            $participant->update([
                'connection_status' => 'disconnected',
                'last_seen_at' => now(),
            ]);
        }

        SecurityLog::logEvent(
            eventType: 'user_left',
            roomId: $room->room_id,
            userId: $user->user_id
        );
    }

    /**
     * Kick participant (Creator only).
     */
    public function kickParticipant(UserSession $actor, Room $room, string $targetUserId): bool
    {
        if (! $room->isCreator($actor->user_id)) {
            return false;
        }

        if ($actor->user_id === $targetUserId) {
            return false; // Creator cannot kick themselves
        }

        $target = $room->getParticipant($targetUserId);
        if (! $target) {
            return false;
        }

        $target->update([
            'is_kicked' => true,
            'connection_status' => 'disconnected',
            'last_seen_at' => now(),
        ]);

        SecurityLog::logEvent(
            eventType: 'user_kicked',
            roomId: $room->room_id,
            userId: $actor->user_id,
            details: ['target_user_id' => $targetUserId]
        );

        return true;
    }

    /**
     * Lock or unlock room (Creator only).
     */
    public function toggleLock(UserSession $actor, Room $room, ?bool $lock = null): bool
    {
        if (! $room->isCreator($actor->user_id)) {
            return false;
        }

        $newStatus = $lock !== null ? $lock : ! $room->is_locked;
        $room->update(['is_locked' => $newStatus]);

        SecurityLog::logEvent(
            eventType: $newStatus ? 'room_locked' : 'room_unlocked',
            roomId: $room->room_id,
            userId: $actor->user_id
        );

        return true;
    }

    /**
     * Update room settings and permissions (Creator only).
     */
    public function updateRoomSettings(UserSession $actor, Room $room, array $settings): ?Room
    {
        if (! $room->isCreator($actor->user_id)) {
            return null;
        }

        $updates = [];

        if (array_key_exists('room_name', $settings)) {
            $updates['room_name'] = trim($settings['room_name']);
        }
        if (array_key_exists('description', $settings)) {
            $updates['description'] = $settings['description'];
        }
        if (array_key_exists('allow_invites', $settings)) {
            $updates['allow_invites'] = (bool) $settings['allow_invites'];
        }
        if (array_key_exists('allow_reactions', $settings)) {
            $updates['allow_reactions'] = (bool) $settings['allow_reactions'];
        }
        if (array_key_exists('allow_replies', $settings)) {
            $updates['allow_replies'] = (bool) $settings['allow_replies'];
        }
        if (array_key_exists('default_ephemeral_duration', $settings)) {
            $updates['default_ephemeral_duration'] = (int) $settings['default_ephemeral_duration'];
        }

        // Change or remove PIN
        if (array_key_exists('pin', $settings)) {
            if ($settings['pin'] === null || trim((string) $settings['pin']) === '') {
                $updates['room_pin_hash'] = null;
                SecurityLog::logEvent('pin_removed', $room->room_id, $actor->user_id);
            } else {
                $updates['room_pin_hash'] = $this->pinService->hashPin((string) $settings['pin']);
                SecurityLog::logEvent('pin_updated', $room->room_id, $actor->user_id);
            }
        }

        if (array_key_exists('pin_expires_in_minutes', $settings)) {
            $updates['pin_expires_at'] = ! empty($settings['pin_expires_in_minutes'])
                ? now()->addMinutes((int) $settings['pin_expires_in_minutes'])
                : null;
        }

        // Dynamic PIN toggle
        if (array_key_exists('dynamic_pin_enabled', $settings)) {
            $dynamicPin = (bool) $settings['dynamic_pin_enabled'];
            $updates['dynamic_pin_enabled'] = $dynamicPin;
            if ($dynamicPin && empty($room->dynamic_pin_secret)) {
                $updates['dynamic_pin_secret'] = $this->pinService->generateTotpSecret();
            }
        }

        $room->update($updates);

        SecurityLog::logEvent(
            eventType: 'room_settings_updated',
            roomId: $room->room_id,
            userId: $actor->user_id,
            details: array_keys($updates)
        );

        return $room->fresh();
    }

    /**
     * Retrieve clean, safe room metadata for clients.
     * PRD compliance: never expose PIN hash or TOTP secret!
     */
    public function getRoomMetadata(Room $room, ?UserSession $forUser = null): array
    {
        $isCreator = $forUser ? $room->isCreator($forUser->user_id) : false;
        $isMember = $forUser ? $room->hasParticipant($forUser->user_id) : false;

        $participants = $room->activeParticipants()->with('userSession')->get()->map(function ($part) {
            return [
                'user_id' => $part->user_id,
                'display_name' => $part->display_name,
                'role' => $part->role,
                'connection_status' => $part->connection_status,
                'avatar_color' => $part->userSession?->avatar_color ?? '#008069',
                'avatar_icon' => $part->userSession?->avatar_icon ?? 'initial',
                'avatar_display' => $part->userSession?->avatar_display ?? strtoupper(substr($part->display_name, 0, 1)),
                'avatar_html' => $part->userSession?->avatar_html ?? strtoupper(substr($part->display_name, 0, 1)),
                'joined_at' => $part->joined_at?->toIso8601String(),
                'last_seen_at' => $part->last_seen_at?->toIso8601String(),
            ];
        });

        $metadata = [
            'room_id' => $room->room_id,
            'room_name' => $room->room_name,
            'description' => $room->description,
            'creator_id' => $room->creator_id,
            'creator_name' => $room->creator?->display_name ?? 'Creator',
            'has_pin' => $room->hasPin(),
            'pin_expired' => $room->isPinExpired(),
            'pin_expires_at' => $room->pin_expires_at?->toIso8601String(),
            'dynamic_pin_enabled' => $room->dynamic_pin_enabled,
            'max_participants' => $room->max_participants,
            'active_participants_count' => $participants->count(),
            'is_full' => $room->isFull(),
            'is_locked' => $room->isLocked(),
            'e2ee_enabled' => $room->e2ee_enabled,
            'ephemeral_enabled' => $room->ephemeral_enabled,
            'default_ephemeral_duration' => $room->default_ephemeral_duration,
            'allow_invites' => $room->allow_invites,
            'allow_reactions' => $room->allow_reactions,
            'allow_replies' => $room->allow_replies,
            'is_creator' => $isCreator,
            'is_member' => $isMember,
            'participants' => $participants,
            'created_at' => $room->created_at?->toIso8601String(),
        ];

        // If current user is the creator and dynamic PIN is enabled, provide the current TOTP for display
        if ($isCreator && $room->dynamic_pin_enabled && ! empty($room->dynamic_pin_secret)) {
            $dynamicStatus = $this->pinService->getDynamicPinStatus($room);
            $metadata['current_dynamic_pin'] = $dynamicStatus['current_pin'];
            $metadata['dynamic_pin_expires_in'] = $dynamicStatus['expires_in_seconds'];
        }

        return $metadata;
    }

    /**
     * Rotate a room's Dynamic TOTP PIN secret (Creator only).
     *
     * @return ?array{enabled: bool, current_pin: ?string, expires_in_seconds: ?int, time_step: int, digits: int}
     */
    public function rotateDynamicPinSecret(UserSession $actor, Room $room): ?array
    {
        if (! $room->isCreator($actor->user_id)) {
            return null;
        }

        $room->update([
            'dynamic_pin_enabled' => true,
            'dynamic_pin_secret' => $this->pinService->generateTotpSecret(),
        ]);

        SecurityLog::logEvent(
            eventType: 'dynamic_pin_secret_rotated',
            roomId: $room->room_id,
            userId: $actor->user_id
        );

        return $this->pinService->getDynamicPinStatus($room->fresh());
    }

    /**
     * Create an invite token for the room.
     *
     * @return array{invitation: RoomInvitation, raw_token: string}
     */
    public function createInvitation(
        UserSession $actor,
        Room $room,
        ?int $usageLimit = null,
        ?int $expiresInMinutes = 1440
    ): array {
        $rawToken = Str::random(32);
        $tokenHash = hash('sha256', $rawToken);

        $invitation = RoomInvitation::create([
            'invite_id' => 'inv_'.Str::random(12),
            'room_id' => $room->room_id,
            'token_hash' => $tokenHash,
            'created_by' => $actor->user_id,
            'usage_limit' => $usageLimit,
            'usage_count' => 0,
            'expires_at' => $expiresInMinutes ? now()->addMinutes($expiresInMinutes) : null,
        ]);

        return [
            'invitation' => $invitation,
            'raw_token' => $rawToken,
        ];
    }

    /**
     * Validate raw invitation token for a room.
     */
    public function validateInvitation(string $rawToken, string $roomId): ?RoomInvitation
    {
        $hash = hash('sha256', $rawToken);
        $invitation = RoomInvitation::where('room_id', $roomId)
            ->where('token_hash', $hash)
            ->first();

        if ($invitation && $invitation->isValid()) {
            return $invitation;
        }

        return null;
    }
}
