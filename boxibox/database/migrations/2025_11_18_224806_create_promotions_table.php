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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Code promo (ex: BIENVENUE30)
            $table->string('name'); // Nom de la promotion
            $table->text('description')->nullable();

            // Type de réduction
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'first_month_free', 'months_free']);
            $table->decimal('discount_value', 10, 2); // % ou € selon type
            $table->integer('free_months')->default(0); // Nombre de mois gratuits

            // Conditions
            $table->decimal('min_duration_months', 10, 2)->nullable(); // Durée minimum
            $table->decimal('min_amount', 10, 2)->nullable(); // Montant minimum
            $table->json('applicable_box_types')->nullable(); // Types de boxes eligibles
            $table->json('applicable_sites')->nullable(); // Sites eligibles

            // Validité
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);

            // Limitations
            $table->integer('max_uses')->nullable(); // Nombre max d'utilisations
            $table->integer('current_uses')->default(0);
            $table->integer('max_uses_per_customer')->default(1);
            $table->boolean('online_only')->default(false); // Réservé aux réservations online
            $table->boolean('new_customers_only')->default(false);

            // Visibilité
            $table->boolean('is_public')->default(true); // Visible sur le site
            $table->boolean('requires_code')->default(true); // Nécessite saisie du code
            $table->boolean('auto_apply')->default(false); // Application automatique

            // Priorité (pour stacking de promos)
            $table->integer('priority')->default(0);
            $table->boolean('stackable')->default(false); // Cumulable avec d'autres promos

            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
