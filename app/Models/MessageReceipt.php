<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReceipt extends Model
{
    use HasFactory;

    protected $table = 'message_receipts';

    protected $fillable = [
        'message_id',
        'room_id',
        'recipient_id',
        'status',
        'delivered_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(RoomMessage::class, 'message_id', 'message_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'recipient_id', 'user_id');
    }
}
