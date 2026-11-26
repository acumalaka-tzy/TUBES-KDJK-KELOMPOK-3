<?php

namespace App\Http\Controllers;

use App\Events\BroadcastMessageSent;
use App\Http\Requests\BroadcastMessageRequest;
use App\Models\Room;
use App\Models\SecurityLog;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class BroadcastComparisonController extends Controller
{
    public function __construct(
        protected UserIdentityService $identityService
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
        $messageId = 'bcast_' . Str::random(12);
        $timestamp = now()->format('H:i');

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
}
