<?php

namespace App\Services;

class WebRTCConfigService
{
    /**
     * Build the WebRTC RTCConfiguration ICE payload for clients.
     */
    public function getClientConfig(): array
    {
        $iceServers = [];

        $stunUrls = config('webrtc.stun_urls', []);
        if (!empty($stunUrls)) {
            $iceServers[] = [
                'urls' => count($stunUrls) === 1 ? $stunUrls[0] : array_values($stunUrls),
            ];
        }

        $turnUrls = config('webrtc.turn.urls', []);
        $turnUsername = config('webrtc.turn.username');
        $turnCredential = config('webrtc.turn.credential');
        $hasTurn = !empty($turnUrls);

        if ($hasTurn) {
            $turnEntry = [
                'urls' => count($turnUrls) === 1 ? $turnUrls[0] : array_values($turnUrls),
            ];

            if (!empty($turnUsername)) {
                $turnEntry['username'] = $turnUsername;
            }
            if (!empty($turnCredential)) {
                $turnEntry['credential'] = $turnCredential;
            }

            $iceServers[] = $turnEntry;
        }

        $policy = config('webrtc.ice_transport_policy', 'all');
        if (!in_array($policy, ['all', 'relay'], true)) {
            $policy = 'all';
        }

        return [
            'iceServers' => $iceServers,
            'iceTransportPolicy' => $policy,
            'iceCandidatePoolSize' => max(0, (int) config('webrtc.ice_candidate_pool_size', 2)),
            'hasTurn' => $hasTurn,
            'stunCount' => count($stunUrls),
            'turnCount' => count($turnUrls),
        ];
    }
}
