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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contract_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');

            // Montant
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');

            // Méthode de paiement
            $table->enum('method', ['card', 'sepa', 'transfer', 'cash', 'check'])->default('card');

            // Informations du paiement
            $table->date('payment_date');
            $table->string('reference')->nullable(); // Référence externe (numéro de chèque, etc.)

            // Stripe
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_refund_id')->nullable();

            // Statut
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->text('status_message')->nullable();

            // Remboursement
            $table->boolean('is_refund')->default(false);
            $table->foreignId('refunded_payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->date('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_number');
            $table->index('invoice_id');
            $table->index('contract_id');
            $table->index('customer_id');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
