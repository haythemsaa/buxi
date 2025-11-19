#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Reset Base de Données             ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Mode de seed
SEED_MODE=${1:-demo}

if [ "$SEED_MODE" != "demo" ] && [ "$SEED_MODE" != "none" ]; then
    echo -e "${RED}❌ Mode invalide: $SEED_MODE${NC}"
    echo ""
    echo "Usage: ./scripts/reset.sh [mode]"
    echo ""
    echo "Modes disponibles:"
    echo "  demo (défaut)  - Réinitialiser avec données de démo"
    echo "  none          - Réinitialiser sans données"
    echo ""
    exit 1
fi

echo -e "${YELLOW}⚠️  ATTENTION: Cette opération va supprimer toutes les données !${NC}"
read -p "Êtes-vous sûr de vouloir continuer ? (o/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Oo]$ ]]; then
    echo -e "${YELLOW}Opération annulée${NC}"
    exit 0
fi

echo ""
echo -e "${GREEN}🔄 Réinitialisation de la base de données...${NC}"

# Drop et recréer les tables
php artisan migrate:fresh

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erreur lors de la migration${NC}"
    exit 1
fi

# Seed si demandé
if [ "$SEED_MODE" = "demo" ]; then
    echo ""
    echo -e "${GREEN}🌱 Chargement des données de démo...${NC}"
    php artisan db:seed --class=CompleteDemoSeeder

    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Erreur lors du seeding${NC}"
        exit 1
    fi

    echo ""
    echo -e "${GREEN}✅ Base de données réinitialisée avec données de démo !${NC}"
    echo ""
    echo -e "${BLUE}🔑 Connexion Admin:${NC}"
    echo "   Email: admin@boxibox.com"
    echo "   Password: password"
else
    echo ""
    echo -e "${GREEN}✅ Base de données réinitialisée (vide) !${NC}"
fi
