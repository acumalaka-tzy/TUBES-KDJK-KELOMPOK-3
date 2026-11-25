<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomInvitation extends Model
{
    use HasFactory;

    protected $table = 'room_invitations';

    protected $fillable = [
        'invite_id',
        'room_id',
        'token_hash',
        'created_by',
        'usage_limit',
        'usage_count',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'created_by', 'user_id');
    }

    /**
     * Check if invitation is valid (not expired, not revoked, under limit).
     */
    public function isValid(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Record a use of this invitation.
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Revoke invitation.
     */
    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }
}
