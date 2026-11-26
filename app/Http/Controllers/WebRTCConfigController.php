<?php

namespace App\Http\Controllers;

use App\Services\WebRTCConfigService;
use Illuminate\Http\JsonResponse;

class WebRTCConfigController extends Controller
{
    public function __construct(
        protected WebRTCConfigService $webrtcConfigService
    ) {}

    /**
     * Return STUN/TURN ICE server configuration for WebRTC RTCPeerConnection.
     */
    public function iceServers(): JsonResponse
    {
        $config = $this->webrtcConfigService->getClientConfig();

        return response()->json([
            'success' => true,
            ...$config,
        ]);
    }
}
