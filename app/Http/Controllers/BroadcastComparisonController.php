<?php

namespace App\Http\Controllers;

use App\Events\BroadcastMessageSent;
use App\Events\MessageReceiptUpdated;
use App\Http\Requests\BroadcastMessageRequest;
use App\Models\MessageReceipt;
use App\Models\Room;
use App\Models\RoomMessage;
use App\Models\RoomParticipant;
use App\Models\SecurityLog;
use App\Services\ConversationStateService;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BroadcastComparisonController extends Controller
{
    public function __construct(
        protected UserIdentityService $identityService,
        protected ConversationStateService $conversationState
    ) {}

    /**
     * Relay a message through the central Laravel Reverb server
     * for Broadcast Comparison Mode (demonstrating server-centric vs P2P).
     */
    public function send(BroadcastMessageRequest $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Identitas tidak valid.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $message = $request->validated('message');
        $ephemeralDuration = (int) ($request->validated('ephemeral_duration') ?? 0);
        $replyTo = $request->validated('reply_to');
        $messageId = $request->input('message_id') ?: ('bcast_' . Str::random(12));
        $timestamp = now()->format('H:i');

        $existing = RoomMessage::where('message_id', $messageId)->first();
        if (!$existing) {
            RoomMessage::create([
                'message_id' => $messageId,
                'room_id' => $room->room_id,
                'sender_id' => $user->user_id,
                'sender_name' => $user->display_name,
                'sender_avatar_color' => $user->avatar_color ?? '#008069',
                'sender_avatar_icon' => $user->preferences['avatar_icon'] ?? 'initial',
                'transport' => 'broadcast',
                'ciphertext' => $message,
                'iv' => null,
                'encrypted' => false,
                'algorithm' => null,
                'ephemeral_duration' => $ephemeralDuration,
                'reply_to' => $replyTo,
                'client_timestamp' => $timestamp,
                'expires_at' => $ephemeralDuration > 0 ? now()->addSeconds($ephemeralDuration) : null,
            ]);

            $recipients = $room->activeParticipants()
                ->where('user_id', '!=', $user->user_id)
                ->get();

            foreach ($recipients as $recipient) {
                MessageReceipt::firstOrCreate(
                    [
                        'message_id' => $messageId,
                        'recipient_id' => $recipient->user_id,
                    ],
                    [
                        'room_id' => $room->room_id,
                        'status' => 'pending',
                    ]
                );
            }
        }

        broadcast(new BroadcastMessageSent(
            roomId: $room->room_id,
            messageId: $messageId,
            senderId: $user->user_id,
            senderName: $user->display_name,
            senderAvatarColor: $user->avatar_color ?? '#008069',
            senderAvatarIcon: $user->preferences['avatar_icon'] ?? 'initial',
            message: $message,
            ephemeralDuration: $ephemeralDuration,
            replyTo: $replyTo,
            timestamp: $timestamp
        ))->toOthers();

        $this->conversationState->notifyRoomParticipants($room, 'message', $user->user_id);

        SecurityLog::logEvent('broadcast_comparison_relayed', $room->room_id, $user->user_id, [
            'mode' => 'server_broadcast',
            'payload_bytes' => strlen($message),
            'message_id' => $messageId,
        ]);

        return response()->json([
            'success' => true,
            'mode' => 'broadcast',
            'id' => $messageId,
            'payload_bytes' => strlen($message),
            'server_relayed_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast a real-time delivery or read receipt via Laravel Reverb.
     */
    public function receipt(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Identitas tidak valid.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'message_id' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:delivered,read'],
        ]);

        $nextStatus = $validated['status'];
        $receipt = MessageReceipt::where('message_id', $validated['message_id'])
            ->where('recipient_id', $user->user_id)
            ->first();

        if ($receipt) {
            $rank = ['pending' => 1, 'delivered' => 2, 'read' => 3];
            $currentRank = $rank[$receipt->status] ?? 1;
            $nextRank = $rank[$nextStatus] ?? 1;

            if ($nextRank > $currentRank) {
                $updates = ['status' => $nextStatus];
                if ($nextStatus === 'delivered' && !$receipt->delivered_at) {
                    $updates['delivered_at'] = now();
                }
                if ($nextStatus === 'read') {
                    if (!$receipt->delivered_at) {
                        $updates['delivered_at'] = now();
                    }
                    $updates['read_at'] = now();
                }
                $receipt->update($updates);
            }
        }

        $unreadCount = null;
        if ($nextStatus === 'read') {
            $participant = RoomParticipant::where('room_id', $room->room_id)
                ->where('user_id', $user->user_id)
                ->where('is_kicked', false)
                ->first();

            if ($participant) {
                $participant->update(['last_read_message_id' => $validated['message_id']]);
                $this->conversationState->notifyUserConversationUpdated($participant, 'read');
            }
            $unreadCount = $this->conversationState->getUnreadCountForUser($room->room_id, $user->user_id);
        }

        broadcast(new MessageReceiptUpdated(
            roomId: $room->room_id,
            messageId: $validated['message_id'],
            userId: $user->user_id,
            status: $nextStatus
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message_id' => $validated['message_id'],
            'status' => $nextStatus,
            'unread_count' => $unreadCount,
        ]);
    }
}
