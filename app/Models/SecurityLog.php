<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    use HasFactory;

    protected $table = 'security_logs';

    protected $fillable = [
        'room_id',
        'user_id',
        'event_type',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'user_id', 'user_id');
    }

    /**
     * Safe logger adhering to PRD requirements:
     * Never store plain-text messages, private keys, shared secrets, or PINs.
     */
    public static function logEvent(
        string $eventType,
        ?string $roomId = null,
        ?string $userId = null,
        array $details = []
    ): self {
        // Strip out forbidden keys if accidentally provided
        $forbiddenKeys = ['pin', 'password', 'key', 'secret', 'private_key', 'plain_text', 'message'];
        $cleanDetails = array_filter($details, function ($key) use ($forbiddenKeys) {
            return !in_array(strtolower($key), $forbiddenKeys);
        }, ARRAY_FILTER_USE_KEY);

        return self::create([
            'room_id' => $roomId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'details' => $cleanDetails,
        ]);
    }
}
