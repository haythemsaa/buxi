#!/bin/bash

# =============================================================================
# Boxibox - Script d'Installation Automatique
# =============================================================================
# Ce script configure automatiquement l'application Boxibox en quelques minutes
# Usage: ./install.sh
# =============================================================================

set -e  # Exit on error

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions d'affichage
print_header() {
    echo -e "${BLUE}"
    echo "╔═══════════════════════════════════════════════════════════════╗"
    echo "║                                                               ║"
    echo "║              🏢  BOXIBOX - Installation                       ║"
    echo "║         Plateforme SaaS de Self-Stockage                      ║"
    echo "║                                                               ║"
    echo "╚═══════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

print_step() {
    echo -e "\n${BLUE}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Vérification des prérequis
check_requirements() {
    print_step "Vérification des prérequis..."

    local missing_requirements=0

    # PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP n'est pas installé"
        missing_requirements=1
    else
        php_version=$(php -r 'echo PHP_VERSION;')
        print_success "PHP $php_version détecté"

        # Vérifier la version de PHP
        if ! php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);'; then
            print_error "PHP 8.2 ou supérieur est requis"
            missing_requirements=1
        fi
    fi

    # Composer
    if ! command -v composer &> /dev/null; then
        print_error "Composer n'est pas installé"
        print_warning "Installer Composer: https://getcomposer.com/download/"
        missing_requirements=1
    else
        composer_version=$(composer --version | grep -oP '\d+\.\d+\.\d+' | head -1)
        print_success "Composer $composer_version détecté"
    fi

    # Node.js & npm
    if ! command -v node &> /dev/null; then
        print_error "Node.js n'est pas installé"
        print_warning "Installer Node.js: https://nodejs.org/"
        missing_requirements=1
    else
        node_version=$(node -v)
        print_success "Node.js $node_version détecté"
    fi

    if ! command -v npm &> /dev/null; then
        print_error "npm n'est pas installé"
        missing_requirements=1
    else
        npm_version=$(npm -v)
        print_success "npm $npm_version détecté"
    fi

    if [ $missing_requirements -eq 1 ]; then
        print_error "Des prérequis sont manquants. Installez-les et relancez ce script."
        exit 1
    fi

    print_success "Tous les prérequis sont satisfaits!"
}

# Installation des dépendances
install_dependencies() {
    print_step "Installation des dépendances..."

    # Dépendances PHP
    echo "Installation des dépendances Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "Dépendances Composer installées"

    # Dépendances JavaScript
    echo "Installation des dépendances npm..."
    npm install
    print_success "Dépendances npm installées"
}

# Configuration de l'environnement
setup_environment() {
    print_step "Configuration de l'environnement..."

    if [ ! -f .env ]; then
        cp .env.example .env
        print_success "Fichier .env créé"
    else
        print_warning "Fichier .env existant conservé"
    fi

    # Générer la clé d'application
    php artisan key:generate --force
    print_success "Clé d'application générée"
}

# Configuration de la base de données
setup_database() {
    print_step "Configuration de la base de données..."

    echo ""
    echo "Choisissez votre base de données:"
    echo "1) SQLite (recommandé pour le développement)"
    echo "2) MySQL"
    echo "3) PostgreSQL"
    read -p "Votre choix (1/2/3) [1]: " db_choice
    db_choice=${db_choice:-1}

    case $db_choice in
        1)
            # SQLite
            print_step "Configuration de SQLite..."
            touch database/database.sqlite
            sed -i.bak 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
            sed -i.bak 's/DB_DATABASE=.*/# DB_DATABASE=/' .env
            print_success "SQLite configuré"
            ;;
        2)
            # MySQL
            print_step "Configuration de MySQL..."
            read -p "Nom de la base de données [boxibox]: " db_name
            db_name=${db_name:-boxibox}
            read -p "Utilisateur MySQL [root]: " db_user
            db_user=${db_user:-root}
            read -p "Mot de passe MySQL: " db_pass

            sed -i.bak "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
            sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
            sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
            sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env

            print_warning "N'oubliez pas de créer la base de données manuellement:"
            echo "  mysql -u $db_user -p -e \"CREATE DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
            ;;
        3)
            # PostgreSQL
            print_step "Configuration de PostgreSQL..."
            read -p "Nom de la base de données [boxibox]: " db_name
            db_name=${db_name:-boxibox}
            read -p "Utilisateur PostgreSQL [postgres]: " db_user
            db_user=${db_user:-postgres}
            read -p "Mot de passe PostgreSQL: " db_pass

            sed -i.bak "s/DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
            sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
            sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
            sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env

            print_warning "N'oubliez pas de créer la base de données manuellement:"
            echo "  createdb -U $db_user $db_name"
            ;;
    esac

    # Nettoyer les fichiers de backup
    rm -f .env.bak
}

# Exécution des migrations
run_migrations() {
    print_step "Exécution des migrations..."

    read -p "Voulez-vous charger les données de démonstration? (O/n) [O]: " load_demo
    load_demo=${load_demo:-O}

    if [[ $load_demo =~ ^[Oo]$ ]]; then
        php artisan migrate:fresh --seed --force
        print_success "Base de données initialisée avec données de démo"

        echo ""
        print_success "🔑 Comptes de test créés:"
        echo "  Admin: admin@boxibox.com / password"
        echo "  Client: client@example.com / password"
    else
        php artisan migrate --force
        print_success "Migrations exécutées"
    fi
}

# Configuration du storage
setup_storage() {
    print_step "Configuration du stockage..."

    # Créer le lien symbolique
    php artisan storage:link 2>/dev/null || true

    # Permissions (Linux/Mac uniquement)
    if [[ "$OSTYPE" != "msys" && "$OSTYPE" != "win32" ]]; then
        chmod -R 775 storage bootstrap/cache 2>/dev/null || true
        print_success "Permissions configurées"
    fi

    print_success "Stockage configuré"
}

# Compilation des assets
build_assets() {
    print_step "Compilation des assets..."

    npm run build
    print_success "Assets compilés"
}

# Configuration finale
final_configuration() {
    print_step "Configuration finale..."

    # Autoload helpers
    composer dump-autoload

    # Caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    print_success "Configuration finale terminée"
}

# Instructions post-installation
show_instructions() {
    echo ""
    echo -e "${GREEN}╔═══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                                                               ║${NC}"
    echo -e "${GREEN}║          🎉  Installation terminée avec succès!               ║${NC}"
    echo -e "${GREEN}║                                                               ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}📋 Prochaines étapes:${NC}"
    echo ""
    echo "1. Démarrer le serveur de développement:"
    echo -e "   ${YELLOW}php artisan serve${NC}"
    echo ""
    echo "2. Accéder à l'application:"
    echo -e "   ${YELLOW}http://localhost:8000${NC}"
    echo ""
    echo "3. Démarrer les workers de queue (optionnel):"
    echo -e "   ${YELLOW}php artisan queue:work${NC}"
    echo ""
    echo "4. Démarrer le serveur Vite pour le HMR (optionnel):"
    echo -e "   ${YELLOW}npm run dev${NC}"
    echo ""
    echo -e "${BLUE}🔑 Comptes de test:${NC}"
    echo ""
    echo "  Admin Web:"
    echo "  - Email: admin@boxibox.com"
    echo "  - Password: password"
    echo ""
    echo "  Client:"
    echo "  - Email: client@example.com"
    echo "  - Password: password"
    echo ""
    echo -e "${BLUE}📚 Documentation:${NC}"
    echo "  - README.md - Guide principal"
    echo "  - API_MOBILE.md - Documentation API"
    echo "  - DEPLOYMENT.md - Guide de déploiement"
    echo ""
    echo -e "${GREEN}Bon développement! 🚀${NC}"
    echo ""
}

# Programme principal
main() {
    print_header

    check_requirements
    install_dependencies
    setup_environment
    setup_database
    run_migrations
    setup_storage
    build_assets
    final_configuration
    show_instructions
}

# Exécution
main
