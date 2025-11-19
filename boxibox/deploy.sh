#!/bin/bash

# ================================================================
# Boxibox - Script de Déploiement Automatique
# ================================================================
# Ce script automatise le déploiement de l'application Boxibox
# Phase 1 Quick Wins - Production Ready
# ================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "${BLUE}================================================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}================================================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}→ $1${NC}"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_error "Ne pas exécuter ce script en tant que root"
    exit 1
fi

print_header "Boxibox - Déploiement Phase 1"

# Step 1: Environment Check
print_info "Étape 1/10: Vérification de l'environnement..."

# Check PHP version
if ! command -v php &> /dev/null; then
    print_error "PHP n'est pas installé"
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
print_success "PHP version: $PHP_VERSION"

# Check Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer n'est pas installé"
    exit 1
fi

COMPOSER_VERSION=$(composer --version | head -n 1)
print_success "Composer installé: $COMPOSER_VERSION"

# Check Node.js
if ! command -v node &> /dev/null; then
    print_error "Node.js n'est pas installé"
    exit 1
fi

NODE_VERSION=$(node --version)
print_success "Node.js version: $NODE_VERSION"

# Step 2: Environment File
print_info "Étape 2/10: Configuration fichier .env..."

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        print_success "Fichier .env créé depuis .env.example"
    else
        print_error "Fichier .env.example introuvable"
        exit 1
    fi
else
    print_warning "Fichier .env existe déjà"
fi

# Append payment env vars if not exists
if ! grep -q "STRIPE_KEY" .env; then
    print_info "Ajout des variables de paiement au .env..."
    cat .env.example.payments >> .env
    print_success "Variables de paiement ajoutées"
fi

# Step 3: Install Dependencies
print_info "Étape 3/10: Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader
print_success "Dépendances Composer installées"

print_info "Installation des dépendances NPM..."
npm install
print_success "Dépendances NPM installées"

# Step 4: Generate App Key
print_info "Étape 4/10: Génération de la clé d'application..."
if grep -q "APP_KEY=$" .env; then
    php artisan key:generate
    print_success "Clé d'application générée"
else
    print_warning "Clé d'application déjà définie"
fi

# Step 5: Build Assets
print_info "Étape 5/10: Compilation des assets..."
npm run build
print_success "Assets compilés"

# Step 6: Database Check
print_info "Étape 6/10: Vérification de la base de données..."

read -p "Voulez-vous exécuter les migrations maintenant? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    print_success "Migrations exécutées"

    read -p "Voulez-vous exécuter les seeders de pricing? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan db:seed --class=DefaultPricingRulesSeeder
        print_success "Règles de pricing par défaut créées"
    fi
else
    print_warning "Migrations non exécutées - à faire manuellement"
fi

# Step 7: Cache Optimization
print_info "Étape 7/10: Optimisation du cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Cache optimisé"

# Step 8: Storage Link
print_info "Étape 8/10: Création du lien de stockage..."
if [ ! -L public/storage ]; then
    php artisan storage:link
    print_success "Lien de stockage créé"
else
    print_warning "Lien de stockage existe déjà"
fi

# Step 9: Permissions
print_info "Étape 9/10: Configuration des permissions..."
chmod -R 755 storage bootstrap/cache
print_success "Permissions configurées"

# Step 10: Final Checks
print_info "Étape 10/10: Vérifications finales..."

# Check if Redis is running
if command -v redis-cli &> /dev/null; then
    if redis-cli ping > /dev/null 2>&1; then
        print_success "Redis est opérationnel"
    else
        print_warning "Redis n'est pas en cours d'exécution"
    fi
else
    print_warning "Redis CLI non installé - cache désactivé"
fi

# Test database connection
if php artisan migrate:status > /dev/null 2>&1; then
    print_success "Connexion base de données OK"
else
    print_error "Impossible de se connecter à la base de données"
fi

# Print Summary
echo ""
print_header "Déploiement Terminé"
echo ""
print_success "Application Boxibox Phase 1 déployée avec succès!"
echo ""
print_info "Prochaines étapes:"
echo "  1. Configurer les clés API Stripe/PayPal dans .env"
echo "  2. Configurer les webhooks Stripe et PayPal"
echo "  3. Activer le pricing dynamique: php artisan tinker"
echo "     > Box::query()->update(['use_dynamic_pricing' => true]);"
echo "  4. Configurer le cron pour le scheduler:"
echo "     * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo -e "${GREEN}Documentation complète: COMPLETION_SUMMARY.md${NC}"
echo -e "${GREEN}Guide de déploiement: DEPLOYMENT_GUIDE.md${NC}"
echo ""

# Optional: Test server
read -p "Voulez-vous lancer le serveur de test? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Lancement du serveur sur http://127.0.0.1:8000"
    php artisan serve
fi
