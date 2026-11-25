<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\JoinRoomRequest;
use App\Http\Requests\KickParticipantRequest;
use App\Http\Requests\UpdateRoomSettingsRequest;
use App\Models\Room;
use App\Services\RoomService;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService,
        protected UserIdentityService $identityService
    ) {}

    /**
     * Get user's active/recent rooms.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'rooms' => []], 401);
        }

        $participations = $user->participations()
            ->where('is_kicked', false)
            ->with(['room' => fn($q) => $q->withCount('activeParticipants')])
            ->latest('last_seen_at')
            ->take(15)
            ->get();

        $rooms = $participations->map(function ($part) {
            $room = $part->room;
            return [
                'room_id' => $room->room_id,
                'room_name' => $room->room_name,
                'role' => $part->role,
                'is_locked' => $room->isLocked(),
                'has_pin' => $room->hasPin(),
                'active_participants_count' => $room->active_participants_count,
                'max_participants' => $room->max_participants,
                'joined_at' => $part->joined_at?->toIso8601String(),
                'last_seen_at' => $part->last_seen_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Create a new room.
     */
    public function store(CreateRoomRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Identitas diperlukan untuk membuat room.'], 401);
            }
            return redirect()->route('identity.setup');
        }

        $room = $this->roomService->createRoom($user, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Room berhasil dibuat.',
                'room' => $this->roomService->getRoomMetadata($room, $user),
            ], 201);
        }

        return redirect()->route('rooms.show', $room->room_id)
            ->with('success', "Room '{$room->room_name}' berhasil dibuat!");
    }

    /**
     * Get room metadata and participants.
     */
    public function show(Request $request, string $roomId): JsonResponse
    {
        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $user = $this->identityService->getCurrentUser($request);
        $metadata = $this->roomService->getRoomMetadata($room, $user);

        return response()->json([
            'success' => true,
            'room' => $metadata,
        ]);
    }

    /**
     * Join room.
     */
    public function join(JoinRoomRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Identitas diperlukan untuk bergabung.'], 401);
            }
            return redirect()->route('identity.setup');
        }

        $result = $this->roomService->joinRoom(
            user: $user,
            roomId: $request->validated('room_id'),
            pin: $request->validated('pin'),
            inviteToken: $request->validated('invite_token')
        );

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], $result['code']);
            }
            return back()->withErrors(['room_id' => $result['message']]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'room' => $this->roomService->getRoomMetadata($result['room'], $user),
                'participant' => [
                    'participant_id' => $result['participant']->participant_id,
                    'role' => $result['participant']->role,
                ],
            ]);
        }

        return redirect()->route('rooms.show', $result['room']->room_id);
    }

    /**
     * Leave room.
     */
    public function leave(Request $request, string $roomId): JsonResponse|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        $room = Room::where('room_id', $roomId)->first();

        if ($user && $room) {
            $this->roomService->leaveRoom($user, $room);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Anda telah keluar dari room.',
            ]);
        }

        return redirect()->route('dashboard')->with('info', 'Anda telah keluar dari room.');
    }

    /**
     * Kick participant (Creator only).
     */
    public function kick(KickParticipantRequest $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $kicked = $this->roomService->kickParticipant(
            actor: $user,
            room: $room,
            targetUserId: $request->validated('target_user_id')
        );

        if (!$kicked) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeluarkan peserta. Pastikan Anda memiliki hak Room Creator.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Peserta berhasil dikeluarkan dari room.',
        ]);
    }

    /**
     * Toggle lock room (Creator only).
     */
    public function toggleLock(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $lockInput = $request->has('is_locked') ? (bool) $request->input('is_locked') : null;
        $toggled = $this->roomService->toggleLock($user, $room, $lockInput);

        if (!$toggled) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Room Creator yang dapat mengunci atau membuka room.',
            ], 403);
        }

        $updatedRoom = $room->fresh();
        return response()->json([
            'success' => true,
            'is_locked' => $updatedRoom->isLocked(),
            'message' => $updatedRoom->isLocked() ? 'Room berhasil dikunci.' : 'Room berhasil dibuka.',
        ]);
    }

    /**
     * Update room settings (Creator only).
     */
    public function updateSettings(UpdateRoomSettingsRequest $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        $updated = $this->roomService->updateRoomSettings($user, $room, $request->validated());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Room Creator yang dapat memperbarui pengaturan room.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan room berhasil diperbarui.',
            'room' => $this->roomService->getRoomMetadata($updated, $user),
        ]);
    }

    /**
     * Create invitation link/token.
     */
    public function createInvite(Request $request, string $roomId): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Tidak terautentikasi.'], 401);
        }

        $room = Room::where('room_id', $roomId)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room tidak ditemukan.'], 404);
        }

        if (!$room->isCreator($user->user_id) && !$room->allow_invites) {
            return response()->json(['success' => false, 'message' => 'Undangan dinonaktifkan untuk room ini.'], 403);
        }

        $inviteData = $this->roomService->createInvitation(
            actor: $user,
            room: $room,
            usageLimit: $request->input('usage_limit'),
            expiresInMinutes: (int) ($request->input('expires_in_minutes', 1440))
        );

        $inviteUrl = url("/rooms/join?room_id={$room->room_id}&invite_token={$inviteData['raw_token']}");

        return response()->json([
            'success' => true,
            'invite_id' => $inviteData['invitation']->invite_id,
            'token' => $inviteData['raw_token'],
            'invite_url' => $inviteUrl,
            'expires_at' => $inviteData['invitation']->expires_at?->toIso8601String(),
        ]);
    }
}
