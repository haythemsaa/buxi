# 🔌 Guide d'Intégration Boxibox

Ce guide fournit des exemples de code pour intégrer Boxibox dans vos applications.

## 📋 Table des Matières

- [API Mobile](#-api-mobile)
- [Webhooks](#-webhooks)
- [Extensions Backend](#-extensions-backend)
- [Personnalisation Frontend](#-personnalisation-frontend)

---

## 📱 API Mobile

### Authentication

#### JavaScript / React Native

```javascript
// api/auth.js
const API_BASE_URL = 'http://localhost:8000/api/v1';

export const login = async (email, password) => {
  try {
    const response = await fetch(`${API_BASE_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Login failed');
    }

    // Stocker le token
    await AsyncStorage.setItem('auth_token', data.token);
    await AsyncStorage.setItem('user', JSON.stringify(data.user));

    return data;
  } catch (error) {
    console.error('Login error:', error);
    throw error;
  }
};

export const register = async (userData) => {
  const response = await fetch(`${API_BASE_URL}/register`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(userData),
  });

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || 'Registration failed');
  }

  return data;
};

export const logout = async () => {
  const token = await AsyncStorage.getItem('auth_token');

  await fetch(`${API_BASE_URL}/logout`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    },
  });

  await AsyncStorage.removeItem('auth_token');
  await AsyncStorage.removeItem('user');
};
```

#### Swift / iOS

```swift
// APIClient.swift
import Foundation

class BoxiboxAPI {
    static let shared = BoxiboxAPI()
    private let baseURL = "http://localhost:8000/api/v1"

    struct LoginRequest: Codable {
        let email: String
        let password: String
    }

    struct LoginResponse: Codable {
        let token: String
        let user: User
    }

    struct User: Codable {
        let id: Int
        let email: String
        let firstName: String
        let lastName: String
    }

    func login(email: String, password: String) async throws -> LoginResponse {
        let url = URL(string: "\(baseURL)/login")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")

        let body = LoginRequest(email: email, password: password)
        request.httpBody = try JSONEncoder().encode(body)

        let (data, response) = try await URLSession.shared.data(for: request)

        guard let httpResponse = response as? HTTPURLResponse,
              httpResponse.statusCode == 200 else {
            throw APIError.invalidResponse
        }

        let loginResponse = try JSONDecoder().decode(LoginResponse.self, from: data)

        // Stocker le token
        UserDefaults.standard.set(loginResponse.token, forKey: "auth_token")

        return loginResponse
    }
}

enum APIError: Error {
    case invalidResponse
    case networkError(Error)
}
```

#### Kotlin / Android

```kotlin
// BoxiboxApiService.kt
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.*

data class LoginRequest(
    val email: String,
    val password: String
)

data class LoginResponse(
    val token: String,
    val user: User
)

data class User(
    val id: Int,
    val email: String,
    val firstName: String,
    val lastName: String
)

interface BoxiboxApiService {
    @POST("login")
    suspend fun login(@Body request: LoginRequest): LoginResponse

    @POST("register")
    suspend fun register(@Body userData: Map<String, Any>): LoginResponse

    @POST("logout")
    suspend fun logout(@Header("Authorization") token: String)

    companion object {
        private const val BASE_URL = "http://10.0.2.2:8000/api/v1/"

        fun create(): BoxiboxApiService {
            val retrofit = Retrofit.Builder()
                .baseUrl(BASE_URL)
                .addConverterFactory(GsonConverterFactory.create())
                .build()

            return retrofit.create(BoxiboxApiService::class.java)
        }
    }
}

// Usage dans un ViewModel
class AuthViewModel : ViewModel() {
    private val api = BoxiboxApiService.create()

    fun login(email: String, password: String) {
        viewModelScope.launch {
            try {
                val response = api.login(LoginRequest(email, password))
                // Stocker le token
                saveToken(response.token)
            } catch (e: Exception) {
                // Gérer l'erreur
                handleError(e)
            }
        }
    }
}
```

### Recherche de Boxes

#### JavaScript / React

```javascript
// components/BoxSearch.jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

const BoxSearch = () => {
  const [filters, setFilters] = useState({
    site_id: '',
    min_size: '',
    max_size: '',
    max_price: '',
  });
  const [boxes, setBoxes] = useState([]);
  const [loading, setLoading] = useState(false);

  const searchBoxes = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('auth_token');
      const response = await axios.get(
        'http://localhost:8000/api/v1/boxes/available',
        {
          params: filters,
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        }
      );
      setBoxes(response.data.data);
    } catch (error) {
      console.error('Search error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="box-search">
      <div className="filters">
        <select
          value={filters.site_id}
          onChange={(e) => setFilters({ ...filters, site_id: e.target.value })}
        >
          <option value="">Tous les sites</option>
          <option value="1">Paris Nord</option>
          <option value="2">Lyon Centre</option>
        </select>

        <input
          type="number"
          placeholder="Taille min (m³)"
          value={filters.min_size}
          onChange={(e) => setFilters({ ...filters, min_size: e.target.value })}
        />

        <input
          type="number"
          placeholder="Taille max (m³)"
          value={filters.max_size}
          onChange={(e) => setFilters({ ...filters, max_size: e.target.value })}
        />

        <input
          type="number"
          placeholder="Prix max (€)"
          value={filters.max_price}
          onChange={(e) => setFilters({ ...filters, max_price: e.target.value })}
        />

        <button onClick={searchBoxes} disabled={loading}>
          {loading ? 'Recherche...' : 'Rechercher'}
        </button>
      </div>

      <div className="results">
        {boxes.map((box) => (
          <div key={box.id} className="box-card">
            <h3>Box {box.number}</h3>
            <p>Taille: {box.size_m3} m³</p>
            <p>Prix: {box.price_monthly_ht} € HT/mois</p>
            <p>Étage: {box.floor.level}</p>
            <button>Réserver</button>
          </div>
        ))}
      </div>
    </div>
  );
};

export default BoxSearch;
```

### Création de Réservation

#### JavaScript / Fetch API

```javascript
// api/reservations.js
export const createReservation = async (boxId, customerData = null) => {
  const token = localStorage.getItem('auth_token');

  const payload = {
    box_id: boxId,
    start_date: new Date().toISOString().split('T')[0],
  };

  // Si réservation invité
  if (customerData) {
    payload.guest_customer = customerData;
  }

  const response = await fetch('http://localhost:8000/api/v1/reservations', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` }),
    },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Failed to create reservation');
  }

  return await response.json();
};

// Usage
const handleReservation = async (boxId) => {
  try {
    // Réservation authentifiée
    const reservation = await createReservation(boxId);
    console.log('Reservation created:', reservation);
  } catch (error) {
    console.error('Error:', error);
  }
};

// Réservation invité
const handleGuestReservation = async (boxId) => {
  try {
    const reservation = await createReservation(boxId, {
      type: 'individual',
      first_name: 'Jean',
      last_name: 'Dupont',
      email: 'jean.dupont@example.com',
      phone: '0612345678',
      address: '123 Rue Example',
      postal_code: '75001',
      city: 'Paris',
    });
    console.log('Guest reservation created:', reservation);
  } catch (error) {
    console.error('Error:', error);
  }
};
```

---

## 🔔 Webhooks

### Configuration

```php
// config/webhooks.php
return [
    'endpoints' => [
        'payment_success' => env('WEBHOOK_PAYMENT_SUCCESS'),
        'payment_failed' => env('WEBHOOK_PAYMENT_FAILED'),
        'contract_created' => env('WEBHOOK_CONTRACT_CREATED'),
        'invoice_generated' => env('WEBHOOK_INVOICE_GENERATED'),
    ],

    'secret' => env('WEBHOOK_SECRET', ''),
];
```

### Envoi de Webhooks (Backend)

```php
// app/Services/WebhookService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function sendWebhook(string $event, array $payload): void
    {
        $url = config("webhooks.endpoints.{$event}");

        if (!$url) {
            return; // Pas de webhook configuré
        }

        $signature = $this->generateSignature($payload);

        try {
            Http::timeout(30)
                ->withHeaders([
                    'X-Boxibox-Event' => $event,
                    'X-Boxibox-Signature' => $signature,
                ])
                ->post($url, $payload);

            Log::info("Webhook sent: {$event}", ['url' => $url]);
        } catch (\Exception $e) {
            Log::error("Webhook failed: {$event}", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function generateSignature(array $payload): string
    {
        $secret = config('webhooks.secret');
        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}
```

### Réception de Webhooks (Node.js / Express)

```javascript
// webhooks.js
const express = require('express');
const crypto = require('crypto');
const router = express.Router();

const WEBHOOK_SECRET = process.env.BOXIBOX_WEBHOOK_SECRET;

// Middleware pour vérifier la signature
const verifySignature = (req, res, next) => {
  const signature = req.headers['x-boxibox-signature'];
  const payload = JSON.stringify(req.body);

  const expectedSignature = crypto
    .createHmac('sha256', WEBHOOK_SECRET)
    .update(payload)
    .digest('hex');

  if (signature !== expectedSignature) {
    return res.status(401).json({ error: 'Invalid signature' });
  }

  next();
};

// Webhook pour paiement réussi
router.post('/payment-success', verifySignature, async (req, res) => {
  const { payment, invoice } = req.body;

  console.log('Payment received:', {
    paymentId: payment.id,
    invoiceId: invoice.id,
    amount: payment.amount,
  });

  // Traiter le paiement
  await processPayment(payment);

  res.json({ received: true });
});

// Webhook pour nouveau contrat
router.post('/contract-created', verifySignature, async (req, res) => {
  const { contract, customer } = req.body;

  console.log('New contract:', {
    contractId: contract.id,
    customerId: customer.id,
    boxNumber: contract.box.number,
  });

  // Envoyer email de bienvenue personnalisé
  await sendWelcomeEmail(customer, contract);

  res.json({ received: true });
});

module.exports = router;
```

---

## 🔧 Extensions Backend

### Ajouter un Service Personnalisé

```php
// app/Services/CustomAnalyticsService.php
namespace App\Services;

use App\Models\Contract;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class CustomAnalyticsService
{
    /**
     * Calculer le taux d'occupation par site
     */
    public function getOccupancyRate(int $siteId): array
    {
        $boxes = DB::table('boxes')
            ->join('floors', 'boxes.floor_id', '=', 'floors.id')
            ->join('buildings', 'floors.building_id', '=', 'buildings.id')
            ->where('buildings.site_id', $siteId)
            ->select('boxes.status', DB::raw('COUNT(*) as count'))
            ->groupBy('boxes.status')
            ->get();

        $total = $boxes->sum('count');
        $rented = $boxes->where('status', 'rented')->first()->count ?? 0;

        return [
            'total_boxes' => $total,
            'rented_boxes' => $rented,
            'occupancy_rate' => $total > 0 ? round(($rented / $total) * 100, 2) : 0,
            'by_status' => $boxes,
        ];
    }

    /**
     * Calculer le revenu mensuel récurrent (MRR)
     */
    public function getMonthlyRecurringRevenue(): float
    {
        return Contract::where('status', 'active')
            ->sum('price_monthly_ht');
    }

    /**
     * Analyser les retards de paiement
     */
    public function getPaymentAnalytics(): array
    {
        $overdue = Invoice::where('status', 'overdue')->count();
        $pending = Invoice::where('status', 'pending')->count();
        $paid = Invoice::where('status', 'paid')->count();

        $totalOverdueAmount = Invoice::where('status', 'overdue')
            ->sum('total_ttc');

        return [
            'overdue_count' => $overdue,
            'pending_count' => $pending,
            'paid_count' => $paid,
            'overdue_amount' => $totalOverdueAmount,
        ];
    }
}
```

### Créer un Middleware Personnalisé

```php
// app/Http/Middleware/CheckSiteAccess.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSiteAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $siteId = $request->route('site');

        // Vérifier si l'utilisateur a accès à ce site
        if (!$user->hasAccessToSite($siteId)) {
            return response()->json([
                'message' => 'Access denied to this site',
            ], 403);
        }

        return $next($request);
    }
}

// Enregistrer dans app/Http/Kernel.php
protected $middlewareAliases = [
    // ...
    'site.access' => \App\Http\Middleware\CheckSiteAccess::class,
];

// Utiliser dans les routes
Route::middleware(['auth:sanctum', 'site.access'])
    ->get('/sites/{site}/analytics', [AnalyticsController::class, 'show']);
```

### Observer Personnalisé

```php
// app/Observers/ContractObserver.php
namespace App\Observers;

use App\Models\Contract;
use App\Notifications\ContractActivated;
use App\Services\WebhookService;

class ContractObserver
{
    public function __construct(
        private WebhookService $webhookService
    ) {}

    public function created(Contract $contract): void
    {
        // Envoyer une notification au client
        if ($contract->customer) {
            $contract->customer->notify(new ContractActivated($contract));
        }

        // Déclencher un webhook
        $this->webhookService->sendWebhook('contract_created', [
            'contract' => $contract->load(['customer', 'box', 'site']),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function updated(Contract $contract): void
    {
        // Détecter les changements de statut
        if ($contract->wasChanged('status')) {
            $this->handleStatusChange($contract);
        }
    }

    private function handleStatusChange(Contract $contract): void
    {
        match ($contract->status) {
            'terminated' => $this->handleTermination($contract),
            'suspended' => $this->handleSuspension($contract),
            default => null,
        };
    }

    private function handleTermination(Contract $contract): void
    {
        // Libérer le box
        $contract->box->update(['status' => 'available']);

        // Envoyer une notification
        $this->webhookService->sendWebhook('contract_terminated', [
            'contract_id' => $contract->id,
            'box_id' => $contract->box_id,
        ]);
    }

    private function handleSuspension(Contract $contract): void
    {
        // Logique de suspension
        $contract->box->update(['status' => 'maintenance']);
    }
}

// Enregistrer dans app/Providers/AppServiceProvider.php
use App\Models\Contract;
use App\Observers\ContractObserver;

public function boot(): void
{
    Contract::observe(ContractObserver::class);
}
```

---

## 🎨 Personnalisation Frontend

### Composant Vue.js Personnalisé

```vue
<!-- resources/js/Components/BoxAvailabilityCalendar.vue -->
<template>
  <div class="availability-calendar">
    <h3>Disponibilité du Box {{ boxNumber }}</h3>

    <div class="calendar-grid">
      <div
        v-for="day in days"
        :key="day.date"
        :class="['day', {
          'available': day.available,
          'reserved': day.reserved,
          'rented': day.rented
        }]"
      >
        <span class="date">{{ formatDate(day.date) }}</span>
        <span class="status">{{ day.status }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  boxId: {
    type: Number,
    required: true,
  },
  boxNumber: {
    type: String,
    required: true,
  },
});

const days = ref([]);

const fetchAvailability = async () => {
  try {
    const response = await axios.get(`/api/v1/boxes/${props.boxId}/availability`);
    days.value = response.data.days;
  } catch (error) {
    console.error('Failed to fetch availability:', error);
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
  });
};

onMounted(() => {
  fetchAvailability();
});
</script>

<style scoped>
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.day {
  padding: 12px;
  border-radius: 8px;
  text-align: center;
}

.day.available {
  background-color: #d4edda;
  border: 2px solid #28a745;
}

.day.reserved {
  background-color: #fff3cd;
  border: 2px solid #ffc107;
}

.day.rented {
  background-color: #f8d7da;
  border: 2px solid #dc3545;
}
</style>
```

### Hook React Personnalisé

```javascript
// hooks/useBoxSearch.js
import { useState, useCallback } from 'react';
import axios from 'axios';

export const useBoxSearch = () => {
  const [boxes, setBoxes] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const searchBoxes = useCallback(async (filters) => {
    setLoading(true);
    setError(null);

    try {
      const token = localStorage.getItem('auth_token');
      const response = await axios.get('/api/v1/boxes/available', {
        params: filters,
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      setBoxes(response.data.data);
      return response.data.data;
    } catch (err) {
      setError(err.response?.data?.message || 'Search failed');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  const compareBoxes = useCallback((boxIds) => {
    return boxes.filter((box) => boxIds.includes(box.id));
  }, [boxes]);

  return {
    boxes,
    loading,
    error,
    searchBoxes,
    compareBoxes,
  };
};

// Usage
import { useBoxSearch } from './hooks/useBoxSearch';

function BoxSearchComponent() {
  const { boxes, loading, error, searchBoxes } = useBoxSearch();

  const handleSearch = async () => {
    await searchBoxes({
      site_id: 1,
      min_size: 5,
      max_size: 15,
    });
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <button onClick={handleSearch}>Search</button>
      {boxes.map((box) => (
        <BoxCard key={box.id} box={box} />
      ))}
    </div>
  );
}
```

---

## 📚 Ressources Supplémentaires

- **API Documentation**: Voir `API_MOBILE.md`
- **Architecture**: Voir `README.md` section Architecture
- **Tests**: Voir `tests/` pour des exemples de tests

## 🆘 Support

Pour toute question d'intégration :
1. Consultez la documentation de l'API
2. Vérifiez les exemples de tests dans `tests/Feature/`
3. Ouvrez une issue sur GitHub
