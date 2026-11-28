<?php

namespace App\Http\Controllers;

use App\Events\BroadcastMessageSent;
use App\Events\MessageReceiptUpdated;
use App\Models\MessageReceipt;
use App\Models\Room;
use App\Models\RoomMessage;
use App\Models\RoomParticipant;
use App\Services\ConversationStateService;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationMessageController extends Controller
{
    public function __construct(
        protected UserIdentityService $identityService,
        protected ConversationStateService $conversationState
    ) {}

    /**
     * Store a message envelope (ciphertext + metadata) for pending/offline delivery
     * and create per-recipient receipt rows. Idempotent by message_id.
     */
    public function store(Request $request, string $roomId): JsonResponse
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
            'ciphertext' => ['required', 'string', 'max:20000'],
            'iv' => ['nullable', 'string', 'max:64'],
            'encrypted' => ['nullable', 'boolean'],
            'algorithm' => ['nullable', 'string', 'max:32'],
            'transport' => ['nullable', 'in:p2p,broadcast'],
            'ephemeral_duration' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'reply_to' => ['nullable', 'array'],
            'client_timestamp' => ['nullable', 'string', 'max:20'],
        ]);

        $ephemeralDuration = (int) ($validated['ephemeral_duration'] ?? 0);
        $expiresAt = $ephemeralDuration > 0 ? now()->addSeconds($ephemeralDuration) : null;
        $transport = $validated['transport'] ?? 'p2p';
        $isEncrypted = array_key_exists('encrypted', $validated) ? (bool) $validated['encrypted'] : true;

        $existing = RoomMessage::where('message_id', $validated['message_id'])->first();
        if (!$existing) {
            $roomMessage = RoomMessage::create([
                'message_id' => $validated['message_id'],
                'room_id' => $room->room_id,
                'sender_id' => $user->user_id,
                'sender_name' => $user->display_name,
                'sender_avatar_color' => $user->avatar_color ?? '#008069',
                'sender_avatar_icon' => $user->preferences['avatar_icon'] ?? 'initial',
                'transport' => $transport,
                'ciphertext' => $validated['ciphertext'],
                'iv' => $validated['iv'] ?? null,
                'encrypted' => $isEncrypted,
                'algorithm' => $validated['algorithm'] ?? null,
                'ephemeral_duration' => $ephemeralDuration,
                'reply_to' => $validated['reply_to'] ?? null,
                'client_timestamp' => $validated['client_timestamp'] ?? now()->format('H:i'),
                'expires_at' => $expiresAt,
            ]);

            $recipients = $room->activeParticipants()
                ->where('user_id', '!=', $user->user_id)
                ->get();

            foreach ($recipients as $recipient) {
                MessageReceipt::firstOrCreate(
                    [
                        'message_id' => $roomMessage->message_id,
                        'recipient_id' => $recipient->user_id,
                    ],
                    [
                        'room_id' => $room->room_id,
                        'status' => 'pending',
                    ]
                );
            }

            if ($transport === 'broadcast') {
                broadcast(new BroadcastMessageSent(
                    roomId: $room->room_id,
                    messageId: $roomMessage->message_id,
                    senderId: $user->user_id,
                    senderName: $user->display_name,
                    senderAvatarColor: $user->avatar_color ?? '#008069',
                    senderAvatarIcon: $user->preferences['avatar_icon'] ?? 'initial',
                    message: $roomMessage->ciphertext,
                    ephemeralDuration: $ephemeralDuration,
                    replyTo: $roomMessage->reply_to,
                    timestamp: $roomMessage->client_timestamp ?? now()->format('H:i')
                ))->toOthers();
            }

            // Update sidebar unread count & preview in real time for all recipients (including archived)
            $this->conversationState->notifyRoomParticipants($room, 'message', $user->user_id);
        } else {
            $roomMessage = $existing;
        }

        $anyRecipientInRoom = $room->activeParticipants()
            ->where('user_id', '!=', $user->user_id)
            ->where('presence_state', 'in_conversation')
            ->exists();

        $pendingCount = MessageReceipt::where('message_id', $roomMessage->message_id)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'success' => true,
            'message_id' => $roomMessage->message_id,
            'status' => $anyRecipientInRoom ? 'sent' : 'queued',
            'pending_recipients' => $pendingCount,
        ]);
    }

    /**
     * Fetch pending (or unread) messages for the authenticated participant when entering a room.
     */
    public function pending(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'messages' => []], 401);
        }

        $receipts = MessageReceipt::query()
            ->where('room_id', $roomId)
            ->where('recipient_id', $user->user_id)
            ->whereIn('status', ['pending', 'delivered'])
            ->with('message')
            ->orderBy('id')
            ->get();

        $messages = [];
        foreach ($receipts as $receipt) {
            $msg = $receipt->message;
            if (!$msg) {
                continue;
            }
            if ($msg->expires_at && $msg->expires_at->isPast()) {
                continue;
            }

            $messages[] = [
                'id' => $msg->message_id,
                'senderId' => $msg->sender_id,
                'senderName' => $msg->sender_name,
                'senderAvatarColor' => $msg->sender_avatar_color,
                'senderAvatarIcon' => $msg->sender_avatar_icon,
                'transport' => $msg->transport,
                'ciphertext' => $msg->ciphertext,
                'iv' => $msg->iv,
                'encrypted' => $msg->encrypted,
                'algorithm' => $msg->algorithm,
                'ephemeralDuration' => $msg->ephemeral_duration,
                'replyTo' => $msg->reply_to,
                'timestamp' => $msg->client_timestamp ?: $msg->created_at?->format('H:i'),
                'receipt_status' => $receipt->status,
            ];
        }

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Update current participant's presence state for a specific conversation.
     */
    public function updatePresence(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $validated = $request->validate([
            'state' => ['required', 'in:in_conversation,online,offline,reconnecting'],
        ]);

        $participant = RoomParticipant::where('room_id', $roomId)
            ->where('user_id', $user->user_id)
            ->where('is_kicked', false)
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Bukan anggota room.'], 403);
        }

        $this->conversationState->updatePresence($participant, $validated['state']);

        return response()->json([
            'success' => true,
            'room_id' => $roomId,
            'presence_state' => $participant->presence_state,
        ]);
    }
}
