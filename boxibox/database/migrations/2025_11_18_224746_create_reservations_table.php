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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('box_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained()->onDelete('cascade');

            // Guest reservation (avant création compte)
            $table->string('guest_email')->nullable();
            $table->string('guest_first_name')->nullable();
            $table->string('guest_last_name')->nullable();
            $table->string('guest_phone')->nullable();

            // Dates
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = durée indéterminée
            $table->integer('duration_months')->default(1);

            // Prix
            $table->decimal('monthly_price_ht', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(20.00);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('first_payment', 10, 2); // 1er mois + dépôt
            $table->foreignId('promotion_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->default(0);

            // Status
            $table->enum('status', ['pending', 'confirmed', 'converted', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Réservation valide 30 jours
            $table->timestamp('converted_to_contract_at')->nullable();
            $table->foreignId('contract_id')->nullable()->constrained()->onDelete('set null');

            // Options
            $table->boolean('requires_insurance')->default(false);
            $table->decimal('insurance_monthly', 10, 2)->nullable();
            $table->json('selected_options')->nullable(); // Accessoires, services

            // Notes
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expires_at']);
            $table->index('reservation_number');
            $table->index('guest_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
