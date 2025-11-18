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
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Règle de prix
            $table->enum('rule_type', ['duration_discount', 'seasonal', 'occupancy_based', 'new_customer', 'referral']);
            $table->decimal('adjustment_value', 10, 2); // % ou montant
            $table->enum('adjustment_type', ['percentage', 'fixed_amount']);

            // Conditions
            $table->integer('min_duration_months')->nullable();
            $table->json('applicable_sites')->nullable();
            $table->json('applicable_box_sizes')->nullable();
            $table->decimal('min_occupancy_rate', 5, 2)->nullable(); // Taux d'occupation min
            $table->decimal('max_occupancy_rate', 5, 2)->nullable(); // Taux d'occupation max

            // Dates
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);

            // Priorité
            $table->integer('priority')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
