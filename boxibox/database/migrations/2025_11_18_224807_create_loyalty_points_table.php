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
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->integer('points_earned')->default(0); // Total gagné
            $table->integer('points_spent')->default(0); // Total dépensé
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->timestamps();

            $table->index('customer_id');
        });

        // Table des transactions de points
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earned', 'spent', 'expired', 'bonus', 'referral']);
            $table->integer('points'); // Positif pour gagné, négatif pour dépensé
            $table->string('description');
            $table->morphs('related'); // Relation polymorphe (contract, invoice, etc.)
            $table->date('expires_at')->nullable(); // Points expirent après 1 an
            $table->timestamps();

            $table->index(['customer_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
    }
};
