<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\RoomParticipant;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoomAndUsers(): array
    {
        $creator = UserSession::create([
            'user_id' => 'usr_qr_creator',
            'display_name' => 'QR Creator',
            'connection_status' => 'online',
        ]);

        $guest = UserSession::create([
            'user_id' => 'usr_qr_guest',
            'display_name' => 'QR Guest',
            'connection_status' => 'online',
        ]);

        $room = Room::create([
            'room_id' => 'SEC-QR-001',
            'room_name' => 'QR Lab Group',
            'creator_id' => $creator->user_id,
            'max_participants' => 10,
            'allow_invites' => true,
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_qr_creator',
            'room_id' => $room->room_id,
            'user_id' => $creator->user_id,
            'display_name' => $creator->display_name,
            'role' => 'creator',
            'connection_status' => 'connected',
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        return [$creator, $guest, $room];
    }

    public function test_create_list_validate_and_revoke_qr_invitation_lifecycle(): void
    {
        [$creator, $guest, $room] = $this->seedRoomAndUsers();

        // 1. Creator generates tokenized invitation
        $createRes = $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/invite", [
                'expires_in_minutes' => 60,
            ]);

        $createRes->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['invite_id', 'token', 'invite_url', 'expires_at']);

        $inviteId = $createRes->json('invite_id');
        $rawToken = $createRes->json('token');

        // 2. Guest validates scanned QR invitation
        $this->withHeaders(['X-User-Id' => $guest->user_id])
            ->postJson('/api/rooms/validate-invite', [
                'room_id' => $room->room_id,
                'invite_token' => $rawToken,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'room' => [
                    'room_id' => $room->room_id,
                    'room_name' => 'QR Lab Group',
                    'is_group' => true,
                    'already_member' => false,
                ],
            ]);

        // 3. List invitations shows active invite
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->getJson("/api/rooms/{$room->room_id}/invites")
            ->assertOk()
            ->assertJsonFragment(['invite_id' => $inviteId, 'status' => 'active']);

        // 4. Guest cannot revoke invitation
        $this->withHeaders(['X-User-Id' => $guest->user_id])
            ->postJson("/api/rooms/{$room->room_id}/invites/{$inviteId}/revoke")
            ->assertStatus(403);

        // 5. Creator revokes invitation
        $this->withHeaders(['X-User-Id' => $creator->user_id])
            ->postJson("/api/rooms/{$room->room_id}/invites/{$inviteId}/revoke")
            ->assertOk()
            ->assertJson(['success' => true, 'invite_id' => $inviteId]);

        // 6. Revoked token fails validation
        $this->withHeaders(['X-User-Id' => $guest->user_id])
            ->postJson('/api/rooms/validate-invite', [
                'room_id' => $room->room_id,
                'invite_token' => $rawToken,
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'code' => 'revoked']);
    }

    public function test_expired_and_invalid_qr_tokens_are_rejected(): void
    {
        [$creator, $guest, $room] = $this->seedRoomAndUsers();

        $expiredRawToken = 'expired_tok_123456';
        RoomInvitation::create([
            'invite_id' => 'inv_expired01',
            'room_id' => $room->room_id,
            'token_hash' => hash('sha256', $expiredRawToken),
            'created_by' => $creator->user_id,
            'expires_at' => now()->subMinute(),
        ]);

        // Expired token
        $this->withHeaders(['X-User-Id' => $guest->user_id])
            ->postJson('/api/rooms/validate-invite', [
                'room_id' => $room->room_id,
                'invite_token' => $expiredRawToken,
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'code' => 'expired']);

        // Invalid token
        $this->withHeaders(['X-User-Id' => $guest->user_id])
            ->postJson('/api/rooms/validate-invite', [
                'room_id' => $room->room_id,
                'invite_token' => 'wrong_token',
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'code' => 'invalid_token']);
    }
}
