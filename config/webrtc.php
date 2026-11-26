<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WebRTC ICE Servers (STUN & TURN)
    |--------------------------------------------------------------------------
    |
    | STUN assists NAT traversal for direct P2P connections.
    | TURN acts as a relay fallback when symmetric NAT or restrictive
    | firewalls prevent direct peer-to-peer connectivity.
    |
    */

    'stun_urls' => array_values(array_filter(array_map(
        'trim',
        explode(',', env(
            'WEBRTC_STUN_URLS',
            'stun:stun.l.google.com:19302,stun:stun1.l.google.com:19302,stun:stun2.l.google.com:19302'
        ))
    ))),

    'turn' => [
        'urls' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('WEBRTC_TURN_URLS', ''))
        ))),
        'username' => env('WEBRTC_TURN_USERNAME'),
        'credential' => env('WEBRTC_TURN_CREDENTIAL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ICE Transport Policy
    |--------------------------------------------------------------------------
    |
    | Supported: "all" (prefer direct P2P, fallback to TURN) or "relay" (force TURN).
    |
    */
    'ice_transport_policy' => env('WEBRTC_ICE_TRANSPORT_POLICY', 'all'),

    'ice_candidate_pool_size' => (int) env('WEBRTC_ICE_CANDIDATE_POOL_SIZE', 2),

];
