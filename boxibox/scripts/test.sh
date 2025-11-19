#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Tests                              ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Options
FILTER=""
COVERAGE=false
PARALLEL=false

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --filter=*)
            FILTER="${1#*=}"
            shift
            ;;
        --coverage)
            COVERAGE=true
            shift
            ;;
        --parallel)
            PARALLEL=true
            shift
            ;;
        *)
            echo -e "${RED}❌ Option inconnue: $1${NC}"
            echo ""
            echo "Usage: ./scripts/test.sh [options]"
            echo ""
            echo "Options:"
            echo "  --filter=<pattern>    Filtrer les tests par nom"
            echo "  --coverage            Générer un rapport de couverture"
            echo "  --parallel            Exécuter les tests en parallèle"
            echo ""
            exit 1
            ;;
    esac
done

# Construire la commande
CMD="php artisan test"

if [ "$PARALLEL" = true ]; then
    CMD="$CMD --parallel"
fi

if [ -n "$FILTER" ]; then
    CMD="$CMD --filter=$FILTER"
fi

if [ "$COVERAGE" = true ]; then
    CMD="$CMD --coverage"
fi

echo -e "${GREEN}🧪 Exécution des tests...${NC}"
echo ""

# Exécuter les tests
$CMD

# Afficher le résultat
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ Tous les tests sont passés !${NC}"
else
    echo ""
    echo -e "${RED}❌ Certains tests ont échoué${NC}"
    exit 1
fi
