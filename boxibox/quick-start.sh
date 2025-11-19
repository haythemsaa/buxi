#!/bin/bash

################################################################################
# BOXIBOX - Script de Démarrage Rapide
# Version: 1.0.0
# Description: Lance l'application Boxibox en 30 secondes
################################################################################

set -e

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Affichage du logo
echo ""
echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                           ║${NC}"
echo -e "${BLUE}║                    ██████╗  ██████╗ ██╗  ██╗              ║${NC}"
echo -e "${BLUE}║                    ██╔══██╗██╔═══██╗╚██╗██╔╝              ║${NC}"
echo -e "${BLUE}║                    ██████╔╝██║   ██║ ╚███╔╝               ║${NC}"
echo -e "${BLUE}║                    ██╔══██╗██║   ██║ ██╔██╗               ║${NC}"
echo -e "${BLUE}║                    ██████╔╝╚██████╔╝██╔╝ ██╗              ║${NC}"
echo -e "${BLUE}║                    ╚═════╝  ╚═════╝ ╚═╝  ╚═╝              ║${NC}"
echo -e "${BLUE}║                                                           ║${NC}"
echo -e "${BLUE}║            Démarrage Rapide - Version 1.0.0               ║${NC}"
echo -e "${BLUE}║                                                           ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Fonction d'affichage de progression
print_step() {
    echo -e "${BLUE}[ÉTAPE $1/$2]${NC} $3"
}

print_success() {
    echo -e "${GREEN}✔${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✖${NC} $1"
}

TOTAL_STEPS=8

# =============================================================================
# ÉTAPE 1: Vérification de l'environnement
# =============================================================================
print_step 1 $TOTAL_STEPS "Vérification de l'environnement..."

# Vérifier si .env existe
if [ ! -f .env ]; then
    print_warning ".env n'existe pas, copie depuis .env.example..."
    cp .env.example .env
    print_success ".env créé avec succès"
else
    print_success ".env existe déjà"
fi

# Vérifier la version de PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    print_success "PHP $PHP_VERSION détecté"
else
    print_error "PHP n'est pas installé. PHP 8.2+ est requis."
    exit 1
fi

# Vérifier Composer
if command -v composer &> /dev/null; then
    print_success "Composer détecté"
else
    print_error "Composer n'est pas installé."
    exit 1
fi

echo ""

# =============================================================================
# ÉTAPE 2: Installation des dépendances PHP
# =============================================================================
print_step 2 $TOTAL_STEPS "Installation des dépendances Composer..."

if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "Dépendances Composer installées"
else
    print_success "Dépendances Composer déjà installées (passer)"
fi

echo ""

# =============================================================================
# ÉTAPE 3: Génération de la clé d'application
# =============================================================================
print_step 3 $TOTAL_STEPS "Configuration de Laravel..."

# Générer la clé d'application si elle n'existe pas
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
    print_success "Clé d'application générée"
else
    print_success "Clé d'application existe déjà"
fi

echo ""

# =============================================================================
# ÉTAPE 4: Configuration de la base de données
# =============================================================================
print_step 4 $TOTAL_STEPS "Configuration de la base de données..."

# Créer la base de données si elle n'existe pas (SQLite pour démo)
if ! grep -q "DB_CONNECTION=sqlite" .env; then
    print_warning "Configuration manuelle de la base de données nécessaire"
    echo -e "${YELLOW}Éditez .env et configurez DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD${NC}"
else
    # Créer le fichier SQLite
    touch database/database.sqlite
    print_success "Base de données SQLite créée"
fi

# Exécuter les migrations
print_step 5 $TOTAL_STEPS "Exécution des migrations..."
php artisan migrate:fresh --force --seed
print_success "Base de données initialisée avec données de démonstration"

echo ""

# =============================================================================
# ÉTAPE 6: Création du lien de stockage
# =============================================================================
print_step 6 $TOTAL_STEPS "Configuration du stockage..."

if [ ! -L "public/storage" ]; then
    php artisan storage:link
    print_success "Lien symbolique créé"
else
    print_success "Lien symbolique existe déjà"
fi

echo ""

# =============================================================================
# ÉTAPE 7: Optimisation du cache
# =============================================================================
print_step 7 $TOTAL_STEPS "Optimisation et cache..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "Cache optimisé"

echo ""

# =============================================================================
# ÉTAPE 8: Démarrage du serveur de développement
# =============================================================================
print_step 8 $TOTAL_STEPS "Démarrage du serveur..."

echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║                  ✨ INSTALLATION TERMINÉE ✨               ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📱 L'application est prête à être utilisée !${NC}"
echo ""
echo -e "${YELLOW}🚀 Pour démarrer le serveur de développement:${NC}"
echo -e "   ${GREEN}php artisan serve${NC}"
echo ""
echo -e "${YELLOW}🌐 L'application sera accessible sur:${NC}"
echo -e "   ${GREEN}http://127.0.0.1:8000${NC}"
echo ""
echo -e "${YELLOW}👤 Comptes de démonstration:${NC}"
echo -e "   ${GREEN}Admin:${NC}    admin@boxibox.fr / password"
echo -e "   ${GREEN}Client:${NC}   client@test.fr / password"
echo ""
echo -e "${YELLOW}📚 Documentation:${NC}"
echo -e "   ${GREEN}README.md${NC}              - Documentation technique complète"
echo -e "   ${GREEN}DEPLOYMENT_GUIDE.md${NC}    - Guide de déploiement production"
echo -e "   ${GREEN}FINAL_SUMMARY.md${NC}       - Résumé du projet complet"
echo ""
echo -e "${YELLOW}💡 Commandes utiles:${NC}"
echo -e "   ${GREEN}php artisan test${NC}                 - Lancer les tests"
echo -e "   ${GREEN}php artisan db:seed${NC}              - Réinitialiser les données de démo"
echo -e "   ${GREEN}npm run dev${NC}                      - Démarrer Vite (mode dev)"
echo -e "   ${GREEN}npm run build${NC}                    - Builder les assets (production)"
echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""

# Demander si l'utilisateur veut démarrer le serveur maintenant
read -p "Voulez-vous démarrer le serveur maintenant? (o/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Oo]$ ]]; then
    echo -e "${GREEN}Démarrage du serveur...${NC}"
    echo ""
    php artisan serve
else
    echo -e "${YELLOW}Serveur non démarré. Exécutez 'php artisan serve' pour le démarrer.${NC}"
fi
