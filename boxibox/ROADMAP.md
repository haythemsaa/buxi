# 🗺️ Roadmap Boxibox 2025
## Plan de Développement Produit

---

## 🎯 Vision

**Objectif 2025** : Devenir la référence SaaS self-stockage en Europe francophone avec :
- 💰 +30% revenus par box vs marché
- ⚡ 100% automation (location sans contact)
- 📱 Mobile-first experience
- 🤖 IA pour pricing & support
- 🌍 Expansion internationale

---

## 📅 Timeline 2025

```
Q1 (Jan-Mar)  │ Q2 (Apr-Jun)  │ Q3 (Jul-Sep)  │ Q4 (Oct-Dec)
──────────────┼───────────────┼───────────────┼──────────────
Phase 1       │ Phase 2A      │ Phase 2B      │ Phase 3
Quick Wins    │ Automation    │ Mobile Native │ Premium
+20% revenue  │ -40% costs    │ UX moderne    │ Market leader
```

---

## 🚀 Phase 1 : Quick Wins & Revenue Boost
**Q1 2025 (Janvier - Mars) | 6-7 semaines**

### 🎯 Objectifs
- ✅ +20-30% revenus immédiat
- ✅ -50% tickets support
- ✅ Décisions data-driven
- ✅ Meilleure conversion

---

### 1️⃣ Revenue Management Dynamique (2 semaines)
**Priorité** : 🔴 CRITIQUE | **ROI** : ★★★★★

#### Fonctionnalités
```
✨ Pricing Dynamique Basique
  - Règles par taux occupation (3 paliers)
    • < 70% : Prix attractifs (-10%)
    • 70-85% : Prix normal
    • > 85% : Prix premium (+15-25%)

  - Ajustements saisonniers
    • Haute saison (été) : +10%
    • Basse saison (hiver) : -10%
    • Événements locaux : +20%

  - Prix différenciés par durée
    • 1-3 mois : Prix standard
    • 6 mois : -5%
    • 12+ mois : -10%

✨ Dashboard Revenue Management
  - Current vs Max Revenue (gap analysis)
  - Recommandations prix par box
  - Prévisions revenus 30/60/90j
  - Impact simulations prix

✨ A/B Testing Prix
  - Test 2 prix simultanés
  - Mesure taux conversion
  - Application automatique meilleur
```

#### Implémentation Technique

**Nouveau Model**
```php
// app/Models/PricingRule.php
class PricingRule extends Model {
    protected $fillable = [
        'site_id',
        'box_size_min',
        'box_size_max',
        'occupancy_threshold_min',
        'occupancy_threshold_max',
        'season',  // winter, spring, summer, fall
        'duration_months_min',
        'adjustment_type',  // percentage, fixed
        'adjustment_value',
        'priority',
        'is_active',
    ];
}
```

**Service Pricing**
```php
// app/Services/DynamicPricingService.php
class DynamicPricingService {
    public function calculateOptimalPrice(Box $box): float
    {
        $basePrice = $box->price_monthly_ht;
        $occupancyRate = $this->getOccupancyRate($box->site_id);
        $season = $this->getCurrentSeason();

        $rules = PricingRule::applicable($box, $occupancyRate, $season)->get();

        foreach ($rules->sortByDesc('priority') as $rule) {
            $basePrice = $this->applyRule($basePrice, $rule);
        }

        return $basePrice;
    }

    public function getRevenueGap(Site $site): array
    {
        $currentRevenue = $site->contracts()->active()->sum('price_monthly_ht');
        $maxRevenue = $this->calculateMaxPotentialRevenue($site);

        return [
            'current' => $currentRevenue,
            'max' => $maxRevenue,
            'gap' => $maxRevenue - $currentRevenue,
            'efficiency' => ($currentRevenue / $maxRevenue) * 100,
        ];
    }
}
```

**API Endpoint**
```php
// routes/api.php
Route::post('/boxes/{box}/optimal-price', [BoxController::class, 'getOptimalPrice']);
Route::get('/sites/{site}/revenue-analysis', [SiteController::class, 'revenueAnalysis']);
```

**Dashboard Vue Component**
```vue
<!-- resources/js/Pages/Dashboard/RevenueManagement.vue -->
<template>
  <div class="revenue-dashboard">
    <KPI :value="currentRevenue" :max="maxRevenue" label="Revenue Efficiency" />
    <RecommendationsList :boxes="underperformingBoxes" />
    <PricingSimulator />
  </div>
</template>
```

**Migration**
```bash
php artisan make:migration create_pricing_rules_table
php artisan make:migration add_dynamic_pricing_to_boxes_table
```

---

### 2️⃣ Intégrations Paiement (1 semaine)
**Priorité** : 🔴 CRITIQUE | **ROI** : ★★★★★

#### Fonctionnalités
```
✨ Stripe Integration
  - Cartes crédit/débit
  - Apple Pay / Google Pay
  - Wallets (Alipay, WeChat Pay)
  - SEPA Direct Debit
  - Save cards pour récurrence
  - 3D Secure automatique

✨ PayPal Integration
  - Express Checkout
  - PayPal Credit
  - Abonnements PayPal

✨ Multi-Gateway Support
  - Fallback automatique
  - Routing intelligent par pays
  - Gestion frais par gateway
```

#### Implémentation Technique

**Packages**
```bash
composer require stripe/stripe-php
composer require paypal/rest-api-sdk-php
```

**Configuration**
```php
// config/payments.php
return [
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    'gateways' => [
        'stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'),
        ],
        'sepa' => [
            // Existing SEPA config
        ],
    ],
];
```

**Payment Service**
```php
// app/Services/PaymentGatewayService.php
class PaymentGatewayService {
    public function charge(
        Invoice $invoice,
        string $gateway,
        array $paymentDetails
    ): Payment {
        $handler = $this->getHandler($gateway);

        try {
            $charge = $handler->charge($invoice->total_ttc, $paymentDetails);

            return $this->recordPayment($invoice, $charge, $gateway);
        } catch (PaymentException $e) {
            $this->handleFailure($invoice, $e);
            throw $e;
        }
    }

    private function getHandler(string $gateway): PaymentHandlerInterface
    {
        return match($gateway) {
            'stripe' => new StripeHandler(),
            'paypal' => new PayPalHandler(),
            'sepa' => new SepaHandler(),
            default => throw new \Exception("Unknown gateway: {$gateway}"),
        };
    }
}
```

**Stripe Handler**
```php
// app/Services/Payments/StripeHandler.php
class StripeHandler implements PaymentHandlerInterface {
    public function charge(float $amount, array $details): object
    {
        \Stripe\Stripe::setApiKey(config('payments.gateways.stripe.secret'));

        return \Stripe\PaymentIntent::create([
            'amount' => $amount * 100, // cents
            'currency' => 'eur',
            'payment_method' => $details['payment_method_id'],
            'confirmation_method' => 'automatic',
            'confirm' => true,
            'metadata' => [
                'invoice_id' => $details['invoice_id'],
                'customer_id' => $details['customer_id'],
            ],
        ]);
    }

    public function setupIntent(Customer $customer): string
    {
        $intent = \Stripe\SetupIntent::create([
            'customer' => $customer->stripe_id,
            'payment_method_types' => ['card', 'sepa_debit'],
        ]);

        return $intent->client_secret;
    }
}
```

**API Routes**
```php
Route::post('/payments/setup-intent', [PaymentController::class, 'setupIntent']);
Route::post('/payments/charge', [PaymentController::class, 'charge']);
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe']);
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal']);
```

**Frontend Component (Vue)**
```vue
<!-- Payment Method Selector -->
<template>
  <div class="payment-methods">
    <button @click="selectMethod('card')">
      <CreditCardIcon /> Carte bancaire
    </button>
    <button @click="selectMethod('paypal')">
      <PayPalIcon /> PayPal
    </button>
    <button @click="selectMethod('sepa')">
      <BankIcon /> Prélèvement SEPA
    </button>

    <StripeElements v-if="method === 'card'" @complete="handleStripe" />
    <PayPalButton v-if="method === 'paypal'" @complete="handlePayPal" />
  </div>
</template>
```

---

### 3️⃣ Portail Client Self-Service (2 semaines)
**Priorité** : 🟠 HAUTE | **ROI** : ★★★★☆

#### Fonctionnalités
```
✨ Dashboard Client Web
  - Vue d'ensemble contrats actifs
  - Prochaines échéances
  - Factures impayées
  - Points fidélité
  - Historique accès (si smart locks)

✨ Gestion Contrats
  - Détails contrat
  - Téléchargement PDF
  - Demande résiliation
  - Demande changement box (upgrade/downgrade)
  - Prolongation automatique

✨ Gestion Paiements
  - Paiement factures en ligne
  - Enregistrement moyens paiement
  - Historique paiements
  - Téléchargement factures PDF
  - Mise à jour IBAN SEPA

✨ Profil & Préférences
  - Modification infos personnelles
  - Ajout bénéficiaires (accès partagé)
  - Préférences notifications
  - Changement mot de passe
  - 2FA activation

✨ Support
  - Tickets support
  - Chat en direct (heures bureau)
  - FAQ interactive
  - Demandes services
```

#### Structure Routes
```php
// routes/web.php - Customer Portal
Route::middleware(['auth:sanctum', 'customer'])->prefix('my')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index']);

    Route::get('/contracts', [CustomerContractController::class, 'index']);
    Route::get('/contracts/{contract}', [CustomerContractController::class, 'show']);
    Route::get('/contracts/{contract}/download', [CustomerContractController::class, 'download']);
    Route::post('/contracts/{contract}/terminate', [CustomerContractController::class, 'requestTermination']);
    Route::post('/contracts/{contract}/upgrade', [CustomerContractController::class, 'requestUpgrade']);

    Route::get('/invoices', [CustomerInvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}/download', [CustomerInvoiceController::class, 'download']);
    Route::post('/invoices/{invoice}/pay', [CustomerInvoiceController::class, 'pay']);

    Route::get('/payments', [CustomerPaymentController::class, 'index']);
    Route::get('/payment-methods', [CustomerPaymentController::class, 'methods']);
    Route::post('/payment-methods', [CustomerPaymentController::class, 'addMethod']);
    Route::delete('/payment-methods/{method}', [CustomerPaymentController::class, 'removeMethod']);

    Route::get('/profile', [CustomerProfileController::class, 'edit']);
    Route::put('/profile', [CustomerProfileController::class, 'update']);
    Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword']);

    Route::get('/support/tickets', [SupportTicketController::class, 'index']);
    Route::post('/support/tickets', [SupportTicketController::class, 'store']);
});
```

#### Vue Components
```
resources/js/Pages/Customer/
├── Dashboard.vue
├── Contracts/
│   ├── Index.vue
│   ├── Show.vue
│   └── RequestChange.vue
├── Invoices/
│   ├── Index.vue
│   └── Pay.vue
├── Payments/
│   ├── History.vue
│   └── Methods.vue
├── Profile/
│   ├── Edit.vue
│   └── Security.vue
└── Support/
    ├── Tickets.vue
    └── CreateTicket.vue
```

---

### 4️⃣ Analytics & Dashboards Avancés (1.5 semaines)
**Priorité** : 🟠 HAUTE | **ROI** : ★★★★☆

#### Fonctionnalités
```
✨ Dashboard Occupancy
  - Taux occupation temps réel
  - Breakdown par statut (available, reserved, rented, maintenance)
  - Heatmap bâtiments
  - Tendances vs année précédente
  - Alertes seuils (<70%, >95%)

✨ Dashboard Revenue
  - MRR (Monthly Recurring Revenue)
  - ARR (Annual Recurring Revenue)
  - Revenue par site/bâtiment/taille
  - RevPAF (Revenue Per Available Foot)
  - Analyse cohorts clients

✨ Dashboard Marketing & Sales
  - Funnel conversion complet
    • Visiteurs site → Leads
    • Leads → Réservations
    • Réservations → Contrats
  - Taux conversion par étape
  - Cost Per Acquisition
  - Temps réponse moyen leads
  - Sources acquisition

✨ Dashboard Operations
  - Coûts opérationnels par site
  - Expense Ratio
  - Net Operating Income (NOI)
  - Length of Stay moyen
  - Customer Lifetime Value

✨ Exports & Rapports
  - Exports Excel personnalisés
  - Rapports planifiés (email automatique)
  - Filtres avancés
  - Graphiques interactifs
```

#### Implémentation

**Analytics Service**
```php
// app/Services/AnalyticsService.php
class AnalyticsService {
    public function getOccupancyMetrics(Site $site, Carbon $date): array
    {
        $boxes = $site->boxes();

        return [
            'total' => $boxes->count(),
            'available' => $boxes->where('status', 'available')->count(),
            'reserved' => $boxes->where('status', 'reserved')->count(),
            'rented' => $boxes->where('status', 'rented')->count(),
            'maintenance' => $boxes->where('status', 'maintenance')->count(),
            'occupancy_rate' => $this->calculateOccupancyRate($site),
            'trend' => $this->getOccupancyTrend($site, $date),
        ];
    }

    public function getRevenueMetrics(Site $site, Carbon $month): array
    {
        $contracts = $site->contracts()->active();

        $mrr = $contracts->sum('price_monthly_ht');
        $arr = $mrr * 12;

        return [
            'mrr' => $mrr,
            'arr' => $arr,
            'by_size' => $this->groupRevenueBySizeCategory($contracts),
            'revpaf' => $this->calculateRevPAF($site),
            'noi' => $this->calculateNOI($site, $month),
        ];
    }

    public function getConversionFunnel(Site $site, Carbon $from, Carbon $to): array
    {
        return [
            'visitors' => $this->getVisitors($site, $from, $to),
            'leads' => $this->getLeads($site, $from, $to),
            'reservations' => $this->getReservations($site, $from, $to),
            'contracts' => $this->getContracts($site, $from, $to),
            'conversion_rates' => $this->calculateConversionRates(...),
        ];
    }
}
```

**Dashboard Components**
```vue
<!-- resources/js/Pages/Analytics/OccupancyDashboard.vue -->
<template>
  <div class="analytics-dashboard">
    <div class="metrics-grid">
      <MetricCard
        title="Taux Occupation"
        :value="occupancyRate"
        :trend="trend"
        format="percentage"
      />
      <MetricCard
        title="Boxes Disponibles"
        :value="available"
        :total="total"
      />
    </div>

    <HeatmapBuilding :site="site" :data="occupancyData" />

    <Chart
      type="line"
      :data="trendData"
      title="Tendance Occupation 12 mois"
    />

    <StatusBreakdown :data="statusData" />
  </div>
</template>
```

**Scheduled Reports**
```php
// app/Console/Commands/SendWeeklyReport.php
class SendWeeklyReport extends Command {
    public function handle(AnalyticsService $analytics) {
        $sites = Site::all();

        foreach ($sites as $site) {
            $report = $analytics->generateWeeklyReport($site);

            Mail::to($site->manager_email)
                ->send(new WeeklyPerformanceReport($report));
        }
    }
}

// Register in Kernel.php
$schedule->command('reports:weekly')->weekly()->mondays()->at('08:00');
```

---

## ⚡ Phase 2A : Automation & Smart Access
**Q2 2025 (Avril - Juin) | 6-8 semaines**

### 🎯 Objectifs
- ✅ -40% coûts staff
- ✅ Location 24/7 sans contact
- ✅ +15% conversions (CRM automation)
- ✅ Sécurité renforcée

---

### 5️⃣ CRM & Marketing Automation (3 semaines)
**Priorité** : 🔴 CRITIQUE | **ROI** : ★★★★☆

#### Fonctionnalités
```
✨ Lead Management
  - Capture automatique leads (formulaires, chat)
  - Lead scoring (hot/warm/cold)
  - Auto-attribution à agents
  - Alertes nouvea leads (SMS/email)

✨ Nurturing Automatisé
  - Séquences email drip
  - Triggers comportementaux
  - Personnalisation 1-to-1
  - A/B testing automatique

✨ Chatbot FAQ
  - Réponses automatiques questions courantes
  - Escalade vers humain si besoin
  - Disponible 24/7
  - Multi-langues (FR/EN)

✨ SMS Notifications
  - Rappels paiement
  - Codes accès
  - Confirmations réservation
  - Promotions géolocalisées

✨ Retention & Upsell
  - Détection risque churn
  - Offres personnalisées
  - Programme parrainage automatisé
  - Win-back campaigns
```

#### Stack Technique
```
- Twilio (SMS, WhatsApp)
- Mailchimp/SendGrid (email marketing)
- Intercom/Drift (chatbot)
- Zapier/Make (workflows)
```

---

### 6️⃣ Smart Access Control Integration (4 semaines)
**Priorité** : 🔴 CRITIQUE | **ROI** : ★★★★★

#### Fonctionnalités
```
✨ Serrures Intelligentes
  - Intégration Nokē API
  - Accès Bluetooth/QR code
  - Auto-lock impayés
  - Gestion temporelle accès

✨ Mobile App Access
  - Ouverture via app
  - Partage accès temporaire
  - Historique accès
  - Notifications ouverture

✨ Workflow Automation
  - Activation accès post-paiement
  - Désactivation automatique fin contrat
  - Alerts sécurité (accès non autorisé)
  - Integration rappels paiement
```

---

## 📱 Phase 2B : Mobile Native & Predictive IA
**Q2-Q3 2025 (Mai - Septembre) | 8-10 semaines**

### 7️⃣ Mobile App Native (6 semaines)
**Priorité** : 🟠 HAUTE | **ROI** : ★★★☆☆

#### Technologies
- React Native (iOS + Android)
- Expo (rapid development)
- Redux (state management)

#### Fonctionnalités
```
✨ Core Features
  - Login / Register
  - Recherche boxes
  - Réservation en ligne
  - Paiement in-app
  - Accès smart locks
  - Notifications push

✨ Advanced Features
  - Visite virtuelle 360°
  - Calculateur espace AR
  - Chat support
  - Géolocalisation sites
  - Multi-comptes (famille)
```

---

### 8️⃣ Predictive Analytics IA (3 semaines)
**Priorité** : 🟡 MOYENNE | **ROI** : ★★★☆☆

#### Fonctionnalités
```
✨ Prévisions Occupation
  - ML models (Prophet, ARIMA)
  - Prévisions 30/60/90 jours
  - Facteurs saisonnalité

✨ Churn Prediction
  - Score risque départ 0-100
  - Interventions proactives
  - Offres rétention automatiques

✨ Revenue Optimization IA
  - Recommandations prix ML
  - Simulation impact prix
  - Optimisation yield automatique
```

---

## 🏆 Phase 3 : Premium Features & Scale
**Q3-Q4 2025 (Juillet - Décembre) | 16-20 semaines**

### 9️⃣ IoT & Advanced Security (4 semaines)
### 🔟 White Label & Multi-Tenant SaaS (6 semaines)
### 1️⃣1️⃣ Marketplace Intégrations (4 semaines)
### 1️⃣2️⃣ Staff & Operations Management (4 semaines)

---

## 📊 KPIs de Succès

### Phase 1
- [ ] Revenue / box : +20% vs baseline
- [ ] Tickets support : -50%
- [ ] Conversion réservation→contrat : >60%
- [ ] Time to insight (data) : <5 min

### Phase 2
- [ ] Staff costs : -40%
- [ ] Lead response time : <5 min
- [ ] Mobile app downloads : 1000+
- [ ] Smart lock adoption : 80%+ boxes

### Phase 3
- [ ] NPS Score : >50
- [ ] Market position : Top 5 Europe
- [ ] White label clients : 10+
- [ ] Revenue growth : 3x vs 2024

---

## 🚦 Gestion des Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Intégration smart locks complexe | Moyenne | Haut | POC 1 site pilote avant déploiement |
| Adoption mobile faible | Moyenne | Moyen | Beta testing, incentives early adopters |
| ML pricing sous-performe | Faible | Moyen | Fallback rules-based, monitoring continu |
| Résistance utilisateurs changement | Haute | Moyen | Formation, onboarding progressif |

---

## 💰 Budget Estimé 2025

### Phase 1 (Q1)
- Développement : 160-200h → 12-18k€
- Intégrations (Stripe, PayPal) : 2k€
- Total : **14-20k€**

### Phase 2 (Q2-Q3)
- CRM/Marketing automation : 8k€
- Smart locks integration : 12k€
- Mobile app development : 25k€
- ML/IA development : 10k€
- Total : **55k€**

### Phase 3 (Q3-Q4)
- IoT/Security : 15k€
- White label : 20k€
- Marketplace : 12k€
- Operations : 10k€
- Total : **57k€**

**Budget Total 2025** : **126-132k€**
**ROI Attendu Année 1** : **+300-500k€** (pour 500 boxes à 100€/mois)

---

## 👥 Ressources Nécessaires

### Phase 1-2
- 1x Senior Backend Dev (Laravel)
- 1x Frontend Dev (Vue.js)
- 1x Designer UX/UI (part-time)
- 1x DevOps (part-time)

### Phase 3
- +1x Mobile Dev (React Native)
- +1x Data Scientist (ML)
- +1x Product Manager

---

## 📅 Prochaines Actions Immédiates

### Semaine 1-2 (Sprint 1)
```
[ ] Setup tracking analytics actuel (baseline)
[ ] Audit technique code existant
[ ] Définir architecture pricing dynamique
[ ] POC algorithme pricing basique
[ ] Design mockups portail client
```

### Semaine 3-4 (Sprint 2)
```
[ ] Implémentation pricing dynamique
[ ] Tests A/B pricing
[ ] Dashboard revenue management
[ ] Intégration Stripe SDK
[ ] Webhooks Stripe
```

### Semaine 5-6 (Sprint 3)
```
[ ] PayPal integration
[ ] Portail client (backend)
[ ] Portail client (frontend)
[ ] Tests utilisateurs portail
```

### Semaine 7 (Sprint 4)
```
[ ] Analytics dashboards
[ ] Exports Excel
[ ] Tests end-to-end Phase 1
[ ] Documentation
[ ] Déploiement production
```

---

**Document créé le** : 19 Janvier 2025
**Statut** : 📋 DRAFT - En attente validation
**Prochaine révision** : Après validation Phase 1
