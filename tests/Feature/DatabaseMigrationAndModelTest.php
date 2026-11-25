<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\RoomParticipant;
use App\Models\SecurityLog;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseMigrationAndModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_session_creation_and_helper_methods(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_test123',
            'display_name' => 'Alice Tester',
            'session_id' => 'sess_abc',
            'avatar_color' => '#10b981',
            'connection_status' => 'online',
            'preferences' => ['theme' => 'dark'],
        ]);

        $this->assertDatabaseHas('user_sessions', [
            'user_id' => 'usr_test123',
            'display_name' => 'Alice Tester',
        ]);

        $this->assertEquals('dark', $user->preferences['theme']);

        $user->setOffline();
        $this->assertEquals('offline', $user->fresh()->connection_status);

        $user->touchLastSeen();
        $this->assertEquals('online', $user->fresh()->connection_status);
        $this->assertNotNull($user->fresh()->last_seen_at);
    }

    public function test_room_and_participant_relationships_and_methods(): void
    {
        $creator = UserSession::create([
            'user_id' => 'usr_creator1',
            'display_name' => 'Bob Creator',
        ]);

        $room = Room::create([
            'room_id' => 'SEC-TEST-9999',
            'room_name' => 'Cryptographic Test Lab',
            'creator_id' => $creator->user_id,
            'room_pin_hash' => bcrypt('123456'),
            'max_participants' => 3,
            'e2ee_enabled' => true,
            'ephemeral_enabled' => true,
            'default_ephemeral_duration' => 60,
        ]);

        $this->assertTrue($room->hasPin());
        $this->assertFalse($room->isLocked());
        $this->assertFalse($room->isFull());
        $this->assertTrue($room->isCreator($creator->user_id));
        $this->assertFalse($room->isCreator('usr_other'));

        // Add creator as participant
        $participant1 = RoomParticipant::create([
            'participant_id' => 'part_1',
            'room_id' => $room->room_id,
            'user_id' => $creator->user_id,
            'display_name' => $creator->display_name,
            'role' => 'creator',
            'connection_status' => 'connected',
        ]);

        $this->assertTrue($participant1->isCreator());
        $this->assertTrue($room->hasParticipant($creator->user_id));
        $this->assertEquals(1, $room->activeParticipants()->count());
        $this->assertEquals($creator->id, $room->creator->id);
    }

    public function test_room_invitation_lifecycle(): void
    {
        $creator = UserSession::create([
            'user_id' => 'usr_inviter',
            'display_name' => 'Charlie Inviter',
        ]);

        $room = Room::create([
            'room_id' => 'SEC-INV-1111',
            'room_name' => 'Invitation Lab',
            'creator_id' => $creator->user_id,
        ]);

        $invitation = RoomInvitation::create([
            'invite_id' => 'inv_test_token',
            'room_id' => $room->room_id,
            'token_hash' => hash('sha256', 'secret-token-123'),
            'created_by' => $creator->user_id,
            'usage_limit' => 2,
            'usage_count' => 0,
            'expires_at' => now()->addHour(),
        ]);

        $this->assertTrue($invitation->isValid());

        $invitation->recordUsage();
        $this->assertEquals(1, $invitation->fresh()->usage_count);
        $this->assertTrue($invitation->isValid());

        $invitation->recordUsage();
        $this->assertEquals(2, $invitation->fresh()->usage_count);
        $this->assertFalse($invitation->isValid());

        $invitation->revoke();
        $this->assertFalse($invitation->isValid());
    }

    public function test_security_log_filters_forbidden_secrets(): void
    {
        $log = SecurityLog::logEvent(
            eventType: 'user_joined',
            roomId: 'SEC-ROOM-001',
            userId: 'usr_safe',
            details: [
                'ip' => '100.64.0.1',
                'pin' => '999999', // Must be filtered out
                'private_key' => 'supersecret', // Must be filtered out
                'mode' => 'p2p',
            ]
        );

        $this->assertArrayHasKey('ip', $log->details);
        $this->assertArrayHasKey('mode', $log->details);
        $this->assertArrayNotHasKey('pin', $log->details);
        $this->assertArrayNotHasKey('private_key', $log->details);
    }
}
