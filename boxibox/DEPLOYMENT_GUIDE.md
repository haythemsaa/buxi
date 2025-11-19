# 🚀 Guide de Déploiement Boxibox - Phase 1
## Production Deployment Guide - 100% Complete

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Installation Automatique](#installation-automatique)
3. [Installation Manuelle](#installation-manuelle)
4. [Configuration](#configuration)
5. [Webhooks](#webhooks)
6. [Tests](#tests)
7. [Mise en Production](#mise-en-production)
8. [Monitoring](#monitoring)
9. [Dépannage](#dépannage)

---

## 🔧 Prérequis

### Serveur

**Recommandé pour 100-500 boxes** :
- **CPU** : 2-4 cores
- **RAM** : 4-8 GB
- **Disque** : 50 GB SSD
- **OS** : Ubuntu 22.04 LTS ou Debian 12

### Logiciels Requis

```bash
# Versions minimales
PHP >= 8.2
Composer >= 2.5
Node.js >= 20.x
NPM >= 10.x
MySQL >= 8.0 ou PostgreSQL >= 14
Redis >= 7.0
Nginx >= 1.24 ou Apache >= 2.4
```

### Comptes Externes

- **Stripe** : Compte Stripe (test + live) - https://dashboard.stripe.com
- **PayPal** : Compte Business PayPal - https://developer.paypal.com
- **Serveur SMTP** : Pour emails (Gmail, Mailgun, SendGrid, etc.)

---

## ⚡ Installation Automatique

### Option 1 : Script de Déploiement (Recommandé)

```bash
# 1. Cloner le repository
git clone https://github.com/votre-repo/boxibox.git
cd boxibox

# 2. Rendre le script exécutable
chmod +x deploy.sh

# 3. Exécuter le déploiement
./deploy.sh
```

Le script automatique effectue :
- ✅ Vérification environnement (PHP, Composer, Node.js)
- ✅ Installation dépendances (Composer + NPM)
- ✅ Configuration .env
- ✅ Génération clé application
- ✅ Compilation assets
- ✅ Migrations + seeders
- ✅ Optimisation cache
- ✅ Configuration permissions

**Durée estimée** : 5-10 minutes

---

## 🛠️ Installation Manuelle

### Étape 1 : Environnement

```bash
# Installation PHP 8.4 (Ubuntu)
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.4 php8.4-fpm php8.4-mysql php8.4-redis \
    php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip \
    php8.4-gd php8.4-intl php8.4-bcmath

# Installation Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Installation MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Installation Redis
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### Étape 2 : Application

```bash
# 1. Cloner le projet
git clone https://github.com/votre-repo/boxibox.git
cd boxibox

# 2. Installer dépendances Composer
composer install --no-dev --optimize-autoloader

# 3. Installer dépendances NPM
npm install

# 4. Configuration environnement
cp .env.example .env
cat .env.example.payments >> .env
php artisan key:generate

# 5. Configurer .env
nano .env  # Éditer avec vos valeurs

# 6. Compiler assets
npm run build

# 7. Migrations
php artisan migrate --force
php artisan db:seed --class=DefaultPricingRulesSeeder

# 8. Optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# 9. Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

---

## ⚙️ Configuration

### 1. Fichier .env

```env
# Application
APP_NAME=Boxibox
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votredomaine.com
APP_KEY=base64:...  # Généré automatiquement

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boxibox
DB_USERNAME=boxibox_user
DB_PASSWORD=votre_mot_de_passe_sécurisé

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre.email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votredomaine.com
MAIL_FROM_NAME="${APP_NAME}"

# Stripe (TEST)
STRIPE_KEY=pk_test_51...
STRIPE_SECRET=sk_test_51...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=eur

# Stripe (PRODUCTION - décommenter en prod)
# STRIPE_KEY=pk_live_51...
# STRIPE_SECRET=sk_live_51...
# STRIPE_WEBHOOK_SECRET=whsec_...

# PayPal (SANDBOX)
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_MODE=sandbox
PAYPAL_CURRENCY=EUR

# PayPal (PRODUCTION - décommenter en prod)
# PAYPAL_MODE=live
# PAYPAL_CLIENT_ID=...
# PAYPAL_SECRET=...

# Dynamic Pricing
PRICING_MIN_PERCENTAGE=50
PRICING_MAX_PERCENTAGE=150
PRICING_CACHE_TTL=300

# Payment Gateway
PAYMENT_DEFAULT_GATEWAY=stripe
PAYMENT_FALLBACK_ENABLED=true
PAYMENT_FALLBACK_GATEWAY=sepa
```

### 2. Configuration Nginx

```nginx
# /etc/nginx/sites-available/boxibox

server {
    listen 80;
    listen [::]:80;
    server_name votredomaine.com www.votredomaine.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name votredomaine.com www.votredomaine.com;
    root /var/www/boxibox/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/votredomaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votredomaine.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    index index.php;

    charset utf-8;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;

    # Max upload size
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
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

### 3. Configuration SSL (Let's Encrypt)

```bash
# Installation Certbot
sudo apt install certbot python3-certbot-nginx

# Obtention certificat
sudo certbot --nginx -d votredomaine.com -d www.votredomaine.com

# Auto-renouvellement
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

### 4. Configuration Crontab

```bash
# Éditer crontab pour www-data
sudo crontab -u www-data -e

# Ajouter cette ligne
* * * * * cd /var/www/boxibox && php artisan schedule:run >> /dev/null 2>&1
```

Le scheduler exécutera automatiquement :
- **2h00** : Nettoyage réservations expirées
- **2h30** : Mise à jour pricing dynamique
- **1h00** (1er du mois) : Génération factures mensuelles
- **3h00** (1er du mois) : Expiration points fidélité
- **9h00** : Rappels renouvellement contrats
- **10h00** : Traitement rappels paiement

---

## 🔗 Webhooks

### Stripe Webhook

#### 1. Configuration dans Stripe Dashboard

1. Aller sur https://dashboard.stripe.com/webhooks
2. Cliquer "Add endpoint"
3. URL : `https://votredomaine.com/webhooks/stripe`
4. Événements à sélectionner :
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
   - `customer.created`
   - `customer.updated`
5. Copier le "Signing Secret" (commence par `whsec_`)
6. Ajouter dans `.env` : `STRIPE_WEBHOOK_SECRET=whsec_...`

#### 2. Test Webhook Stripe

```bash
# Installation Stripe CLI
curl -s https://packages.stripe.com/api/security/keypair/stripe-cli-gpg/public | gpg --dearmor | sudo tee /usr/share/keyrings/stripe.gpg
echo "deb [signed-by=/usr/share/keyrings/stripe.gpg] https://packages.stripe.com/stripe-cli-debian-local stable main" | sudo tee -a /etc/apt/sources.list.d/stripe.list
sudo apt update
sudo apt install stripe

# Login
stripe login

# Forward webhooks en local
stripe listen --forward-to localhost:8000/webhooks/stripe

# Tester un événement
stripe trigger payment_intent.succeeded
```

### PayPal Webhook

#### 1. Configuration dans PayPal Dashboard

1. Aller sur https://developer.paypal.com/dashboard
2. Aller dans Apps & Credentials
3. Sélectionner votre App
4. Aller dans "Webhooks"
5. Cliquer "Add Webhook"
6. URL : `https://votredomaine.com/webhooks/paypal`
7. Événements à sélectionner :
   - `PAYMENT.SALE.COMPLETED`
   - `PAYMENT.SALE.REFUNDED`
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.REFUNDED`

#### 2. Test Webhook PayPal

```bash
# Test avec curl
curl -X POST https://votredomaine.com/webhooks/paypal \
  -H "Content-Type: application/json" \
  -d '{
    "event_type": "PAYMENT.SALE.COMPLETED",
    "resource": {
      "id": "TEST123",
      "amount": {
        "total": "100.00",
        "currency": "EUR"
      },
      "state": "completed"
    }
  }'
```

### Vérification Webhooks

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs Nginx
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

---

## ✅ Tests

### Tests Unitaires

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=PricingRuleTest
php artisan test --filter=DynamicPricingServiceTest
php artisan test --filter=StripeWebhookTest
php artisan test --filter=PayPalWebhookTest

# Avec coverage
php artisan test --coverage
```

### Tests Manuels

#### 1. Test Revenue Management

```bash
# Mode dry-run
php artisan pricing:update-all --dry-run

# Site spécifique
php artisan pricing:update-all --site=1

# Réel
php artisan pricing:update-all
```

Vérifier dashboard : https://votredomaine.com/admin/revenue-management

#### 2. Test Paiements

**Test Stripe** :
- Carte test : `4242 4242 4242 4242`
- Date : n'importe quelle date future
- CVC : n'importe quel 3 chiffres
- ZIP : n'importe quel 5 chiffres

**Test 3D Secure** :
- Carte : `4000 0027 6000 3184`
- Suivre le flow 3DS

**Test échec** :
- Carte : `4000 0000 0000 0002` (declined)

#### 3. Test Portail Client

1. Créer un compte client test
2. Créer un contrat actif
3. Générer une facture
4. Tester paiement
5. Télécharger PDF
6. Modifier profil
7. Demander résiliation

---

## 🚀 Mise en Production

### Checklist Pré-Production

- [ ] `.env` configuré avec valeurs production
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Clés Stripe LIVE configurées
- [ ] PayPal en mode LIVE
- [ ] SSL actif (HTTPS)
- [ ] Webhooks configurés
- [ ] Cron scheduler actif
- [ ] Backups automatiques configurés
- [ ] Monitoring actif
- [ ] Tests end-to-end passés
- [ ] Documentation à jour

### Activation Pricing Dynamique

```bash
php artisan tinker
```

```php
// Activer pour toutes les boxes
Box::query()->update(['use_dynamic_pricing' => true]);

// Ou par site
Box::whereHas('floor.building', fn($q) =>
    $q->where('site_id', 1)
)->update(['use_dynamic_pricing' => true]);

// Calculer prix initiaux
$service = app(\App\Services\DynamicPricingService::class);
$service->updateSitePrices(App\Models\Site::find(1));

// Vérifier
Box::where('use_dynamic_pricing', true)->count();
// Devrait afficher le nombre de boxes activées
```

### Passage Stripe en LIVE

1. Dans `.env`, remplacer :
```env
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...  # Nouveau secret pour prod
```

2. Reconfigurer webhook dans Stripe Dashboard (mode Live)
3. Redémarrer application :
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
sudo systemctl restart php8.4-fpm
```

### Passage PayPal en LIVE

1. Dans `.env` :
```env
PAYPAL_MODE=live
PAYPAL_CLIENT_ID=...  # Credentials LIVE
PAYPAL_SECRET=...     # Credentials LIVE
```

2. Reconfigurer webhooks dans PayPal Dashboard (mode Live)
3. Redémarrer application

---

## 📊 Monitoring

### Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Erreurs uniquement
tail -f storage/logs/laravel.log | grep ERROR

# Webhook logs
tail -f storage/logs/laravel.log | grep webhook
```

### Métriques à Surveiller

**Application** :
- Temps de réponse moyen (< 200ms)
- Taux d'erreur (< 0.1%)
- Uptime (> 99.9%)

**Business** :
- Taux conversion paiements (> 90%)
- MRR (Monthly Recurring Revenue)
- Taux occupation
- Revenue gap

**Serveur** :
- CPU usage (< 70%)
- RAM usage (< 80%)
- Disk space (> 20% libre)
- Redis memory (< 80%)

### Outils Recommandés

- **Monitoring** : New Relic, DataDog, ou Laravel Telescope
- **Uptime** : UptimeRobot, Pingdom
- **Logs** : Papertrail, Loggly, ou Sentry
- **Backups** : Backupninja, Restic

---

## 🐛 Dépannage

### Problème 1 : Webhook Stripe ne fonctionne pas

**Symptômes** : Paiements réussis mais factures restent en "pending"

**Solutions** :
1. Vérifier signature secret dans `.env`
2. Vérifier logs : `tail -f storage/logs/laravel.log | grep stripe`
3. Tester avec Stripe CLI : `stripe listen --forward-to localhost:8000/webhooks/stripe`
4. Vérifier que CSRF est désactivé pour `/webhooks/*` dans `VerifyCsrfToken.php`

### Problème 2 : Pricing dynamique ne se met pas à jour

**Solutions** :
1. Vérifier cron : `sudo crontab -u www-data -l`
2. Exécuter manuellement : `php artisan pricing:update-all --dry-run`
3. Vérifier Redis : `redis-cli ping`
4. Vérifier logs scheduler : `tail -f storage/logs/laravel.log | grep schedule`

### Problème 3 : Erreur 500 après déploiement

**Solutions** :
1. Vérifier permissions :
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

2. Vider caches :
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

3. Vérifier logs :
```bash
tail -f storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log
```

### Problème 4 : Paiements Stripe échouent

**Solutions** :
1. Vérifier clés API dans `.env`
2. Vérifier montant (> 0.50€ pour Stripe)
3. Tester avec carte test en mode test
4. Vérifier logs Stripe Dashboard : https://dashboard.stripe.com/logs

### Problème 5 : Redis connexion refused

**Solutions** :
```bash
# Vérifier status
sudo systemctl status redis-server

# Redémarrer
sudo systemctl restart redis-server

# Vérifier connexion
redis-cli ping
# Devrait afficher: PONG
```

---

## 📚 Ressources

### Documentation

- **Boxibox** :
  - [COMPLETION_SUMMARY.md](./COMPLETION_SUMMARY.md) - Détails implémentation
  - [STATUS.md](./STATUS.md) - État du projet
  - [ROADMAP.md](./ROADMAP.md) - Phases 2 & 3

- **Laravel** : https://laravel.com/docs/12.x
- **Stripe** : https://stripe.com/docs
- **PayPal** : https://developer.paypal.com/docs

### Support

**Issues GitHub** : https://github.com/votre-repo/boxibox/issues
**Email** : support@votredomaine.com

---

## 🎉 Succès du Déploiement

Si vous voyez cette page sans erreur, félicitations ! 🎊

**Votre application Boxibox Phase 1 est maintenant déployée.**

**Prochaines étapes** :
1. Tester tous les flux utilisateur
2. Monitorer les premiers paiements
3. Analyser les métriques revenue management
4. Planifier Phase 2 (CRM, Smart Locks, Mobile App)

**ROI Attendu** :
- **Revenue** : +20-30% dans les 3 premiers mois
- **Conversions** : +30% grâce aux nouveaux modes de paiement
- **Support** : -50% tickets grâce au portail client

**Bonne chance ! 🚀**

---

**Document Version** : 1.0
**Date** : 19 Janvier 2025
**Phase** : Phase 1 Quick Wins - 100% Complete
