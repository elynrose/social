<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // alt_text stores a description of the attached media for
            // accessibility; captions_path points to generated subtitles
            // for video content.
            $table->string('alt_text')->nullable()->after('media_path');
            $table->string('captions_path')->nullable()->after('alt_text');
        });
    }
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['alt_text', 'captions_path']);
        });
    }
};