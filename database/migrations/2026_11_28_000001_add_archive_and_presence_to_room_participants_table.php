<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_participants', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->after('is_kicked')->index();
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->string('last_read_message_id', 100)->nullable()->after('archived_at');
            $table->string('presence_state', 24)->default('offline')->after('connection_status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('room_participants', function (Blueprint $table) {
            $table->dropColumn(['is_archived', 'archived_at', 'last_read_message_id', 'presence_state']);
        });
    }
};
