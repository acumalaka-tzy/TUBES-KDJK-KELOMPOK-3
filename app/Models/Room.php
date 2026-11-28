<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $hidden = [
        'room_pin_hash',
        'dynamic_pin_secret',
    ];

    protected $fillable = [
        'room_id',
        'room_name',
        'description',
        'creator_id',
        'room_pin_hash',
        'dynamic_pin_enabled',
        'dynamic_pin_secret',
        'pin_expires_at',
        'max_participants',
        'e2ee_enabled',
        'ephemeral_enabled',
        'default_ephemeral_duration',
        'is_locked',
        'allow_invites',
        'allow_reactions',
        'allow_replies',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'dynamic_pin_enabled' => 'boolean',
            'dynamic_pin_secret' => 'encrypted',
            'pin_expires_at' => 'datetime',
            'e2ee_enabled' => 'boolean',
            'ephemeral_enabled' => 'boolean',
            'default_ephemeral_duration' => 'integer',
            'is_locked' => 'boolean',
            'allow_invites' => 'boolean',
            'allow_reactions' => 'boolean',
            'allow_replies' => 'boolean',
            'max_participants' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'room_id';
    }

    /**
     * Creator of the room.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'creator_id', 'user_id');
    }

    /**
     * All participants of the room.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(RoomParticipant::class, 'room_id', 'room_id');
    }

    /**
     * Active (non-kicked) participants.
     */
    public function activeParticipants(): HasMany
    {
        return $this->hasMany(RoomParticipant::class, 'room_id', 'room_id')
            ->where('is_kicked', false);
    }

    /**
     * Room invitations.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(RoomInvitation::class, 'room_id', 'room_id');
    }

    /**
     * Security logs for the room.
     */
    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class, 'room_id', 'room_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(RoomMessage::class, 'room_id', 'room_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class, 'room_id', 'room_id');
    }

    /**
     * Check if room is protected with a static or dynamic PIN.
     */
    public function hasPin(): bool
    {
        return ! empty($this->room_pin_hash) || $this->dynamic_pin_enabled;
    }

    /**
     * Check if the room's configured PIN has expired.
     */
    public function isPinExpired(): bool
    {
        return $this->pin_expires_at !== null && $this->pin_expires_at->isPast();
    }

    /**
     * Check if room is locked.
     */
    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    /**
     * Check if room has reached maximum participants capacity.
     */
    public function isFull(): bool
    {
        return $this->activeParticipants()->count() >= $this->max_participants;
    }

    /**
     * Check if room has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if the specified user_id is the creator.
     */
    public function isCreator(?string $userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        return $this->creator_id === $userId;
    }

    /**
     * Check if the specified user is an active participant.
     */
    public function hasParticipant(?string $userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        return $this->activeParticipants()->where('user_id', $userId)->exists();
    }

    /**
     * Get participant record for given user ID.
     */
    public function getParticipant(?string $userId): ?RoomParticipant
    {
        if (empty($userId)) {
            return null;
        }

        return $this->participants()->where('user_id', $userId)->first();
    }
}
