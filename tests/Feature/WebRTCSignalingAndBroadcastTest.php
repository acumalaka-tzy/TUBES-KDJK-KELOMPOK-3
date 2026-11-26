<?php

namespace Tests\Feature;

use App\Events\BroadcastMessageSent;
use App\Events\WebRTCSignalSent;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\SecurityLog;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WebRTCSignalingAndBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private function createUserAndRoom(): array
    {
        $creator = UserSession::create([
            'user_id' => 'usr_creator001',
            'display_name' => 'Creator Peer',
            'avatar_color' => '#008069',
            'connection_status' => 'online',
            'preferences' => ['avatar_icon' => 'guardian'],
            'last_seen_at' => now(),
        ]);

        $peer = UserSession::create([
            'user_id' => 'usr_member002',
            'display_name' => 'Member Peer',
            'avatar_color' => '#25D366',
            'connection_status' => 'online',
            'preferences' => ['avatar_icon' => 'ninja'],
            'last_seen_at' => now(),
        ]);

        $outsider = UserSession::create([
            'user_id' => 'usr_outsider03',
            'display_name' => 'Outsider',
            'avatar_color' => '#EA0038',
            'connection_status' => 'online',
            'last_seen_at' => now(),
        ]);

        $room = Room::create([
            'room_id' => 'SLK-TEST01',
            'room_name' => 'WebRTC Test Room',
            'creator_id' => $creator->user_id,
            'max_participants' => 10,
            'is_locked' => false,
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_creator001',
            'room_id' => $room->room_id,
            'user_id' => $creator->user_id,
            'display_name' => $creator->display_name,
            'role' => 'creator',
            'connection_status' => 'connected',
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_member002',
            'room_id' => $room->room_id,
            'user_id' => $peer->user_id,
            'display_name' => $peer->display_name,
            'role' => 'participant',
            'connection_status' => 'connected',
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        return [$creator, $peer, $outsider, $room];
    }

    public function test_broadcasting_auth_authorizes_valid_room_member_and_rejects_outsider(): void
    {
        config(['broadcasting.default' => 'reverb']);
        config(['broadcasting.connections.reverb.key' => 'securelink_key']);
        config(['broadcasting.connections.reverb.secret' => 'securelink_secret']);
        config(['broadcasting.connections.reverb.app_id' => 'securelink_app']);
        \Illuminate\Support\Facades\Broadcast::purge();
        require base_path('routes/channels.php');

        [$creator, , $outsider, $room] = $this->createUserAndRoom();

        // Unauthenticated
        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'presence-room.' . $room->room_id,
        ])->assertStatus(403);

        // Outsider
        $this->withHeaders(['X-User-Id' => $outsider->user_id])
            ->postJson('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => 'presence-room.' . $room->room_id,
            ])->assertStatus(403);

        // Valid member
        $response = $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => 'presence-room.' . $room->room_id,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['auth', 'channel_data']);

        $channelData = json_decode($response->json('channel_data'), true);
        $this->assertSame($creator->user_id, $channelData['user_id']);
        $this->assertSame('Creator Peer', $channelData['user_info']['name']);
        $this->assertSame('guardian', $channelData['user_info']['avatar_icon']);
    }

    public function test_webrtc_signaling_relays_sdp_and_ice_candidates_to_valid_peer(): void
    {
        Event::fake([WebRTCSignalSent::class]);
        [$creator, $peer, $outsider, $room] = $this->createUserAndRoom();

        // Offer from creator to peer
        $response = $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/signal", [
                'target_user_id' => $peer->user_id,
                'type' => 'offer',
                'signal' => ['type' => 'offer', 'sdp' => 'v=0...fake-sdp'],
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'room_id' => $room->room_id,
                'type' => 'offer',
                'to' => $peer->user_id,
            ]);

        Event::assertDispatched(WebRTCSignalSent::class, function (WebRTCSignalSent $event) use ($room, $creator, $peer) {
            return $event->roomId === $room->room_id
                && $event->fromUserId === $creator->user_id
                && $event->toUserId === $peer->user_id
                && $event->type === 'offer'
                && $event->signal['sdp'] === 'v=0...fake-sdp';
        });

        // ICE candidate from peer to creator
        $this->withHeaders(['X-User-Id' => $peer->user_id])
            ->postJson("/api/rooms/{$room->room_id}/signal", [
                'target_user_id' => $creator->user_id,
                'type' => 'candidate',
                'signal' => ['type' => 'candidate', 'candidate' => ['candidate' => 'candidate:1 1 UDP 2122252543 10.0.0.1 54321 typ host']],
            ])
            ->assertOk();

        // Self-signaling rejected
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/signal", [
                'target_user_id' => $creator->user_id,
                'type' => 'offer',
                'signal' => ['sdp' => 'self'],
            ])
            ->assertStatus(422);

        // Signaling to outsider rejected
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/signal", [
                'target_user_id' => $outsider->user_id,
                'type' => 'offer',
                'signal' => ['sdp' => 'outsider'],
            ])
            ->assertStatus(403);
    }

    public function test_ice_servers_endpoint_returns_stun_and_optional_turn_config(): void
    {
        [$creator] = $this->createUserAndRoom();

        // Unauthenticated
        $this->getJson('/api/webrtc/ice-servers')->assertStatus(401);

        // Default STUN-only config
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->getJson('/api/webrtc/ice-servers')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'hasTurn' => false,
                'iceTransportPolicy' => 'all',
            ])
            ->assertJsonStructure(['iceServers']);

        // With TURN configured
        config([
            'webrtc.turn.urls' => ['turn:turn.securelink.test:3478?transport=udp'],
            'webrtc.turn.username' => 'slk_user',
            'webrtc.turn.credential' => 'slk_secret',
            'webrtc.ice_transport_policy' => 'relay',
        ]);

        $response = $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->getJson('/api/webrtc/ice-servers');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'hasTurn' => true,
                'iceTransportPolicy' => 'relay',
                'turnCount' => 1,
            ]);

        $iceServers = $response->json('iceServers');
        $this->assertCount(2, $iceServers);
        $this->assertSame('slk_user', $iceServers[1]['username']);
        $this->assertSame('slk_secret', $iceServers[1]['credential']);
    }

    public function test_broadcast_comparison_mode_relays_message_via_reverb_without_storing_plaintext(): void
    {
        Event::fake([BroadcastMessageSent::class]);
        [$creator, , $outsider, $room] = $this->createUserAndRoom();

        $secretMessage = 'Pesan pengujian Broadcast Comparison Mode';

        // Outsider cannot broadcast
        $this->withHeaders(['X-User-Id' => $outsider->user_id])
            ->postJson("/api/rooms/{$room->room_id}/broadcast-message", [
                'message' => $secretMessage,
            ])
            ->assertStatus(403);

        // Valid member broadcasts
        $response = $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/broadcast-message", [
                'message' => $secretMessage,
                'ephemeral_duration' => 30,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'mode' => 'broadcast',
                'payload_bytes' => strlen($secretMessage),
            ]);

        Event::assertDispatched(BroadcastMessageSent::class, function (BroadcastMessageSent $event) use ($room, $creator, $secretMessage) {
            $payload = $event->broadcastWith();
            return $event->roomId === $room->room_id
                && $event->senderId === $creator->user_id
                && $payload['text'] === $secretMessage
                && $payload['transport'] === 'broadcast'
                && $payload['isE2EE'] === false;
        });

        $log = SecurityLog::where('event_type', 'broadcast_comparison_relayed')->first();
        $this->assertNotNull($log);
        $this->assertSame('server_broadcast', $log->details['mode']);
        $this->assertArrayNotHasKey('message', $log->details);
        $this->assertArrayNotHasKey('plain_text', $log->details);
    }
}
