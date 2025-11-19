#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Serveur de Développement          ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Vérifier si le fichier .env existe
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  Fichier .env manquant. Lancement de l'installation...${NC}"
    ./install.sh
    exit 0
fi

# Port par défaut
PORT=${1:-8000}

echo -e "${GREEN}🚀 Démarrage du serveur sur http://localhost:$PORT${NC}"
echo -e "${GREEN}📧 Admin: admin@boxibox.com / password${NC}"
echo ""
echo -e "${YELLOW}Appuyez sur Ctrl+C pour arrêter${NC}"
echo ""

# Démarrer le serveur
php artisan serve --port=$PORT
