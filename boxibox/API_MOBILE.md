# API Mobile Boxibox - Documentation

API REST pour l'application mobile des locataires de box de stockage.

## 📱 Base URL

```
Production: https://api.boxibox.fr/api/v1
Development: http://localhost:8000/api/v1
```

## 🔐 Authentification

L'API utilise Laravel Sanctum pour l'authentification par tokens.

### Login

**POST** `/login`

Authentifie un client et retourne un token d'accès.

**Body:**
```json
{
  "email": "client@example.com",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "token": "1|abc123def456...",
  "customer": {
    "id": 1,
    "customer_number": "CL000001",
    "type": "individual",
    "name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "0612345678"
  }
}
```

**Response 401:**
```json
{
  "message": "Les identifiants fournis sont incorrects.",
  "errors": {
    "email": ["Les identifiants fournis sont incorrects."]
  }
}
```

**Response 403:**
```json
{
  "message": "Votre compte est inactif. Veuillez contacter le support."
}
```

---

### Logout

**POST** `/logout`

Déconnecte l'utilisateur et révoque le token actuel.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "message": "Déconnexion réussie"
}
```

---

## 👤 Profil

### Get Profile

**GET** `/me`

Récupère les informations du profil de l'utilisateur connecté.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "customer": {
    "id": 1,
    "customer_number": "CL000001",
    "type": "individual",
    "name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "0612345678",
    "phone_secondary": "0123456789",
    "address": "123 Rue de la Paix",
    "postal_code": "75001",
    "city": "Paris",
    "country": "France",
    "status": "active"
  }
}
```

---

### Update Profile

**PUT** `/profile`

Met à jour les informations du profil.

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
  "phone": "0612345678",
  "phone_secondary": "0123456789",
  "address": "123 Rue de la Paix",
  "postal_code": "75001",
  "city": "Paris",
  "country": "France"
}
```

**Response 200:**
```json
{
  "message": "Profil mis à jour avec succès",
  "customer": { /* customer object */ }
}
```

---

### Update Password

**PUT** `/profile/password`

Change le mot de passe de l'utilisateur.

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
  "current_password": "oldpassword",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

**Response 200:**
```json
{
  "message": "Mot de passe mis à jour avec succès. Veuillez vous reconnecter."
}
```

**Response 422:**
```json
{
  "message": "Le mot de passe actuel est incorrect",
  "errors": {
    "current_password": ["Le mot de passe actuel est incorrect"]
  }
}
```

---

### Get Statistics

**GET** `/profile/statistics`

Récupère les statistiques du client.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "statistics": {
    "active_contracts": 2,
    "total_contracts": 3,
    "total_paid": 1250.50,
    "pending_invoices": 1,
    "overdue_invoices": 0
  }
}
```

---

## 📋 Contrats

### List Contracts

**GET** `/contracts`

Liste tous les contrats du client.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "contracts": [
    {
      "id": 1,
      "contract_number": "CO00000001",
      "start_date": "2024-01-01",
      "end_date": null,
      "status": "active",
      "status_label": "Actif",
      "total_monthly_amount": 125.50,
      "payment_method": "sepa",
      "payment_day": 5,
      "access_code": "1234",
      "box": {
        "id": 10,
        "number": "A-101",
        "volume": 10,
        "surface": 5.5,
        "floor": "Rez-de-chaussée",
        "building": "Bâtiment A",
        "site": "Boxibox Paris Nord",
        "site_address": "123 Avenue du Stockage",
        "site_city": "Paris"
      }
    }
  ]
}
```

---

### Get Contract Details

**GET** `/contracts/{id}`

Récupère les détails complets d'un contrat.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "contract": {
    "id": 1,
    "contract_number": "CO00000001",
    "start_date": "2024-01-01",
    "end_date": null,
    "status": "active",
    "status_label": "Actif",
    "initial_duration_months": 12,
    "price_monthly_ht": 104.58,
    "tax_rate": 20,
    "insurance_monthly": 0,
    "total_monthly_amount": 125.50,
    "deposit_amount": 250,
    "payment_method": "sepa",
    "payment_method_label": "Prélèvement SEPA",
    "payment_day": 5,
    "access_code": "1234",
    "notes": null,
    "box": {
      "id": 10,
      "number": "A-101",
      "volume": 10,
      "surface": 5.5,
      "length": 2.5,
      "width": 2.2,
      "height": 2.0,
      "climate_controlled": true,
      "ground_floor": true,
      "vehicle_access": true,
      "has_electricity": false,
      "floor": "Rez-de-chaussée",
      "building": "Bâtiment A",
      "site": {
        "id": 1,
        "name": "Boxibox Paris Nord",
        "address": "123 Avenue du Stockage",
        "postal_code": "75018",
        "city": "Paris",
        "phone": "0140000000",
        "email": "paris@boxibox.fr",
        "gps_latitude": 48.8566,
        "gps_longitude": 2.3522
      }
    }
  }
}
```

**Response 404:**
```json
{
  "message": "Contrat non trouvé"
}
```

---

## 🧾 Factures

### List Invoices

**GET** `/invoices`

Liste toutes les factures du client.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "invoices": [
    {
      "id": 1,
      "invoice_number": "INV-2024-001",
      "invoice_date": "2024-01-01",
      "due_date": "2024-01-15",
      "total_ht": 104.58,
      "tax_amount": 20.92,
      "total_ttc": 125.50,
      "paid_amount": 125.50,
      "remaining_amount": 0,
      "status": "paid",
      "status_label": "Payée",
      "contract_number": "CO00000001"
    }
  ]
}
```

---

### Get Invoice Details

**GET** `/invoices/{id}`

Récupère les détails complets d'une facture.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "invoice": {
    "id": 1,
    "invoice_number": "INV-2024-001",
    "invoice_date": "2024-01-01",
    "due_date": "2024-01-15",
    "paid_at": "2024-01-05T10:30:00Z",
    "total_ht": 104.58,
    "tax_rate": 20,
    "tax_amount": 20.92,
    "total_ttc": 125.50,
    "paid_amount": 125.50,
    "remaining_amount": 0,
    "status": "paid",
    "status_label": "Payée",
    "notes": null,
    "line_items": [
      {
        "description": "Location box A-101 - Janvier 2024",
        "quantity": 1,
        "unit_price": 104.58,
        "total": 104.58
      }
    ],
    "contract": {
      "contract_number": "CO00000001",
      "box_number": "A-101"
    },
    "payments": [
      {
        "id": 1,
        "payment_number": "PAY-2024-001",
        "amount": 125.50,
        "payment_date": "2024-01-05",
        "method": "sepa",
        "method_label": "Prélèvement SEPA",
        "status": "succeeded",
        "status_label": "Réussi"
      }
    ]
  }
}
```

**Response 404:**
```json
{
  "message": "Facture non trouvée"
}
```

---

### Download Invoice PDF

**GET** `/invoices/{id}/download`

Télécharge le PDF de la facture.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "message": "Téléchargement de facture PDF - À implémenter",
  "invoice_id": 1
}
```

> **Note:** La génération de PDF sera implémentée dans une version ultérieure.

---

## 📊 Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 401 | Unauthorized - Token invalide ou absent |
| 403 | Forbidden - Compte inactif ou accès refusé |
| 404 | Not Found - Ressource introuvable |
| 422 | Validation Error - Données invalides |
| 500 | Server Error |

---

## 🔒 Sécurité

- Tous les endpoints (sauf `/login`) requièrent un token Bearer
- Les tokens sont générés par l'endpoint `/login`
- Les tokens sont stockés de manière sécurisée avec Sanctum
- Un client ne peut accéder qu'à ses propres données
- Le changement de mot de passe révoque tous les tokens existants

---

## 📝 Exemples d'utilisation

### iOS (Swift)

```swift
// Login
let loginURL = URL(string: "http://localhost:8000/api/v1/login")!
var request = URLRequest(url: loginURL)
request.httpMethod = "POST"
request.setValue("application/json", forHTTPHeaderField: "Content-Type")

let body: [String: String] = [
    "email": "client@example.com",
    "password": "password123"
]
request.httpBody = try? JSONEncoder().encode(body)

URLSession.shared.dataTask(with: request) { data, response, error in
    // Handle response
}.resume()
```

### Android (Kotlin)

```kotlin
// Login
val client = OkHttpClient()
val json = JSONObject()
json.put("email", "client@example.com")
json.put("password", "password123")

val body = json.toString().toRequestBody("application/json".toMediaType())
val request = Request.Builder()
    .url("http://localhost:8000/api/v1/login")
    .post(body)
    .build()

client.newCall(request).enqueue(object : Callback {
    override fun onResponse(call: Call, response: Response) {
        // Handle response
    }
})
```

### React Native

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('http://localhost:8000/api/v1/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });

  const data = await response.json();
  // Store token
  await AsyncStorage.setItem('token', data.token);
};

// Get Contracts with token
const getContracts = async () => {
  const token = await AsyncStorage.getItem('token');
  const response = await fetch('http://localhost:8000/api/v1/contracts', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  const data = await response.json();
  return data.contracts;
};
```

---

## 📢 Signalements (Issues)

### Lister les signalements

**GET** `/issues`

Liste tous les signalements du client.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "issues": [
    {
      "id": 1,
      "issue_number": "ISS-ABC123",
      "type": "maintenance",
      "type_label": "Maintenance",
      "subject": "Problème avec la serrure",
      "priority": "high",
      "priority_label": "Haute",
      "status": "open",
      "status_label": "Ouvert",
      "created_at": "2025-11-18T10:00:00.000000Z",
      "resolved_at": null
    }
  ]
}
```

### Obtenir les détails d'un signalement

**GET** `/issues/{id}`

**Response 200:**
```json
{
  "issue": {
    "id": 1,
    "issue_number": "ISS-ABC123",
    "type": "maintenance",
    "type_label": "Maintenance",
    "subject": "Problème avec la serrure",
    "description": "La serrure de mon box ne fonctionne plus correctement...",
    "priority": "high",
    "priority_label": "Haute",
    "status": "open",
    "status_label": "Ouvert",
    "resolution_notes": null,
    "resolved_at": null,
    "created_at": "2025-11-18T10:00:00.000000Z",
    "updated_at": "2025-11-18T10:00:00.000000Z",
    "contract": {
      "id": 1,
      "contract_number": "CT000001"
    }
  }
}
```

### Créer un signalement

**POST** `/issues`

**Body:**
```json
{
  "contract_id": 1,
  "type": "maintenance",
  "subject": "Problème avec la serrure",
  "description": "La serrure de mon box ne fonctionne plus correctement depuis ce matin",
  "priority": "high"
}
```

**Types disponibles:** `access`, `maintenance`, `billing`, `security`, `other`
**Priorités disponibles:** `low`, `medium`, `high`, `urgent`

**Response 201:**
```json
{
  "message": "Signalement créé avec succès",
  "issue": {
    "id": 1,
    "issue_number": "ISS-ABC123",
    "type": "maintenance",
    "subject": "Problème avec la serrure",
    "status": "open",
    "created_at": "2025-11-18T10:00:00.000000Z"
  }
}
```

---

## 🔚 Résiliation de contrat

### Demander la résiliation d'un contrat

**POST** `/contracts/{id}/request-termination`

**Body:**
```json
{
  "requested_termination_date": "2026-01-31",
  "reason": "Je déménage dans une autre ville et n'ai plus besoin de ce box"
}
```

**Response 201:**
```json
{
  "message": "Demande de résiliation envoyée avec succès",
  "termination_request": {
    "id": 1,
    "contract_number": "CT000001",
    "requested_termination_date": "2026-01-31",
    "status": "pending",
    "status_label": "En attente",
    "created_at": "2025-11-18T10:00:00.000000Z"
  }
}
```

### Lister les demandes de résiliation

**GET** `/contracts/termination-requests`

**Response 200:**
```json
{
  "termination_requests": [
    {
      "id": 1,
      "contract_number": "CT000001",
      "requested_termination_date": "2026-01-31",
      "approved_termination_date": null,
      "status": "pending",
      "status_label": "En attente",
      "reason": "Je déménage...",
      "admin_notes": null,
      "created_at": "2025-11-18T10:00:00.000000Z",
      "processed_at": null
    }
  ]
}
```

---

## 🔔 Notifications Push

### Enregistrer un token de notification

**POST** `/notifications/register-token`

**Body:**
```json
{
  "token": "fcm_device_token_here",
  "platform": "ios",
  "device_name": "iPhone 14 Pro"
}
```

**Plateformes:** `ios`, `android`

**Response 201:**
```json
{
  "message": "Token enregistré avec succès",
  "token": {
    "id": 1,
    "platform": "ios",
    "device_name": "iPhone 14 Pro",
    "last_used_at": "2025-11-18T10:00:00.000000Z",
    "created_at": "2025-11-18T10:00:00.000000Z"
  }
}
```

### Désenregistrer un token

**POST** `/notifications/unregister-token`

**Body:**
```json
{
  "token": "fcm_device_token_here"
}
```

**Response 200:**
```json
{
  "message": "Token désactivé avec succès"
}
```

### Lister les tokens enregistrés

**GET** `/notifications/tokens`

**Response 200:**
```json
{
  "tokens": [
    {
      "id": 1,
      "platform": "ios",
      "device_name": "iPhone 14 Pro",
      "last_used_at": "2025-11-18T10:00:00.000000Z",
      "created_at": "2025-11-18T10:00:00.000000Z"
    }
  ]
}
```

### Mettre à jour les préférences de notifications

**PUT** `/notifications/preferences`

**Body:**
```json
{
  "invoice_notifications": true,
  "payment_reminders": true,
  "contract_notifications": true,
  "promotional_notifications": false
}
```

**Response 200:**
```json
{
  "message": "Préférences de notifications mises à jour",
  "preferences": {
    "invoice_notifications": true,
    "payment_reminders": true,
    "contract_notifications": true,
    "promotional_notifications": false
  }
}
```

---

## 🚀 Fonctionnalités futures

- [x] Génération et téléchargement de factures en PDF
- [x] Notifications push
- [x] Demande de résiliation de contrat
- [x] Signalement de problème/incident
- [ ] Paiement en ligne
- [ ] Upload de documents
- [ ] Historique des accès au box
- [ ] Réservation de box supplémentaire
- [ ] Chat support en temps réel

---

## 📞 Support

Pour toute question concernant l'API, contactez : api@boxibox.fr
