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
        Schema::table('boxes', function (Blueprint $table) {
            $table->decimal('base_price_monthly_ht', 10, 2)->nullable()->after('price_monthly_ht');
            $table->decimal('current_optimal_price', 10, 2)->nullable()->after('base_price_monthly_ht');
            $table->timestamp('price_last_updated')->nullable()->after('current_optimal_price');
            $table->boolean('use_dynamic_pricing')->default(false)->after('price_last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boxes', function (Blueprint $table) {
            $table->dropColumn([
                'base_price_monthly_ht',
                'current_optimal_price',
                'price_last_updated',
                'use_dynamic_pricing'
            ]);
        });
    }
};
