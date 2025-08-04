<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('posts')) {
            return; // Table doesn't exist, skip this migration
        }

        Schema::table('posts', function (Blueprint $table) {
            // alt_text stores a description of the attached media for
            // accessibility; captions_path points to generated subtitles
            // for video content.
            if (!Schema::hasColumn('posts', 'alt_text')) {
                $table->string('alt_text')->nullable()->after('media_path');
            }
            if (!Schema::hasColumn('posts', 'captions_path')) {
                $table->string('captions_path')->nullable()->after('alt_text');
            }
        });
    }
    public function down(): void
    {
        if (!Schema::hasTable('posts')) {
            return; // Table doesn't exist, skip this migration
        }

        Schema::table('posts', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('posts', 'alt_text')) {
                $columnsToDrop[] = 'alt_text';
            }
            if (Schema::hasColumn('posts', 'captions_path')) {
                $columnsToDrop[] = 'captions_path';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};