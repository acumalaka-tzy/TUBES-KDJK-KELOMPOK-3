<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Str;

class RoomIdGeneratorService
{
    /**
     * Generate a unique Room ID.
     * Default format: SEC-XXX-YYY (e.g., SEC-8F2-9K1)
     */
    public function generate(string $format = 'code'): string
    {
        $attempts = 0;
        $maxAttempts = 10;

        do {
            $roomId = match ($format) {
                'numeric' => 'SEC-' . sprintf('%03d-%03d', random_int(100, 999), random_int(100, 999)),
                'slug' => 'slk-' . strtolower(Str::random(4)) . '-' . strtolower(Str::random(4)),
                default => 'SEC-' . strtoupper(Str::random(3)) . '-' . strtoupper(Str::random(3)),
            };
            $attempts++;
        } while (Room::where('room_id', $roomId)->exists() && $attempts < $maxAttempts);

        if ($attempts >= $maxAttempts) {
            // High entropy fallback
            $roomId = 'SEC-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        }

        return $roomId;
    }

    /**
     * Validate whether a given string is a valid Room ID format.
     */
    public function isValidFormat(string $roomId): bool
    {
        $clean = trim($roomId);
        // Allows alphanumeric characters, hyphens, underscores between 4 and 64 chars
        return (bool) preg_match('/^[a-zA-Z0-9_\-]{4,64}$/', $clean);
    }
}
