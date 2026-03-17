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
        Schema::create('product_vehicle_fits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('manufacturer_external_id');
            $table->unsignedBigInteger('model_external_id');
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();
            $table->string('volume')->nullable();
            $table->boolean('is_main')->default(false);

            $table->index(['product_id', 'is_main'], 'pvf_prod_main_idx');
            $table->index(['manufacturer_external_id', 'model_external_id'], 'pvf_make_model_idx');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vehicle_fits');
    }
};
