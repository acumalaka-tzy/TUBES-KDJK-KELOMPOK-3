<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 100)->unique();
            $table->string('room_id', 64)->index();
            $table->string('sender_id', 64)->index();
            $table->string('sender_name', 50);
            $table->string('sender_avatar_color', 20)->default('#008069');
            $table->string('sender_avatar_icon', 32)->default('initial');
            $table->string('transport', 16)->default('p2p');
            $table->longText('ciphertext');
            $table->string('iv', 64)->nullable();
            $table->boolean('encrypted')->default(true);
            $table->string('algorithm', 32)->nullable();
            $table->unsignedInteger('ephemeral_duration')->default(0);
            $table->json('reply_to')->nullable();
            $table->string('client_timestamp', 20)->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('room_id')->references('room_id')->on('rooms')->cascadeOnDelete();
            $table->foreign('sender_id')->references('user_id')->on('user_sessions')->cascadeOnDelete();
            $table->index(['room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_messages');
    }
};
