<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\SecurityInformationService;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityInformationController extends Controller
{
    public function __construct(
        protected SecurityInformationService $securityInfoService,
        protected UserIdentityService $identityService
    ) {}

    /**
     * Return general session security information (PRD Section 3.7).
     */
    public function sessionInfo(Request $request): JsonResponse
    {
        $user = $request->attributes->get('currentUser') ?? $this->identityService->getCurrentUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        return response()->json([
            'success' => true,
            'security' => $this->securityInfoService->getSessionSecurityInfo($user),
        ]);
    }

    /**
     * Return room-scoped verified security information (room members only).
     */
    public function roomInfo(Request $request, string $roomId): JsonResponse
    {
        $user = $request->attributes->get('currentUser') ?? $this->identityService->getCurrentUser($request);
        $room = $request->attributes->get('currentRoom') ?? Room::where('room_id', $roomId)->first();

        if (! $user || ! $room) {
            return response()->json(['success' => false, 'message' => 'Sesi atau room tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'security' => $this->securityInfoService->getRoomSecurityInfo($room, $user),
        ]);
    }
}
