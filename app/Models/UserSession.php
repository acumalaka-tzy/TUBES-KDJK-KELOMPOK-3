<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class UserSession extends Model
{
    use HasFactory;

    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'display_name',
        'session_id',
        'avatar_color',
        'connection_status',
        'preferences',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'preferences' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Map of avatar character identifiers to their vector SVG markup (non-emoji).
     */
    public static array $avatarSvgs = [
        'agent' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 5 5v3a5 5 0 0 1-10 0V7a5 5 0 0 1 5-5z"/><path d="M8 8h3l.5 1h1l.5-1h3v2.5a1.5 1.5 0 0 1-3 0V9.5h-2v1a1.5 1.5 0 0 1-3 0V8z" fill="currentColor"/><path d="M17 9.5c1 .5 1.5 1.5 1.5 2.5v1"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M10 15l2 3 2-3"/></svg>',
        'ninja' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c-4.5 0-8 3.5-8 8v3c0 4.5 3.5 8 8 8s8-3.5 8-8v-3c0-4.5-3.5-8-8-8z"/><path d="M4 8h16"/><path d="M6 10h12v3H6z" fill="currentColor" fill-opacity="0.25"/><path d="M8 11.5h2.5"/><path d="M13.5 11.5H16"/><path d="M12 15v3"/></svg>',
        'robot' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v3"/><circle cx="12" cy="2" r="1" fill="currentColor"/><rect x="4" y="5" width="16" height="14" rx="3"/><path d="M2 10v4"/><path d="M22 10v4"/><rect x="7" y="8" width="10" height="4" rx="1" fill="currentColor" fill-opacity="0.3"/><circle cx="9.5" cy="10" r="1" fill="currentColor"/><circle cx="14.5" cy="10" r="1" fill="currentColor"/><path d="M8 15h8"/><path d="M10 17h4"/></svg>',
        'hacker' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c-5 0-9 4.5-9 10 0 4 2 7.5 5 9.5h8c3-2 5-5.5 5-9.5 0-5.5-4-10-9-10z"/><path d="M12 5c-3 0-6 3-6 7 0 2.5 1.5 5 4 6.5h4c2.5-1.5 4-4 4-6.5 0-4-3-7-6-7z" fill="currentColor" fill-opacity="0.2"/><path d="M8.5 11h7v2.5a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1V11z" fill="currentColor"/><path d="M10.5 16l-1 1.5"/><path d="M13.5 16l1 1.5"/></svg>',
        'guardian' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c-4.5 0-7.5 3-7.5 8v3l2.5 4 5 4 5-4 2.5-4v-3c0-5-3-8-7.5-8z"/><path d="M12 2v6"/><path d="M6.5 11h11"/><path d="M12 11v7"/><path d="M8 17l-1.5-3"/><path d="M16 17l1.5-3"/></svg>',
        'detective' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9.5c3-1 6-1.5 10-1.5s7 .5 10 1.5"/><path d="M6 9l1.5-5.5c.5-1 2-1.5 4.5-1.5s4 .5 4.5 1.5L18 9"/><path d="M6.5 7h11"/><path d="M7 12h10v2a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-2z" fill="currentColor" fill-opacity="0.3"/><path d="M7 12h4.5l.5 1 .5-1H17v1.5a1.5 1.5 0 0 1-3 0V13h-2v.5a1.5 1.5 0 0 1-3 0V12z" fill="currentColor"/><path d="M7 21l3-4h4l3 4"/></svg>',
        'astronaut' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="11" r="8"/><rect x="7" y="8" width="10" height="6" rx="3" fill="currentColor" fill-opacity="0.3"/><path d="M9 10c1-1 3-1.5 5-1"/><path d="M7 19h10"/><rect x="3" y="10" width="1.5" height="4" rx="0.75" fill="currentColor"/><rect x="19.5" y="10" width="1.5" height="4" rx="0.75" fill="currentColor"/><path d="M5 22a7 7 0 0 1 14 0"/></svg>',
        'ghost' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a8 8 0 0 0-8 8v9c0 .6.4 1 1 1 1 0 1.5-.7 2-1.2.6-.6 1.4-.8 2.2-.5.8.4 1.8.4 2.6 0 .8-.3 1.6-.1 2.2.5.5.5 1 1.2 2 1.2s1.5-.7 2-1.2c.6-.6 1.4-.8 2.2-.5.8.4 1.8.4 2.6 0 .8-.3 1.6-.1 2.2.5.5.5 1 1.2 2 1.2.6 0 1-.4 1-1v-9a8 8 0 0 0-8-8z"/><ellipse cx="9" cy="10" rx="1.5" ry="2.2" fill="currentColor"/><ellipse cx="15" cy="10" rx="1.5" ry="2.2" fill="currentColor"/><ellipse cx="12" cy="15" rx="1.5" ry="1" fill="currentColor" fill-opacity="0.6"/></svg>',
        'fox' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5l3.5 6L3 14l4 4 5 3 5-3 4-4-4.5-3L20 5l-6 2-2-1-2 1-6-2z"/><path d="M6 7l2 3"/><path d="M18 7l-2 3"/><path d="M7.5 13.5l2.5-1"/><path d="M16.5 13.5l-2.5-1"/><polygon points="12,18 10.5,16.5 13.5,16.5" fill="currentColor"/></svg>',
        'cat' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l3 3.5C8.5 9 10 9 12 9s3.5 0 5-.5L20 6l-1.5 6.5c1 1.5 1.5 3 1.5 4.5 0 3-3.5 5-8 5s-8-2-8-5c0-1.5.5-3 1.5-4.5L4 6z"/><ellipse cx="8.5" cy="13.5" rx="1.5" ry="2" transform="rotate(-15 8.5 13.5)" fill="currentColor"/><ellipse cx="15.5" cy="13.5" rx="1.5" ry="2" transform="rotate(15 15.5 13.5)" fill="currentColor"/><polygon points="12,16 11,15 13,15" fill="currentColor"/><path d="M6 15l-3-.5"/><path d="M6 16.5l-3 1"/><path d="M18 15l3-.5"/><path d="M18 16.5l3 1"/></svg>',
        'owl' => '<svg class="w-full h-full p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l3 3c1.5-.5 3-.5 5-.5s3.5 0 5 .5l3-3-1 6.5c1 1.5 1 3 1 4.5 0 4.5-3.5 8-8 8s-8-3.5-8-8c0-1.5 0-3 1-4.5L4 4z"/><circle cx="8.5" cy="13" r="2.75"/><circle cx="15.5" cy="13" r="2.75"/><circle cx="8.5" cy="13" r="1" fill="currentColor"/><circle cx="15.5" cy="13" r="1" fill="currentColor"/><path d="M11.25 13h1.5"/><polygon points="12,14.5 10.75,16.5 13.25,16.5" fill="currentColor"/></svg>',
    ];

    /**
     * Aliases for legacy/alternative identifiers.
     */
    public static array $legacyAliases = [
        'shield' => 'guardian',
        'bot' => 'robot',
        'flash' => 'robot',
        'lock' => 'guardian',
        'rocket' => 'astronaut',
        'diamond' => 'agent',
        'globe' => 'hacker',
    ];

    /**
     * Legacy mapping for backwards-compatibility with previous emoji definitions.
     */
    public static array $avatarSymbols = [
        'agent' => 'agent',
        'ninja' => 'ninja',
        'robot' => 'robot',
        'hacker' => 'hacker',
        'guardian' => 'guardian',
        'detective' => 'detective',
        'astronaut' => 'astronaut',
        'ghost' => 'ghost',
        'fox' => 'fox',
        'cat' => 'cat',
        'owl' => 'owl',
        'shield' => 'guardian',
        'bot' => 'robot',
        'flash' => 'robot',
        'lock' => 'guardian',
        'rocket' => 'astronaut',
        'diamond' => 'agent',
        'globe' => 'hacker',
    ];

    /**
     * Presets metadata for UI presentation.
     */
    public static array $characterMetadata = [
        'initial' => ['label' => 'Inisial', 'desc' => 'Huruf pertama nama tampilan'],
        'agent' => ['label' => 'Agent', 'desc' => 'Karakter agen taktis bersetelan'],
        'ninja' => ['label' => 'Ninja', 'desc' => 'Karakter shinobi bertopeng'],
        'robot' => ['label' => 'Robot', 'desc' => 'Karakter cyborg berantena'],
        'hacker' => ['label' => 'Hacker', 'desc' => 'Karakter spesialis berhoodie'],
        'guardian' => ['label' => 'Guardian', 'desc' => 'Karakter helm baja berpelindung'],
        'detective' => ['label' => 'Detective', 'desc' => 'Karakter berfedora incognito'],
        'astronaut' => ['label' => 'Astronaut', 'desc' => 'Karakter kosmonaut luar angkasa'],
        'ghost' => ['label' => 'Phantom', 'desc' => 'Karakter siluet hantu stealth'],
        'fox' => ['label' => 'Cyber Fox', 'desc' => 'Karakter rubah futuristik'],
        'cat' => ['label' => 'Shadow Cat', 'desc' => 'Karakter kucing bersiluet tajam'],
        'owl' => ['label' => 'Night Owl', 'desc' => 'Karakter burung hantu pengintai'],
    ];

    /**
     * Get avatar icon / preset if selected.
     */
    public function getAvatarIconAttribute(): string
    {
        return $this->preferences['avatar_icon'] ?? 'initial';
    }

    /**
     * Get avatar SVG or initial HTML (Non-emoji).
     */
    public function getAvatarHtmlAttribute(): string
    {
        $icon = $this->avatar_icon;
        $resolvedKey = self::$legacyAliases[$icon] ?? $icon;

        if ($resolvedKey !== 'initial' && isset(self::$avatarSvgs[$resolvedKey])) {
            return self::$avatarSvgs[$resolvedKey];
        }

        $letter = strtoupper(substr(trim($this->display_name ?? 'U'), 0, 1) ?: 'U');
        return '<span class="font-bold select-none leading-none">' . e($letter) . '</span>';
    }

    /**
     * Backward-compatible attribute returning vector SVG or initial character.
     */
    public function getAvatarDisplayAttribute(): string
    {
        return $this->avatar_html;
    }

    /**
     * Rooms created by this user.
     */
    public function createdRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'creator_id', 'user_id');
    }

    /**
     * Room participations for this user.
     */
    public function participations(): HasMany
    {
        return $this->hasMany(RoomParticipant::class, 'user_id', 'user_id');
    }

    /**
     * Security logs associated with this user.
     */
    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class, 'user_id', 'user_id');
    }

    /**
     * Touch last seen timestamp.
     */
    public function touchLastSeen(): void
    {
        $this->update([
            'last_seen_at' => now(),
            'connection_status' => 'online',
        ]);
    }

    /**
     * Mark user as offline.
     */
    public function setOffline(): void
    {
        $this->update([
            'connection_status' => 'offline',
            'last_seen_at' => now(),
        ]);
    }
}
