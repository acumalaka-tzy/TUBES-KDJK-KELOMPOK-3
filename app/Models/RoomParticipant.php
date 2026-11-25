<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomParticipant extends Model
{
    use HasFactory;

    protected $table = 'room_participants';

    protected $fillable = [
        'participant_id',
        'room_id',
        'user_id',
        'display_name',
        'role',
        'connection_status',
        'is_kicked',
        'joined_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_kicked' => 'boolean',
            'joined_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    /**
     * UserSession relation.
     */
    public function userSession(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'user_id', 'user_id');
    }

    /**
     * Check if participant is creator.
     */
    public function isCreator(): bool
    {
        return $this->role === 'creator';
    }

    /**
     * Update connection status.
     */
    public function updateConnectionStatus(string $status): void
    {
        $this->update([
            'connection_status' => $status,
            'last_seen_at' => now(),
        ]);
    }
}
