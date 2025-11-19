#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Queue Worker                       ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Queue par défaut
QUEUE=${1:-default}

echo -e "${GREEN}🔄 Démarrage du worker pour la queue: $QUEUE${NC}"
echo ""
echo -e "${YELLOW}Jobs disponibles:${NC}"
echo "   • SendPaymentReminder"
echo "   • SendContractRenewalReminder"
echo "   • ProcessLoyaltyPointsExpiry"
echo "   • SendInvoiceReminder"
echo ""
echo -e "${YELLOW}Appuyez sur Ctrl+C pour arrêter${NC}"
echo ""

# Démarrer le worker
php artisan queue:work --queue=$QUEUE --verbose --tries=3 --timeout=90
