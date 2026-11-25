<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupIdentityRequest;
use App\Http\Requests\UpdateIdentityRequest;
use App\Services\UserIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserIdentityController extends Controller
{
    public function __construct(
        protected UserIdentityService $identityService
    ) {}

    /**
     * Get current user identity details.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);

        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'user' => null,
            ]);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'user_id' => $user->user_id,
                'display_name' => $user->display_name,
                'avatar_color' => $user->avatar_color,
                'avatar_icon' => $user->avatar_icon,
                'avatar_display' => $user->avatar_display,
                'connection_status' => $user->connection_status,
                'preferences' => $user->preferences,
                'last_seen_at' => $user->last_seen_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Setup or create identity (via API or web form).
     */
    public function setup(SetupIdentityRequest $request): JsonResponse|RedirectResponse
    {
        $preferences = $request->validated('preferences') ?: [];
        if ($request->filled('avatar_icon')) {
            $preferences['avatar_icon'] = $request->input('avatar_icon');
        }

        $user = $this->identityService->setupIdentity(
            request: $request,
            displayName: $request->validated('display_name'),
            avatarColor: $request->validated('avatar_color'),
            preferences: $preferences,
            existingUserId: $request->validated('user_id')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Identitas berhasil disimpan.',
                'user' => [
                    'user_id' => $user->user_id,
                    'display_name' => $user->display_name,
                    'avatar_color' => $user->avatar_color,
                    'preferences' => $user->preferences,
                ],
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Selamat datang di SecureLink, ' . $user->display_name . '!');
    }

    /**
     * Update existing identity.
     */
    public function update(UpdateIdentityRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->identityService->getCurrentUser($request);

        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identitas pengguna belum disetup.',
                ], 401);
            }
            return redirect()->route('identity.setup')->withErrors(['error' => 'Silakan setup identitas terlebih dahulu.']);
        }

        $preferences = $user->preferences ?: [];
        if ($request->has('preferences')) {
            $preferences = array_merge($preferences, $request->validated('preferences') ?: []);
        }
        if ($request->filled('avatar_icon')) {
            $preferences['avatar_icon'] = $request->input('avatar_icon');
        }

        $updatedUser = $this->identityService->updateIdentity(
            user: $user,
            displayName: $request->validated('display_name'),
            avatarColor: $request->validated('avatar_color'),
            preferences: $preferences
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Identitas berhasil diperbarui.',
                'user' => [
                    'user_id' => $updatedUser->user_id,
                    'display_name' => $updatedUser->display_name,
                    'avatar_color' => $updatedUser->avatar_color,
                    'preferences' => $updatedUser->preferences,
                ],
            ]);
        }

        return back()->with('success', 'Profil identitas berhasil diperbarui.');
    }

    /**
     * Heartbeat ping to keep session/status alive.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $this->identityService->getCurrentUser($request);

        if (!$user) {
            return response()->json(['online' => false], 401);
        }

        $this->identityService->touchHeartbeat($user);

        return response()->json([
            'online' => true,
            'user_id' => $user->user_id,
            'last_seen_at' => $user->fresh()->last_seen_at?->toIso8601String(),
        ]);
    }

    /**
     * End user session.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $this->identityService->clearSession($request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sesi identitas telah diakhiri.',
            ]);
        }

        return redirect()->route('landing')->with('info', 'Sesi Anda telah diakhiri.');
    }
}
