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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();

            // Box size filters
            $table->decimal('box_size_min', 8, 2)->nullable();
            $table->decimal('box_size_max', 8, 2)->nullable();

            // Occupancy thresholds
            $table->decimal('occupancy_threshold_min', 5, 2)->nullable(); // 0.00 to 100.00
            $table->decimal('occupancy_threshold_max', 5, 2)->nullable();

            // Season filter
            $table->enum('season', ['winter', 'spring', 'summer', 'fall', 'all'])->default('all');

            // Duration filter (in months)
            $table->integer('duration_months_min')->nullable();
            $table->integer('duration_months_max')->nullable();

            // Day of week filter (JSON array of days)
            $table->json('days_of_week')->nullable();

            // Adjustment
            $table->enum('adjustment_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('adjustment_value', 8, 2); // Can be positive or negative

            // Priority and status
            $table->integer('priority')->default(0); // Higher = applied first
            $table->boolean('is_active')->default(true);

            // Validity dates
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('site_id');
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
