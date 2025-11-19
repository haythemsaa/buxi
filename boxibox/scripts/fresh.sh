#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Installation Fraîche               ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${YELLOW}⚠️  Cette opération va réinitialiser complètement l'application${NC}"
read -p "Êtes-vous sûr de vouloir continuer ? (o/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Oo]$ ]]; then
    echo -e "${YELLOW}Opération annulée${NC}"
    exit 0
fi

echo ""
echo -e "${GREEN}🧹 Nettoyage des caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo -e "${GREEN}📦 Installation des dépendances...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo ""
echo -e "${GREEN}🎨 Installation des assets...${NC}"
npm install

echo ""
echo -e "${GREEN}🔨 Compilation des assets...${NC}"
npm run build

echo ""
echo -e "${GREEN}🗄️  Configuration de la base de données...${NC}"
php artisan migrate:fresh --seed --seeder=CompleteDemoSeeder

echo ""
echo -e "${GREEN}🔐 Génération de la clé d'application...${NC}"
php artisan key:generate

echo ""
echo -e "${GREEN}🔗 Création du lien symbolique pour le stockage...${NC}"
php artisan storage:link

echo ""
echo -e "${GREEN}⚡ Optimisation...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo -e "${GREEN}✅ Installation fraîche terminée !${NC}"
echo ""
echo -e "${BLUE}🚀 Pour démarrer le serveur:${NC}"
echo "   ./scripts/dev.sh"
echo ""
echo -e "${BLUE}🔑 Connexion Admin:${NC}"
echo "   Email: admin@boxibox.com"
echo "   Password: password"
