<?php

namespace App\Http\Middleware;

use App\Models\Room;
use App\Services\UserIdentityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoomCreator
{
    public function __construct(
        protected UserIdentityService $identityService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->attributes->get('currentUser') ?? $this->identityService->getCurrentUser($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $roomId = $request->route('roomId') ?? $request->input('room_id');
        $room = Room::where('room_id', $roomId)->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan.',
            ], 404);
        }

        if (!$room->isCreator($user->user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Room Creator yang memiliki otoritas untuk tindakan ini.',
            ], 403);
        }

        $request->attributes->set('currentRoom', $room);

        return $next($request);
    }
}
