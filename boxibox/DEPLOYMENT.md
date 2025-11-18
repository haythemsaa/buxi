# Guide de Déploiement - Boxibox

Guide complet pour déployer l'application Boxibox en production.

## 📋 Prérequis Serveur

### Serveur Web
- **OS** : Ubuntu 22.04 LTS ou supérieur
- **Web Server** : Nginx ou Apache avec support PHP-FPM
- **PHP** : 8.4+
- **Base de données** : MySQL 8.0+ ou PostgreSQL 14+
- **Cache** : Redis 6.0+
- **Node.js** : 18+ (pour compilation des assets)
- **Supervisor** : Pour gestion des queue workers

### Extensions PHP Requises
```bash
sudo apt install php8.4-fpm php8.4-mysql php8.4-pgsql php8.4-redis \
  php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-curl php8.4-zip \
  php8.4-intl php8.4-gd php8.4-imagick
```

### Ressources Recommandées
- **RAM** : Minimum 4GB, recommandé 8GB+
- **CPU** : 2 cores minimum, 4+ recommandé
- **Disque** : 50GB minimum SSD
- **Bande passante** : 100 Mbps

---

## 🚀 Installation Initiale

### 1. Cloner le Repository

```bash
cd /var/www
sudo git clone <repository-url> boxibox
cd boxibox
sudo chown -R www-data:www-data /var/www/boxibox
sudo chmod -R 755 /var/www/boxibox
```

### 2. Installer les Dépendances

```bash
# Dépendances PHP
composer install --optimize-autoloader --no-dev

# Dépendances JavaScript
npm install
npm run build
```

### 3. Configuration de l'Environnement

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

Éditer `.env` avec les paramètres de production :

```env
APP_NAME=Boxibox
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://boxibox.fr

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boxibox_production
DB_USERNAME=boxibox_user
DB_PASSWORD=secure_password_here

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache et Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (exemple avec SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@boxibox.fr
MAIL_FROM_NAME="${APP_NAME}"

# Multi-tenancy
TENANCY_DATABASE_PREFIX=tenant_
TENANCY_DATABASE_AUTO_CREATE=true

# Sanctum
SANCTUM_STATEFUL_DOMAINS=boxibox.fr,api.boxibox.fr
SESSION_DOMAIN=.boxibox.fr

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=warning

# Filesystem
FILESYSTEM_DISK=local
```

### 4. Configuration de la Base de Données

```bash
# Créer la base de données
mysql -u root -p
```

```sql
CREATE DATABASE boxibox_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'boxibox_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON boxibox_production.* TO 'boxibox_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Exécuter les migrations
php artisan migrate --force

# Seeder (optionnel - seulement pour démo)
# php artisan db:seed
```

### 5. Permissions des Fichiers

```bash
# Permissions storage et cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Créer les liens symboliques
php artisan storage:link
```

### 6. Optimisation Laravel

```bash
# Cache de configuration
php artisan config:cache

# Cache des routes
php artisan route:cache

# Cache des vues
php artisan view:cache

# Cache des events
php artisan event:cache
```

---

## 🔧 Configuration Nginx

Créer `/etc/nginx/sites-available/boxibox` :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name boxibox.fr www.boxibox.fr;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name boxibox.fr www.boxibox.fr;

    root /var/www/boxibox/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/boxibox.fr/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/boxibox.fr/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Logs
    access_log /var/log/nginx/boxibox-access.log;
    error_log /var/log/nginx/boxibox-error.log;

    # Client max body size
    client_max_body_size 20M;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Activer le site :

```bash
sudo ln -s /etc/nginx/sites-available/boxibox /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 📅 Configuration du Scheduler (Cron)

Le scheduler Laravel doit tourner toutes les minutes :

```bash
sudo crontab -e -u www-data
```

Ajouter :

```cron
* * * * * cd /var/www/boxibox && php artisan schedule:run >> /dev/null 2>&1
```

### Tâches Planifiées Actives

Le fichier `routes/console.php` configure automatiquement :

1. **Rappels de paiement** : Tous les jours à 10h00 (Paris)
   ```php
   Schedule::command('reminders:process')
       ->dailyAt('10:00')
       ->timezone('Europe/Paris');
   ```

Vérifier que le cron fonctionne :

```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log

# Tester manuellement
sudo -u www-data php artisan reminders:process --dry-run
```

---

## 👷 Configuration des Queue Workers

### 1. Configuration Supervisor

Créer `/etc/supervisor/conf.d/boxibox-worker.conf` :

```ini
[program:boxibox-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/boxibox/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/boxibox/storage/logs/worker.log
stopwaitsecs=3600
```

Démarrer les workers :

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start boxibox-worker:*
```

Vérifier le statut :

```bash
sudo supervisorctl status boxibox-worker:*
```

### 2. Jobs en Queue

Les jobs suivants utilisent les queues :

- **PaymentReminderNotification** : Envoi d'emails de rappels de paiement
- Notifications push (futures)
- Génération de PDF (futures)

---

## 📧 Configuration Email

### Option 1 : SMTP (Recommandé pour production)

Services recommandés :
- **SendGrid** (99% deliverability)
- **Mailgun** (Laravel friendly)
- **Amazon SES** (Low cost)
- **Postmark** (Transactional emails)

Exemple avec Mailgun :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@boxibox.fr
MAIL_FROM_NAME="Boxibox"
```

### Option 2 : API (Plus rapide)

Exemple avec Mailgun API :

```bash
composer require symfony/mailgun-mailer symfony/http-client
```

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.boxibox.fr
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.eu.mailgun.net
```

### Tester l'envoi d'emails

```bash
php artisan tinker
```

```php
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test Boxibox');
});
```

---

## 🏢 Configuration Multi-Tenancy

### Création d'un Nouveau Tenant (Site)

```bash
php artisan tinker
```

```php
$site = \App\Models\Site::create([
    'name' => 'Boxibox Paris Nord',
    'code' => 'PARIS_NORD',
    'address' => '123 Avenue du Stockage',
    'postal_code' => '75018',
    'city' => 'Paris',
    'phone' => '0140000000',
    'email' => 'paris@boxibox.fr',
    'status' => 'active',
]);

// Une base de données tenant sera créée automatiquement
// tenant_paris_nord
```

### Migration des Tenants

```bash
# Migrer tous les tenants
php artisan tenants:migrate

# Migrer un tenant spécifique
php artisan tenants:migrate --tenants=1
```

---

## 🔒 Checklist Sécurité

### 1. Configuration Laravel

- [ ] `APP_DEBUG=false` en production
- [ ] `APP_ENV=production`
- [ ] Clé APP_KEY générée et sécurisée
- [ ] CSRF protection activée
- [ ] Force HTTPS

```php
// app/Providers/AppServiceProvider.php
if ($this->app->environment('production')) {
    \URL::forceScheme('https');
}
```

### 2. Base de Données

- [ ] Utilisateur DB dédié avec permissions limitées
- [ ] Mot de passe fort
- [ ] Backup quotidien automatisé
- [ ] SSL/TLS pour connexions DB (si DB distante)

### 3. Fichiers et Permissions

- [ ] `.env` non accessible publiquement
- [ ] Permissions 644 pour les fichiers
- [ ] Permissions 755 pour les dossiers
- [ ] Storage et cache : 775 avec owner www-data

### 4. Serveur Web

- [ ] SSL/TLS configuré (Let's Encrypt)
- [ ] Headers de sécurité activés
- [ ] Rate limiting configuré
- [ ] Firewall activé (UFW)

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 5. Monitoring et Logs

- [ ] Logs Laravel en place
- [ ] Monitoring serveur (CPU, RAM, Disk)
- [ ] Alertes email pour erreurs critiques
- [ ] Rotation des logs

```bash
# Logrotate pour Laravel
sudo nano /etc/logrotate.d/laravel
```

```
/var/www/boxibox/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 664 www-data www-data
}
```

---

## 📊 Monitoring et Maintenance

### Commandes de Maintenance

```bash
# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Vérifier la santé de l'application
php artisan tinker
>>> \DB::connection()->getPdo();

# Vérifier les queues
php artisan queue:monitor redis

# Vérifier les failed jobs
php artisan queue:failed
```

### Backup Automatique

Script de backup `/var/www/boxibox/backup.sh` :

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/boxibox"
DB_NAME="boxibox_production"
DB_USER="boxibox_user"
DB_PASS="secure_password_here"

mkdir -p $BACKUP_DIR

# Backup base de données
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup fichiers storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/boxibox/storage

# Garder seulement les 30 derniers jours
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

Ajouter au cron (quotidien à 2h du matin) :

```bash
sudo crontab -e
```

```cron
0 2 * * * /var/www/boxibox/backup.sh >> /var/log/boxibox-backup.log 2>&1
```

---

## 🔄 Mise à Jour de l'Application

Script de déploiement `/var/www/boxibox/deploy.sh` :

```bash
#!/bin/bash

echo "Starting deployment..."

# Mode maintenance
php artisan down

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Restart queue workers
sudo supervisorctl restart boxibox-worker:*

# Exit maintenance mode
php artisan up

echo "Deployment completed successfully!"
```

Rendre exécutable :

```bash
chmod +x /var/www/boxibox/deploy.sh
```

---

## 🧪 Vérification Post-Déploiement

### Checklist de Vérification

1. **Application Web**
   - [ ] Site accessible via HTTPS
   - [ ] Redirections HTTP → HTTPS fonctionnent
   - [ ] Authentification admin fonctionne
   - [ ] Pas d'erreurs 500

2. **API Mobile**
   - [ ] Endpoint `/api/v1/login` fonctionne
   - [ ] Authentification Sanctum fonctionne
   - [ ] Tous les endpoints API retournent 200

3. **Scheduler**
   - [ ] Cron fonctionne
   - [ ] Commande `reminders:process` s'exécute
   - [ ] Logs dans `storage/logs/laravel.log`

4. **Queue Workers**
   - [ ] Workers actifs (supervisorctl status)
   - [ ] Jobs traités correctement
   - [ ] Emails envoyés

5. **Base de Données**
   - [ ] Migrations appliquées
   - [ ] Connexion fonctionne
   - [ ] Tenants créés

### Tests Fonctionnels

```bash
# Test API login
curl -X POST https://boxibox.fr/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@boxibox.com","password":"password"}'

# Test scheduler
sudo -u www-data php artisan schedule:run -v

# Test queue
php artisan queue:work --once

# Test email
php artisan tinker
>>> \Illuminate\Support\Facades\Mail::raw('Test', fn($m) => $m->to('test@test.com'));
```

---

## 📞 Support et Assistance

### Logs Importants

- **Laravel** : `/var/www/boxibox/storage/logs/laravel.log`
- **Nginx** : `/var/log/nginx/boxibox-error.log`
- **PHP-FPM** : `/var/log/php8.4-fpm.log`
- **Supervisor** : `/var/www/boxibox/storage/logs/worker.log`

### Commandes de Debug

```bash
# Vérifier la configuration
php artisan about

# Vérifier les routes
php artisan route:list

# Vérifier les événements
php artisan event:list

# Vérifier les jobs en échec
php artisan queue:failed
```

### Contacts

- **Email** : support@boxibox.fr
- **Documentation** : https://docs.boxibox.fr
- **Status Page** : https://status.boxibox.fr

---

## 📝 Notes Importantes

1. **Rappels de Paiement** : S'exécutent automatiquement à 10h chaque jour. Ne pas désactiver le scheduler.

2. **Queue Workers** : Essentiels pour l'envoi d'emails. Surveiller avec Supervisor.

3. **Multi-tenancy** : Chaque site (tenant) a sa propre base de données. Gérer avec précaution.

4. **Backups** : Critique ! Vérifier quotidiennement que les backups fonctionnent.

5. **SSL Certificate** : Renouveler automatiquement avec certbot :
   ```bash
   sudo certbot renew --dry-run
   ```

6. **Monitoring** : Mettre en place un monitoring (UptimeRobot, Pingdom, etc.)

---

**Version** : 1.0
**Dernière mise à jour** : 2025-11-18
