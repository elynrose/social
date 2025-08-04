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
        Schema::create('api_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // Encrypted
            $table->string('redirect_uri')->nullable();
            $table->json('scopes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Additional platform-specific settings
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'platform']);
            $table->index(['tenant_id', 'platform', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_configurations');
    }
}; 