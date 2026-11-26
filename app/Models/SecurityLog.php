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
     * Forbidden keys that must never be persisted to security logs (PRD Section 3.8).
     *
     * @var array<int, string>
     */
    public const FORBIDDEN_KEYS = [
        'pin',
        'room_pin',
        'room_pin_hash',
        'pin_value',
        'password',
        'passphrase',
        'key',
        'private_key',
        'privatekey',
        'public_key_private',
        'shared_secret',
        'sharedsecret',
        'secret',
        'totp_secret',
        'dynamic_pin_secret',
        'plain_text',
        'plaintext',
        'message',
        'text',
        'body',
        'payload',
        'ciphertext',
        'token',
        'raw_token',
        'session_token',
        'session_id',
    ];

    /**
     * Safe logger adhering to PRD requirements:
     * Never store plain-text messages, private keys, shared secrets, tokens, or PINs.
     */
    public static function logEvent(
        string $eventType,
        ?string $roomId = null,
        ?string $userId = null,
        array $details = []
    ): self {
        return self::create([
            'room_id' => $roomId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'details' => self::sanitizeDetails($details),
        ]);
    }

    /**
     * Recursively remove forbidden sensitive keys from log metadata.
     *
     * @param  array<string|int, mixed>  $details
     * @return array<string|int, mixed>
     */
    public static function sanitizeDetails(array $details): array
    {
        $clean = [];

        foreach ($details as $key => $value) {
            if (is_string($key) && self::isForbiddenKey($key)) {
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = self::sanitizeDetails($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    protected static function isForbiddenKey(string $key): bool
    {
        $normalized = strtolower(trim($key));

        if (in_array($normalized, self::FORBIDDEN_KEYS, true)) {
            return true;
        }

        foreach (['secret', 'password', 'private_key', 'privatekey', 'shared_secret', 'plaintext', 'plain_text', 'ciphertext', 'session_token'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }
}
