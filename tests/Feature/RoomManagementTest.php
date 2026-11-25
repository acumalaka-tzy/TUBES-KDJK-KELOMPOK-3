<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    protected UserSession $creator;
    protected UserSession $participant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = UserSession::create([
            'user_id' => 'usr_creator',
            'display_name' => 'Alice Creator',
            'connection_status' => 'online',
        ]);

        $this->participant = UserSession::create([
            'user_id' => 'usr_participant',
            'display_name' => 'Bob Participant',
            'connection_status' => 'online',
        ]);
    }

    public function test_create_room_success(): void
    {
        $response = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson('/api/rooms', [
                'room_name' => 'Cyber Security Hub',
                'pin' => '7788',
                'max_participants' => 5,
                'e2ee_enabled' => true,
                'ephemeral_enabled' => true,
                'default_ephemeral_duration' => 30,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'room' => [
                    'room_name' => 'Cyber Security Hub',
                    'has_pin' => true,
                    'is_creator' => true,
                    'max_participants' => 5,
                ],
            ]);

        $roomId = $response->json('room.room_id');
        $this->assertDatabaseHas('rooms', [
            'room_id' => $roomId,
            'creator_id' => $this->creator->user_id,
        ]);

        // Creator must be automatically enrolled
        $this->assertDatabaseHas('room_participants', [
            'room_id' => $roomId,
            'user_id' => $this->creator->user_id,
            'role' => 'creator',
        ]);
    }

    public function test_join_room_with_correct_and_incorrect_pin(): void
    {
        // Create protected room
        $createRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson('/api/rooms', [
                'room_name' => 'Secret Chamber',
                'pin' => '5566',
            ]);

        $roomId = $createRes->json('room.room_id');

        // Bob tries with wrong PIN
        $wrongRes = $this->withSession(['securelink_user_id' => $this->participant->user_id])
            ->postJson('/api/rooms/join', [
                'room_id' => $roomId,
                'pin' => '0000',
            ]);

        $wrongRes->assertStatus(401)
            ->assertJson(['success' => false]);

        // Bob tries with correct PIN
        $correctRes = $this->withSession(['securelink_user_id' => $this->participant->user_id])
            ->postJson('/api/rooms/join', [
                'room_id' => $roomId,
                'pin' => '5566',
            ]);

        $correctRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'participant' => ['role' => 'participant'],
            ]);

        $this->assertDatabaseHas('room_participants', [
            'room_id' => $roomId,
            'user_id' => $this->participant->user_id,
            'role' => 'participant',
        ]);
    }

    public function test_join_room_blocked_when_locked(): void
    {
        $createRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson('/api/rooms', [
                'room_name' => 'Lockable Room',
            ]);

        $roomId = $createRes->json('room.room_id');

        // Creator locks the room
        $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson("/api/rooms/{$roomId}/lock", ['is_locked' => true])
            ->assertStatus(200)
            ->assertJson(['is_locked' => true]);

        // Bob tries to join locked room
        $joinRes = $this->withSession(['securelink_user_id' => $this->participant->user_id])
            ->postJson('/api/rooms/join', ['room_id' => $roomId]);

        $joinRes->assertStatus(423)
            ->assertJson(['success' => false]);
    }

    public function test_join_room_blocked_when_capacity_reached(): void
    {
        $createRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson('/api/rooms', [
                'room_name' => 'Duo Room',
                'max_participants' => 2, // Creator + 1 member
            ]);

        $roomId = $createRes->json('room.room_id');

        // Bob joins -> now 2 participants (full)
        $this->withSession(['securelink_user_id' => $this->participant->user_id])
            ->postJson('/api/rooms/join', ['room_id' => $roomId])
            ->assertStatus(200);

        // Charlie tries to join -> capacity full
        $charlie = UserSession::create(['user_id' => 'usr_charlie', 'display_name' => 'Charlie']);
        $fullRes = $this->withSession(['securelink_user_id' => $charlie->user_id])
            ->postJson('/api/rooms/join', ['room_id' => $roomId]);

        $fullRes->assertStatus(409)
            ->assertJson(['success' => false]);
    }

    public function test_kick_participant_and_prevent_rejoin(): void
    {
        $createRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson('/api/rooms', ['room_name' => 'Moderation Room']);
        $roomId = $createRes->json('room.room_id');

        // Bob joins
        $this->withSession(['securelink_user_id' => $this->participant->user_id])
            ->postJson('/api/rooms/join', ['room_id' => $roomId]);

        // Bob cannot kick Alice (creator)
        $unauthKick = $this->withSession(['securelink_user_id' => $this->participant->user_id])
            ->postJson("/api/rooms/{$roomId}/kick", ['target_user_id' => $this->creator->user_id]);
        $unauthKick->assertStatus(403);

        // Alice kicks Bob
        $kickRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson("/api/rooms/{$roomId}/kick", ['target_user_id' => $this->participant->user_id]);
        $kickRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // Bob tries to join again -> blocked
        $rejoinRes = $this->withSession(['securelink_user_id' => $this->participant->user_id])
            ->postJson('/api/rooms/join', ['room_id' => $roomId]);
        $rejoinRes->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_update_room_settings_by_creator(): void
    {
        $createRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson('/api/rooms', ['room_name' => 'Original Room Name']);
        $roomId = $createRes->json('room.room_id');

        $updateRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->putJson("/api/rooms/{$roomId}/settings", [
                'room_name' => 'Updated Cryptographic Room',
                'allow_invites' => false,
                'allow_reactions' => true,
                'default_ephemeral_duration' => 60,
            ]);

        $updateRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'room' => [
                    'room_name' => 'Updated Cryptographic Room',
                    'allow_invites' => false,
                    'default_ephemeral_duration' => 60,
                ],
            ]);
    }

    public function test_room_invitation_generation(): void
    {
        $createRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson('/api/rooms', ['room_name' => 'Invited Room']);
        $roomId = $createRes->json('room.room_id');

        $inviteRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson("/api/rooms/{$roomId}/invite", [
                'usage_limit' => 10,
                'expires_in_minutes' => 60,
            ]);

        $inviteRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'invite_url',
                'expires_at',
            ]);
    }
}
