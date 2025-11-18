# Guide d'installation Boxibox

## 🚀 Installation rapide

### Prérequis
- PHP 8.3+
- Composer
- Node.js 20+ & NPM
- PostgreSQL 15+ (ou MySQL 8+)
- Redis (optionnel pour le cache)

### Étape 1 : Cloner et installer les dépendances

```bash
cd /home/user/buxi/boxibox

# Installer les dépendances PHP
composer install

# Installer les dépendances Node
npm install
```

### Étape 2 : Configuration de l'environnement

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### Étape 3 : Configurer la base de données

Éditez le fichier `.env` et configurez votre base de données :

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=boxibox
DB_USERNAME=postgres
DB_PASSWORD=votre_mot_de_passe
```

### Étape 4 : Créer la base de données

```bash
# Créer la base de données (PostgreSQL)
createdb boxibox

# Ou avec MySQL
mysql -u root -p -e "CREATE DATABASE boxibox;"
```

### Étape 5 : Exécuter les migrations et seeders

```bash
# Exécuter les migrations
php artisan migrate

# Générer des données de test (IMPORTANT pour tester l'application)
php artisan db:seed --class=BoxiboxSeeder
```

Cette commande créera :
- ✅ 1 site de test (Boxibox Paris Nord)
- ✅ 2 bâtiments
- ✅ 4 étages
- ✅ ~46 boxes (70% occupées)
- ✅ 50 clients
- ✅ ~32 contrats actifs
- ✅ Factures et paiements des 6 derniers mois
- ✅ Un utilisateur admin : `admin@boxibox.com` / `password`

### Étape 6 : Compiler les assets

```bash
# Mode développement (avec hot reload)
npm run dev

# Ou mode production
npm run build
```

### Étape 7 : Lancer le serveur

```bash
# Dans un terminal
php artisan serve

# L'application sera accessible sur http://localhost:8000
```

## 📊 Accès à l'application

### Page d'accueil
- URL : http://localhost:8000
- Page de présentation de Boxibox

### Dashboard Admin
- URL : http://localhost:8000/dashboard
- **Email** : `admin@boxibox.com`
- **Mot de passe** : `password`

## 🎯 Fonctionnalités disponibles

### ✅ Dashboard
- KPIs en temps réel (taux d'occupation, revenus, contrats)
- Graphiques de revenus mensuels
- Évolution du taux d'occupation
- Liste des contrats récents
- Factures en retard

### ✅ Gestion complète
- **Sites** : Centres de stockage
- **Boxes** : Unités de stockage avec dimensions et tarifs
- **Clients** : CRM avec particuliers et professionnels
- **Contrats** : Cycle de vie complet des contrats

### ✅ Architecture
- Multi-tenant prêt
- Authentification Laravel Sanctum
- API REST complète
- Frontend Vue.js 3 avec Inertia.js
- Tailwind CSS 4 pour le design

## 🗄️ Structure de la base de données

```
Tenant (Opérateur)
  └── Sites (Centres)
        ├── Buildings (Bâtiments)
        │     └── Floors (Étages)
        │           └── Boxes (Unités de stockage)
        ├── Customers (Clients)
        └── Contracts (Contrats)
              ├── Invoices (Factures)
              └── Payments (Paiements)
```

## 🛠️ Commandes utiles

```bash
# Réinitialiser la base de données
php artisan migrate:fresh --seed

# Générer à nouveau des données de test
php artisan db:seed --class=BoxiboxSeeder

# Lancer les tests
php artisan test

# Créer un nouveau contrôleur
php artisan make:controller NomController

# Créer un nouveau modèle avec migration
php artisan make:model NomModel -m

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 📝 Routes disponibles

### Routes publiques
- `GET /` - Page d'accueil

### Routes authentifiées
- `GET /dashboard` - Dashboard principal
- `GET /sites` - Liste des sites
- `GET /boxes` - Liste des boxes
- `GET /customers` - Liste des clients
- `GET /contracts` - Liste des contrats

## 🔧 Configuration avancée

### Multi-tenancy
Pour activer complètement le multi-tenancy, configurez dans `config/multitenancy.php` :

```php
'tenant_finder' => \Spatie\Multitenancy\TenantFinder\DomainTenantFinder::class,
```

### Cache Redis
Pour utiliser Redis comme cache :

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Email
Configurez vos paramètres SMTP dans `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

## 🐛 Dépannage

### Erreur de permission
```bash
chmod -R 775 storage bootstrap/cache
```

### Erreur de clé d'application
```bash
php artisan key:generate
```

### Problème avec npm
```bash
rm -rf node_modules package-lock.json
npm install
```

### Base de données non accessible
Vérifiez que PostgreSQL/MySQL est démarré et que les credentials dans `.env` sont corrects.

## 📚 Documentation

- [Laravel 11](https://laravel.com/docs/11.x)
- [Vue.js 3](https://vuejs.org/)
- [Inertia.js](https://inertiajs.com/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Spatie Multitenancy](https://spatie.be/docs/laravel-multitenancy/)

## 🤝 Support

Pour toute question ou problème :
1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez la console du navigateur pour les erreurs frontend
3. Assurez-vous que tous les services (DB, Redis) sont démarrés

---

**Version** : 1.0.0
**Date** : Novembre 2025
**Projet** : Boxibox - Plateforme SaaS de gestion de self-stockage
