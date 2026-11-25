<?php

namespace App\Http\Middleware;

use App\Models\Room;
use App\Services\UserIdentityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoomMember
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
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('dashboard')->withErrors(['error' => 'Room tidak ditemukan.']);
        }

        $participant = $room->getParticipant($user->user_id);

        if (!$participant || $participant->is_kicked) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda bukan anggota aktif dari room ini.',
                ], 403);
            }
            return redirect()->route('dashboard')->withErrors(['error' => 'Anda bukan anggota dari room ini.']);
        }

        $request->attributes->set('currentRoom', $room);
        $request->attributes->set('currentParticipant', $participant);

        return $next($request);
    }
}
