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
        Schema::create('marketplace_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->default('myparts');
            $table->unsignedBigInteger('external_category_id');
            $table->string('external_category_title')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->unique(['channel', 'external_category_id'], 'mcm_channel_extcat_unique');
            $table->index('category_id');
            $table->timestamps();
        });

        Schema::table('marketplace_category_mappings', function (Blueprint $table) {
            $table
                ->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_category_mappings', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::dropIfExists('marketplace_category_mappings');
    }
};
