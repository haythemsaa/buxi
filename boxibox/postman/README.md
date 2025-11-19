# 📮 Collection Postman Boxibox

Cette collection Postman contient tous les endpoints de l'API mobile Boxibox avec des exemples de requêtes.

## 📦 Contenu

- **Boxibox_API.postman_collection.json** - Collection complète avec toutes les requêtes
- **Boxibox_Local.postman_environment.json** - Environnement de développement local
- **README.md** - Ce fichier

## 🚀 Installation

### 1. Importer dans Postman

#### Option A: Import direct
1. Ouvrir Postman
2. Cliquer sur "Import" (en haut à gauche)
3. Glisser-déposer les deux fichiers JSON :
   - `Boxibox_API.postman_collection.json`
   - `Boxibox_Local.postman_environment.json`

#### Option B: Import depuis GitHub
1. Ouvrir Postman
2. Cliquer sur "Import"
3. Sélectionner "Link"
4. Coller l'URL du fichier sur GitHub

### 2. Sélectionner l'environnement

1. Dans Postman, cliquer sur le menu déroulant "Environments" (en haut à droite)
2. Sélectionner "Boxibox - Local"

## 🎯 Utilisation

### Quick Start

1. **Lancer le serveur Boxibox**
   ```bash
   cd boxibox
   ./scripts/dev.sh
   ```

2. **Authentification**
   - Ouvrir la collection "Boxibox API"
   - Aller dans "Authentication > Login"
   - Exécuter la requête
   - Le token est automatiquement sauvegardé dans les variables d'environnement

3. **Tester les endpoints**
   - Toutes les autres requêtes utilisent automatiquement le token
   - Explorer les différents dossiers de la collection

### Structure de la Collection

```
Boxibox API
├── Authentication          # Login, Register, Logout
├── Sites                   # Gestion des sites
├── Boxes                   # Recherche et comparaison de boxes
├── Reservations            # Réservations (client et invité)
├── Contracts               # Contrats et téléchargements
├── Invoices                # Factures et PDFs
├── Payments                # Paiements
├── Loyalty                 # Points de fidélité
└── Promotions              # Codes promo
```

### Comptes de Test

Les comptes suivants sont disponibles avec les données de démo :

#### Admin
- Email: `admin@boxibox.com`
- Password: `password`
- Permissions: Accès complet

#### Client Test
- Email: `client@example.com`
- Password: `password`
- Permissions: Endpoints client uniquement

## 🔧 Configuration

### Variables d'Environnement

L'environnement "Boxibox - Local" contient ces variables :

| Variable | Valeur | Description |
|----------|--------|-------------|
| `base_url` | `http://localhost:8000/api/v1` | URL de base de l'API |
| `token` | (auto) | Token d'authentification (auto-rempli après login) |
| `admin_email` | `admin@boxibox.com` | Email admin par défaut |
| `admin_password` | `password` | Mot de passe admin |
| `client_email` | `client@example.com` | Email client par défaut |
| `client_password` | `password` | Mot de passe client |

### Personnaliser l'URL

Pour utiliser un autre serveur (staging, production) :

1. Dupliquer l'environnement "Boxibox - Local"
2. Renommer (ex: "Boxibox - Staging")
3. Modifier la variable `base_url`
4. Sauvegarder

## 📝 Exemples de Flux

### Flux 1: Recherche et Réservation

```
1. Authentication > Login
2. Sites > List Sites
3. Boxes > Search Available Boxes
4. Boxes > Calculate Price
5. Reservations > Create Reservation
```

### Flux 2: Comparaison de Boxes

```
1. Authentication > Login
2. Sites > List Sites
3. Boxes > Search Available Boxes
4. Boxes > Compare Boxes (avec les IDs trouvés)
5. Boxes > Calculate Price (pour le meilleur choix)
```

### Flux 3: Réservation Invité

```
1. Sites > List Sites (pas d'auth nécessaire)
2. Boxes > Search Available Boxes (pas d'auth)
3. Reservations > Create Reservation (Guest) (pas d'auth)
```

### Flux 4: Consultation Factures

```
1. Authentication > Login
2. Contracts > List My Contracts
3. Invoices > List My Invoices
4. Invoices > Download Invoice PDF
```

## 🧪 Tests Automatisés

La collection inclut des tests automatiques pour certaines requêtes :

### Login
- ✅ Sauvegarde automatique du token
- ✅ Vérification du code 200
- ✅ Vérification de la présence du token

Pour ajouter vos propres tests :
1. Sélectionner une requête
2. Aller dans l'onglet "Tests"
3. Ajouter du code JavaScript

Exemple :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has data", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});
```

## 🔄 Runner Collection

Pour exécuter toute la collection :

1. Cliquer sur "..." à côté du nom de la collection
2. Sélectionner "Run collection"
3. Sélectionner les requêtes à exécuter
4. Cliquer sur "Run Boxibox API"

**Note:** Assurez-vous que les requêtes d'authentification s'exécutent en premier.

## 📚 Ressources Complémentaires

- **Documentation API complète**: Voir `../API_MOBILE.md`
- **Exemples d'intégration**: Voir `../INTEGRATION.md`
- **Guide de démarrage**: Voir `../README.md`

## 🆘 Dépannage

### "Could not get any response"
- Vérifiez que le serveur Laravel est démarré (`./scripts/dev.sh`)
- Vérifiez l'URL dans les variables d'environnement

### "401 Unauthorized"
- Re-exécutez la requête "Authentication > Login"
- Vérifiez que le token est bien sauvegardé dans les variables

### "404 Not Found"
- Vérifiez que l'URL de base est correcte (`/api/v1`)
- Vérifiez que les routes API sont bien définies

### Variables non remplacées ({{variable}})
- Vérifiez que l'environnement "Boxibox - Local" est sélectionné
- Vérifiez que les variables sont bien définies dans l'environnement

## 📖 Documentation Postman

- [Importing data into Postman](https://learning.postman.com/docs/getting-started/importing-and-exporting-data/)
- [Using environments](https://learning.postman.com/docs/sending-requests/managing-environments/)
- [Writing tests](https://learning.postman.com/docs/writing-scripts/test-scripts/)
