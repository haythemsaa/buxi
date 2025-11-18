# BOXIBOX - Plateforme SaaS Multi-Tenant de Gestion de Self-Stockage

## 📋 Vue d'ensemble

Boxibox est une plateforme SaaS moderne de gestion complète de centres de self-stockage destinée au marché européen. La solution permet à des opérateurs de centres de stockage (garde-meubles, box de stockage) de gérer l'intégralité de leurs opérations via une interface web moderne et mobile.

## 🚀 Technologies

### Backend
- **Framework**: Laravel 11.x
- **Base de données**: PostgreSQL 15+ (avec support MySQL/SQLite)
- **Cache**: Redis
- **Authentication**: Laravel Sanctum
- **Multi-tenancy**: Spatie Laravel Multitenancy
- **Permissions**: Spatie Laravel Permission
- **Media**: Spatie Laravel MediaLibrary

### Frontend
- **Framework**: Vue.js 3 (Composition API)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS 4
- **Routing**: Inertia.js
- **State Management**: Vue Composition API
- **Charts**: Chart.js + Vue-ChartJS
- **Icons**: HeroIcons

## 📁 Structure de la Base de Données

### Architecture Multi-Tenant

```
Tenant (Opérateur)
├── Sites (Centres de stockage)
│   ├── Buildings (Bâtiments)
│   │   ├── Floors (Étages)
│   │   │   └── Boxes (Unités de stockage)
│   ├── Customers (Clients)
│   ├── Contracts (Contrats de location)
│   ├── Invoices (Factures)
│   └── Payments (Paiements)
```

### Tables Principales

- **landlord_tenants**: Opérateurs (tenants)
- **sites**: Centres de stockage
- **buildings**: Bâtiments dans un site
- **floors**: Étages dans un bâtiment
- **boxes**: Unités de stockage louables
- **customers**: Clients finaux (particuliers/professionnels)
- **contracts**: Contrats de location
- **invoices**: Factures
- **payments**: Paiements
- **users**: Utilisateurs (staff + clients)
- **permissions/roles**: Gestion des droits (Spatie)

## 🎯 Fonctionnalités Principales (MVP)

### Module Administration (Back-Office)

#### 1. Dashboard
- KPIs en temps réel (taux d'occupation, revenus, nb contrats)
- Graphiques d'évolution
- Alertes et tâches en attente
- Vue d'ensemble multi-sites

#### 2. Gestion des Sites
- Configuration de sites multiples
- Hiérarchie: Site → Bâtiment → Étage → Box
- Plan interactif 2D
- Horaires d'ouverture et d'accès
- Photos et descriptions

#### 3. Gestion des Box
- CRUD complet
- Dimensions (L x l x H)
- Tarification (prix de base + prix dynamique)
- Statuts: disponible, occupé, réservé, maintenance
- Caractéristiques: climatisé, accès véhicule, électricité, etc.
- Photos

#### 4. Gestion des Clients (CRM)
- Fiche client complète (particulier/professionnel)
- Documents KYC (pièce d'identité, justificatif)
- Historique des contrats et paiements
- Scoring et tags
- Communication (email, SMS)
- Notes internes

#### 5. Gestion des Contrats
- Création de contrat (manuelle ou en ligne)
- Dates de début/fin
- Reconduction automatique
- Tarification et modalités de paiement
- Inventaire des biens stockés
- Signature électronique
- Codes d'accès
- Résiliation avec préavis

#### 6. Facturation
- Génération automatique récurrente
- Multi-devises
- Gestion TVA par pays
- Lignes de facturation personnalisables
- Statuts: pending, paid, overdue, refunded
- Export PDF
- Relances automatiques

#### 7. Paiements
- Intégration Stripe (CB, SEPA)
- Prélèvements automatiques
- Historique complet
- Rapprochement bancaire
- Gestion des impayés

### Module Client (Front-Office)

#### 1. Site Public
- Page d'accueil du centre
- Catalogue de box en ligne
- Filtres de recherche avancés
- Calculateur de volume
- Réservation en ligne
- Multilingue (FR, EN, NL, DE, ES, etc.)

#### 2. Espace Client
- Tableau de bord personnel
- Mes contrats actifs
- Mes factures et paiements
- Gestion du compte
- Demandes de service
- Messagerie

## 🛠️ Installation

### Prérequis

- PHP 8.3+
- Composer
- Node.js 20+ & NPM
- PostgreSQL 15+ (ou MySQL 8+)
- Redis 7+

### Installation

```bash
# Cloner le repository
cd boxibox

# Installer les dépendances PHP
composer install

# Installer les dépendances Node
npm install

# Configurer l'environnement
cp .env.example .env
php artisan key:generate

# Configurer la base de données dans .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=boxibox
DB_USERNAME=postgres
DB_PASSWORD=

# Exécuter les migrations
php artisan migrate

# Publier les assets
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Multitenancy\MultitenancyServiceProvider"

# Compiler les assets
npm run dev

# Lancer le serveur
php artisan serve
```

## 📦 Packages Laravel Installés

- `laravel/sanctum` - API authentication
- `spatie/laravel-multitenancy` - Multi-tenancy
- `spatie/laravel-permission` - Roles & permissions
- `spatie/laravel-medialibrary` - Media management
- `inertiajs/inertia-laravel` - Inertia.js adapter
- `tightenco/ziggy` - Routes JavaScript

## 🎨 Structure du Frontend

```
resources/
├── js/
│   ├── app.js               # Point d'entrée Inertia
│   ├── Pages/               # Composants pages Vue
│   │   ├── Dashboard/
│   │   ├── Sites/
│   │   ├── Boxes/
│   │   ├── Customers/
│   │   ├── Contracts/
│   │   └── Auth/
│   ├── Components/          # Composants réutilisables
│   └── Layouts/             # Layouts de page
└── css/
    └── app.css              # Styles Tailwind

```

## 🔐 Authentification & Permissions

### Rôles par défaut
- **Super Admin**: Gestion plateforme
- **Tenant Admin**: Administration tenant
- **Manager**: Gestion site
- **Staff**: Opérations quotidiennes
- **Customer**: Client final

### Permissions principales
- manage_sites
- manage_boxes
- manage_customers
- manage_contracts
- manage_invoices
- view_dashboard
- etc.

## 🌍 Multi-Tenancy

L'application utilise une architecture multi-tenant où:

- Chaque **tenant** (opérateur) a ses propres données isolées
- Un tenant peut gérer plusieurs **sites**
- Isolation au niveau de la base de données via `tenant_id`
- Support des sous-domaines personnalisés

## 💳 Intégrations

### Paiement (À implémenter)
- Stripe (paiements CB, SEPA Direct Debit)
- Support multi-devises

### Signature Électronique (À implémenter)
- DocuSign / Yousign / Universign

### Communication (À implémenter)
- SendGrid / Mailgun (emails transactionnels)
- Twilio / Vonage (SMS)

### Maps (À implémenter)
- Google Maps Platform (localisation, autocomplete)

## 📊 Modèles de Données

### Site
- Informations du centre de stockage
- Localisation GPS
- Horaires d'ouverture/accès
- Photos et équipements

### Box
- Dimensions (L x l x H)
- Volume et surface
- Tarification
- Caractéristiques techniques
- Statut (disponible, occupé, etc.)

### Customer
- Type: particulier / professionnel
- Informations de contact
- Documents KYC
- Scoring et tags

### Contract
- Dates et durée
- Tarification
- Mode de paiement
- Inventaire des biens
- Codes d'accès
- Statut

### Invoice
- Lignes de facturation
- Montants HT/TTC
- TVA par pays
- Statuts de paiement

### Payment
- Montant et devise
- Méthode de paiement
- Intégration Stripe
- Remboursements

## 🚧 Roadmap

### Phase 1 - MVP (Actuelle)
- [x] Architecture multi-tenant
- [x] Migrations base de données
- [x] Modèles Eloquent avec relations
- [ ] Authentication & Authorization
- [ ] Dashboard administrateur
- [ ] Gestion des box (CRUD)
- [ ] Gestion des clients
- [ ] Gestion des contrats
- [ ] Système de facturation basique
- [ ] Interface Vue.js 3

### Phase 2 - Fonctionnalités Avancées
- [ ] Réservation en ligne
- [ ] Espace client
- [ ] Intégration Stripe
- [ ] Signature électronique
- [ ] Facturation automatique récurrente
- [ ] Application mobile

### Phase 3 - Intelligence & Scale
- [ ] Tarification dynamique (ML)
- [ ] Contrôle d'accès connecté
- [ ] Analytics avancés
- [ ] API publique
- [ ] Marketplace partenaires

## 📝 Cahier des Charges

Le projet est basé sur le cahier des charges complet disponible dans le repository initial:
`Cahier_Specifications_Self_Stockage_Europe.md`

## 🤝 Contribution

Ce projet est en développement actif. Toute contribution est la bienvenue.

## 📄 Licence

Propriétaire - Tous droits réservés

## 👥 Contact

Pour toute question concernant le projet, veuillez contacter l'équipe de développement.

---

**Version**: 0.1.0 (MVP en cours)
**Date**: Novembre 2025
**Framework**: Laravel 11 + Vue.js 3
**Nom du projet**: Boxibox
