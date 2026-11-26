<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\UserSession;
use App\Services\RoomPinValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityLogAndInfoTest extends TestCase
{
    use RefreshDatabase;

    protected UserSession $creator;

    protected UserSession $member;

    protected UserSession $outsider;

    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $pinService = new RoomPinValidationService;

        $this->creator = UserSession::create([
            'user_id' => 'usr_sec_api_creator',
            'display_name' => 'Alice Sec',
            'connection_status' => 'online',
        ]);

        $this->member = UserSession::create([
            'user_id' => 'usr_sec_api_member',
            'display_name' => 'Bob Sec',
            'connection_status' => 'online',
        ]);

        $this->outsider = UserSession::create([
            'user_id' => 'usr_sec_api_outsider',
            'display_name' => 'Eve Sec',
            'connection_status' => 'online',
        ]);

        $this->room = Room::create([
            'room_id' => 'SEC-INFO-001',
            'room_name' => 'Security Audit Room',
            'creator_id' => $this->creator->user_id,
            'room_pin_hash' => $pinService->hashPin('2468'),
            'dynamic_pin_enabled' => true,
            'dynamic_pin_secret' => $pinService->generateTotpSecret(),
            'e2ee_enabled' => true,
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_sec_creator',
            'room_id' => $this->room->room_id,
            'user_id' => $this->creator->user_id,
            'display_name' => $this->creator->display_name,
            'role' => 'creator',
            'connection_status' => 'connected',
        ]);

        RoomParticipant::create([
            'participant_id' => 'part_sec_member',
            'room_id' => $this->room->room_id,
            'user_id' => $this->member->user_id,
            'display_name' => $this->member->display_name,
            'role' => 'participant',
            'connection_status' => 'connected',
        ]);
    }

    public function test_security_logs_endpoint_requires_room_membership_and_sanitizes_payload(): void
    {
        // Unauthenticated blocked
        $this->getJson("/api/rooms/{$this->room->room_id}/security-logs")
            ->assertStatus(401);

        // Outsider blocked
        $this->withSession(['securelink_user_id' => $this->outsider->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/security-logs", [
                'event_type' => 'dtls_connected',
            ])
            ->assertStatus(403);

        // Invalid event type rejected
        $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/security-logs", [
                'event_type' => 'arbitrary_untrusted_event',
            ])
            ->assertStatus(422);

        // Member logs valid DTLS event with forbidden secret accidentally attached -> sanitized
        $storeRes = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/security-logs", [
                'event_type' => 'dtls_connected',
                'details' => [
                    'dtls_cipher' => 'TLS_AES_256_GCM_SHA384',
                    'shared_secret' => 'must-be-stripped',
                    'plaintext' => 'must-be-stripped',
                ],
            ]);

        $storeRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('log.event_type', 'dtls_connected')
            ->assertJsonPath('log.details.dtls_cipher', 'TLS_AES_256_GCM_SHA384');

        $this->assertArrayNotHasKey('shared_secret', $storeRes->json('log.details'));
        $this->assertArrayNotHasKey('plaintext', $storeRes->json('log.details'));

        $listRes = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->getJson("/api/rooms/{$this->room->room_id}/security-logs");

        $listRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('room_id', $this->room->room_id);
    }

    public function test_security_information_endpoints_separate_dtls_and_e2ee_honestly(): void
    {
        $this->getJson('/api/security/info')->assertStatus(401);

        $sessionInfo = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->getJson('/api/security/info');

        $sessionInfo->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('security.crypto_suite.key_derivation', 'HKDF-SHA-256')
            ->assertJsonPath('security.honest_claims.dtls_distinct_from_e2ee', true);

        // Outsider blocked from room security info
        $this->withSession(['securelink_user_id' => $this->outsider->user_id])
            ->getJson("/api/rooms/{$this->room->room_id}/security-info")
            ->assertStatus(403);

        // Report DTLS connected, but E2EE not yet initialized -> honest_secure_connection MUST remain false
        $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/security-logs", [
                'event_type' => 'dtls_connected',
                'details' => ['dtls_cipher' => 'TLS_AES_128_GCM_SHA256'],
            ])->assertStatus(201);

        $partialInfo = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->getJson("/api/rooms/{$this->room->room_id}/security-info");

        $partialInfo->assertStatus(200)
            ->assertJsonPath('security.transport_and_crypto.dtls.last_reported_verified', true)
            ->assertJsonPath('security.transport_and_crypto.e2ee.last_reported_active', false)
            ->assertJsonPath('security.transport_and_crypto.honest_secure_connection', false);

        // Now report E2EE initialized -> honest_secure_connection becomes true
        $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/security-logs", [
                'event_type' => 'e2ee_initialized',
            ])->assertStatus(201);

        $fullInfo = $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->getJson("/api/rooms/{$this->room->room_id}/security-info");

        $fullInfo->assertStatus(200)
            ->assertJsonPath('security.transport_and_crypto.honest_secure_connection', true)
            ->assertJsonPath('security.room.pin_protection_mode', 'hybrid');

        $rawContent = $fullInfo->getContent();
        $this->assertStringNotContainsString($this->room->dynamic_pin_secret, $rawContent);
        $this->assertStringNotContainsString($this->room->room_pin_hash, $rawContent);
    }

    public function test_dynamic_pin_api_is_restricted_to_room_creator_and_rotates_secret(): void
    {
        // Participant cannot view or rotate Dynamic PIN
        $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->getJson("/api/rooms/{$this->room->room_id}/dynamic-pin")
            ->assertStatus(403);

        $this->withSession(['securelink_user_id' => $this->member->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/dynamic-pin/rotate")
            ->assertStatus(403);

        // Creator can view current 6-digit Dynamic PIN without exposing TOTP secret
        $creatorView = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->getJson("/api/rooms/{$this->room->room_id}/dynamic-pin");

        $creatorView->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('dynamic_pin.enabled', true)
            ->assertJsonPath('dynamic_pin.digits', 6);

        $this->assertStringNotContainsString($this->room->dynamic_pin_secret, $creatorView->getContent());

        $oldSecret = $this->room->dynamic_pin_secret;

        $rotateRes = $this->withSession(['securelink_user_id' => $this->creator->user_id])
            ->postJson("/api/rooms/{$this->room->room_id}/dynamic-pin/rotate");

        $rotateRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('dynamic_pin.enabled', true);

        $this->assertNotSame($oldSecret, $this->room->fresh()->dynamic_pin_secret);
    }
}
