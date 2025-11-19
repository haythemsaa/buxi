# 🚀 Guide d'Implémentation - Phase 1 Quick Wins
## Boxibox - Janvier 2025

> Ce guide détaille l'implémentation concrète des 4 fonctionnalités prioritaires de la Phase 1

---

## 📋 État d'Avancement

### ✅ Déjà Implémenté

#### 1. Revenue Management Dynamique (80% complété)

**Fichiers créés** :
- ✅ `database/migrations/2025_01_19_create_pricing_rules_table.php`
- ✅ `database/migrations/2025_01_19_add_dynamic_pricing_to_boxes_table.php`
- ✅ `app/Models/PricingRule.php`
- ✅ `app/Services/DynamicPricingService.php`
- ✅ `app/Http/Controllers/Admin/PricingRuleController.php`
- ✅ `app/Http/Controllers/Admin/RevenueManagementController.php`
- ✅ `routes/admin_revenue.php`
- ✅ `database/seeders/DefaultPricingRulesSeeder.php`

**Reste à faire** :
- ⏳ Composants Vue pour dashboard Revenue Management
- ⏳ Tests unitaires PricingRule
- ⏳ Commande artisan `pricing:update-all`

#### 2. Intégrations Paiement (Configuration créée)

**Fichiers créés** :
- ✅ `config/payments.php`
- ✅ `.env.example.payments`

**Reste à faire** :
- ⏳ PaymentGatewayService et handlers (Stripe, PayPal)
- ⏳ Webhooks controllers
- ⏳ Frontend payment selector
- ⏳ Installation packages (`stripe/stripe-php`, `paypal/rest-api-sdk-php`)

#### 3. Portail Client (0% - À implémenter)
#### 4. Analytics Avancés (0% - À implémenter)

---

## 🎯 Partie 1 : Revenue Management Dynamique

### Étape 1.1 : Migrations Database

**✅ DÉJÀ FAIT** - Voir fichiers créés

**Test des migrations** :
```bash
php artisan migrate

# Vérifier les tables créées
php artisan tinker
>>> Schema::hasTable('pricing_rules')
=> true
>>> Schema::hasColumns('boxes', ['base_price_monthly_ht', 'current_optimal_price'])
=> true
```

### Étape 1.2 : Seeder de Règles Par Défaut

**✅ DÉJÀ FAIT** - `DefaultPricingRulesSeeder.php`

**Exécution** :
```bash
php artisan db:seed --class=DefaultPricingRulesSeeder
```

**Résultat** :
- 10 règles de tarification créées
- Couvrent : occupation, saisons, durées, tailles de boxes
- Prêtes à l'emploi

### Étape 1.3 : Activer le Pricing Dynamique sur les Boxes

**Script de migration des données** :
```bash
php artisan tinker
```

```php
// Activer le pricing dynamique sur tous les boxes
use App\Models\Box;

Box::chunk(100, function ($boxes) {
    foreach ($boxes as $box) {
        $box->update([
            'base_price_monthly_ht' => $box->price_monthly_ht,
            'use_dynamic_pricing' => true,
        ]);
    }
});

echo "✓ Pricing dynamique activé sur tous les boxes\n";
```

### Étape 1.4 : Créer Commande Artisan de Mise à Jour

**Fichier** : `app/Console/Commands/UpdateDynamicPricing.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\DynamicPricingService;
use Illuminate\Console\Command;

class UpdateDynamicPricing extends Command
{
    protected $signature = 'pricing:update-all
                            {--site= : ID du site spécifique}
                            {--dry-run : Simulation sans mise à jour}';

    protected $description = 'Met à jour les prix dynamiques de tous les boxes';

    public function handle(DynamicPricingService $service)
    {
        $this->info('🔄 Mise à jour des prix dynamiques...');

        $sites = $this->option('site')
            ? [Site::findOrFail($this->option('site'))]
            : Site::all();

        $totalUpdated = 0;

        foreach ($sites as $site) {
            $this->line("\n📍 Site: {$site->name}");

            $occupancy = $service->getOccupancyRate($site->id);
            $this->line("   Taux occupation: {$occupancy}%");

            if ($this->option('dry-run')) {
                $recommendations = $service->getPricingRecommendations($site);
                $this->table(
                    ['Box', 'Prix Actuel', 'Prix Recommandé', 'Changement'],
                    collect($recommendations)->map(fn($r) => [
                        $r['box_number'],
                        $r['current_price'] . '€',
                        $r['recommended_price'] . '€',
                        $r['percentage_change'] . '%',
                    ])
                );
            } else {
                $updated = $service->updateSitePrices($site);
                $totalUpdated += $updated;
                $this->info("   ✓ {$updated} prix mis à jour");
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("\n✅ Total: {$totalUpdated} boxes mis à jour");
        } else {
            $this->warn("\n⚠️  Mode dry-run: aucune modification effectuée");
        }

        return 0;
    }
}
```

**Enregistrement** dans `app/Console/Kernel.php` :
```php
protected function schedule(Schedule $schedule)
{
    // Mise à jour automatique des prix tous les jours à 2h du matin
    $schedule->command('pricing:update-all')->daily()->at('02:00');
}
```

**Test** :
```bash
# Simulation
php artisan pricing:update-all --dry-run

# Exécution réelle
php artisan pricing:update-all

# Pour un site spécifique
php artisan pricing:update-all --site=1
```

### Étape 1.5 : Dashboard Revenue Management (Vue.js)

**Fichier** : `resources/js/Pages/Admin/RevenueManagement/Dashboard.vue`

```vue
<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Select from '@/Components/Select.vue';

const props = defineProps({
    sites: Array,
    selected_site_id: Number,
    revenue_gap: Object,
    occupancy_rate: Number,
    recommendations: Array,
});

const selectedSite = ref(props.selected_site_id);
const simulationPercentage = ref(0);
const simulationResult = ref(null);

const changeSite = () => {
    router.get(route('admin.revenue-management.index'), {
        site_id: selectedSite.value,
    });
};

const updatePrices = () => {
    if (!confirm('Confirmer la mise à jour des prix pour tous les boxes disponibles ?')) {
        return;
    }

    router.post(route('admin.revenue-management.update-prices', selectedSite.value), {}, {
        onSuccess: () => {
            alert('Prix mis à jour avec succès !');
        },
    });
};

const runSimulation = async () => {
    const response = await axios.post(
        route('admin.revenue-management.simulate', selectedSite.value),
        { percentage_change: simulationPercentage.value }
    );
    simulationResult.value = response.data;
};

const efficiencyColor = computed(() => {
    if (!props.revenue_gap) return 'gray';
    const eff = props.revenue_gap.efficiency_percentage;
    if (eff >= 90) return 'green';
    if (eff >= 75) return 'yellow';
    return 'red';
});
</script>

<template>
    <AdminLayout title="Revenue Management">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Revenue Management</h1>

                <div class="flex items-center gap-4">
                    <Select
                        v-model="selectedSite"
                        @change="changeSite"
                        class="w-64"
                    >
                        <option
                            v-for="site in sites"
                            :key="site.id"
                            :value="site.id"
                        >
                            {{ site.name }}
                        </option>
                    </Select>

                    <Button @click="updatePrices" variant="primary">
                        🔄 Mettre à Jour Prix
                    </Button>
                </div>
            </div>

            <!-- KPIs Grid -->
            <div class="grid grid-cols-4 gap-6 mb-6">
                <!-- Occupancy Rate -->
                <Card>
                    <div class="text-sm text-gray-500">Taux d'Occupation</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ occupancy_rate?.toFixed(1) }}%
                    </div>
                    <div class="mt-2 h-2 bg-gray-200 rounded">
                        <div
                            class="h-full bg-blue-500 rounded"
                            :style="{ width: occupancy_rate + '%' }"
                        ></div>
                    </div>
                </Card>

                <!-- Current MRR -->
                <Card>
                    <div class="text-sm text-gray-500">MRR Actuel</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ revenue_gap?.current_mrr?.toLocaleString() }}€
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ (revenue_gap?.annual_current / 1000)?.toFixed(0) }}k€/an
                    </div>
                </Card>

                <!-- Max Potential MRR -->
                <Card>
                    <div class="text-sm text-gray-500">MRR Potentiel Max</div>
                    <div class="text-3xl font-bold mt-2 text-green-600">
                        {{ revenue_gap?.max_potential_mrr?.toLocaleString() }}€
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ (revenue_gap?.annual_potential / 1000)?.toFixed(0) }}k€/an
                    </div>
                </Card>

                <!-- Revenue Gap -->
                <Card :class="`border-2 border-${efficiencyColor}-500`">
                    <div class="text-sm text-gray-500">Gap Revenus</div>
                    <div class="text-3xl font-bold mt-2 text-red-600">
                        {{ revenue_gap?.gap_mrr?.toLocaleString() }}€
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Efficacité: {{ revenue_gap?.efficiency_percentage }}%
                    </div>
                </Card>
            </div>

            <!-- Recommendations -->
            <Card class="mb-6">
                <h2 class="text-xl font-bold mb-4">
                    📊 Recommandations Pricing (Top 10)
                </h2>

                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2">Box</th>
                            <th class="text-right p-2">Prix Actuel</th>
                            <th class="text-right p-2">Prix Recommandé</th>
                            <th class="text-right p-2">Changement</th>
                            <th class="text-left p-2">Raison</th>
                            <th class="text-center p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="rec in recommendations"
                            :key="rec.box_id"
                            class="border-b hover:bg-gray-50"
                        >
                            <td class="p-2 font-medium">{{ rec.box_number }}</td>
                            <td class="p-2 text-right">{{ rec.current_price }}€</td>
                            <td class="p-2 text-right font-bold">
                                {{ rec.recommended_price }}€
                            </td>
                            <td
                                class="p-2 text-right font-bold"
                                :class="rec.action === 'increase' ? 'text-green-600' : 'text-orange-600'"
                            >
                                {{ rec.action === 'increase' ? '+' : '' }}{{ rec.percentage_change }}%
                            </td>
                            <td class="p-2 text-sm text-gray-600">{{ rec.reason }}</td>
                            <td class="p-2 text-center">
                                <span
                                    class="px-2 py-1 rounded text-xs"
                                    :class="rec.action === 'increase' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'"
                                >
                                    {{ rec.action === 'increase' ? '⬆️ Augmenter' : '⬇️ Réduire' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Card>

            <!-- Price Change Simulator -->
            <Card>
                <h2 class="text-xl font-bold mb-4">🎯 Simulateur d'Impact Prix</h2>

                <div class="flex items-center gap-4 mb-4">
                    <label class="w-48">Changement Prix Global:</label>
                    <input
                        v-model.number="simulationPercentage"
                        type="range"
                        min="-30"
                        max="30"
                        step="1"
                        class="flex-1"
                    />
                    <span class="w-16 text-right font-bold">
                        {{ simulationPercentage > 0 ? '+' : '' }}{{ simulationPercentage }}%
                    </span>
                    <Button @click="runSimulation">Simuler</Button>
                </div>

                <div v-if="simulationResult" class="grid grid-cols-3 gap-4 mt-4">
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-sm text-gray-500">MRR Actuel</div>
                        <div class="text-2xl font-bold">
                            {{ simulationResult.current_mrr.toLocaleString() }}€
                        </div>
                    </div>
                    <div class="p-4 bg-blue-50 rounded">
                        <div class="text-sm text-gray-500">MRR Projeté</div>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ simulationResult.projected_mrr.toLocaleString() }}€
                        </div>
                    </div>
                    <div class="p-4 bg-green-50 rounded">
                        <div class="text-sm text-gray-500">Impact Annuel</div>
                        <div
                            class="text-2xl font-bold"
                            :class="simulationResult.annual_impact > 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            {{ simulationResult.annual_impact > 0 ? '+' : '' }}
                            {{ simulationResult.annual_impact.toLocaleString() }}€
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
```

**Route à ajouter dans `routes/web.php`** :
```php
require __DIR__.'/admin_revenue.php';
```

---

## 🎯 Partie 2 : Intégrations Paiement (Stripe + PayPal)

### Étape 2.1 : Installation des Packages

```bash
composer require stripe/stripe-php
composer require paypal/rest-api-sdk-php
```

### Étape 2.2 : PaymentGatewayService

**Fichier** : `app/Services/PaymentGatewayService.php`

```php
<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payments\StripeHandler;
use App\Services\Payments\PayPalHandler;
use App\Services\Payments\SepaHandler;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Charge a payment
     */
    public function charge(
        Invoice $invoice,
        string $gateway,
        array $paymentDetails
    ): Payment {
        $handler = $this->getHandler($gateway);

        try {
            $charge = $handler->charge($invoice->total_ttc, array_merge($paymentDetails, [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->contract->customer_id,
            ]));

            return $this->recordPayment($invoice, $charge, $gateway);
        } catch (\Exception $e) {
            Log::error("Payment failed for invoice {$invoice->id}", [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            // Try fallback if enabled
            if (config('payments.fallback.enabled') && $gateway !== config('payments.fallback.gateway')) {
                Log::info("Trying fallback gateway: " . config('payments.fallback.gateway'));
                return $this->charge($invoice, config('payments.fallback.gateway'), $paymentDetails);
            }

            throw $e;
        }
    }

    /**
     * Get payment handler
     */
    private function getHandler(string $gateway): object
    {
        return match($gateway) {
            'stripe' => new StripeHandler(),
            'paypal' => new PayPalHandler(),
            'sepa' => new SepaHandler(),
            default => throw new \Exception("Unknown payment gateway: {$gateway}"),
        };
    }

    /**
     * Record payment in database
     */
    private function recordPayment(Invoice $invoice, object $charge, string $gateway): Payment
    {
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_ttc,
            'payment_date' => now(),
            'payment_method' => $gateway,
            'payment_reference' => $charge->id ?? $charge->getId(),
            'status' => 'completed',
        ]);

        // Update invoice status
        $invoice->update(['status' => 'paid']);

        return $payment;
    }
}
```

### Étape 2.3 : Stripe Handler

**Fichier** : `app/Services/Payments/StripeHandler.php`

```php
<?php

namespace App\Services\Payments;

use Stripe\StripeClient;

class StripeHandler implements PaymentHandlerInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('payments.gateways.stripe.secret'));
    }

    public function charge(float $amount, array $details): object
    {
        return $this->stripe->paymentIntents->create([
            'amount' => (int)($amount * 100), // Convert to cents
            'currency' => config('payments.gateways.stripe.currency'),
            'payment_method' => $details['payment_method_id'],
            'confirmation_method' => 'automatic',
            'confirm' => true,
            'metadata' => [
                'invoice_id' => $details['invoice_id'],
                'customer_id' => $details['customer_id'],
            ],
        ]);
    }

    public function setupIntent(string $customerId): string
    {
        $intent = $this->stripe->setupIntents->create([
            'customer' => $customerId,
            'payment_method_types' => ['card', 'sepa_debit'],
        ]);

        return $intent->client_secret;
    }

    public function createCustomer(array $customerData): object
    {
        return $this->stripe->customers->create([
            'email' => $customerData['email'],
            'name' => $customerData['name'],
            'metadata' => [
                'customer_id' => $customerData['id'],
            ],
        ]);
    }
}
```

**Interface** : `app/Services/Payments/PaymentHandlerInterface.php`

```php
<?php

namespace App\Services\Payments;

interface PaymentHandlerInterface
{
    public function charge(float $amount, array $details): object;
}
```

### Étape 2.4 : Webhooks Controller

**Fichier** : `app/Http/Controllers/WebhookController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('payments.gateways.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response('Webhook signature verification failed', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                Log::info('PaymentIntent succeeded', ['id' => $paymentIntent->id]);
                // Update payment status in database
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                Log::warning('PaymentIntent failed', ['id' => $paymentIntent->id]);
                // Handle failed payment
                break;

            default:
                Log::info('Unhandled Stripe event type', ['type' => $event->type]);
        }

        return response('Webhook received', 200);
    }

    public function paypal(Request $request)
    {
        // Implement PayPal webhook handling
        Log::info('PayPal webhook received', $request->all());

        return response('Webhook received', 200);
    }
}
```

**Routes** dans `routes/web.php` :
```php
// Webhooks (CSRF disabled)
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

### Étape 2.5 : Payment Selector Component (Vue)

**Fichier** : `resources/js/Components/PaymentMethodSelector.vue`

```vue
<script setup>
import { ref } from 'vue';
import { loadStripe } from '@stripe/stripe-js';

const props = defineProps({
    amount: Number,
    invoiceId: Number,
});

const emit = defineEmits(['paymentComplete']);

const selectedMethod = ref('card');
const loading = ref(false);

const methods = [
    { id: 'card', name: 'Carte Bancaire', icon: '💳' },
    { id: 'paypal', name: 'PayPal', icon: '🅿️' },
    { id: 'sepa', name: 'Prélèvement SEPA', icon: '🏦' },
];

const processPayment = async () => {
    loading.value = true;

    try {
        if (selectedMethod.value === 'card') {
            await processStripePayment();
        } else if (selectedMethod.value === 'paypal') {
            await processPayPalPayment();
        } else if (selectedMethod.value === 'sepa') {
            await processSepaPayment();
        }

        emit('paymentComplete');
    } catch (error) {
        alert('Erreur de paiement: ' + error.message);
    } finally {
        loading.value = false;
    }
};

const processStripePayment = async () => {
    const stripe = await loadStripe(import.meta.env.VITE_STRIPE_KEY);
    // Implement Stripe payment flow
    console.log('Processing Stripe payment...');
};

const processPayPalPayment = async () => {
    // Implement PayPal payment flow
    console.log('Processing PayPal payment...');
};

const processSepaPayment = async () => {
    // Implement SEPA payment flow
    console.log('Processing SEPA payment...');
};
</script>

<template>
    <div class="payment-selector">
        <h3 class="text-lg font-bold mb-4">Choisir un mode de paiement</h3>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <button
                v-for="method in methods"
                :key="method.id"
                @click="selectedMethod = method.id"
                class="p-4 border-2 rounded-lg transition"
                :class="selectedMethod === method.id
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-300 hover:border-gray-400'"
            >
                <div class="text-3xl mb-2">{{ method.icon }}</div>
                <div class="font-medium">{{ method.name }}</div>
            </button>
        </div>

        <div class="mb-4">
            <div class="text-2xl font-bold">Total: {{ amount }}€</div>
        </div>

        <button
            @click="processPayment"
            :disabled="loading"
            class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50"
        >
            {{ loading ? 'Traitement...' : `Payer ${amount}€` }}
        </button>
    </div>
</template>
```

---

## 🎯 Partie 3 : Portail Client Self-Service

### Étape 3.1 : Routes Portail Client

**Fichier** : `routes/customer.php`

```php
<?php

use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\ContractController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'customer'])->prefix('my')->name('customer.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contracts
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::post('/contracts/{contract}/terminate', [ContractController::class, 'requestTermination'])->name('contracts.terminate');
    Route::post('/contracts/{contract}/upgrade', [ContractController::class, 'requestUpgrade'])->name('contracts.upgrade');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payment-methods', [PaymentController::class, 'methods'])->name('payment-methods.index');
    Route::post('/payment-methods', [PaymentController::class, 'addMethod'])->name('payment-methods.store');
    Route::delete('/payment-methods/{method}', [PaymentController::class, 'removeMethod'])->name('payment-methods.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Support
    Route::get('/support/tickets', [SupportTicketController::class, 'index'])->name('support.tickets.index');
    Route::post('/support/tickets', [SupportTicketController::class, 'store'])->name('support.tickets.store');
    Route::get('/support/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('support.tickets.show');
});
```

### Étape 3.2 : Customer Dashboard Controller

**Fichier** : `app/Http/Controllers/Customer/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        $activeContracts = $customer->contracts()
            ->where('status', 'active')
            ->with(['box.floor.building.site'])
            ->get();

        $pendingInvoices = $customer->invoices()
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentPayments = $customer->payments()
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        $loyaltyPoints = $customer->loyalty_points;

        return Inertia::render('Customer/Dashboard', [
            'active_contracts' => $activeContracts,
            'pending_invoices' => $pendingInvoices,
            'recent_payments' => $recentPayments,
            'loyalty_points' => $loyaltyPoints,
        ]);
    }
}
```

### Étape 3.3 : Customer Dashboard Vue

**Fichier** : `resources/js/Pages/Customer/Dashboard.vue`

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    active_contracts: Array,
    pending_invoices: Array,
    recent_payments: Array,
    loyalty_points: Number,
});

const totalDue = props.pending_invoices.reduce((sum, inv) => sum + inv.total_ttc, 0);
</script>

<template>
    <CustomerLayout title="Mon Tableau de Bord">
        <div class="p-6">
            <!-- Welcome -->
            <h1 class="text-3xl font-bold mb-6">Bienvenue sur votre espace client</h1>

            <!-- KPIs -->
            <div class="grid grid-cols-4 gap-6 mb-6">
                <Card>
                    <div class="text-sm text-gray-500">Contrats Actifs</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ active_contracts.length }}
                    </div>
                </Card>

                <Card>
                    <div class="text-sm text-gray-500">Factures En Attente</div>
                    <div class="text-3xl font-bold mt-2 text-orange-600">
                        {{ pending_invoices.length }}
                    </div>
                </Card>

                <Card>
                    <div class="text-sm text-gray-500">Total À Payer</div>
                    <div class="text-3xl font-bold mt-2 text-red-600">
                        {{ totalDue.toFixed(2) }}€
                    </div>
                </Card>

                <Card>
                    <div class="text-sm text-gray-500">Points Fidélité</div>
                    <div class="text-3xl font-bold mt-2 text-blue-600">
                        {{ loyalty_points }}
                    </div>
                </Card>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <!-- Active Contracts -->
                <Card>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Mes Contrats</h2>
                        <Link
                            :href="route('customer.contracts.index')"
                            class="text-blue-600 hover:underline"
                        >
                            Voir tout
                        </Link>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="contract in active_contracts"
                            :key="contract.id"
                            class="p-3 border rounded hover:bg-gray-50"
                        >
                            <div class="font-bold">
                                Box {{ contract.box.number }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ contract.box.floor.building.site.name }}
                            </div>
                            <div class="text-sm mt-1">
                                {{ contract.price_monthly_ht }}€/mois
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Pending Invoices -->
                <Card>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Factures À Payer</h2>
                        <Link
                            :href="route('customer.invoices.index')"
                            class="text-blue-600 hover:underline"
                        >
                            Voir tout
                        </Link>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="invoice in pending_invoices"
                            :key="invoice.id"
                            class="p-3 border rounded hover:bg-gray-50 flex justify-between items-center"
                        >
                            <div>
                                <div class="font-bold">
                                    Facture #{{ invoice.invoice_number }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Échéance: {{ invoice.due_date }}
                                </div>
                            </div>
                            <div>
                                <div class="font-bold text-right">
                                    {{ invoice.total_ttc }}€
                                </div>
                                <Link
                                    :href="route('customer.invoices.pay', invoice.id)"
                                    class="text-sm text-blue-600 hover:underline"
                                >
                                    Payer →
                                </Link>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </CustomerLayout>
</template>
```

---

## 🎯 Partie 4 : Analytics Avancés

### Étape 4.1 : AnalyticsService

**Fichier** : `app/Services/AnalyticsService.php`

```php
<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Contract;
use App\Models\Box;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Get occupancy metrics for a site
     */
    public function getOccupancyMetrics(Site $site, ?Carbon $date = null): array
    {
        $date = $date ?? now();

        $cacheKey = "occupancy_metrics_{$site->id}_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 300, function () use ($site) {
            $boxes = Box::whereHas('floor.building', function ($query) use ($site) {
                $query->where('site_id', $site->id);
            })->get();

            $total = $boxes->count();
            $byStatus = $boxes->groupBy('status')->map->count();

            $available = $byStatus['available'] ?? 0;
            $reserved = $byStatus['reserved'] ?? 0;
            $rented = $byStatus['rented'] ?? 0;
            $maintenance = $byStatus['maintenance'] ?? 0;

            $occupancyRate = $total > 0 ? (($rented / $total) * 100) : 0;

            return [
                'total' => $total,
                'available' => $available,
                'reserved' => $reserved,
                'rented' => $rented,
                'maintenance' => $maintenance,
                'occupancy_rate' => round($occupancyRate, 2),
                'trend' => $this->getOccupancyTrend($site, 12),
            ];
        });
    }

    /**
     * Get occupancy trend (last N months)
     */
    private function getOccupancyTrend(Site $site, int $months = 12): array
    {
        $trend = [];
        $start = now()->subMonths($months);

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $trend[] = [
                'month' => $month->format('Y-m'),
                'rate' => $this->getHistoricalOccupancyRate($site, $month),
            ];
        }

        return $trend;
    }

    private function getHistoricalOccupancyRate(Site $site, Carbon $month): float
    {
        // This would require historical data tracking
        // For now, return current rate
        return $this->getOccupancyMetrics($site)['occupancy_rate'];
    }

    /**
     * Get revenue metrics
     */
    public function getRevenueMetrics(Site $site, Carbon $month): array
    {
        $contracts = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->where('status', 'active')->get();

        $mrr = $contracts->sum('price_monthly_ht');
        $arr = $mrr * 12;

        $totalM3 = Box::whereHas('floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->sum('size_m3');

        $revpaf = $totalM3 > 0 ? ($mrr / $totalM3) : 0;

        return [
            'mrr' => round($mrr, 2),
            'arr' => round($arr, 2),
            'by_size' => $this->groupRevenueBySizeCategory($contracts),
            'revpaf' => round($revpaf, 2),
        ];
    }

    private function groupRevenueBySizeCategory($contracts): array
    {
        $grouped = $contracts->groupBy(function ($contract) {
            $size = $contract->box->size_m3;
            if ($size < 5) return '< 5m³';
            if ($size < 10) return '5-10m³';
            if ($size < 15) return '10-15m³';
            return '> 15m³';
        });

        return $grouped->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => round($group->sum('price_monthly_ht'), 2),
            ];
        })->toArray();
    }

    /**
     * Get conversion funnel
     */
    public function getConversionFunnel(Site $site, Carbon $from, Carbon $to): array
    {
        $reservations = Reservation::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->whereBetween('created_at', [$from, $to])->count();

        $converted = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->whereBetween('created_at', [$from, $to])->count();

        $conversionRate = $reservations > 0 ? ($converted / $reservations) * 100 : 0;

        return [
            'reservations' => $reservations,
            'contracts' => $converted,
            'conversion_rate' => round($conversionRate, 2),
        ];
    }
}
```

---

## 📝 Checklist d'Implémentation Complète

### Phase 1: Revenue Management (✅ 80%)
- [x] Migrations database
- [x] Model PricingRule
- [x] DynamicPricingService
- [x] Controllers Admin
- [x] Routes admin
- [x] Seeder règles par défaut
- [ ] Dashboard Vue Revenue Management
- [ ] Command `pricing:update-all`
- [ ] Tests unitaires

### Phase 2: Paiements (✅ 40%)
- [x] Configuration `config/payments.php`
- [x] `.env.example.payments`
- [ ] Installation packages Stripe/PayPal
- [ ] PaymentGatewayService
- [ ] StripeHandler
- [ ] PayPalHandler
- [ ] WebhookController
- [ ] PaymentMethodSelector (Vue)
- [ ] Tests intégration

### Phase 3: Portail Client (✅ 20%)
- [ ] Routes `routes/customer.php`
- [ ] Middleware `customer`
- [ ] Controllers Customer/*
- [ ] Layout CustomerLayout
- [ ] Dashboard Vue
- [ ] Pages Contracts, Invoices, Profile
- [ ] Tests fonctionnels

### Phase 4: Analytics (✅ 30%)
- [ ] AnalyticsService complet
- [ ] Dashboards Vue (Occupancy, Revenue, Sales)
- [ ] Exports Excel (package maatwebsite/excel)
- [ ] Rapports planifiés (emails)
- [ ] Tests

---

## 🚀 Prochaines Actions

1. **Exécuter migrations** :
```bash
php artisan migrate
php artisan db:seed --class=DefaultPricingRulesSeeder
```

2. **Installer packages paiement** :
```bash
composer require stripe/stripe-php paypal/rest-api-sdk-php
```

3. **Configurer .env** :
```bash
cp .env.example.payments .env
# Ajouter vos clés API Stripe/PayPal
```

4. **Créer composants Vue manquants** selon les templates fournis

5. **Tester en local** :
```bash
./scripts/dev.sh
php artisan pricing:update-all --dry-run
```

---

**Document créé le** : 19 Janvier 2025
**Statut** : 🚧 EN COURS D'IMPLÉMENTATION
**Progression Globale** : 35%
