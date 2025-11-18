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
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->nullable()->constrained()->onDelete('set null');

            // Type de rappel
            $table->enum('phase', ['phase_1', 'phase_2', 'phase_3']);
            $table->integer('days_overdue'); // Nombre de jours de retard

            // Montants
            $table->decimal('amount_due', 10, 2); // Montant dû
            $table->decimal('late_fee', 10, 2)->default(0); // Pénalités de retard

            // Statut et envoi
            $table->enum('status', ['pending', 'sent', 'acknowledged', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable(); // Client a vu le rappel
            $table->timestamp('paid_at')->nullable();

            // Méthode d'envoi
            $table->json('sent_via')->nullable(); // ['email', 'sms', 'push', 'mail']

            // Contenu
            $table->text('message')->nullable(); // Message personnalisé
            $table->json('metadata')->nullable(); // Données additionnelles

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['invoice_id', 'phase']);
            $table->index(['customer_id', 'status']);
            $table->index(['status', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
    }
};
