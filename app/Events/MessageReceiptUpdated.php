<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceiptUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomId,
        public string $messageId,
        public string $userId,
        public string $status
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.' . $this->roomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.receipt';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'user_id' => $this->userId,
            'status' => $this->status,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
