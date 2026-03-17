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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedSmallInteger('condition_type_id')->nullable();
            $table->integer('quantity_in_stock')->default(0);

            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->unsignedSmallInteger('currency_id')->nullable();

            $table->string('phone')->nullable();
            $table->string('location_label')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->unique('sku');
            $table->index(['category_id', 'is_active']);
            $table->index('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
