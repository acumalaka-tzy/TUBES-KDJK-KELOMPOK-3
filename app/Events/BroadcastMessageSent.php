<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomId,
        public string $messageId,
        public string $senderId,
        public string $senderName,
        public string $senderAvatarColor,
        public string $senderAvatarIcon,
        public string $message,
        public int $ephemeralDuration = 0,
        public ?array $replyTo = null,
        public string $timestamp = ''
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.' . $this->roomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'broadcast.message';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->messageId,
            'senderId' => $this->senderId,
            'senderName' => $this->senderName,
            'senderAvatarColor' => $this->senderAvatarColor,
            'senderAvatarIcon' => $this->senderAvatarIcon,
            'text' => $this->message,
            'ephemeralDuration' => $this->ephemeralDuration,
            'replyTo' => $this->replyTo,
            'timestamp' => $this->timestamp,
            'transport' => 'broadcast',
            'isE2EE' => false,
            'serverRelayedAt' => now()->toIso8601String(),
        ];
    }
}
