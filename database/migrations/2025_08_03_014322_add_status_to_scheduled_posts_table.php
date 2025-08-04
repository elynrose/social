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
        if (!Schema::hasTable('scheduled_posts')) {
            return; // Table doesn't exist, skip this migration
        }

        if (!Schema::hasColumn('scheduled_posts', 'status')) {
            Schema::table('scheduled_posts', function (Blueprint $table) {
                $table->string('status')->default('scheduled')->after('time_zone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('scheduled_posts')) {
            return; // Table doesn't exist, skip this migration
        }

        if (Schema::hasColumn('scheduled_posts', 'status')) {
            Schema::table('scheduled_posts', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
