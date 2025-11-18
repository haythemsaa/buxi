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

## 🚀 Fonctionnalités futures

- [ ] Génération et téléchargement de factures en PDF
- [ ] Notifications push
- [ ] Demande de résiliation de contrat
- [ ] Signalement de problème/incident
- [ ] Paiement en ligne
- [ ] Upload de documents
- [ ] Historique des accès au box
- [ ] Réservation de box supplémentaire
- [ ] Chat support en temps réel

---

## 📞 Support

Pour toute question concernant l'API, contactez : api@boxibox.fr
