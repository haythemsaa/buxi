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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('box_id')->constrained()->onDelete('restrict');
            $table->foreignId('site_id')->constrained()->onDelete('restrict');

            // Dates
            $table->date('start_date');
            $table->date('end_date')->nullable(); // NULL = durée indéterminée
            $table->date('actual_end_date')->nullable();
            $table->integer('initial_duration_months')->nullable(); // 1, 3, 6, 12
            $table->boolean('auto_renewal')->default(true);
            $table->integer('notice_period_days')->default(30);

            // Tarification
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('setup_fee', 10, 2)->default(0);
            $table->enum('billing_frequency', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->integer('billing_day')->default(1); // Jour du mois pour la facturation

            // Paiement
            $table->enum('payment_method', ['card', 'sepa', 'transfer', 'cash', 'check'])->default('card');
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('sepa_mandate_id')->nullable();

            // Assurance
            $table->boolean('has_insurance')->default(false);
            $table->decimal('insurance_amount_monthly', 10, 2)->default(0);
            $table->decimal('declared_value', 12, 2)->nullable();

            // Inventaire
            $table->json('inventory')->nullable(); // Liste des objets stockés
            $table->json('forbidden_items_check')->nullable();

            // Documents
            $table->string('contract_document_path')->nullable();
            $table->string('terms_document_path')->nullable();
            $table->string('inventory_document_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_ip')->nullable();

            // Statut
            $table->enum('status', ['draft', 'pending_payment', 'active', 'suspended', 'terminated', 'cancelled'])->default('draft');
            $table->text('status_reason')->nullable();

            // Accès
            $table->string('access_code')->nullable();
            $table->string('badge_number')->nullable();
            $table->json('access_hours')->nullable();

            // Résiliation
            $table->date('termination_notice_date')->nullable();
            $table->string('termination_reason')->nullable();
            $table->text('termination_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('box_id');
            $table->index('site_id');
            $table->index('status');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
