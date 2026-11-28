<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomMessage extends Model
{
    use HasFactory;

    protected $table = 'room_messages';

    protected $fillable = [
        'message_id',
        'room_id',
        'sender_id',
        'sender_name',
        'sender_avatar_color',
        'sender_avatar_icon',
        'transport',
        'ciphertext',
        'iv',
        'encrypted',
        'algorithm',
        'ephemeral_duration',
        'reply_to',
        'client_timestamp',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'encrypted' => 'boolean',
            'ephemeral_duration' => 'integer',
            'reply_to' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'sender_id', 'user_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class, 'message_id', 'message_id');
    }
}
