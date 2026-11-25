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
        Schema::create('room_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('invite_id', 64)->unique();
            $table->string('room_id', 64)->index();
            $table->string('token_hash', 64)->index();
            $table->string('created_by', 64)->index();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('room_id')->references('room_id')->on('rooms')->cascadeOnDelete();
            $table->foreign('created_by')->references('user_id')->on('user_sessions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_invitations');
    }
};
