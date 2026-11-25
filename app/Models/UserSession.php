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
     * Map of avatar icon identifiers to display characters.
     */
    public static array $avatarSymbols = [
        'shield' => '🛡️',
        'flash' => '⚡',
        'bot' => '🤖',
        'fox' => '🦊',
        'lock' => '🔐',
        'rocket' => '🚀',
        'cat' => '🐱',
        'owl' => '🦉',
        'diamond' => '💎',
        'globe' => '🌐',
        'ninja' => '🕶️',
    ];

    /**
     * Get avatar icon / preset if selected.
     */
    public function getAvatarIconAttribute(): string
    {
        return $this->preferences['avatar_icon'] ?? 'initial';
    }

    /**
     * Get avatar display character or emoji.
     */
    public function getAvatarDisplayAttribute(): string
    {
        $icon = $this->avatar_icon;
        if (!empty($icon) && isset(self::$avatarSymbols[$icon])) {
            return self::$avatarSymbols[$icon];
        }

        return strtoupper(substr(trim($this->display_name ?? 'U'), 0, 1) ?: 'U');
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
