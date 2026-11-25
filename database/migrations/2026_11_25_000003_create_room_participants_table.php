<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_participants', function (Blueprint $table) {
            $table->id();
            $table->string('participant_id', 64)->unique();
            $table->string('room_id', 64)->index();
            $table->string('user_id', 64)->index();
            $table->string('display_name', 50);
            $table->enum('role', ['creator', 'participant'])->default('participant');
            $table->enum('connection_status', ['connected', 'connecting', 'disconnected', 'reconnecting', 'failed'])->default('connected');
            $table->boolean('is_kicked')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->foreign('room_id')->references('room_id')->on('rooms')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('user_sessions')->cascadeOnDelete();
            $table->unique(['room_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_participants');
    }
};
