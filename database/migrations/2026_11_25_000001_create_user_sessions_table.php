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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 64)->unique();
            $table->string('display_name', 50);
            $table->string('session_id', 128)->nullable()->index();
            $table->string('avatar_color', 20)->default('#2563eb');
            $table->enum('connection_status', ['online', 'offline', 'reconnecting'])->default('online');
            $table->json('preferences')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
