<?php

namespace App\Services;

use App\Models\Room;
use App\Models\SecurityLog;
use App\Models\UserSession;

class SecurityInformationService
{
    public function __construct(
        protected SecurityLogService $securityLogService
    ) {}

    /**
     * Build global Security Information summary for the authenticated session (PRD Section 3.7 & Appendix B).
     *
     * @return array<string, mixed>
     */
    public function getSessionSecurityInfo(UserSession $user): array
    {
        $broadcastDriver = config('broadcasting.default', 'reverb');
        $reverbScheme = config('broadcasting.connections.reverb.options.scheme', env('REVERB_SCHEME', 'http'));

        return [
            'identity' => [
                'user_id' => $user->user_id,
                'display_name' => $user->display_name,
                'connection_status' => $user->connection_status,
                'session_model' => 'ephemeral_session_identity',
            ],
            'crypto_suite' => [
                'key_exchange' => 'ECDH P-256 (Web Crypto API)',
                'key_derivation' => 'HKDF-SHA-256',
                'cipher' => 'AES-GCM (256-bit)',
                'auth_tag_bits' => 128,
                'nonce_iv' => '96-bit unique (32-bit random prefix + 64-bit monotonic counter)',
                'transport_security' => 'WebRTC DTLS (verifikasi di sisi klien)',
                'pin_hashing' => config('hashing.driver', 'bcrypt'),
                'dynamic_pin' => 'RFC 6238 TOTP (30s, 6-digit, encrypted secret at rest)',
            ],
            'signaling' => [
                'driver' => $broadcastDriver,
                'scheme' => $reverbScheme,
                'wss_enforced_on_https' => true,
                'zero_chat_retention' => true,
            ],
            'vpn' => [
                'provider' => 'Tailscale',
                'browser_verifiable' => false,
                'guide_url' => route('tailscale.guide'),
            ],
            'honest_claims' => [
                'dtls_distinct_from_e2ee' => true,
                'direct_vs_relay_based_on_ice' => true,
                'ephemeral_client_memory_only' => true,
            ],
            'recent_events_count' => $this->securityLogService->getUserLogs($user, 30)->count(),
        ];
    }

    /**
     * Build room-specific Security Information summary (PRD Section 3.7 & Appendix B).
     * Never exposes PIN hashes, TOTP secrets, private keys, or message contents.
     *
     * @return array<string, mixed>
     */
    public function getRoomSecurityInfo(Room $room, UserSession $user): array
    {
        $pinMode = 'none';
        if ($room->dynamic_pin_enabled && ! empty($room->room_pin_hash)) {
            $pinMode = 'hybrid';
        } elseif ($room->dynamic_pin_enabled) {
            $pinMode = 'dynamic_totp';
        } elseif (! empty($room->room_pin_hash)) {
            $pinMode = 'static';
        }

        $latestDtlsEvent = SecurityLog::query()
            ->where('room_id', $room->room_id)
            ->whereIn('event_type', ['dtls_connected', 'dtls_failed'])
            ->latest('id')
            ->first();

        $latestE2eeEvent = SecurityLog::query()
            ->where('room_id', $room->room_id)
            ->whereIn('event_type', ['e2ee_initialized', 'e2ee_failed'])
            ->latest('id')
            ->first();

        $latestWebRtcEvent = SecurityLog::query()
            ->where('room_id', $room->room_id)
            ->whereIn('event_type', ['webrtc_connected', 'webrtc_failed', 'connection_reconnected'])
            ->latest('id')
            ->first();

        $dtlsVerifiedFromLogs = $latestDtlsEvent?->event_type === 'dtls_connected';
        $e2eeInitializedFromLogs = $latestE2eeEvent?->event_type === 'e2ee_initialized';

        $recentLogs = $this->securityLogService->getRoomLogs($room, 10);

        return [
            'room' => [
                'room_id' => $room->room_id,
                'room_name' => $room->room_name,
                'is_creator' => $room->isCreator($user->user_id),
                'is_locked' => $room->isLocked(),
                'has_pin' => $room->hasPin(),
                'pin_protection_mode' => $pinMode,
                'pin_expired' => $room->isPinExpired(),
                'e2ee_enabled' => $room->e2ee_enabled,
                'ephemeral_enabled' => $room->ephemeral_enabled,
                'default_ephemeral_duration' => $room->default_ephemeral_duration,
                'active_participants_count' => $room->activeParticipants()->count(),
            ],
            'transport_and_crypto' => [
                'dtls' => [
                    'required' => true,
                    'separate_from_e2ee' => true,
                    'last_reported_state' => $latestDtlsEvent?->event_type ?? 'unverified',
                    'last_reported_verified' => $dtlsVerifiedFromLogs,
                    'last_reported_cipher' => $latestDtlsEvent?->details['dtls_cipher'] ?? null,
                ],
                'e2ee' => [
                    'enabled' => $room->e2ee_enabled,
                    'algorithm' => 'ECDH P-256 + HKDF-SHA256 + AES-GCM-256',
                    'auth_tag_bits' => 128,
                    'last_reported_state' => $latestE2eeEvent?->event_type ?? ($room->e2ee_enabled ? 'pending_handshake' : 'disabled'),
                    'last_reported_active' => $room->e2ee_enabled && $e2eeInitializedFromLogs,
                ],
                'webrtc' => [
                    'last_reported_state' => $latestWebRtcEvent?->event_type ?? 'pending',
                    'connection_type' => $latestWebRtcEvent?->details['connection_type'] ?? 'unknown',
                ],
                'honest_secure_connection' => $dtlsVerifiedFromLogs && (! $room->e2ee_enabled || $e2eeInitializedFromLogs),
            ],
            'signaling' => [
                'driver' => config('broadcasting.default', 'reverb'),
                'zero_chat_retention' => true,
            ],
            'vpn' => [
                'provider' => 'Tailscale',
                'browser_verifiable' => false,
                'guide_url' => route('tailscale.guide'),
            ],
            'recent_security_events_count' => SecurityLog::query()->where('room_id', $room->room_id)->count(),
            'latest_events' => $recentLogs->take(5)->values()->all(),
        ];
    }
}
