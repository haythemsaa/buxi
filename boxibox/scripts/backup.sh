#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║              🏢  BOXIBOX - Sauvegarde                         ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Créer le dossier de backups s'il n'existe pas
mkdir -p storage/backups

# Timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Nom du fichier de backup
BACKUP_FILE="storage/backups/boxibox_backup_$TIMESTAMP"

echo -e "${GREEN}💾 Création de la sauvegarde...${NC}"
echo ""

# Déterminer le type de base de données
DB_CONNECTION=$(php artisan tinker --execute="echo config('database.default');")

case $DB_CONNECTION in
    sqlite)
        echo -e "${GREEN}📦 Sauvegarde SQLite...${NC}"
        DB_PATH=$(php artisan tinker --execute="echo config('database.connections.sqlite.database');")

        if [ -f "$DB_PATH" ]; then
            cp "$DB_PATH" "${BACKUP_FILE}.sqlite"
            echo -e "${GREEN}✅ Base de données sauvegardée: ${BACKUP_FILE}.sqlite${NC}"
        else
            echo -e "${RED}❌ Fichier de base de données introuvable: $DB_PATH${NC}"
            exit 1
        fi
        ;;

    mysql)
        echo -e "${GREEN}📦 Sauvegarde MySQL...${NC}"

        DB_HOST=$(php artisan tinker --execute="echo config('database.connections.mysql.host');")
        DB_PORT=$(php artisan tinker --execute="echo config('database.connections.mysql.port');")
        DB_NAME=$(php artisan tinker --execute="echo config('database.connections.mysql.database');")
        DB_USER=$(php artisan tinker --execute="echo config('database.connections.mysql.username');")
        DB_PASS=$(php artisan tinker --execute="echo config('database.connections.mysql.password');")

        if [ -z "$DB_PASS" ]; then
            mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" > "${BACKUP_FILE}.sql"
        else
            mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "${BACKUP_FILE}.sql"
        fi

        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ Base de données sauvegardée: ${BACKUP_FILE}.sql${NC}"
        else
            echo -e "${RED}❌ Erreur lors de la sauvegarde MySQL${NC}"
            exit 1
        fi
        ;;

    pgsql)
        echo -e "${GREEN}📦 Sauvegarde PostgreSQL...${NC}"

        DB_HOST=$(php artisan tinker --execute="echo config('database.connections.pgsql.host');")
        DB_PORT=$(php artisan tinker --execute="echo config('database.connections.pgsql.port');")
        DB_NAME=$(php artisan tinker --execute="echo config('database.connections.pgsql.database');")
        DB_USER=$(php artisan tinker --execute="echo config('database.connections.pgsql.username');")
        DB_PASS=$(php artisan tinker --execute="echo config('database.connections.pgsql.password');")

        PGPASSWORD="$DB_PASS" pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" "$DB_NAME" > "${BACKUP_FILE}.sql"

        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ Base de données sauvegardée: ${BACKUP_FILE}.sql${NC}"
        else
            echo -e "${RED}❌ Erreur lors de la sauvegarde PostgreSQL${NC}"
            exit 1
        fi
        ;;

    *)
        echo -e "${RED}❌ Type de base de données non supporté: $DB_CONNECTION${NC}"
        exit 1
        ;;
esac

# Sauvegarde des fichiers uploadés (storage/app)
echo ""
echo -e "${GREEN}📁 Sauvegarde des fichiers...${NC}"
tar -czf "${BACKUP_FILE}_files.tar.gz" storage/app/public 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Fichiers sauvegardés: ${BACKUP_FILE}_files.tar.gz${NC}"
fi

echo ""
echo -e "${GREEN}✅ Sauvegarde terminée !${NC}"
echo ""
echo -e "${BLUE}📦 Fichiers créés:${NC}"
ls -lh storage/backups/*$TIMESTAMP*

# Nettoyer les anciennes sauvegardes (garder les 10 dernières)
echo ""
echo -e "${GREEN}🧹 Nettoyage des anciennes sauvegardes...${NC}"
cd storage/backups
ls -t | tail -n +21 | xargs -r rm --
cd ../..

echo -e "${GREEN}✅ Anciennes sauvegardes nettoyées (conservation des 10 dernières)${NC}"
