<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return; // Table doesn't exist, skip this migration
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'consent_at')) {
                $table->timestamp('consent_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'consent_preferences')) {
                $table->json('consent_preferences')->nullable();
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('UTC');
            }
            if (!Schema::hasColumn('users', 'notification_preferences')) {
                $table->json('notification_preferences')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return; // Table doesn't exist, skip this migration
        }

        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'consent_at')) {
                $columnsToDrop[] = 'consent_at';
            }
            if (Schema::hasColumn('users', 'consent_preferences')) {
                $columnsToDrop[] = 'consent_preferences';
            }
            if (Schema::hasColumn('users', 'timezone')) {
                $columnsToDrop[] = 'timezone';
            }
            if (Schema::hasColumn('users', 'notification_preferences')) {
                $columnsToDrop[] = 'notification_preferences';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
}; 