<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\SecurityLog;
use App\Models\UserSession;
use App\Services\RoomPinValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DynamicPinAndSecretProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected RoomPinValidationService $pinService;

    protected UserSession $creator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pinService = new RoomPinValidationService;
        $this->creator = UserSession::create([
            'user_id' => 'usr_sec_creator',
            'display_name' => 'Security Host',
        ]);
    }

    public function test_room_model_hides_pin_hash_and_totp_secret_from_serialization(): void
    {
        $room = Room::create([
            'room_id' => 'SEC-HIDE-001',
            'room_name' => 'Hidden Secrets Room',
            'creator_id' => $this->creator->user_id,
            'room_pin_hash' => $this->pinService->hashPin('4321'),
            'dynamic_pin_enabled' => true,
            'dynamic_pin_secret' => 'JBSWY3DPEHPK3PXP',
        ]);

        $serialized = $room->toArray();

        $this->assertArrayNotHasKey('room_pin_hash', $serialized);
        $this->assertArrayNotHasKey('dynamic_pin_secret', $serialized);
        $this->assertStringNotContainsString('JBSWY3DPEHPK3PXP', $room->toJson());
    }

    public function test_dynamic_pin_secret_is_encrypted_at_rest_in_database(): void
    {
        $plainSecret = 'JBSWY3DPEHPK3PXP';

        $room = Room::create([
            'room_id' => 'SEC-ENC-001',
            'room_name' => 'Encrypted Secret Room',
            'creator_id' => $this->creator->user_id,
            'dynamic_pin_enabled' => true,
            'dynamic_pin_secret' => $plainSecret,
        ]);

        $rawDbValue = DB::table('rooms')
            ->where('room_id', 'SEC-ENC-001')
            ->value('dynamic_pin_secret');

        $this->assertNotSame($plainSecret, $rawDbValue);
        $this->assertSame($plainSecret, $room->fresh()->dynamic_pin_secret);
    }

    public function test_static_pin_zero_string_and_expired_pin_handling(): void
    {
        $roomZero = Room::create([
            'room_id' => 'SEC-ZERO-001',
            'room_name' => 'Zero PIN Room',
            'creator_id' => $this->creator->user_id,
            'room_pin_hash' => $this->pinService->hashPin('0000'),
        ]);

        $validZero = $this->pinService->validatePin($roomZero, '0000', 'usr_guest');
        $this->assertTrue($validZero['valid']);

        $expiredRoom = Room::create([
            'room_id' => 'SEC-EXP-001',
            'room_name' => 'Expired PIN Room',
            'creator_id' => $this->creator->user_id,
            'room_pin_hash' => $this->pinService->hashPin('1234'),
            'pin_expires_at' => now()->subMinute(),
        ]);

        $expiredResult = $this->pinService->validatePin($expiredRoom, '1234', 'usr_guest');
        $this->assertFalse($expiredResult['valid']);
        $this->assertStringContainsString('kedaluwarsa', $expiredResult['message']);

        $this->assertDatabaseHas('security_logs', [
            'room_id' => 'SEC-EXP-001',
            'event_type' => 'pin_expired_rejected',
        ]);
    }

    public function test_dynamic_pin_status_exposes_only_current_code_and_remaining_seconds(): void
    {
        $secret = $this->pinService->generateTotpSecret();
        $room = Room::create([
            'room_id' => 'SEC-STATUS-001',
            'room_name' => 'TOTP Status Room',
            'creator_id' => $this->creator->user_id,
            'dynamic_pin_enabled' => true,
            'dynamic_pin_secret' => $secret,
        ]);

        $status = $this->pinService->getDynamicPinStatus($room);

        $this->assertTrue($status['enabled']);
        $this->assertSame(6, strlen($status['current_pin']));
        $this->assertGreaterThanOrEqual(1, $status['expires_in_seconds']);
        $this->assertLessThanOrEqual(30, $status['expires_in_seconds']);
        $this->assertArrayNotHasKey('secret', $status);
        $this->assertArrayNotHasKey('dynamic_pin_secret', $status);
    }

    public function test_security_log_recursively_strips_nested_forbidden_secrets(): void
    {
        $log = SecurityLog::logEvent(
            eventType: 'e2ee_initialized',
            roomId: 'SEC-LOG-001',
            userId: $this->creator->user_id,
            details: [
                'algorithm' => 'ECDH-P256+HKDF-SHA256+AES-GCM-256',
                'nested' => [
                    'dtls_cipher' => 'TLS_AES_256_GCM_SHA384',
                    'shared_secret' => 'deadbeef',
                    'private_key' => 'pem-secret',
                    'pin' => '9999',
                    'plaintext' => 'halo rahasia',
                ],
            ]
        );

        $this->assertSame('ECDH-P256+HKDF-SHA256+AES-GCM-256', $log->details['algorithm']);
        $this->assertSame('TLS_AES_256_GCM_SHA384', $log->details['nested']['dtls_cipher']);
        $this->assertArrayNotHasKey('shared_secret', $log->details['nested']);
        $this->assertArrayNotHasKey('private_key', $log->details['nested']);
        $this->assertArrayNotHasKey('pin', $log->details['nested']);
        $this->assertArrayNotHasKey('plaintext', $log->details['nested']);
    }
}
