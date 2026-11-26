<?php

namespace App\Http\Controllers;

use App\Events\WebRTCSignalSent;
use App\Http\Requests\SendSignalRequest;
use App\Models\Room;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;

class SignalingController extends Controller
{
    public function __construct(
        protected UserIdentityService $identityService
    ) {}

    /**
     * Relay WebRTC SDP Offer/Answer or ICE Candidate signaling payload
     * between validated participants of the same room via Laravel Reverb.
     */
    public function send(SendSignalRequest $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Identitas tidak valid.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $targetUserId = $request->validated('target_user_id');
        if ($targetUserId === $user->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengirim sinyal WebRTC ke diri sendiri.',
            ], 422);
        }

        $targetParticipant = $room->getParticipant($targetUserId);
        if (!$targetParticipant || $targetParticipant->is_kicked) {
            return response()->json([
                'success' => false,
                'message' => 'Peer tujuan bukan peserta aktif dalam room ini.',
            ], 403);
        }

        $type = $request->validated('type');
        $signal = $request->validated('signal');

        broadcast(new WebRTCSignalSent(
            roomId: $room->room_id,
            fromUserId: $user->user_id,
            toUserId: $targetUserId,
            type: $type,
            signal: $signal
        ))->toOthers();

        return response()->json([
            'success' => true,
            'room_id' => $room->room_id,
            'type' => $type,
            'to' => $targetUserId,
        ]);
    }
}
