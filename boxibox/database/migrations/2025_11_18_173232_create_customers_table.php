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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('landlord_tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Lié au compte utilisateur
            $table->string('customer_number')->unique(); // Numéro client

            // Type
            $table->enum('type', ['individual', 'professional'])->default('individual');

            // Informations personnelles (Particulier)
            $table->string('civility')->nullable(); // M., Mme, etc.
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();

            // Informations professionnelles
            $table->string('company_name')->nullable();
            $table->string('siret')->nullable();
            $table->string('vat_number')->nullable();

            // Contact
            $table->string('email')->index();
            $table->string('phone');
            $table->string('mobile')->nullable();

            // Adresse
            $table->text('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country', 2)->default('FR');

            // Adresse de facturation (si différente)
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country', 2)->nullable();

            // Préférences
            $table->string('language', 2)->default('fr');
            $table->json('communication_preferences')->nullable();

            // Documents
            $table->string('id_document_path')->nullable();
            $table->string('proof_of_address_path')->nullable();
            $table->string('bank_details_path')->nullable();
            $table->string('kbis_path')->nullable();
            $table->enum('documents_verified', ['pending', 'verified', 'rejected'])->default('pending');

            // Scoring et tags
            $table->string('payment_score')->default('new'); // good, average, bad, new
            $table->json('tags')->nullable();
            $table->string('acquisition_source')->nullable(); // web, phone, visit, etc.
            $table->boolean('is_vip')->default(false);

            // Notes internes
            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'email']);
            $table->index('customer_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
