<?php

namespace Tests\Feature;

use App\Events\RoomParticipantUpdated;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RoomAdministrationAndChatFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoom(): array
    {
        $creator = UserSession::create([
            'user_id' => 'usr_adm_creator',
            'display_name' => 'Admin Host',
            'connection_status' => 'online',
        ]);

        $member = UserSession::create([
            'user_id' => 'usr_adm_member',
            'display_name' => 'Regular Member',
            'connection_status' => 'online',
        ]);

        $outsider = UserSession::create([
            'user_id' => 'usr_adm_outsider',
            'display_name' => 'Outsider User',
            'connection_status' => 'online',
        ]);

        $room = Room::create([
            'room_id' => 'SEC-ADM-001',
            'room_name' => 'Admin Test Group',
            'creator_id' => $creator->user_id,
            'max_participants' => 10,
            'is_locked' => false,
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_adm_creator',
            'room_id' => $room->room_id,
            'user_id' => $creator->user_id,
            'display_name' => $creator->display_name,
            'role' => 'creator',
            'connection_status' => 'connected',
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_adm_member',
            'room_id' => $room->room_id,
            'user_id' => $member->user_id,
            'display_name' => $member->display_name,
            'role' => 'participant',
            'connection_status' => 'connected',
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        return [$creator, $member, $outsider, $room];
    }

    public function test_creator_can_kick_participant_and_broadcasts_realtime_event(): void
    {
        Event::fake([RoomParticipantUpdated::class]);
        [$creator, $member, , $room] = $this->seedRoom();

        // Non-creator cannot kick
        $this->withHeaders(['X-User-Id' => $member->user_id])
            ->postJson("/api/rooms/{$room->room_id}/kick", [
                'target_user_id' => $creator->user_id,
            ])
            ->assertStatus(403);

        // Creator kicks member
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/kick", [
                'target_user_id' => $member->user_id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        Event::assertDispatched(RoomParticipantUpdated::class, function (RoomParticipantUpdated $event) use ($room, $member) {
            return $event->roomId === $room->room_id
                && $event->userId === $member->user_id
                && $event->action === 'kicked';
        });

        // Kicked member cannot rejoin
        $this->withHeaders(['X-User-Id' => $member->user_id])
            ->postJson('/api/rooms/join', ['room_id' => $room->room_id])
            ->assertStatus(403);
    }

    public function test_creator_can_lock_and_unlock_room_with_realtime_broadcast(): void
    {
        Event::fake([RoomParticipantUpdated::class]);
        [$creator, $member, $outsider, $room] = $this->seedRoom();

        // Non-creator cannot lock
        $this->withHeaders(['X-User-Id' => $member->user_id])
            ->postJson("/api/rooms/{$room->room_id}/lock")
            ->assertStatus(403);

        // Creator locks room
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/lock")
            ->assertOk()
            ->assertJson(['success' => true, 'is_locked' => true]);

        Event::assertDispatched(RoomParticipantUpdated::class, function (RoomParticipantUpdated $event) use ($room) {
            return $event->roomId === $room->room_id && $event->action === 'locked';
        });

        // Outsider cannot join locked room
        $this->withHeaders(['X-User-Id' => $outsider->user_id])
            ->postJson('/api/rooms/join', ['room_id' => $room->room_id])
            ->assertStatus(423);

        // Creator unlocks room
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/lock")
            ->assertOk()
            ->assertJson(['success' => true, 'is_locked' => false]);

        Event::assertDispatched(RoomParticipantUpdated::class, function (RoomParticipantUpdated $event) use ($room) {
            return $event->roomId === $room->room_id && $event->action === 'unlocked';
        });
    }

    public function test_chat_view_contains_reply_reaction_copy_delete_ephemeral_and_logout_confirm(): void
    {
        [$creator, , , $room] = $this->seedRoom();

        $response = $this->withSession(['securelink_user_id' => $creator->user_id])
            ->get("/rooms/{$room->room_id}");

        $response->assertOk()
            ->assertSee('reactToMessage')
            ->assertSee('handleReplyFromBubble')
            ->assertSee('copyMessageText')
            ->assertSee('deleteMessage')
            ->assertSee('ephemeral_duration')
            ->assertSee('Apakah Anda yakin ingin keluar dari sesi ini?');
    }
}
