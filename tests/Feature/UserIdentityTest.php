<?php

namespace Tests\Feature;

use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_me_unauthenticated_returns_false(): void
    {
        $response = $this->getJson('/api/identity/me');

        $response->assertStatus(200)
            ->assertJson([
                'authenticated' => false,
                'user' => null,
            ]);
    }

    public function test_setup_identity_validates_display_name_length(): void
    {
        $response = $this->postJson('/api/identity/setup', [
            'display_name' => 'A', // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['display_name']);
    }

    public function test_setup_identity_success(): void
    {
        $response = $this->postJson('/api/identity/setup', [
            'display_name' => 'Ksatria P2P',
            'avatar_color' => '#2563eb',
            'preferences' => ['theme' => 'dark'],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'display_name' => 'Ksatria P2P',
                    'avatar_color' => '#2563eb',
                ],
            ]);

        $userId = $response->json('user.user_id');
        $this->assertStringStartsWith('usr_', $userId);
        $this->assertDatabaseHas('user_sessions', [
            'user_id' => $userId,
            'display_name' => 'Ksatria P2P',
            'connection_status' => 'online',
        ]);
    }

    public function test_get_me_authenticated_returns_user(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_active123',
            'display_name' => 'Budi Security',
            'avatar_color' => '#059669',
            'connection_status' => 'online',
        ]);

        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->getJson('/api/identity/me');

        $response->assertStatus(200)
            ->assertJson([
                'authenticated' => true,
                'user' => [
                    'user_id' => 'usr_active123',
                    'display_name' => 'Budi Security',
                ],
            ]);
    }

    public function test_authenticate_via_x_user_id_header(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_header_test',
            'display_name' => 'Header User',
        ]);

        $response = $this->withHeader('X-User-Id', $user->user_id)
            ->getJson('/api/identity/me');

        $response->assertStatus(200)
            ->assertJson([
                'authenticated' => true,
                'user' => [
                    'user_id' => 'usr_header_test',
                ],
            ]);
    }

    public function test_update_identity(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_update_me',
            'display_name' => 'Old Name',
        ]);

        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->putJson('/api/identity', [
                'display_name' => 'New Updated Name',
                'avatar_color' => '#d97706',
                'preferences' => ['ephemeral_duration' => 30],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'display_name' => 'New Updated Name',
                    'avatar_color' => '#d97706',
                ],
            ]);

        $this->assertDatabaseHas('user_sessions', [
            'user_id' => 'usr_update_me',
            'display_name' => 'New Updated Name',
        ]);
    }

    public function test_heartbeat_updates_user(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_heartbeat',
            'display_name' => 'Heartbeat User',
        ]);

        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->postJson('/api/identity/heartbeat');

        $response->assertStatus(200)
            ->assertJson([
                'online' => true,
                'user_id' => 'usr_heartbeat',
            ]);
    }

    public function test_logout_sets_user_offline_and_clears_session(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_logout_test',
            'display_name' => 'Logout User',
            'connection_status' => 'online',
        ]);

        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->postJson('/api/identity/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('offline', $user->fresh()->connection_status);
        $this->assertNull(session('securelink_user_id'));
    }

    public function test_setup_and_update_with_avatar_icon(): void
    {
        $response = $this->postJson('/api/identity/setup', [
            'display_name' => 'Cyber Ninja',
            'avatar_color' => '#008069',
            'avatar_icon' => 'ninja',
        ]);

        $response->assertStatus(200);
        $userId = $response->json('user.user_id');

        $user = UserSession::where('user_id', $userId)->first();
        $this->assertEquals('ninja', $user->avatar_icon);
        $this->assertStringContainsString('<svg', $user->avatar_display);
        $this->assertStringContainsString('<svg', $user->avatar_html);

        // Update avatar icon to shield (legacy alias for guardian)
        $updateResponse = $this->withSession(['securelink_user_id' => $userId])
            ->putJson('/api/identity', [
                'avatar_icon' => 'shield',
            ]);

        $updateResponse->assertStatus(200);
        $user->refresh();
        $this->assertEquals('shield', $user->avatar_icon);
        $this->assertStringContainsString('<svg', $user->avatar_display);
        $this->assertStringContainsString('<svg', $user->avatar_html);
    }
}
