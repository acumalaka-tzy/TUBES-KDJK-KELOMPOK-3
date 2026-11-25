<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SecureLink');
        $response->assertSee('WebRTC DataChannel');
    }

    public function test_setup_page_renders_successfully(): void
    {
        $response = $this->get('/setup');
        $response->assertStatus(200);
        $response->assertSee('Identitas Sesi');
        $response->assertSee('Display Name');
    }

    public function test_setup_submit_creates_user_and_redirects(): void
    {
        $response = $this->post('/setup', [
            'display_name' => 'Cyber Knight',
            'avatar_color' => '#2563eb',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('user_sessions', [
            'display_name' => 'Cyber Knight',
        ]);
    }

    public function test_dashboard_redirects_if_unauthenticated(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/setup');
    }

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_dash_test',
            'display_name' => 'Dash User',
            'connection_status' => 'online',
        ]);

        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Halo, Dash User!');
        $response->assertSee('Buat Room Baru');
    }

    public function test_create_room_page_and_submission(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_create_test',
            'display_name' => 'Room Creator User',
            'connection_status' => 'online',
        ]);

        // Form page
        $this->withSession(['securelink_user_id' => $user->user_id])
            ->get('/rooms/create')
            ->assertStatus(200)
            ->assertSee('Buat Chat Room Baru');

        // Form submission
        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->post('/rooms', [
                'room_name' => 'Blade View Test Lab',
                'pin' => '1234',
                'max_participants' => 8,
            ]);

        $room = Room::where('room_name', 'Blade View Test Lab')->first();
        $this->assertNotNull($room);
        $response->assertRedirect("/rooms/{$room->room_id}");
    }

    public function test_join_room_page_and_chat_view(): void
    {
        $creator = UserSession::create([
            'user_id' => 'usr_room_host',
            'display_name' => 'Host User',
            'connection_status' => 'online',
        ]);

        $room = Room::create([
            'room_id' => 'SEC-VIEW-001',
            'room_name' => 'View Test Room',
            'creator_id' => $creator->user_id,
        ]);

        // Join page renders
        $this->withSession(['securelink_user_id' => $creator->user_id])
            ->get('/rooms/join')
            ->assertStatus(200)
            ->assertSee('Gabung ke Room');

        // Accessing chat room as creator
        $response = $this->withSession(['securelink_user_id' => $creator->user_id])
            ->get("/rooms/{$room->room_id}");

        $response->assertStatus(200);
        $response->assertSee('View Test Room');
        $response->assertSee('SEC-VIEW-001');
    }

    public function test_settings_page_renders(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_settings_test',
            'display_name' => 'Settings User',
            'connection_status' => 'online',
        ]);

        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan & Diagnostik');
        $response->assertSee('WebRTC DataChannel DTLS');
    }

    public function test_demo_page_renders(): void
    {
        $response = $this->get('/demo');
        $response->assertStatus(200);
        $response->assertSee('Perbandingan Model Komunikasi');
        $response->assertSee('Server Broadcast Mode');
        $response->assertSee('P2P WebRTC Mode');
    }

    public function test_tailscale_guide_page_renders(): void
    {
        $response = $this->get('/tailscale-guide');
        $response->assertStatus(200);
        $response->assertSee('Panduan Integrasi VPN Tailscale');
        $response->assertSee('100.x.y.z');
    }

    public function test_chat_view_renders_mobile_sidebar_toggle(): void
    {
        $creator = UserSession::create([
            'user_id' => 'usr_mobile_test',
            'display_name' => 'Mobile Host',
            'connection_status' => 'online',
        ]);

        $room = Room::create([
            'room_id' => 'SEC-MOBI-001',
            'room_name' => 'Mobile Test Room',
            'creator_id' => $creator->user_id,
        ]);

        $response = $this->withSession(['securelink_user_id' => $creator->user_id])
            ->get("/rooms/{$room->room_id}");

        $response->assertStatus(200);
        $response->assertSee('toggleChatSidebar');
        $response->assertSee('chat-sidebar');
    }

    public function test_mobile_nav_drawer_includes_logout_for_authenticated_user(): void
    {
        $user = UserSession::create([
            'user_id' => 'usr_nav_test',
            'display_name' => 'Nav User',
            'connection_status' => 'online',
        ]);

        $response = $this->withSession(['securelink_user_id' => $user->user_id])
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Keluar Sesi (Logout)');
    }
}
