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
        Schema::table('product_images', function (Blueprint $table) {
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        Schema::table('product_vehicle_fits', function (Blueprint $table) {
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table
                ->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table
                ->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table
                ->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['product_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('product_vehicle_fits', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
    }
};
