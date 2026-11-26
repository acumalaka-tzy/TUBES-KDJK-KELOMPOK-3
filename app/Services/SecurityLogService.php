<?php

namespace App\Services;

use App\Models\Room;
use App\Models\SecurityLog;
use App\Models\UserSession;
use Illuminate\Support\Collection;

class SecurityLogService
{
    /**
     * Client-reported WebRTC, DTLS, E2EE, and delivery events permitted per PRD Section 3.8.
     *
     * @var array<int, string>
     */
    public const ALLOWED_CLIENT_EVENTS = [
        'peer_connection_started',
        'sdp_offer_exchanged',
        'sdp_answer_exchanged',
        'ice_candidate_exchanged',
        'webrtc_connected',
        'webrtc_failed',
        'dtls_connected',
        'dtls_failed',
        'e2ee_initialized',
        'e2ee_failed',
        'message_delivery_acknowledged',
        'connection_reconnected',
    ];

    /**
     * Record a sanitized client-side security event for a room member.
     *
     * @param  array<string, mixed>  $details
     */
    public function recordClientEvent(
        Room $room,
        UserSession $user,
        string $eventType,
        array $details = []
    ): SecurityLog {
        return SecurityLog::logEvent(
            eventType: $eventType,
            roomId: $room->room_id,
            userId: $user->user_id,
            details: $details
        );
    }

    /**
     * Retrieve recent sanitized security logs for a room.
     *
     * @return Collection<int, array{id: int, event_type: string, user_id: ?string, details: array<string, mixed>, created_at: ?string}>
     */
    public function getRoomLogs(Room $room, int $limit = 50): Collection
    {
        $boundedLimit = max(1, min($limit, 100));

        return SecurityLog::query()
            ->where('room_id', $room->room_id)
            ->latest('id')
            ->take($boundedLimit)
            ->get()
            ->map(function (SecurityLog $log): array {
                return [
                    'id' => $log->id,
                    'event_type' => $log->event_type,
                    'user_id' => $log->user_id,
                    'details' => SecurityLog::sanitizeDetails($log->details ?? []),
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            });
    }

    /**
     * Retrieve recent sanitized security logs associated with a user session.
     *
     * @return Collection<int, array{id: int, room_id: ?string, event_type: string, details: array<string, mixed>, created_at: ?string}>
     */
    public function getUserLogs(UserSession $user, int $limit = 30): Collection
    {
        $boundedLimit = max(1, min($limit, 100));

        return SecurityLog::query()
            ->where('user_id', $user->user_id)
            ->latest('id')
            ->take($boundedLimit)
            ->get()
            ->map(function (SecurityLog $log): array {
                return [
                    'id' => $log->id,
                    'room_id' => $log->room_id,
                    'event_type' => $log->event_type,
                    'details' => SecurityLog::sanitizeDetails($log->details ?? []),
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            });
    }
}
