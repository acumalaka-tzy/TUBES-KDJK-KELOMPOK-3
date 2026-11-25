<?php

namespace App\Services;

use App\Models\Room;
use App\Models\SecurityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class RoomPinValidationService
{
    public const MAX_ATTEMPTS = 5;
    public const COOLDOWN_SECONDS = 300; // 5 minutes cooldown
    public const DECAY_MINUTES = 5;

    /**
     * Validate room PIN (either static hashed PIN or dynamic TOTP PIN).
     *
     * @return array{valid: bool, message: string, cooldown: int|null}
     */
    public function validatePin(Room $room, ?string $inputPin, string $userId): array
    {
        // Check if room requires PIN
        if (!$room->hasPin()) {
            return [
                'valid' => true,
                'message' => 'Room tidak memerlukan PIN.',
                'cooldown' => null,
            ];
        }

        // Check rate limiting / cooldown
        if ($this->isRateLimited($room->room_id, $userId)) {
            $remaining = $this->getRemainingCooldown($room->room_id, $userId);
            SecurityLog::logEvent(
                eventType: 'pin_attempt_blocked_cooldown',
                roomId: $room->room_id,
                userId: $userId,
                details: ['remaining_cooldown' => $remaining]
            );

            return [
                'valid' => false,
                'message' => "Terlalu banyak percobaan PIN gagal. Coba lagi dalam {$remaining} detik.",
                'cooldown' => $remaining,
            ];
        }

        // Must provide a PIN
        if (empty($inputPin)) {
            return [
                'valid' => false,
                'message' => 'PIN room wajib diisi.',
                'cooldown' => null,
            ];
        }

        $isValid = false;

        // 1. Dynamic TOTP PIN verification if enabled
        if ($room->dynamic_pin_enabled && !empty($room->dynamic_pin_secret)) {
            $isValid = $this->verifyTotp($room->dynamic_pin_secret, $inputPin);
        }

        // 2. Static PIN verification if dynamic is not enabled or if static was also configured
        if (!$isValid && !empty($room->room_pin_hash)) {
            $isValid = Hash::check($inputPin, $room->room_pin_hash);
        }

        if ($isValid) {
            $this->clearFailedAttempts($room->room_id, $userId);
            SecurityLog::logEvent(
                eventType: 'pin_verification_success',
                roomId: $room->room_id,
                userId: $userId
            );

            return [
                'valid' => true,
                'message' => 'PIN valid.',
                'cooldown' => null,
            ];
        }

        // Failed attempt
        $attempts = $this->recordFailedAttempt($room->room_id, $userId);
        $remainingAttempts = max(0, self::MAX_ATTEMPTS - $attempts);

        SecurityLog::logEvent(
            eventType: 'pin_verification_failed',
            roomId: $room->room_id,
            userId: $userId,
            details: ['attempt_count' => $attempts]
        );

        if ($remainingAttempts === 0) {
            $cooldown = self::COOLDOWN_SECONDS;
            return [
                'valid' => false,
                'message' => "PIN salah. Batas percobaan tercapai. Silakan tunggu {$cooldown} detik.",
                'cooldown' => $cooldown,
            ];
        }

        return [
            'valid' => false,
            'message' => "PIN salah. Sisa percobaan: {$remainingAttempts} kali.",
            'cooldown' => null,
        ];
    }

    /**
     * Hash a static room PIN safely.
     */
    public function hashPin(string $pin): string
    {
        return Hash::make($pin);
    }

    /**
     * Generate standard Base32 Secret for TOTP.
     */
    public function generateTotpSecret(int $length = 16): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $base32Chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Calculate current TOTP code based on RFC 6238.
     */
    public function getTotpCode(string $secret, ?int $timestamp = null, int $timeStep = 30, int $digits = 6): string
    {
        $time = $timestamp ?? time();
        $counter = floor($time / $timeStep);

        // Pack counter into 8-byte big-endian binary
        $binaryCounter = pack('N*', 0) . pack('N*', $counter);

        // Decode Base32 secret
        $decodedSecret = $this->base32Decode($secret);

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $binaryCounter, $decodedSecret, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binaryCode = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $totp = $binaryCode % (10 ** $digits);
        return str_pad((string) $totp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verify TOTP code with time drift window tolerance.
     */
    public function verifyTotp(string $secret, string $inputCode, int $window = 1, int $timeStep = 30): bool
    {
        $currentTime = time();
        $cleanInput = trim($inputCode);

        for ($i = -$window; $i <= $window; $i++) {
            $testTime = $currentTime + ($i * $timeStep);
            $calculatedCode = $this->getTotpCode($secret, $testTime, $timeStep);
            if (hash_equals($calculatedCode, $cleanInput)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is currently in cooldown rate limit for this room.
     */
    public function isRateLimited(string $roomId, string $userId): bool
    {
        $key = $this->getRateLimitKey($roomId, $userId);
        $attempts = (int) Cache::get($key, 0);
        return $attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Get remaining cooldown seconds.
     */
    public function getRemainingCooldown(string $roomId, string $userId): int
    {
        $lockKey = $this->getLockExpiryKey($roomId, $userId);
        $lockExpiresAt = Cache::get($lockKey);
        if ($lockExpiresAt) {
            return max(0, $lockExpiresAt - time());
        }
        return self::COOLDOWN_SECONDS;
    }

    /**
     * Record a failed PIN attempt.
     */
    public function recordFailedAttempt(string $roomId, string $userId): int
    {
        $key = $this->getRateLimitKey($roomId, $userId);
        $attempts = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(self::DECAY_MINUTES));

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockKey = $this->getLockExpiryKey($roomId, $userId);
            Cache::put($lockKey, time() + self::COOLDOWN_SECONDS, now()->addSeconds(self::COOLDOWN_SECONDS));
        }

        return $attempts;
    }

    /**
     * Clear failed PIN attempts upon success.
     */
    public function clearFailedAttempts(string $roomId, string $userId): void
    {
        Cache::forget($this->getRateLimitKey($roomId, $userId));
        Cache::forget($this->getLockExpiryKey($roomId, $userId));
    }

    protected function getRateLimitKey(string $roomId, string $userId): string
    {
        return "pin_attempts:{$roomId}:{$userId}";
    }

    protected function getLockExpiryKey(string $roomId, string $userId): string
    {
        return "pin_lock_expiry:{$roomId}:{$userId}";
    }

    /**
     * Simple RFC 4648 Base32 decode.
     */
    protected function base32Decode(string $b32): string
    {
        $lut = [
            'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3,
            'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7,
            'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11,
            'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15,
            'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19,
            'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
            'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27,
            '4' => 28, '5' => 29, '6' => 30, '7' => 31,
        ];

        $b32 = strtoupper(rtrim($b32, '='));
        $binary = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            if (!isset($lut[$char])) {
                continue;
            }
            $buffer = ($buffer << 5) | $lut[$char];
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }
}
