<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\UserSession;
use App\Services\RoomIdGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomIdGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoomIdGeneratorService $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new RoomIdGeneratorService();
    }

    public function test_generates_valid_default_room_id(): void
    {
        $roomId = $this->generator->generate();

        $this->assertNotEmpty($roomId);
        $this->assertStringStartsWith('SEC-', $roomId);
        $this->assertTrue($this->generator->isValidFormat($roomId));
    }

    public function test_generates_valid_numeric_room_id(): void
    {
        $roomId = $this->generator->generate('numeric');

        $this->assertNotEmpty($roomId);
        $this->assertMatchesRegularExpression('/^SEC-\d{3}-\d{3}$/', $roomId);
        $this->assertTrue($this->generator->isValidFormat($roomId));
    }

    public function test_generates_valid_slug_room_id(): void
    {
        $roomId = $this->generator->generate('slug');

        $this->assertNotEmpty($roomId);
        $this->assertMatchesRegularExpression('/^slk-[a-z0-9]{4}-[a-z0-9]{4}$/', $roomId);
        $this->assertTrue($this->generator->isValidFormat($roomId));
    }

    public function test_avoids_collisions_when_room_id_exists(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_generator_test',
            'display_name' => 'Generator Test User',
        ]);

        $existingId = 'SEC-AAA-BBB';
        Room::create([
            'room_id' => $existingId,
            'room_name' => 'Collision Room',
            'creator_id' => $user->user_id,
        ]);

        $newId = $this->generator->generate();
        $this->assertNotEquals($existingId, $newId);
        $this->assertTrue($this->generator->isValidFormat($newId));
    }
}
