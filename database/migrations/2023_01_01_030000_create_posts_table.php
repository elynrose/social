<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('campaign_id')->nullable(); // Create column without foreign key constraint
            $table->string('status')->default('draft');
            $table->longText('content');
            $table->string('media_path')->nullable();
            $table->unsignedBigInteger('variant_of')->nullable(); // Create column without foreign key constraint
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};