#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Optimisation Production            ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${GREEN}⚡ Optimisation de l'application pour la production...${NC}"
echo ""

# Vérifier l'environnement
if grep -q "APP_ENV=local" .env; then
    echo -e "${YELLOW}⚠️  L'application est en mode local${NC}"
    read -p "Voulez-vous continuer quand même ? (o/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Oo]$ ]]; then
        echo -e "${YELLOW}Opération annulée${NC}"
        exit 0
    fi
fi

echo -e "${GREEN}🧹 Nettoyage des caches existants...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo -e "${GREEN}📦 Installation des dépendances (production)...${NC}"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo ""
echo -e "${GREEN}🎨 Compilation des assets (production)...${NC}"
npm run build

echo ""
echo -e "${GREEN}⚡ Mise en cache des configurations...${NC}"
php artisan config:cache

echo ""
echo -e "${GREEN}⚡ Mise en cache des routes...${NC}"
php artisan route:cache

echo ""
echo -e "${GREEN}⚡ Mise en cache des vues...${NC}"
php artisan view:cache

echo ""
echo -e "${GREEN}⚡ Optimisation de l'autoloader...${NC}"
composer dump-autoload --optimize

echo ""
echo -e "${GREEN}✅ Optimisation terminée !${NC}"
echo ""
echo -e "${YELLOW}📝 Recommandations supplémentaires:${NC}"
echo "   1. Vérifiez que APP_ENV=production dans .env"
echo "   2. Vérifiez que APP_DEBUG=false dans .env"
echo "   3. Configurez un système de queue (Redis, Beanstalkd, etc.)"
echo "   4. Configurez le cron pour les tâches planifiées"
echo "   5. Utilisez un serveur web (Nginx/Apache) avec PHP-FPM"
