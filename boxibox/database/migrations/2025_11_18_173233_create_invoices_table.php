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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('contract_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('site_id')->constrained()->onDelete('restrict');

            // Type
            $table->enum('type', ['rental', 'service', 'deposit', 'fee', 'credit_note'])->default('rental');

            // Dates
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            // Montants
            $table->decimal('subtotal_ht', 10, 2); // HT
            $table->decimal('tax_amount', 10, 2)->default(0); // TVA
            $table->decimal('tax_rate', 5, 2)->default(0); // Taux de TVA en %
            $table->decimal('total_ttc', 10, 2); // TTC
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();

            // Lignes de facturation (JSON)
            $table->json('line_items'); // [{description, quantity, unit_price, total, tax_rate}, ...]

            // Statut
            $table->enum('status', ['draft', 'pending', 'paid', 'partial', 'overdue', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('paid_at')->nullable();

            // Documents
            $table->string('pdf_path')->nullable();
            $table->string('xml_path')->nullable(); // Pour facturation électronique

            // Relances
            $table->integer('reminder_count')->default(0);
            $table->date('last_reminder_sent')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_number');
            $table->index('contract_id');
            $table->index('customer_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
