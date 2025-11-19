#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Fichier de log
LOG_FILE="storage/logs/laravel.log"

# Options
FOLLOW=false
LINES=50
LEVEL=""

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -f|--follow)
            FOLLOW=true
            shift
            ;;
        -n|--lines)
            LINES="$2"
            shift 2
            ;;
        --error)
            LEVEL="ERROR"
            shift
            ;;
        --warning)
            LEVEL="WARNING"
            shift
            ;;
        --info)
            LEVEL="INFO"
            shift
            ;;
        *)
            echo -e "${RED}❌ Option inconnue: $1${NC}"
            echo ""
            echo "Usage: ./scripts/logs.sh [options]"
            echo ""
            echo "Options:"
            echo "  -f, --follow          Suivre les logs en temps réel"
            echo "  -n, --lines <number>  Nombre de lignes à afficher (défaut: 50)"
            echo "  --error               Afficher uniquement les erreurs"
            echo "  --warning             Afficher uniquement les warnings"
            echo "  --info                Afficher uniquement les infos"
            echo ""
            exit 1
            ;;
    esac
done

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Logs                               ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Vérifier si le fichier existe
if [ ! -f "$LOG_FILE" ]; then
    echo -e "${YELLOW}⚠️  Aucun fichier de log trouvé${NC}"
    exit 0
fi

# Afficher les logs
if [ "$FOLLOW" = true ]; then
    echo -e "${GREEN}📜 Suivi des logs en temps réel (Ctrl+C pour arrêter)...${NC}"
    echo ""

    if [ -n "$LEVEL" ]; then
        tail -f "$LOG_FILE" | grep --line-buffered "$LEVEL"
    else
        tail -f "$LOG_FILE"
    fi
else
    echo -e "${GREEN}📜 Dernières $LINES lignes du log:${NC}"
    echo ""

    if [ -n "$LEVEL" ]; then
        tail -n "$LINES" "$LOG_FILE" | grep "$LEVEL"
    else
        tail -n "$LINES" "$LOG_FILE"
    fi
fi
