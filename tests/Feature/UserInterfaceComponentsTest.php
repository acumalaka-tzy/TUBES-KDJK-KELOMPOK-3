<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UserInterfaceComponentsTest extends TestCase
{
    public function test_avatar_component_renders_with_initial_and_color(): void
    {
        $html = Blade::render('<x-avatar name="Alice Crypt" color="#315CE8" size="md" :online="true" />');

        $this->assertStringContainsString('A', $html);
        $this->assertStringContainsString('#315CE8', $html);
        $this->assertStringContainsString('bg-emerald-500', $html);
    }

    public function test_connection_badge_component_renders_direct_and_relay_states(): void
    {
        $directHtml = Blade::render('<x-connection-badge type="direct" :dtls="true" :e2ee="true" />');
        $this->assertStringContainsString('P2P Direct', $directHtml);
        $this->assertStringContainsString('DTLS', $directHtml);
        $this->assertStringContainsString('E2EE', $directHtml);

        $relayHtml = Blade::render('<x-connection-badge type="relay" />');
        $this->assertStringContainsString('TURN Relay', $relayHtml);
    }

    public function test_message_bubble_component_renders_reply_and_delivery_status(): void
    {
        $bubbleHtml = Blade::render(
            '<x-message-bubble :isSelf="true" senderName="Alice" timestamp="14:30" status="delivered" :ephemeralDuration="10" replyAuthor="Bob" replySnippet="Hello Alice">
                Halo, pesan diterima!
            </x-message-bubble>'
        );

        $this->assertStringContainsString('Halo, pesan diterima!', $bubbleHtml);
        $this->assertStringContainsString('Delivered', $bubbleHtml);
        $this->assertStringContainsString('Ephemeral (10s)', $bubbleHtml);
        $this->assertStringContainsString('Bob', $bubbleHtml);
        $this->assertStringContainsString('Hello Alice', $bubbleHtml);
    }

    public function test_toast_alert_component_renders_and_is_dismissible(): void
    {
        $toastHtml = Blade::render(
            '<x-toast-alert type="success" title="Berhasil">
                Room berhasil dikonfigurasi.
            </x-toast-alert>'
        );

        $this->assertStringContainsString('Berhasil', $toastHtml);
        $this->assertStringContainsString('Room berhasil dikonfigurasi.', $toastHtml);
        $this->assertStringContainsString('&times;', $toastHtml);
    }
}
