<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSecurityLogRequest;
use App\Models\Room;
use App\Services\SecurityLogService;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityLogController extends Controller
{
    public function __construct(
        protected SecurityLogService $securityLogService,
        protected UserIdentityService $identityService
    ) {}

    /**
     * List recent sanitized security logs for a room (room members only).
     */
    public function index(Request $request, string $roomId): JsonResponse
    {
        $room = $request->attributes->get('currentRoom') ?? Room::where('room_id', $roomId)->first();
        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $limit = (int) $request->query('limit', 50);

        return response()->json([
            'success' => true,
            'room_id' => $room->room_id,
            'logs' => $this->securityLogService->getRoomLogs($room, $limit),
        ]);
    }

    /**
     * Record a client-side WebRTC/DTLS/E2EE security event (room members only).
     */
    public function store(StoreSecurityLogRequest $request, string $roomId): JsonResponse
    {
        $user = $request->attributes->get('currentUser') ?? $this->identityService->getCurrentUser($request);
        $room = $request->attributes->get('currentRoom') ?? Room::where('room_id', $roomId)->first();

        if (! $user || ! $room) {
            return response()->json(['success' => false, 'message' => 'Sesi atau room tidak valid.'], 404);
        }

        $log = $this->securityLogService->recordClientEvent(
            room: $room,
            user: $user,
            eventType: $request->validated('event_type'),
            details: $request->validated('details') ?? []
        );

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'event_type' => $log->event_type,
                'room_id' => $log->room_id,
                'user_id' => $log->user_id,
                'details' => $log->details,
                'created_at' => $log->created_at?->toIso8601String(),
            ],
        ], 201);
    }
}
