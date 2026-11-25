<?php

namespace App\Services;

use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserIdentityService
{
    public const SESSION_USER_KEY = 'securelink_user_id';
    public const COOKIE_USER_KEY = 'slk_uid';

    /**
     * Generate a unique user ID.
     */
    public function generateUserId(): string
    {
        do {
            $userId = 'usr_' . strtolower(Str::random(12));
        } while (UserSession::where('user_id', $userId)->exists());

        return $userId;
    }

    /**
     * Get currently active user from request session, header, or cookie.
     */
    public function getCurrentUser(Request $request): ?UserSession
    {
        $userId = ($request->hasSession() ? $request->session()->get(self::SESSION_USER_KEY) : null)
            ?: $request->header('X-User-Id')
            ?: $request->cookie(self::COOKIE_USER_KEY);

        if (!$userId) {
            return null;
        }

        $user = UserSession::where('user_id', $userId)->first();
        if ($user) {
            $user->touchLastSeen();
        }

        return $user;
    }

    /**
     * Setup or initialize user identity in session and database.
     */
    public function setupIdentity(
        Request $request,
        string $displayName,
        ?string $avatarColor = null,
        ?array $preferences = null,
        ?string $existingUserId = null
    ): UserSession {
        $sessionUserId = $request->hasSession() ? $request->session()->get(self::SESSION_USER_KEY) : null;
        $userId = $existingUserId ?: ($sessionUserId ?: $this->generateUserId());
        $avatarColor = $avatarColor ?: $this->generateRandomColor();

        $user = UserSession::updateOrCreate(
            ['user_id' => $userId],
            [
                'display_name' => trim($displayName),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'avatar_color' => $avatarColor,
                'connection_status' => 'online',
                'preferences' => $preferences ?: ['theme' => 'system'],
                'last_seen_at' => now(),
            ]
        );

        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_USER_KEY, $user->user_id);
        }

        return $user;
    }

    /**
     * Update user identity preferences and display name.
     */
    public function updateIdentity(
        UserSession $user,
        ?string $displayName = null,
        ?string $avatarColor = null,
        ?array $preferences = null
    ): UserSession {
        $updates = ['last_seen_at' => now()];

        if ($displayName !== null) {
            $updates['display_name'] = trim($displayName);
        }

        if ($avatarColor !== null) {
            $updates['avatar_color'] = $avatarColor;
        }

        if ($preferences !== null) {
            $currentPreferences = $user->preferences ?: [];
            $updates['preferences'] = array_merge($currentPreferences, $preferences);
        }

        $user->update($updates);

        return $user->fresh();
    }

    /**
     * Touch user heartbeat.
     */
    public function touchHeartbeat(UserSession $user): void
    {
        $user->touchLastSeen();
    }

    /**
     * Clear user session on logout.
     */
    public function clearSession(Request $request): void
    {
        $currentUser = $this->getCurrentUser($request);
        if ($currentUser) {
            $currentUser->setOffline();
        }

        if ($request->hasSession()) {
            $request->session()->forget(self::SESSION_USER_KEY);
        }
    }

    /**
     * Generate a pleasant default avatar hex color.
     */
    public function generateRandomColor(): string
    {
        $colors = [
            '#2563eb', // Blue
            '#7c3aed', // Purple
            '#059669', // Emerald
            '#d97706', // Amber
            '#e11d48', // Rose
            '#0891b2', // Cyan
            '#4f46e5', // Indigo
            '#0d9488', // Teal
        ];

        return $colors[array_rand($colors)];
    }
}
