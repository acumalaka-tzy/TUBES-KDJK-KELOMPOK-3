<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $userId,
        public string $roomId,
        public int $unreadCount,
        public bool $isArchived,
        public string $action = 'message',
        public ?string $lastMessagePreview = null,
        public ?string $lastMessageTime = null,
        public ?string $lastActivityAt = null,
        public ?array $lastMessageEnvelope = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'unread_count' => $this->unreadCount,
            'is_archived' => $this->isArchived,
            'action' => $this->action,
            'last_message_preview' => $this->lastMessagePreview,
            'last_message_time' => $this->lastMessageTime,
            'last_activity_at' => $this->lastActivityAt ?? now()->toIso8601String(),
            'last_message_envelope' => $this->lastMessageEnvelope,
        ];
    }
}
