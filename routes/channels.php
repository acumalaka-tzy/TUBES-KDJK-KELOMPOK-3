<?php

use App\Models\Room;
use App\Services\UserIdentityService;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels for SecureLink Signaling & Presence
|--------------------------------------------------------------------------
|
| PRD Section 12.2 Compliance:
| Channel authorization checks session identity and valid room membership.
|
*/

Broadcast::channel('room.{roomId}', function ($user, string $roomId) {
    /** @var UserIdentityService $identityService */
    $identityService = app(UserIdentityService::class);
    $currentUser = $identityService->getCurrentUser(request());

    if (!$currentUser) {
        return false;
    }

    $room = Room::where('room_id', $roomId)->first();
    if (!$room) {
        return false;
    }

    $participant = $room->getParticipant($currentUser->user_id);
    if (!$participant || $participant->is_kicked) {
        return false;
    }

    return [
        'id' => $currentUser->user_id,
        'name' => $participant->display_name,
        'role' => $participant->role,
        'avatar_color' => $currentUser->avatar_color,
        'avatar_icon' => $currentUser->preferences['avatar_icon'] ?? 'initial',
    ];
});
