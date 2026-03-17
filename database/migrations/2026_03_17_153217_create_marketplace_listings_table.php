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
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('channel')->default('myparts');
            $table->string('external_id');
            $table->unsignedInteger('external_status_id')->nullable();
            $table->unsignedBigInteger('external_category_id')->nullable();
            $table->string('external_category_title')->nullable();
            $table->decimal('external_price', 12, 2)->nullable();
            $table->unsignedSmallInteger('external_currency_id')->nullable();
            $table->integer('external_quantity')->nullable();
            $table->unsignedInteger('views')->nullable();
            $table->timestamp('create_date')->nullable();
            $table->timestamp('update_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->unique(['channel', 'external_id']);
            $table->index(['product_id', 'channel']);
            $table->index('last_synced_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};
