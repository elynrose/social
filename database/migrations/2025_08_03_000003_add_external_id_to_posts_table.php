<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('posts')) {
            return; // Table doesn't exist, skip this migration
        }

        if (!Schema::hasColumn('posts', 'external_id')) {
            // Add an external_id column to the posts table so we can
            // persist the remote identifier returned by each social
            // platform when a post is published. This identifier is
            // required to query analytics and mentions via platform
            // APIs.
            Schema::table('posts', function (Blueprint $table) {
                $table->string('external_id')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('posts')) {
            return; // Table doesn't exist, skip this migration
        }

        if (Schema::hasColumn('posts', 'external_id')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('external_id');
            });
        }
    }
};