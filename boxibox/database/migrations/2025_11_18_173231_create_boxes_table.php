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
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->onDelete('cascade');
            $table->string('number')->unique(); // Numéro unique de la box
            $table->string('name')->nullable();
            $table->string('type')->default('standard'); // standard, climate_controlled, exterior, premium
            $table->string('size_category')->nullable(); // mini, small, medium, large, xl

            // Dimensions
            $table->decimal('length', 6, 2); // en cm
            $table->decimal('width', 6, 2); // en cm
            $table->decimal('height', 6, 2); // en cm
            $table->decimal('volume', 8, 3); // en m³
            $table->decimal('surface', 7, 2); // en m²

            // Tarification
            $table->decimal('base_price_monthly', 10, 2); // Prix de base mensuel
            $table->decimal('current_price_monthly', 10, 2); // Prix actuel (peut varier)

            // Caractéristiques
            $table->boolean('ground_floor')->default(false);
            $table->boolean('vehicle_access')->default(false);
            $table->boolean('climate_controlled')->default(false);
            $table->boolean('has_electricity')->default(false);
            $table->json('features')->nullable();
            $table->json('photos')->nullable();

            // Statut
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance', 'out_of_service'])->default('available');
            $table->text('status_note')->nullable();
            $table->date('available_from')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('floor_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
