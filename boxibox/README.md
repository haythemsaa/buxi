# Boxibox - Self-Storage Management Platform

Boxibox est une plateforme SaaS complète de gestion de self-storage développée avec Laravel 11 et Vue.js 3.

## 🚀 Fonctionnalités

### Core Features
- 🏢 **Multi-tenancy** : Architecture multi-tenant isolée avec Spatie Laravel Multitenancy
- 🔐 **Authentification** : Laravel Sanctum pour API mobile + Laravel Breeze pour admin web
- 📱 **API Mobile** : API REST complète pour application mobile (iOS/Android)
- 💳 **Gestion des paiements** : Facturation automatique, prélèvements, historique
- 📊 **Dashboard** : Interface d'administration complète avec Inertia.js

### Advanced Features (Phase 1 - Completed)

#### 🎫 Système de Réservation
- Recherche de boxes avec filtres avancés (volume, localisation, équipements)
- Réservation avec ou sans compte (guest reservations)
- Validité 30 jours avec conversion automatique en contrat
- Calcul dynamique des prix avec application des promotions
- Interface web et API mobile

#### 💰 Moteur de Promotions
- 4 types de réductions :
  - Pourcentage (ex: -30%)
  - Montant fixe (ex: -50€)
  - Premier mois gratuit
  - X mois gratuits
- Conditions d'éligibilité flexibles :
  - Durée minimale
  - Sites applicables
  - Types de boxes
  - Nouveaux clients uniquement
  - En ligne uniquement
- Empilable ou exclusif
- Priorités configurables
- Auto-application possible

#### 🎁 Programme de Fidélité
- 4 paliers (Bronze, Argent, Or, Platine)
- Réductions automatiques selon le palier (0%, 5%, 10%, 15%)
- Gains de points :
  - 100 points à la signature
  - 10 points par mois de paiement
  - 50 points par parrainage
  - Bonus spéciaux
- Dépense de points pour réductions
- Expiration après 12 mois
- Historique des transactions

#### 📐 Calcul de Prix Dynamique
- Réductions selon la durée :
  - 3-5 mois : -2%
  - 6-11 mois : -5%
  - 12+ mois : -10%
- Ajustement selon l'occupation :
  - Faible (<60%) : -5%
  - Élevée (>90%) : +5%
- Application de promotions
- Assurance optionnelle
- Dépôt de garantie
- Calcul taxes (TVA 20%)

#### 🔍 Comparateur de Boxes
- Comparaison jusqu'à 5 boxes simultanément
- Scoring intelligent (prix, volume, emplacement)
- Tableau comparatif détaillé
- Interface responsive

#### 📧 Système de Rappels de Paiement (3 Phases)
- **Phase 1 - Rappel Amical** (7 jours après échéance)
  - Message courtois
  - Pas de pénalité
  - Envoi par email
- **Phase 2 - Rappel Ferme** (15 jours après échéance)
  - Ton plus ferme
  - Pénalités de retard : 5%
  - Délai de 7 jours pour régulariser
- **Phase 3 - Mise en Demeure** (30 jours après échéance)
  - Procédure formelle
  - Pénalités de retard : 10%
  - Menace de suspension et poursuites
- Automatisation via commande artisan (`php artisan reminders:process`)
- Tracking complet des rappels (envoyé, accusé réception, payé)
- Statistiques en temps réel
- API mobile pour consultation par les clients

### Phase 2 (Planned)
- 💳 Intégration Stripe pour paiements en ligne
- 🌍 Support multi-langues (FR, EN, DE, ES, IT)
- 💬 Chat en direct avec support IA
- 🏗️ Visites virtuelles 3D/VR

## 📋 Prérequis

- PHP 8.4+
- Composer
- Node.js 18+ & NPM
- MySQL 8.0+ ou PostgreSQL 14+
- Redis (optionnel, recommandé pour les sessions)

## 🛠️ Installation

```bash
# Cloner le repository
git clone <repository-url>
cd boxibox

# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript
npm install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=boxibox
# DB_USERNAME=root
# DB_PASSWORD=

# Exécuter les migrations
php artisan migrate

# Peupler la base de données avec des données de test
php artisan db:seed

# Compiler les assets
npm run build
```

## 🚀 Démarrage

```bash
# Démarrer le serveur de développement Laravel
php artisan serve

# Dans un autre terminal, compiler les assets en mode watch
npm run dev
```

L'application sera accessible à : `http://localhost:8000`

## ⚙️ Commandes Utiles

### Rappels de Paiement Automatiques

```bash
# Traiter les rappels de paiement (en heures ouvrées)
php artisan reminders:process

# Forcer l'envoi même hors heures ouvrées
php artisan reminders:process --force

# Mode simulation (sans envoi réel)
php artisan reminders:process --dry-run
```

**Automatisation recommandée** :
Ajouter au crontab pour exécution quotidienne :
```
0 10 * * * cd /path/to/boxibox && php artisan reminders:process
```

## 📱 API Mobile

L'API REST est documentée dans `/boxibox/API_MOBILE.md`

### Endpoints principaux

#### Authentification
- `POST /api/v1/login` - Connexion

#### Réservations
- `POST /api/v1/boxes/search` - Rechercher des boxes
- `POST /api/v1/boxes/calculate-price` - Calculer un prix
- `POST /api/v1/reservations` - Créer une réservation
- `GET /api/v1/reservations` - Liste des réservations
- `POST /api/v1/reservations/{id}/cancel` - Annuler

#### Promotions
- `GET /api/v1/promotions` - Liste publique
- `POST /api/v1/promotions/validate` - Valider un code

#### Fidélité
- `GET /api/v1/loyalty/balance` - Solde de points
- `GET /api/v1/loyalty/history` - Historique
- `GET /api/v1/loyalty/info` - Informations du programme

#### Rappels de Paiement
- `GET /api/v1/payment-reminders` - Liste des rappels
- `GET /api/v1/payment-reminders/{id}` - Détails d'un rappel
- `POST /api/v1/payment-reminders/{id}/acknowledge` - Accuser réception

## 🗄️ Données de Test

Après avoir exécuté `php artisan db:seed`, vous aurez accès à :

### Compte Admin
- Email : `admin@boxibox.com`
- Mot de passe : `password`

### Clients
- 50 clients avec email : `prenom.nom@example.com`
- Mot de passe : `password123`

### Promotions
- `BIENVENUE30` : 30% pour nouveaux clients
- `ETE2025` : 2 mois gratuits pour 12 mois
- `ONLINE15` : 15% auto-appliqué
- `LONGDUR50` : 50€ pour 12 mois
- `1ERMOIS` : Premier mois offert

### Sites
- 1 site : Boxibox Paris Nord
- 2 bâtiments (A intérieur, B extérieur)
- ~64 boxes de différentes tailles

## 🏗️ Architecture

### Backend
- **Framework** : Laravel 11
- **Database** : MySQL avec Eloquent ORM
- **API** : RESTful avec Laravel Sanctum
- **Multi-tenancy** : Spatie Laravel Multitenancy
- **PDF** : DomPDF pour factures

### Frontend
- **Framework** : Vue.js 3
- **UI** : Inertia.js + Tailwind CSS
- **Authentification** : Laravel Breeze

### Structure des Dossiers
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/           # Contrôleurs API mobile
│   │   └── Web/           # Contrôleurs admin web
│   ├── Middleware/        # Middlewares (tenant, auth, etc.)
│   └── Requests/          # Form Requests pour validation
├── Models/                # Modèles Eloquent
├── Services/              # Services métier
└── Policies/              # Policies d'autorisation

database/
├── migrations/            # Migrations de schéma
└── seeders/               # Seeders de données

resources/
├── js/
│   ├── Pages/             # Vues Inertia
│   └── Layouts/           # Layouts Vue
└── views/                 # Vues Blade

routes/
├── api.php                # Routes API mobile
└── web.php                # Routes admin web
```

## 📝 Modèles Principaux

- `Site` : Sites de stockage (multi-tenant)
- `Building` : Bâtiments d'un site
- `Floor` : Étages d'un bâtiment
- `Box` : Boxes de stockage
- `Customer` : Clients
- `Contract` : Contrats de location
- `Invoice` : Factures
- `Payment` : Paiements
- `PaymentReminder` : Rappels de paiement (3 phases)
- `Reservation` : Réservations
- `Promotion` : Promotions et codes promo
- `LoyaltyPoint` : Solde de points de fidélité
- `LoyaltyTransaction` : Transactions de points
- `PriceRule` : Règles de tarification dynamique

## 🔒 Sécurité

- Policies pour autorisation fine
- Form Requests pour validation côté serveur
- CSRF protection
- XSS protection via Blade/Vue
- SQL injection prevention via Eloquent
- Password hashing avec bcrypt

## 🧪 Tests

```bash
# Exécuter les tests
php artisan test

# Avec coverage
php artisan test --coverage
```

## 📊 Analyse Concurrentielle

L'analyse détaillée des concurrents est disponible dans `/boxibox/ANALYSE_CONCURRENTS.md`

**Positionnement** : Boxibox combine toutes les fonctionnalités des leaders (Shurgard, Homebox, Une Pièce en Plus) + innovations uniques (programme fidélité, comparateur intelligent).

## 🤝 Contribution

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est propriétaire. Tous droits réservés.

## 📞 Support

Pour toute question ou support :
- Email : support@boxibox.com
- Documentation : `/boxibox/API_MOBILE.md`

---

Développé avec ❤️ par l'équipe Boxibox
