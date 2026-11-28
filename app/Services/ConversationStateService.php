<?php

namespace App\Services;

use App\Events\UserConversationUpdated;
use App\Models\MessageReceipt;
use App\Models\Room;
use App\Models\RoomMessage;
use App\Models\RoomParticipant;

class ConversationStateService
{
    /**
     * Count unread messages for a specific user in a specific room.
     */
    public function getUnreadCountForUser(string $roomId, string $userId): int
    {
        return MessageReceipt::query()
            ->where('room_id', $roomId)
            ->where('recipient_id', $userId)
            ->whereIn('status', ['pending', 'delivered'])
            ->whereHas('message', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                });
            })
            ->count();
    }

    /**
     * Build sidebar summary metadata for a participant's room.
     */
    public function getRoomSummaryForParticipant(RoomParticipant $participation): array
    {
        $room = $participation->room;
        $unreadCount = $this->getUnreadCountForUser($room->room_id, $participation->user_id);

        $latestMessage = RoomMessage::query()
            ->where('room_id', $room->room_id)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        $preview = null;
        if ($latestMessage) {
            $preview = ($latestMessage->transport === 'broadcast' && !$latestMessage->encrypted)
                ? mb_strimwidth($latestMessage->ciphertext, 0, 48, '...')
                : 'Pesan terenkripsi';
        }

        $lastTime = $latestMessage?->client_timestamp
            ?: ($latestMessage?->created_at?->format('H:i') ?: $participation->last_seen_at?->format('H:i'));

        $lastActivity = $latestMessage?->created_at ?: $participation->last_seen_at ?: $room->updated_at;

        return [
            'room_id' => $room->room_id,
            'room_name' => $room->room_name,
            'is_group' => $room->max_participants > 2,
            'is_archived' => (bool) $participation->is_archived,
            'archived_at' => $participation->archived_at?->toIso8601String(),
            'unread_count' => $unreadCount,
            'last_message_preview' => $preview,
            'last_message_time' => $lastTime,
            'last_activity_at' => $lastActivity?->toIso8601String(),
        ];
    }

    /**
     * Broadcast conversation summary updates to specific participants.
     */
    public function notifyUserConversationUpdated(RoomParticipant $participation, string $action = 'message'): void
    {
        $summary = $this->getRoomSummaryForParticipant($participation);

        broadcast(new UserConversationUpdated(
            userId: $participation->user_id,
            roomId: $summary['room_id'],
            unreadCount: $summary['unread_count'],
            isArchived: $summary['is_archived'],
            action: $action,
            lastMessagePreview: $summary['last_message_preview'],
            lastMessageTime: $summary['last_message_time'],
            lastActivityAt: $summary['last_activity_at']
        ));
    }

    /**
     * Notify all active participants of a room (except optional excluded user).
     */
    public function notifyRoomParticipants(Room $room, string $action = 'message', ?string $excludeUserId = null): void
    {
        $participants = $room->activeParticipants()->with('room')->get();

        foreach ($participants as $participant) {
            if ($excludeUserId && $participant->user_id === $excludeUserId) {
                continue;
            }
            $this->notifyUserConversationUpdated($participant, $action);
        }
    }

    /**
     * Update a participant's room presence state.
     */
    public function updatePresence(RoomParticipant $participant, string $state): void
    {
        $connectionStatus = match ($state) {
            'in_conversation' => 'connected',
            'reconnecting' => 'reconnecting',
            'offline' => 'disconnected',
            default => $participant->connection_status,
        };

        $participant->update([
            'presence_state' => $state,
            'connection_status' => $connectionStatus,
            'last_seen_at' => now(),
        ]);
    }
}
