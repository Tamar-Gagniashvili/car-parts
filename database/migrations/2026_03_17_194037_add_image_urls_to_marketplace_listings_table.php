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
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->string('external_thumb_url')->nullable()->after('external_quantity');
            $table->string('external_large_url')->nullable()->after('external_thumb_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropColumn(['external_thumb_url', 'external_large_url']);
        });
    }
};
