<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 100)->index();
            $table->string('room_id', 64)->index();
            $table->string('recipient_id', 64)->index();
            $table->string('status', 16)->default('pending'); // pending, delivered, read
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('message_id')->on('room_messages')->cascadeOnDelete();
            $table->foreign('room_id')->references('room_id')->on('rooms')->cascadeOnDelete();
            $table->foreign('recipient_id')->references('user_id')->on('user_sessions')->cascadeOnDelete();
            $table->unique(['message_id', 'recipient_id']);
            $table->index(['room_id', 'recipient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_receipts');
    }
};
