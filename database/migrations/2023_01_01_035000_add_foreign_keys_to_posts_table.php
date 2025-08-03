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
        // Add foreign key constraints after all tables exist
        Schema::table('posts', function (Blueprint $table) {
            // Add foreign key for campaign_id (campaigns table should exist by now)
            if (Schema::hasTable('campaigns')) {
                $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
            }
            
            // Add foreign key for variant_of (self-referencing)
            $table->foreign('variant_of')->references('id')->on('posts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['variant_of']);
        });
    }
}; 