<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scheduled_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->timestamp('publish_at');
            $table->string('time_zone')->default('UTC');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('scheduled_posts');
    }
};