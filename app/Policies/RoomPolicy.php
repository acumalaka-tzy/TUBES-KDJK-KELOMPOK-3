<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\UserSession;

class RoomPolicy
{
    /**
     * Determine whether the user can view the room details/chat.
     */
    public function view(UserSession $user, Room $room): bool
    {
        if ($room->isCreator($user->user_id)) {
            return true;
        }

        $participant = $room->getParticipant($user->user_id);
        return $participant !== null && !$participant->is_kicked;
    }

    /**
     * Determine whether the user can update settings of the room.
     */
    public function update(UserSession $user, Room $room): bool
    {
        return $room->isCreator($user->user_id);
    }

    /**
     * Determine whether the user can lock or unlock the room.
     */
    public function lock(UserSession $user, Room $room): bool
    {
        return $room->isCreator($user->user_id);
    }

    /**
     * Determine whether the user can kick participants from the room.
     */
    public function kick(UserSession $user, Room $room): bool
    {
        return $room->isCreator($user->user_id);
    }

    /**
     * Determine whether the user can create invitations for the room.
     */
    public function invite(UserSession $user, Room $room): bool
    {
        if ($room->isCreator($user->user_id)) {
            return true;
        }

        if (!$room->allow_invites) {
            return false;
        }

        return $room->hasParticipant($user->user_id);
    }

    /**
     * Determine whether the user can delete or terminate the room.
     */
    public function delete(UserSession $user, Room $room): bool
    {
        return $room->isCreator($user->user_id);
    }
}
