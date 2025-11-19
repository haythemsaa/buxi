# 🏢 Boxibox - Plateforme SaaS de Gestion de Self-Stockage

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.0-4FC08D?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

Application SaaS complète pour la gestion de sites de self-stockage en France, avec interface d'administration web et API mobile.

## 📋 Table des Matières

- [Démarrage Rapide](#-démarrage-rapide-5-minutes)
- [Fonctionnalités](#-fonctionnalités)
- [Prérequis](#-prérequis)
- [Installation Détaillée](#-installation-détaillée)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [API Mobile](#-api-mobile)
- [Tests](#-tests)
- [Déploiement](#-déploiement)
- [Architecture](#-architecture)
- [Roadmap & Évolutions](#-roadmap--évolutions)

---

## 🚀 Démarrage Rapide (5 minutes)

### Installation Automatique

```bash
# Cloner le repository
git clone <repository-url> boxibox
cd boxibox

# Exécuter le script d'installation
chmod +x install.sh
./install.sh

# Lancer le serveur de développement
php artisan serve
```

🎉 **C'est tout !** L'application est accessible sur http://localhost:8000

### 🔑 Accès par Défaut

**Admin Web:**
- URL: http://localhost:8000
- Email: `admin@boxibox.com`
- Password: `password`

**Client Test:**
- Email: `client@example.com`
- Password: `password`

**API Mobile:**
- Base URL: http://localhost:8000/api/v1
- Documentation: Voir `API_MOBILE.md`

---

## ✨ Fonctionnalités

### 🎯 Core Business

#### Gestion des Sites
- ✅ Multi-tenancy (bases de données séparées par site)
- ✅ Gestion complète des sites avec GPS
- ✅ Bâtiments, étages et boxes
- ✅ Statuts en temps réel (disponible, loué, maintenance)

#### Réservations en Ligne
- ✅ Recherche de boxes avec filtres avancés
- ✅ Comparateur de boxes (jusqu'à 5)
- ✅ Calculateur de prix dynamique
- ✅ Réservations invités (sans compte)
- ✅ Expiration automatique (30 jours)

#### Contrats
- ✅ Génération automatique de contrats
- ✅ Codes d'accès uniques
- ✅ Gestion des échéances
- ✅ Demandes de résiliation
- ✅ Renouvellement automatique

#### Facturation
- ✅ Génération automatique mensuelle
- ✅ Prélèvement SEPA
- ✅ Génération PDF
- ✅ Historique complet
- ✅ Paiements partiels

#### Rappels de Paiement (3 Phases)
- ✅ **Phase 1** (7j) : Rappel amical - 0% pénalité
- ✅ **Phase 2** (15j) : Rappel ferme - 5% pénalité
- ✅ **Phase 3** (30j) : Mise en demeure - 10% pénalité
- ✅ Envoi automatique quotidien
- ✅ Notifications email personnalisées

#### Programme de Fidélité
- ✅ 4 paliers (Bronze, Argent, Or, Platine)
- ✅ Points sur chaque action
- ✅ Réductions progressives (0%, 5%, 10%, 15%)
- ✅ Expiration automatique (12 mois)
- ✅ Historique détaillé

#### Promotions
- ✅ 4 types de réductions
- ✅ Codes promo
- ✅ Conditions multiples
- ✅ Application automatique

### 📱 API Mobile

- ✅ 40+ endpoints REST
- ✅ Laravel Sanctum
- ✅ Réponses standardisées
- ✅ Notifications push (FCM/APNS)

### 🔧 Administration

- ✅ 32 vues Inertia.js + Vue 3
- ✅ Dashboard avec statistiques
- ✅ CRUD complets
- ✅ Recherche et filtres

### 🤖 Automation

- ✅ Génération factures mensuelles
- ✅ Rappels paiement quotidiens
- ✅ Rappels renouvellement
- ✅ Nettoyage réservations expirées
- ✅ Expiration points fidélité

---

## 💻 Prérequis

- **PHP** : 8.4+
- **Composer** : 2.8+
- **Node.js** : 18+ et npm
- **Base de données** : MySQL 8.0+ ou PostgreSQL 14+
- **Redis** : 6.0+ (recommandé)

---

## 📦 Installation Détaillée

### 1. Dépendances

```bash
composer install
npm install
```

### 2. Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Base de Données

#### MySQL
```sql
CREATE DATABASE boxibox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_DATABASE=boxibox
DB_USERNAME=root
DB_PASSWORD=
```

#### SQLite (Dev)
```bash
touch database/database.sqlite
```

```env
DB_CONNECTION=sqlite
```

### 4. Migrations & Seeders

```bash
php artisan migrate:fresh --seed
```

### 5. Assets

```bash
npm run build
```

### 6. Storage

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 7. Lancement

```bash
php artisan serve
```

---

## ⚙️ Configuration

### Variables Essentielles

```env
APP_NAME=Boxibox
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Europe/Paris
APP_LOCALE=fr

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=log
```

---

## 🎮 Utilisation

### 🛠️ Scripts Utilitaires

Des scripts bash sont fournis pour faciliter les tâches courantes :

```bash
# Démarrer le serveur de développement
./scripts/dev.sh          # Par défaut sur le port 8000
./scripts/dev.sh 8080     # Spécifier un port personnalisé

# Lancer les tests
./scripts/test.sh                    # Tous les tests
./scripts/test.sh --filter=Auth      # Filtrer par nom
./scripts/test.sh --coverage         # Avec couverture de code
./scripts/test.sh --parallel         # En parallèle

# Réinitialiser la base de données
./scripts/reset.sh        # Avec données de démo
./scripts/reset.sh none   # Sans données

# Installation fraîche complète
./scripts/fresh.sh        # Réinstalle tout depuis zéro

# Optimiser pour la production
./scripts/optimize.sh     # Cache configs, routes, views

# Consulter les logs
./scripts/logs.sh                # Dernières 50 lignes
./scripts/logs.sh -f             # Suivre en temps réel
./scripts/logs.sh --error        # Uniquement les erreurs
./scripts/logs.sh -n 100         # Dernières 100 lignes

# Lancer le worker de queue
./scripts/queue.sh               # Queue par défaut
./scripts/queue.sh high          # Queue haute priorité

# Sauvegarder la base de données
./scripts/backup.sh              # Crée un backup horodaté
```

### Commandes Artisan

```bash
# Rappels de paiement
php artisan reminders:process
php artisan reminders:process --dry-run

# Réservations
php artisan reservations:cleanup

# Factures
php artisan invoices:generate-monthly
php artisan invoices:generate-monthly --month=2025-12

# Fidélité
php artisan loyalty:process-expiry --dry-run

# Renouvellement
php artisan contracts:send-renewal-reminders
```

### Scheduler (Cron)

```bash
* * * * * cd /path/to/boxibox && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Workers

```bash
php artisan queue:work
php artisan queue:work --tries=3 --timeout=60
```

---

## 📱 API Mobile

### Quick Start

```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"client@example.com","password":"password"}'

# Utiliser le token
curl -X GET http://localhost:8000/api/v1/contracts \
  -H "Authorization: Bearer {token}"
```

### 📮 Collection Postman

Une collection Postman complète est disponible dans `postman/` :
- Import en 1 clic dans Postman
- Tous les endpoints avec exemples
- Variables d'environnement préconfigurées
- Tests automatisés

Voir `postman/README.md` pour les instructions.

### 📚 Ressources

- **Documentation API complète**: Voir `API_MOBILE.md`
- **Guide d'intégration**: Voir `INTEGRATION.md` (exemples React, Vue, Swift, Kotlin)
- **Collection Postman**: Voir `postman/README.md`

---

## 🧪 Tests

```bash
php artisan test
php artisan test --filter=AuthenticationTest
```

---

## 🚢 Déploiement

Voir `DEPLOYMENT.md` pour le guide complet.

```bash
php artisan down
git pull
composer install --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan up
```

---

## 🏗️ Architecture

### Stack
- Laravel 12, PHP 8.4
- Vue.js 3, Inertia.js
- MySQL/PostgreSQL
- Redis
- DomPDF

### Structure
```
app/
├── Console/Commands/      # 5 commandes
├── Events/                # 3 événements
├── Helpers/               # 20+ helpers
├── Http/
│   ├── Controllers/       # 29 contrôleurs
│   ├── Requests/          # 11 requests
│   └── Resources/         # 6 resources
├── Jobs/                  # 5 jobs
├── Notifications/         # 5 notifications
├── Policies/              # 7 policies
└── Services/              # Services métier

database/
├── factories/             # 13 factories
├── migrations/            # 20+ migrations
└── seeders/               # 7 seeders

resources/js/Pages/        # 32 vues
```

---

## 🗺️ Roadmap & Évolutions

### 📊 Analyse Concurrentielle
Étude complète du marché et identification des opportunités d'amélioration.

👉 **Voir [COMPETITIVE_ANALYSIS.md](COMPETITIVE_ANALYSIS.md)**

**Contenu** :
- Analyse des 7 principaux concurrents (SiteLink, Storable Edge, Storeganise, etc.)
- Comparaison détaillée des fonctionnalités par catégorie
- Identification des gaps critiques dans Boxibox
- Recommandations prioritaires avec ROI estimé

**Highlights** :
- 🔴 **Pricing dynamique IA** : +10-20% revenus potentiels
- 🔴 **Smart locks** : -40% coûts staff
- 🟠 **CRM automation** : +15-25% conversions
- 🟠 **Analytics avancés** : Décisions data-driven

### 🚀 Roadmap 2025
Plan de développement produit détaillé sur 4 trimestres.

👉 **Voir [ROADMAP.md](ROADMAP.md)**

**Timeline** :
- **Q1 2025** : Quick Wins & Revenue Boost (+20-30% revenus)
- **Q2 2025** : Automation & Smart Access (-40% coûts)
- **Q3 2025** : Mobile Native & Predictive IA
- **Q4 2025** : Premium Features & Scale

**Budget estimé** : 126-132k€
**ROI attendu Année 1** : +300-500k€ (pour 500 boxes)

### ⚡ Quick Wins - Actions Immédiates
Plan d'action concret pour les 6-7 prochaines semaines.

👉 **Voir [QUICK_WINS.md](QUICK_WINS.md)**

**4 Priorités** :
1. **Revenue Management Dynamique** (2 sem) - +10-20% revenus
2. **Stripe + PayPal Integration** (1 sem) - +30% conversions
3. **Portail Client Self-Service** (2 sem) - -50% support
4. **Analytics & Dashboards KPIs** (1.5 sem) - Data-driven decisions

**Investissement** : 12-18k€
**Gain Année 1** : +51k€
**Payback** : < 4 mois

### 🎯 Vision 2025
**Devenir la référence SaaS self-stockage en Europe francophone**

- 💰 +30% revenus par box vs marché
- ⚡ 100% automation (location sans contact)
- 📱 Mobile-first experience
- 🤖 IA pour pricing & support
- 🌍 Expansion internationale

---

## 📚 Documentation

### Documentation Technique
- `API_MOBILE.md` - Documentation API mobile complète
- `INTEGRATION.md` - Guide d'intégration avec exemples (React, Vue, Swift, Kotlin)
- `DEPLOYMENT.md` - Guide de déploiement production
- `postman/README.md` - Guide collection Postman

### Stratégie & Roadmap
- `COMPETITIVE_ANALYSIS.md` - Analyse concurrentielle détaillée (7 concurrents, 10 catégories)
- `ROADMAP.md` - Plan de développement 2025 (3 phases, 12 features, budget 126-132k€)
- `QUICK_WINS.md` - Actions immédiates prioritaires (6-7 semaines, ROI +51k€/an)
- `IMPLEMENTATION_GUIDE.md` - Guide technique d'implémentation avec templates
- `STATUS.md` - État actuel du projet (35% Phase 1 complété)

---

## 📄 License

MIT License

---

**🚀 Prêt à Démarrer ? Lancez `./install.sh` !**
