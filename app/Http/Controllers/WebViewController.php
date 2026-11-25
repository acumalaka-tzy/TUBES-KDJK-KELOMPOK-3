<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\RoomIdGeneratorService;
use App\Services\RoomService;
use App\Services\UserIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebViewController extends Controller
{
    public function __construct(
        protected UserIdentityService $identityService,
        protected RoomService $roomService,
        protected RoomIdGeneratorService $idGenerator
    ) {}

    /**
     * Landing page.
     */
    public function landing(Request $request): View|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);

        return view('pages.landing', compact('user'));
    }

    /**
     * User identity setup page.
     */
    public function setup(Request $request): View
    {
        $user = $this->identityService->getCurrentUser($request);
        $suggestedUserId = $user ? $user->user_id : $this->identityService->generateUserId();

        return view('pages.setup', compact('user', 'suggestedUserId'));
    }

    /**
     * User dashboard / chat home.
     */
    public function dashboard(Request $request): View|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return redirect()->route('identity.setup')
                ->with('info', 'Silakan tentukan nama Anda sebelum masuk ke dashboard.');
        }

        $participations = $user->participations()
            ->where('is_kicked', false)
            ->with(['room' => fn($q) => $q->withCount('activeParticipants')])
            ->latest('last_seen_at')
            ->take(15)
            ->get();

        return view('pages.dashboard', compact('user', 'participations'));
    }

    /**
     * Create room form page.
     */
    public function createRoom(Request $request): View|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return redirect()->route('identity.setup');
        }

        $suggestedRoomId = $this->idGenerator->generate('code');

        return view('pages.rooms.create', compact('user', 'suggestedRoomId'));
    }

    /**
     * Join room form page.
     */
    public function joinRoom(Request $request): View|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return redirect()->route('identity.setup');
        }

        $prefilledRoomId = $request->query('room_id', '');
        $inviteToken = $request->query('invite_token', '');

        return view('pages.rooms.join', compact('user', 'prefilledRoomId', 'inviteToken'));
    }

    /**
     * Interactive Chat Room view.
     */
    public function chatRoom(Request $request, string $roomId): View|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return redirect()->route('identity.setup');
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Room tidak ditemukan.']);
        }

        $participant = $room->getParticipant($user->user_id);

        // Auto-enroll creator if needed
        if ($room->isCreator($user->user_id) && !$participant) {
            $participant = \App\Models\RoomParticipant::create([
                'participant_id' => 'part_' . \Illuminate\Support\Str::random(12),
                'room_id' => $room->room_id,
                'user_id' => $user->user_id,
                'display_name' => $user->display_name,
                'role' => 'creator',
                'connection_status' => 'connected',
                'joined_at' => now(),
                'last_seen_at' => now(),
            ]);
        }

        // If user is not yet a participant, redirect to join room form with prefilled ID
        if (!$participant || $participant->is_kicked) {
            return redirect()->route('rooms.join', ['room_id' => $room->room_id])
                ->with('warning', 'Silakan masukkan PIN atau bergabung terlebih dahulu.');
        }

        $metadata = $this->roomService->getRoomMetadata($room, $user);

        $userRooms = $user->participations()
            ->where('is_kicked', false)
            ->with(['room' => fn($q) => $q->withCount('activeParticipants')])
            ->latest('last_seen_at')
            ->take(15)
            ->get();

        return view('pages.rooms.chat', compact('user', 'room', 'participant', 'metadata', 'userRooms'));
    }

    /**
     * Settings page.
     */
    public function settings(Request $request): View|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return redirect()->route('identity.setup');
        }

        return view('pages.settings', compact('user'));
    }

    /**
     * Demonstration page comparing Server Broadcast and WebRTC P2P.
     */
    public function demo(Request $request): View
    {
        $user = $this->identityService->getCurrentUser($request);

        return view('pages.demo', compact('user'));
    }
}
