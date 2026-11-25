<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\UserSession;
use App\Policies\RoomPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected UserSession $creator;
    protected UserSession $member;
    protected UserSession $outsider;
    protected Room $room;
    protected RoomPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new RoomPolicy();

        $this->creator = UserSession::create([
            'user_id' => 'usr_policy_creator',
            'display_name' => 'Creator User',
            'connection_status' => 'online',
        ]);

        $this->member = UserSession::create([
            'user_id' => 'usr_policy_member',
            'display_name' => 'Member User',
            'connection_status' => 'online',
        ]);

        $this->outsider = UserSession::create([
            'user_id' => 'usr_policy_outsider',
            'display_name' => 'Outsider User',
            'connection_status' => 'online',
        ]);

        $this->room = Room::create([
            'room_id' => 'SEC-AUTH-001',
            'room_name' => 'Authorization Matrix Lab',
            'creator_id' => $this->creator->user_id,
            'allow_invites' => true,
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_creator',
            'room_id' => $this->room->room_id,
            'user_id' => $this->creator->user_id,
            'display_name' => $this->creator->display_name,
            'role' => 'creator',
            'connection_status' => 'connected',
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_member',
            'room_id' => $this->room->room_id,
            'user_id' => $this->member->user_id,
            'display_name' => $this->member->display_name,
            'role' => 'participant',
            'connection_status' => 'connected',
        ]);
    }

    public function test_ensure_user_identity_blocks_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/rooms');
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'authenticated' => false,
            ]);
    }

    public function test_ensure_room_member_allows_member_and_blocks_outsider(): void
    {
        // Member can invite
        $memberResponse = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/invite");

        $memberResponse->assertStatus(200);

        // Outsider is blocked from invite
        $outsiderResponse = $this->withSession(['securelink_user_id' => $this->outsider->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/invite");

        $outsiderResponse->assertStatus(403);
    }

    public function test_ensure_room_creator_allows_creator_and_blocks_normal_member(): void
    {
        // Member tries to lock room -> blocked (403)
        $memberLock = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/lock", ['is_locked' => true]);

        $memberLock->assertStatus(403)
            ->assertJson(['success' => false]);

        // Creator can lock room -> success (200)
        $creatorLock = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/lock", ['is_locked' => true]);

        $creatorLock->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_kicked_participant_is_blocked_by_member_middleware(): void
    {
        // Kick the member
        $this->room->getParticipant($this->member->user_id)->update(['is_kicked' => true]);

        $response = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/invite");

        $response->assertStatus(403);
    }

    public function test_room_policy_authorization_methods(): void
    {
        // Creator has full permissions
        $this->assertTrue($this->policy->view($this->creator, $this->room));
        $this->assertTrue($this->policy->update($this->creator, $this->room));
        $this->assertTrue($this->policy->lock($this->creator, $this->room));
        $this->assertTrue($this->policy->kick($this->creator, $this->room));
        $this->assertTrue($this->policy->invite($this->creator, $this->room));
        $this->assertTrue($this->policy->delete($this->creator, $this->room));

        // Member can view and invite (if allowed), but cannot update, lock, kick, delete
        $this->assertTrue($this->policy->view($this->member, $this->room));
        $this->assertTrue($this->policy->invite($this->member, $this->room));
        $this->assertFalse($this->policy->update($this->member, $this->room));
        $this->assertFalse($this->policy->lock($this->member, $this->room));
        $this->assertFalse($this->policy->kick($this->member, $this->room));
        $this->assertFalse($this->policy->delete($this->member, $this->room));

        // Outsider cannot view or invite
        $this->assertFalse($this->policy->view($this->outsider, $this->room));
        $this->assertFalse($this->policy->invite($this->outsider, $this->room));
    }
}
