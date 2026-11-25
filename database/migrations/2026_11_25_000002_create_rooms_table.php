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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_id', 64)->unique();
            $table->string('room_name', 100);
            $table->text('description')->nullable();
            $table->string('creator_id', 64)->index();
            $table->string('room_pin_hash')->nullable();
            $table->boolean('dynamic_pin_enabled')->default(false);
            $table->string('dynamic_pin_secret')->nullable();
            $table->unsignedInteger('max_participants')->default(10);
            $table->boolean('e2ee_enabled')->default(true);
            $table->boolean('ephemeral_enabled')->default(false);
            $table->unsignedInteger('default_ephemeral_duration')->default(0); // 0 (off), 10, 30, 60, 300 seconds
            $table->boolean('is_locked')->default(false);
            $table->boolean('allow_invites')->default(true);
            $table->boolean('allow_reactions')->default(true);
            $table->boolean('allow_replies')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('creator_id')->references('user_id')->on('user_sessions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
