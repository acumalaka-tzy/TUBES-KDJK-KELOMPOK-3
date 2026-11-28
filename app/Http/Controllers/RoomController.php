<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\JoinRoomRequest;
use App\Http\Requests\KickParticipantRequest;
use App\Http\Requests\UpdateRoomSettingsRequest;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Services\ConversationStateService;
use App\Services\RoomPinValidationService;
use App\Services\RoomService;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService,
        protected UserIdentityService $identityService,
        protected RoomPinValidationService $pinService,
        protected ConversationStateService $conversationState
    ) {}

    /**
     * Get user's active/recent rooms.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'rooms' => []], 401);
        }

        $participations = $user->participations()
            ->where('is_kicked', false)
            ->with(['room' => fn ($q) => $q->withCount('activeParticipants')])
            ->latest('last_seen_at')
            ->take(15)
            ->get();

        $rooms = $participations->map(function ($part) {
            $room = $part->room;
            $summary = $this->conversationState->getRoomSummaryForParticipant($part);

            return [
                'room_id' => $room->room_id,
                'room_name' => $room->room_name,
                'role' => $part->role,
                'is_locked' => $room->isLocked(),
                'has_pin' => $room->hasPin(),
                'is_archived' => $summary['is_archived'],
                'unread_count' => $summary['unread_count'],
                'last_message_preview' => $summary['last_message_preview'],
                'last_message_time' => $summary['last_message_time'],
                'active_participants_count' => $room->active_participants_count,
                'max_participants' => $room->max_participants,
                'joined_at' => $part->joined_at?->toIso8601String(),
                'last_seen_at' => $part->last_seen_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Create a new room.
     */
    public function store(CreateRoomRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Identitas diperlukan untuk membuat room.'], 401);
            }

            return redirect()->route('identity.setup');
        }

        $room = $this->roomService->createRoom($user, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Room berhasil dibuat.',
                'room' => $this->roomService->getRoomMetadata($room, $user),
            ], 201);
        }

        return redirect()->route('rooms.show', $room->room_id)
            ->with('just_created_room', true)
            ->with('success', "Room '{$room->room_name}' berhasil dibuat!");
    }

    /**
     * Get room metadata and participants.
     */
    public function show(Request $request, string $roomId): JsonResponse
    {
        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $user = $this->identityService->getCurrentUser($request);
        $metadata = $this->roomService->getRoomMetadata($room, $user);

        return response()->json([
            'success' => true,
            'room' => $metadata,
        ]);
    }

    /**
     * Join room.
     */
    public function join(JoinRoomRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Identitas diperlukan untuk bergabung.'], 401);
            }

            return redirect()->route('identity.setup');
        }

        $result = $this->roomService->joinRoom(
            user: $user,
            roomId: $request->validated('room_id'),
            pin: $request->validated('pin'),
            inviteToken: $request->validated('invite_token')
        );

        if (! $result['success']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], $result['code']);
            }

            return back()->withErrors(['room_id' => $result['message']]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'room' => $this->roomService->getRoomMetadata($result['room'], $user),
                'participant' => [
                    'participant_id' => $result['participant']->participant_id,
                    'role' => $result['participant']->role,
                ],
            ]);
        }

        return redirect()->route('rooms.show', $result['room']->room_id);
    }

    /**
     * Leave room.
     */
    public function leave(Request $request, string $roomId): JsonResponse|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        $room = Room::where('room_id', $roomId)->first();

        if ($user && $room) {
            $this->roomService->leaveRoom($user, $room);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Anda telah keluar dari room.',
            ]);
        }

        return redirect()->route('dashboard')->with('info', 'Anda telah keluar dari room.');
    }

    /**
     * Kick participant (Creator only).
     */
    public function kick(KickParticipantRequest $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $kicked = $this->roomService->kickParticipant(
            actor: $user,
            room: $room,
            targetUserId: $request->validated('target_user_id')
        );

        if (! $kicked) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeluarkan peserta. Pastikan Anda memiliki hak Room Creator.',
            ], 403);
        }

        broadcast(new \App\Events\RoomParticipantUpdated(
            roomId: $room->room_id,
            userId: $request->validated('target_user_id'),
            action: 'kicked'
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Peserta berhasil dikeluarkan dari room.',
        ]);
    }

    /**
     * Toggle lock room (Creator only).
     */
    public function toggleLock(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $lockInput = $request->has('is_locked') ? (bool) $request->input('is_locked') : null;
        $toggled = $this->roomService->toggleLock($user, $room, $lockInput);

        if (! $toggled) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Room Creator yang dapat mengunci atau membuka room.',
            ], 403);
        }

        $updatedRoom = $room->fresh();

        broadcast(new \App\Events\RoomParticipantUpdated(
            roomId: $room->room_id,
            userId: $user->user_id,
            action: $updatedRoom->isLocked() ? 'locked' : 'unlocked'
        ))->toOthers();

        return response()->json([
            'success' => true,
            'is_locked' => $updatedRoom->isLocked(),
            'message' => $updatedRoom->isLocked() ? 'Room berhasil dikunci.' : 'Room berhasil dibuka.',
        ]);
    }

    /**
     * Update room settings (Creator only).
     */
    public function updateSettings(UpdateRoomSettingsRequest $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $updated = $this->roomService->updateRoomSettings($user, $room, $request->validated());

        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Room Creator yang dapat memperbarui pengaturan room.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan room berhasil diperbarui.',
            'room' => $this->roomService->getRoomMetadata($updated, $user),
        ]);
    }

    /**
     * Create invitation link/token.
     */
    public function createInvite(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        if (! $room->isCreator($user->user_id) && ! $room->allow_invites) {
            return response()->json(['success' => false, 'message' => 'Undangan dinonaktifkan untuk room ini.'], 403);
        }

        $inviteData = $this->roomService->createInvitation(
            actor: $user,
            room: $room,
            usageLimit: $request->input('usage_limit'),
            expiresInMinutes: (int) ($request->input('expires_in_minutes', 1440))
        );

        $baseUrl = $request->getSchemeAndHttpHost();
        $inviteUrl = "{$baseUrl}/rooms/join?room_id={$room->room_id}&invite_token={$inviteData['raw_token']}";

        return response()->json([
            'success' => true,
            'invite_id' => $inviteData['invitation']->invite_id,
            'token' => $inviteData['raw_token'],
            'invite_url' => $inviteUrl,
            'expires_at' => $inviteData['invitation']->expires_at?->toIso8601String(),
            'usage_limit' => $inviteData['invitation']->usage_limit,
        ]);
    }

    /**
     * List invitations for a room (Member/Creator).
     */
    public function listInvites(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $invites = $room->invitations()
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(function ($inv) {
                $status = 'active';
                if ($inv->revoked_at !== null) {
                    $status = 'revoked';
                } elseif ($inv->expires_at !== null && $inv->expires_at->isPast()) {
                    $status = 'expired';
                } elseif ($inv->usage_limit !== null && $inv->usage_count >= $inv->usage_limit) {
                    $status = 'exhausted';
                }

                return [
                    'invite_id' => $inv->invite_id,
                    'status' => $status,
                    'usage_count' => $inv->usage_count,
                    'usage_limit' => $inv->usage_limit,
                    'expires_at' => $inv->expires_at?->toIso8601String(),
                    'revoked_at' => $inv->revoked_at?->toIso8601String(),
                    'created_at' => $inv->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'invites' => $invites,
        ]);
    }

    /**
     * Revoke an active invitation (Creator only).
     */
    public function revokeInvite(Request $request, string $roomId, string $inviteId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        if (! $room->isCreator($user->user_id)) {
            return response()->json(['success' => false, 'message' => 'Hanya pembuat room yang dapat mencabut undangan.'], 403);
        }

        $invitation = $room->invitations()->where('invite_id', $inviteId)->first();
        if (! $invitation) {
            return response()->json(['success' => false, 'message' => 'Undangan tidak ditemukan.'], 404);
        }

        $invitation->revoke();

        \App\Models\SecurityLog::logEvent('invite_revoked', $room->room_id, $user->user_id, [
            'invite_id' => $invitation->invite_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Undangan berhasil dicabut.',
            'invite_id' => $invitation->invite_id,
        ]);
    }

    /**
     * Validate a scanned QR Code or pasted invitation URL/token before joining.
     */
    public function validateInvite(Request $request): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Identitas diperlukan.'], 401);
        }

        $roomId = strtoupper(trim((string) $request->input('room_id', '')));
        $inviteToken = trim((string) $request->input('invite_token', ''));

        if (! $roomId) {
            return response()->json([
                'success' => false,
                'code' => 'invalid_qr',
                'message' => 'Room ID atau QR Code tidak valid.',
            ], 422);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json([
                'success' => false,
                'code' => 'room_not_found',
                'message' => 'Room tidak ditemukan atau sudah berakhir.',
            ], 404);
        }

        $existing = $room->getParticipant($user->user_id);
        if ($existing && $existing->is_kicked) {
            return response()->json([
                'success' => false,
                'code' => 'kicked',
                'message' => 'Anda tidak memiliki izin bergabung ke room ini.',
            ], 403);
        }

        $alreadyMember = $existing !== null && ! $existing->is_kicked;

        if (! $alreadyMember && $room->isLocked()) {
            return response()->json([
                'success' => false,
                'code' => 'room_locked',
                'message' => 'Room sedang dikunci oleh pembuat room.',
            ], 403);
        }

        if ($inviteToken !== '') {
            $tokenHash = hash('sha256', $inviteToken);
            $invitation = $room->invitations()->where('token_hash', $tokenHash)->first();

            if (! $invitation) {
                return response()->json([
                    'success' => false,
                    'code' => 'invalid_token',
                    'message' => 'Token undangan tidak valid.',
                ], 403);
            }

            if ($invitation->revoked_at !== null) {
                return response()->json([
                    'success' => false,
                    'code' => 'revoked',
                    'message' => 'Undangan ini telah dicabut oleh pemilik room.',
                ], 403);
            }

            if ($invitation->expires_at !== null && $invitation->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'code' => 'expired',
                    'message' => 'Undangan ini telah kedaluwarsa.',
                ], 403);
            }

            if ($invitation->usage_limit !== null && $invitation->usage_count >= $invitation->usage_limit) {
                return response()->json([
                    'success' => false,
                    'code' => 'limit_reached',
                    'message' => 'Batas penggunaan undangan ini sudah habis.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'room' => [
                'room_id' => $room->room_id,
                'room_name' => $room->room_name,
                'is_group' => $room->max_participants > 2,
                'has_pin' => $room->hasPin() && $inviteToken === '',
                'requires_pin' => $room->hasPin() && $inviteToken === '',
                'active_participants_count' => $room->activeParticipants()->count(),
                'max_participants' => $room->max_participants,
                'already_member' => $alreadyMember,
            ],
        ]);
    }

    /**
     * Get current Dynamic PIN (TOTP) status for Room Creator only.
     */
    public function dynamicPin(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room || ! $room->isCreator($user->user_id)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'dynamic_pin' => $this->pinService->getDynamicPinStatus($room),
        ]);
    }

    /**
     * Rotate Dynamic PIN TOTP secret (Creator only).
     */
    public function rotateDynamicPin(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $status = $this->roomService->rotateDynamicPinSecret($user, $room);
        if ($status === null) {
            return response()->json(['success' => false, 'message' => 'Hanya Room Creator yang dapat merotasi Dynamic PIN.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Secret Dynamic PIN (TOTP) berhasil dirotasi.',
            'dynamic_pin' => $status,
        ]);
    }

    /**
     * Archive a conversation for the current user only.
     */
    public function archive(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $participant = RoomParticipant::with('room')
            ->where('room_id', $roomId)
            ->where('user_id', $user->user_id)
            ->where('is_kicked', false)
            ->first();

        if (! $participant) {
            return response()->json(['success' => false, 'message' => 'Anda bukan anggota room ini.'], 403);
        }

        $participant->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        $this->conversationState->notifyUserConversationUpdated($participant->fresh(['room']), 'archived');

        return response()->json([
            'success' => true,
            'room_id' => $roomId,
            'is_archived' => true,
        ]);
    }

    /**
     * Unarchive a conversation for the current user only.
     */
    public function unarchive(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $participant = RoomParticipant::with('room')
            ->where('room_id', $roomId)
            ->where('user_id', $user->user_id)
            ->where('is_kicked', false)
            ->first();

        if (! $participant) {
            return response()->json(['success' => false, 'message' => 'Anda bukan anggota room ini.'], 403);
        }

        $participant->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        $this->conversationState->notifyUserConversationUpdated($participant->fresh(['room']), 'unarchived');

        return response()->json([
            'success' => true,
            'room_id' => $roomId,
            'is_archived' => false,
        ]);
    }
}
