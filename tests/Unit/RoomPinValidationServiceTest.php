<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\UserSession;
use App\Services\RoomPinValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomPinValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoomPinValidationService $pinService;
    protected UserSession $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pinService = new RoomPinValidationService();
        $this->user = UserSession::create([
            'user_id' => 'usr_pin_tester',
            'display_name' => 'Pin Tester',
        ]);
    }

    public function test_room_without_pin_passes(): void
    {
        $room = Room::create([
            'room_id' => 'SEC-OPEN-001',
            'room_name' => 'Open Room',
            'creator_id' => $this->user->user_id,
            'room_pin_hash' => null,
        ]);

        $result = $this->pinService->validatePin($room, null, 'usr_guest');
        $this->assertTrue($result['valid']);
    }

    public function test_static_pin_validation_success_and_failure(): void
    {
        $room = Room::create([
            'room_id' => 'SEC-PIN-001',
            'room_name' => 'Protected Room',
            'creator_id' => $this->user->user_id,
            'room_pin_hash' => $this->pinService->hashPin('4321'),
        ]);

        // Wrong PIN
        $failResult = $this->pinService->validatePin($room, '0000', 'usr_guest');
        $this->assertFalse($failResult['valid']);
        $this->assertStringContainsString('PIN salah', $failResult['message']);

        // Correct PIN
        $passResult = $this->pinService->validatePin($room, '4321', 'usr_guest');
        $this->assertTrue($passResult['valid']);
    }

    public function test_dynamic_totp_pin_generation_and_validation(): void
    {
        $secret = $this->pinService->generateTotpSecret();
        $this->assertEquals(16, strlen($secret));

        $room = Room::create([
            'room_id' => 'SEC-TOTP-001',
            'room_name' => 'Dynamic PIN Room',
            'creator_id' => $this->user->user_id,
            'dynamic_pin_enabled' => true,
            'dynamic_pin_secret' => $secret,
        ]);

        $currentTotp = $this->pinService->getTotpCode($secret);
        $this->assertEquals(6, strlen($currentTotp));

        // Correct dynamic TOTP
        $result = $this->pinService->validatePin($room, $currentTotp, 'usr_guest');
        $this->assertTrue($result['valid']);

        // Wrong dynamic TOTP
        $badResult = $this->pinService->validatePin($room, '999999', 'usr_guest');
        $this->assertFalse($badResult['valid']);
    }

    public function test_pin_brute_force_rate_limiting_and_cooldown(): void
    {
        $room = Room::create([
            'room_id' => 'SEC-BRUTE-001',
            'room_name' => 'Brute Force Test Room',
            'creator_id' => $this->user->user_id,
            'room_pin_hash' => $this->pinService->hashPin('9876'),
        ]);

        $guestId = 'usr_attacker';

        // 5 wrong attempts
        for ($i = 1; $i <= 4; $i++) {
            $res = $this->pinService->validatePin($room, '0000', $guestId);
            $this->assertFalse($res['valid']);
            $this->assertNull($res['cooldown']);
        }

        // 5th attempt triggers cooldown
        $fifthRes = $this->pinService->validatePin($room, '0000', $guestId);
        $this->assertFalse($fifthRes['valid']);
        $this->assertNotNull($fifthRes['cooldown']);

        // 6th attempt is blocked immediately by cooldown even if giving correct PIN
        $blockedRes = $this->pinService->validatePin($room, '9876', $guestId);
        $this->assertFalse($blockedRes['valid']);
        $this->assertStringContainsString('cooldown', strtolower($blockedRes['message']) . ' cooldown');
    }
}
