# CAHIER DES SPÉCIFICATIONS FONCTIONNELLES DÉTAILLÉES

## Plateforme de Gestion de Self-Stockage Multi-Tenant Européenne

**Version :** 1.0  
**Date :** 15 novembre 2025  
**Technologie :** Laravel 11.x + Vue.js 3  
**Portée géographique :** Union Européenne (27 pays)

---

## TABLE DES MATIÈRES

1. [Vue d'ensemble du projet](#1-vue-densemble-du-projet)
2. [Contexte et objectifs](#2-contexte-et-objectifs)
3. [Architecture multi-tenant](#3-architecture-multi-tenant)
4. [Fonctionnalités détaillées](#4-fonctionnalités-détaillées)
5. [Modules spécifiques](#5-modules-spécifiques)
6. [Aspects techniques](#6-aspects-techniques)
7. [Conformité réglementaire européenne](#7-conformité-réglementaire-européenne)
8. [Sécurité et données](#8-sécurité-et-données)
9. [Intégrations tierces](#9-intégrations-tierces)
10. [Roadmap et phases](#10-roadmap-et-phases)

---

## 1. VUE D'ENSEMBLE DU PROJET

### 1.1 Description générale

Développement d'une plateforme SaaS multi-tenant de gestion complète de centres de self-stockage destinée au marché européen. La solution permettra à des opérateurs de centres de stockage (garde-meubles, box de stockage) de gérer l'intégralité de leurs opérations via une interface web moderne et mobile.

### 1.2 Proposition de valeur

- **Pour les opérateurs** : Solution clé en main pour gérer un ou plusieurs centres de stockage
- **Multi-sites** : Gestion centralisée de plusieurs sites depuis une seule interface
- **Multi-pays** : Support natif de 27 pays européens avec spécificités locales
- **Évolutif** : Architecture multi-tenant permettant de servir des centaines d'opérateurs
- **Complet** : De la réservation en ligne à la facturation automatique

### 1.3 Utilisateurs cibles

1. **Opérateurs indépendants** : Propriétaires de 1 à 5 centres de stockage
2. **Chaînes régionales** : Entreprises gérant 5 à 20 centres
3. **Groupes nationaux/internationaux** : Réseaux de plus de 20 centres
4. **Clients finaux** : Particuliers et professionnels louant des box

---

## 2. CONTEXTE ET OBJECTIFS

### 2.1 Analyse du marché

**Marché européen du self-stockage :**
- Marché estimé à 5+ milliards d'euros
- Croissance annuelle de 7-10%
- Fragmentation importante : nombreux acteurs locaux
- Digitalisation en cours mais inégale selon les pays
- Leaders : Shurgard, Una Pièce en Plus, HOMEBOX, Big Yellow, Safestore

**Opportunités :**
- Opérateurs cherchant à moderniser leurs outils
- Besoin de solutions complètes et abordables
- Réservation en ligne devenue standard post-COVID
- Automatisation des tâches administratives

### 2.2 Objectifs business

**Court terme (6-12 mois) :**
- Lancement MVP avec fonctionnalités core
- 20 centres pilotes dans 3 pays (France, Belgique, Allemagne)
- Validation du product-market fit

**Moyen terme (12-24 mois) :**
- 100+ centres clients
- Expansion dans 10 pays européens
- Intégrations avancées (contrôle d'accès, IoT)

**Long terme (24+ mois) :**
- 500+ centres clients
- Couverture de toute l'Europe
- Marketplace de services additionnels
- API publique pour partenaires

### 2.3 KPIs de succès

- Taux de satisfaction client > 85%
- Churn rate < 5% annuel
- Temps de réservation moyen < 3 minutes
- Taux de conversion visiteur → réservation > 15%
- Automatisation de 80%+ des tâches administratives

---

## 3. ARCHITECTURE MULTI-TENANT

### 3.1 Modèle de tenancy

**Approche choisie : Base de données unique avec schémas séparés**

```
Raisons du choix :
- Isolation logique forte des données (conformité RGPD)
- Performance optimale (pas de surcoûts de latence)
- Scalabilité horizontale facilitée
- Coûts d'infrastructure maîtrisés
- Sauvegardes et restaurations granulaires
```

### 3.2 Structure organisationnelle

```
Platform (Super Admin)
    ├── Tenant 1 (Opérateur A)
    │   ├── Site 1 (Paris Nord)
    │   │   ├── Building A
    │   │   │   ├── Floor 1
    │   │   │   │   ├── Box 001
    │   │   │   │   ├── Box 002
    │   │   │   │   └── ...
    │   │   │   └── Floor 2
    │   │   └── Building B
    │   └── Site 2 (Lyon Centre)
    │
    ├── Tenant 2 (Opérateur B)
    │   └── Site 1 (Bruxelles)
    │
    └── Tenant N...
```

### 3.3 Gestion des domaines

**Options d'accès pour chaque tenant :**

1. **Sous-domaine dédié** : `operateur-a.votreplatform.com`
2. **Domaine personnalisé** : `reservation.operateur-a.com` (CNAME)
3. **Accès unifié** : `votreplatform.com/operateur-a`

**Fonctionnalités :**
- Certificats SSL automatiques (Let's Encrypt)
- Redirection HTTP → HTTPS obligatoire
- White-labeling complet (logo, couleurs, favicon)

### 3.4 Isolation des données

**Sécurité multi-tenant :**

```php
// Middleware automatique dans Laravel
- Scoped queries automatiques par tenant_id
- Protection contre les fuites inter-tenant
- Validation des accès à chaque requête
- Logs d'audit par tenant

// Exemple de modèle
class Box extends Model {
    protected static function booted() {
        static::addGlobalScope(new TenantScope);
    }
}
```

**Données partagées (niveau plateforme) :**
- Référentiels pays/langues/devises
- Templates d'emails/documents
- Tarification plateforme
- Metrics agrégées (anonymisées)

---

## 4. FONCTIONNALITÉS DÉTAILLÉES

### 4.1 MODULE SITE WEB PUBLIC (Front-Office Client)

#### 4.1.1 Page d'accueil du centre

**Éléments obligatoires :**
- Présentation du centre (photos, vidéo)
- Localisation interactive (Google Maps)
- Horaires d'accès et informations de contact
- Call-to-action "Réserver maintenant"
- Témoignages clients
- Certifications et labels qualité
- Chat en direct (optionnel)

**SEO & Performance :**
- Contenu optimisé par ville/quartier
- Schema.org markup (LocalBusiness)
- Core Web Vitals optimisés
- Multi-langue selon pays
- Responsive mobile-first

#### 4.1.2 Catalogue de box en ligne

**Fonctionnalités de recherche :**

```
Filtres disponibles :
- Taille de box (m², m³)
- Fourchette de prix
- Type de stockage (intérieur/extérieur, climatisé, sécurisé)
- Étage (rez-de-chaussée, sous-sol, étages)
- Accessibilité (24/7, horaires, véhicule)
- Services inclus (assurance, cadenas, etc.)
- Date de disponibilité
- Durée souhaitée
```

**Affichage des box :**
- Vue grille avec photos
- Dimensions exactes (L x l x H)
- Prix mensuel TTC avec détails
- Disponibilité en temps réel
- Suggestions "Box similaires"
- Calculateur de volume (outil d'aide)
- Visite virtuelle 3D (optionnel)

#### 4.1.3 Tunnel de réservation

**Étape 1 : Sélection box**
- Récapitulatif box choisie
- Dates de début/fin de location
- Calcul automatique du montant
- Option assurance
- Services additionnels (cadenas, cartons, etc.)

**Étape 2 : Identification client**

```
Informations requises :
- Type de client : Particulier / Professionnel
- Civilité, nom, prénom
- Email (vérification double saisie)
- Téléphone mobile
- Date de naissance
- Adresse complète
- Pièce d'identité (upload)
- Justificatif de domicile (upload si requis)
```

**Étape 3 : Inventaire des biens**
- Liste descriptive des objets à stocker
- Valeur déclarée totale
- Validation des objets interdits
- Acceptation des CGV spécifiques stockage

**Étape 4 : Paiement**

```
Modes de paiement acceptés :
- Carte bancaire (Stripe, Adyen)
- SEPA Direct Debit (prélèvement automatique)
- Virement bancaire (paiement unique)
- PayPal (selon pays)
- Apple Pay / Google Pay

Montants perçus :
- 1er mois de location
- Caution/dépôt de garantie
- Frais de dossier (si applicable)
- Assurance (si souscrite)
```

**Étape 5 : Confirmation**
- Envoi email de confirmation
- Envoi SMS avec code d'accès
- Téléchargement du contrat signé électroniquement
- Ajout au calendrier (ics)
- Instructions d'accès au site

#### 4.1.4 Espace client en ligne

**Tableau de bord :**
- Vue d'ensemble des locations actives
- Statut des paiements
- Prochaine échéance
- Historique des factures
- Documents contractuels

**Gestion du contrat :**
- Prolongation de location
- Demande de résiliation
- Modification de box (changement de taille)
- Mise à jour des informations personnelles
- Gestion des modes de paiement

**Communication :**
- Messagerie avec le centre
- Notifications (email + SMS + push)
- Historique des échanges
- Base de connaissances / FAQ

**Services :**
- Achat en ligne (cadenas, cartons, etc.)
- Demande de service (déménagement, assurance complémentaire)
- Parrainage (offre promotionnelle)

#### 4.1.5 Calculateur de volume

**Outil d'aide à la décision :**

```
Méthodes proposées :
1. Sélection visuelle d'objets
   - Bibliothèque d'objets courants avec volumes
   - Catégories : meubles, électroménager, cartons, etc.
   - Calcul automatique du total

2. Saisie manuelle
   - Nombre de pièces à stocker
   - Surface habitable actuelle
   - Type de logement

3. Upload de photos
   - IA pour estimation de volume (roadmap)

Résultat :
- Volume estimé en m³
- Recommandation de taille de box
- Lien direct vers catalogue filtré
```

---

### 4.2 MODULE GESTION BACK-OFFICE (Admin Centre)

#### 4.2.1 Tableau de bord principal

**KPIs en temps réel :**

```
Indicateurs commerciaux :
- Taux d'occupation global et par type de box
- Revenus du mois en cours vs N-1
- Nombre de nouvelles locations (semaine/mois)
- Nombre de résiliations
- Panier moyen
- Durée moyenne de location

Indicateurs opérationnels :
- Tâches en attente (nettoyage, maintenance)
- Paiements en attente
- Relances impayés à effectuer
- Contrats arrivant à échéance (30/60/90 jours)
- Alertes (accès non autorisés, incidents)

Indicateurs marketing :
- Sources d'acquisition (site web, téléphone, visite)
- Taux de conversion du site web
- Performance campagnes publicitaires
```

**Graphiques et visualisations :**
- Évolution occupation sur 12 mois
- Revenus mensuels (bar chart)
- Répartition par type de box (pie chart)
- Prévisions de revenus
- Heatmap d'occupation des bâtiments

**Widgets personnalisables :**
- Drag & drop pour réorganiser
- Choix des KPIs affichés
- Export PDF/Excel des dashboards

#### 4.2.2 Gestion des sites et infrastructure

**Paramétrage site :**

```
Informations générales :
- Nom du site
- Adresse complète + coordonnées GPS
- Photos et visuels
- Horaires d'ouverture bureau
- Horaires d'accès clients (24/7 ou restreints)
- Contacts (manager, téléphone, email)
- Description et équipements

Configuration technique :
- Type de contrôle d'accès (code, badge, biométrique)
- Système de vidéosurveillance
- Alarme et sécurité
- Équipements disponibles (chariots, diables, ascenseurs)
```

**Gestion de l'infrastructure :**

```
Hiérarchie :
Site → Bâtiment → Étage → Zone → Box

Bâtiment :
- Nom/Numéro
- Type (intérieur/extérieur)
- Année de construction
- Surface totale
- Climatisation (oui/non)

Étage :
- Niveau
- Accessibilité (ascenseur, monte-charge)
- Largeur couloirs

Zone (optionnel) :
- Pour découpage logique (aile nord, section A)

Box (unité de location) :
- Numéro unique
- Dimensions (L x l x H en cm)
- Volume (m³)
- Surface (m²)
- Type (standard, climatisé, extérieur)
- Caractéristiques (accès véhicule, rez-de-chaussée)
- État (disponible, occupé, maintenance, hors-service)
- Prix de base mensuel
- Photos
```

**Plan interactif :**
- Visualisation en plan 2D du site
- Code couleur par statut (libre/occupé/maintenance)
- Clic sur box pour détails et actions rapides
- Upload de plans (PDF, images)
- Version 3D future

#### 4.2.3 Gestion des box et tarification

**Types de box standards :**

```
Catégories prédéfinies (personnalisables) :
- Mini box (1-3 m²) : cartons, archives
- Petite box (3-5 m²) : studio, moto
- Moyenne box (5-10 m²) : appartement 2 pièces
- Grande box (10-15 m²) : maison 3-4 pièces
- Très grande box (15+ m²) : maison entière, pro
```

**Stratégies de tarification :**

```
1. Tarification fixe
   - Prix mensuel fixe par box
   - Ajustement manuel possible

2. Tarification dynamique
   - Prix variable selon :
     * Taux d'occupation du site
     * Saisonnalité
     * Durée engagement (réduction si > 6/12 mois)
     * Taille de box
   - Règles configurables
   - Suggestions de prix optimaux (ML - phase 2)

3. Promotions
   - 1er mois offert
   - X% de réduction sur Y mois
   - Codes promo
   - Offres de parrainage
   - Offres étudiants/seniors

4. Tarifs pro
   - Grilles tarifaires entreprises
   - Réductions volume (plusieurs box)
   - Facturation centralisée
```

**Options tarifaires :**
- Assurance incluse ou en supplément
- Services additionnels (accès étendu, électricité)
- Frais de dossier
- Caution/dépôt de garantie

#### 4.2.4 Gestion des réservations et contrats

**Canal de réservation multiple :**

```
1. Réservation en ligne (site web)
   - Automatique avec paiement
   - En attente si paiement différé

2. Réservation par téléphone
   - Saisie manuelle dans le back-office
   - Envoi email/SMS de confirmation
   - Paiement CB à distance (lien sécurisé)

3. Réservation sur place
   - Walk-in client
   - Saisie directe dans le système
   - Paiement CB/espèces/chèque

4. Réservation par un agent commercial
   - Devis puis conversion en contrat
   - Workflow d'approbation si nécessaire
```

**Workflow de réservation :**

```
États possibles d'une réservation :
1. Demande → en attente de validation
2. Validée → en attente de paiement
3. Confirmée → paiement OK, contrat envoyé
4. Active → client a accès à la box
5. Suspendue → impayé en cours
6. Résiliée → fin de contrat
7. Annulée → avant activation

Transitions automatiques :
- Demande → Confirmée (si paiement CB OK)
- Confirmée → Active (à date début location)
- Active → Suspendue (si impayé > X jours)
- Demande → Annulée (si pas de paiement sous 48h)
```

**Gestion du contrat :**

```
Informations contrat :
- Numéro unique
- Date de début
- Durée initiale (1 mois, 3 mois, indéterminée)
- Date de fin (si durée déterminée)
- Reconduction automatique (oui/non)
- Client (lié)
- Box (liée)
- Tarif mensuel
- Fréquence facturation (mensuelle/trimestrielle/annuelle)
- Mode de paiement
- Inventaire des biens stockés
- Valeur déclarée

Documents générés :
- Contrat de location (PDF signé électroniquement)
- Conditions générales
- État des lieux d'entrée
- Mandat SEPA (si prélèvement)
- Attestation d'assurance
```

**Signature électronique :**
- Intégration DocuSign / Yousign / Universign
- Signature à distance (email avec lien)
- Signature sur tablette (sur place)
- Valeur légale dans toute l'UE
- Archivage sécurisé 10 ans minimum

**Actions sur contrat :**
- Renouvellement (automatique ou manuel)
- Modification de box (upgrade/downgrade)
- Suspension temporaire (vacances)
- Résiliation (préavis configurable)
- Transfert à un autre client

#### 4.2.5 Gestion des clients (CRM)

**Fiche client complète :**

```
Informations personnelles :
- Type : Particulier / Professionnel
- Civilité, nom, prénom / Raison sociale
- Email (principal + secondaire)
- Téléphones (fixe, mobile, professionnel)
- Date de naissance / SIRET
- Adresse postale
- Adresse de facturation (si différente)
- Langue préférée
- Préférences de communication

Documents :
- Pièce d'identité (CNI, passeport)
- Justificatif de domicile
- RIB (pour prélèvement)
- Kbis (si pro)
- Statut de vérification

Historique :
- Tous les contrats (actifs, passés)
- Historique des paiements
- Historique des communications
- Notes internes
- Tickets support

Segmentation :
- Tags personnalisables
- Scoring (bon/mauvais payeur)
- Origine (web, téléphone, prescripteur)
- VIP / Pro / Standard
```

**Fonctionnalités CRM :**

```
Communication :
- Envoi email individuel ou en masse
- Envoi SMS
- Historique des échanges
- Modèles de messages
- Campagnes marketing

Relances :
- Relances automatiques impayés
- Rappels échéance contrat
- Demande d'avis/satisfaction
- Offres promotionnelles ciblées

Gestion commerciale :
- Pipeline de prospects
- Devis et propositions commerciales
- Suivi des opportunités
- Taux de conversion

Reporting :
- Valeur vie client (LTV)
- Taux de rétention
- Durée moyenne de location
- Panier moyen
```

#### 4.2.6 Facturation et comptabilité

**Génération automatique des factures :**

```
Déclencheurs :
- Facturation récurrente mensuelle (à date anniversaire)
- Facturation à date fixe (ex: le 1er de chaque mois)
- Facturation à la demande
- Facturation services additionnels

Composition facture :
- En-tête avec logos et mentions légales
- Coordonnées émetteur/client
- Numéro de facture unique (séquence par année)
- Date d'émission et date d'échéance
- Lignes de facturation :
  * Location box [Numéro] - Période
  * Assurance
  * Services additionnels
  * Promotions/réductions
- Montant HT / TVA / TTC
- Modalités de paiement
- Mentions légales selon pays

Formats :
- PDF (envoi email)
- XML (exports comptables)
- UBL (format européen)
```

**TVA et fiscalité multi-pays :**

```
Gestion automatique par pays :
- Taux de TVA selon pays et type de service
- Règles d'exigibilité TVA
- Numéros de TVA intracommunautaire
- Autoliquidation si applicable
- Exports extra-UE

Conformité :
- RGPD pour données clients
- Facturation électronique (obligation FR 2024-2026)
- Conservation légale des documents
- Déclarations TVA assistées
```

**Gestion des paiements :**

```
Modes de paiement :
1. Carte bancaire (Stripe/Adyen)
   - Paiement ponctuel
   - Paiement récurrent (tokenization)
   - 3D Secure v2

2. Prélèvement SEPA
   - Mandat signé électroniquement
   - Fichier SEPA XML généré
   - Intégration bancaire directe
   - Gestion rejets et impayés

3. Virement bancaire
   - Génération QR code (European Payment Initiative)
   - Référence unique par facture
   - Rapprochement automatique

4. Espèces (sur place uniquement)
   - Ticket de caisse
   - Intégration avec caisse enregistreuse

5. Chèque
   - Suivi des remises
   - Numéro de chèque

Statuts paiement :
- En attente
- Payé
- Partiel
- En retard
- Rejeté
- Remboursé
```

**Gestion des impayés :**

```
Processus automatisé :
1. J+0 : Tentative de prélèvement
2. J+3 : Email de relance si échec
3. J+7 : SMS de relance + tentative 2
4. J+14 : Courrier recommandé + suspension accès
5. J+30 : Mise en demeure
6. J+45 : Procédure contentieuse

Actions possibles :
- Suspension accès à la box
- Facturation pénalités de retard
- Plan de paiement échelonné
- Vente des biens stockés (procédure légale)
```

**Exports comptables :**
- Format FEC (Fichier Écriture Comptable)
- Export vers Sage, Cegid, QuickBooks
- Journal des ventes
- Grand livre client
- Balance âgée

#### 4.2.7 Contrôle d'accès et sécurité

**Système de contrôle d'accès :**

```
Types de contrôle supportés :
1. Code PIN personnel
   - Généré automatiquement
   - Envoyé par SMS/email
   - Révocable instantanément

2. Badge RFID
   - Attribution lors de la signature
   - Lecture par bornes
   - Désactivation à distance

3. Application mobile
   - QR code dynamique
   - Bluetooth (future)
   - Validation 2FA

4. Reconnaissance biométrique (optionnel)
   - Empreinte digitale
   - Reconnaissance faciale
```

**Gestion des accès :**

```
Règles d'accès :
- Horaires autorisés par contrat
- Nombre d'accès max par jour
- Accès limité à zone spécifique
- Accès temporaire pour tiers (déménageurs)
- Accès accompagné pour visites

Logs d'accès :
- Horodatage entrée/sortie
- Identité du client
- Moyen d'accès utilisé
- Durée de présence
- Anomalies détectées

Alertes :
- Accès en dehors des horaires
- Tentatives d'accès refusées
- Présence prolongée inhabituelle
- Accès multiples simultanés
```

**Vidéosurveillance :**
- Intégration caméras IP
- Enregistrement continu
- Conservation selon réglementation (30j mini)
- Accès par client à ses propres entrées/sorties
- Exports vidéo sur demande (incidents)

**Alarme et incidents :**
- Détection intrusion
- Détection incendie
- Détection inondation
- Alertes en temps réel (email, SMS, push)
- Main courante des incidents
- Photos/vidéos associées

#### 4.2.8 Gestion des stocks et services

**Boutique de fournitures :**

```
Catalogue produits :
- Cartons de déménagement (plusieurs tailles)
- Cadenas et antivols
- Matériel d'emballage (papier bulle, scotch)
- Housses de protection
- Étiquettes et marqueurs

Gestion des stocks :
- Entrées/sorties
- Seuil d'alerte
- Commande fournisseur
- Inventaire
- Prix d'achat et prix de vente

Vente :
- En ligne sur espace client
- Sur place au comptoir
- Intégration dans facture client
- Retrait sur place ou livraison
```

**Services additionnels :**

```
Services proposés :
- Assurance complémentaire
- Location utilitaire (camionnette)
- Service de déménagement
- Mise en cartons
- Garde de clés
- Réception de colis
- Domiciliation commerciale

Configuration :
- Prix et conditions
- Disponibilité
- Prestataires partenaires
- Commission plateforme
```

#### 4.2.9 Maintenance et nettoyage

**Planning de maintenance :**

```
Types de tâches :
- Nettoyage box libérée
- État des lieux sortie
- Vérification équipements (portes, serrures)
- Entretien zones communes
- Vidanges et révisions (équipements)
- Travaux de rénovation

Workflow :
1. Création tâche (manuelle ou automatique)
2. Attribution à un agent
3. Planification date/heure
4. Exécution avec checklist
5. Validation et photos
6. Clôture

Statuts :
- À planifier
- Planifiée
- En cours
- Terminée
- Reportée
- Annulée
```

**Checklist qualité :**
- Liste de vérifications par type de tâche
- Photos avant/après obligatoires
- Signature agent
- Notation qualité
- Temps passé

#### 4.2.10 Marketing et communication

**Campagnes email :**

```
Types de campagnes :
- Newsletters mensuelles
- Offres promotionnelles
- Relances panier abandonné (réservation)
- Demandes d'avis après location
- Reconquête clients partis
- Upselling (proposer box plus grande)

Fonctionnalités :
- Éditeur drag & drop (ou intégration Mailchimp)
- Segmentation avancée
- A/B testing
- Tracking ouvertures et clics
- Désabonnement RGPD-compliant
```

**Campagnes SMS :**
- SMS transactionnels (codes d'accès, rappels)
- SMS promotionnels (avec opt-in)
- SMS d'urgence (incidents)

**Programme de parrainage :**
- Lien unique par client
- Récompense parrain et filleul
- Suivi des conversions
- Paiement automatique des récompenses

**Avis clients :**
- Intégration Google Reviews / Trustpilot
- Demande automatique après 30 jours
- Affichage sur site public
- Réponse aux avis

**Publicité en ligne :**
- Suivi des sources d'acquisition
- Pixels de conversion (Google Ads, Meta)
- UTM tracking
- ROI par canal

---

### 4.3 MODULE SUPER ADMIN (Gestion Plateforme)

#### 4.3.1 Gestion des tenants (opérateurs)

**Création d'un nouveau tenant :**

```
Informations tenant :
- Nom de l'entreprise
- Pays principal d'opération
- Langue par défaut
- Devise par défaut
- Contact administrateur
- Email et téléphone
- Logo et branding

Configuration initiale :
- Sous-domaine attribué
- Nombre de sites inclus dans le plan
- Plan tarifaire choisi
- Date début facturation
- Options activées

Onboarding :
- Email de bienvenue
- Guide de démarrage
- Session de formation (optionnelle)
- Import données existantes (si migration)
```

**Plans tarifaires plateforme :**

```
Formule Starter (0-50 box)
- 1 site
- Fonctionnalités core
- Support email
- 99€/mois

Formule Professional (50-200 box)
- 5 sites
- Fonctionnalités avancées
- Support prioritaire
- Intégrations incluses
- 299€/mois

Formule Enterprise (200+ box)
- Sites illimités
- Toutes fonctionnalités
- Support dédié
- API access
- SLA 99.9%
- Sur devis

Facturation :
- Mensuelle ou annuelle (-15%)
- Par nombre de box gérées
- Frais par transaction (0.5-1%)
- Options à la carte
```

#### 4.3.2 Monitoring plateforme

**Métriques globales :**

```
Performance :
- Nombre de tenants actifs
- Nombre total de sites
- Nombre total de box gérées
- MRR (Monthly Recurring Revenue)
- ARR (Annual Recurring Revenue)
- Churn rate
- LTV (Lifetime Value)

Utilisation :
- Requêtes API par minute
- Temps de réponse moyen
- Taux d'erreur
- Espace disque utilisé
- Bande passante
- Pics de charge

Tenants :
- Top 10 par revenus
- Top 10 par utilisation
- Tenants à risque (faible activité)
- Tenants en retard de paiement
```

**Alertes système :**
- Serveurs surchargés
- Erreurs critiques
- Tentatives d'intrusion
- Expiration certificats SSL
- Sauvegardes échouées

#### 4.3.3 Support et assistance

**Système de tickets :**

```
Canaux :
- Email support
- Chat en direct
- Téléphone (heures bureau)
- Base de connaissance

Niveaux de priorité :
- P1 : Critique (système down) - 1h
- P2 : Majeur (fonctionnalité bloquante) - 4h
- P3 : Mineur (bug non bloquant) - 24h
- P4 : Amélioration - 7j

Workflow :
1. Création ticket
2. Catégorisation automatique
3. Assignation agent
4. Traitement
5. Résolution
6. Satisfaction client
```

**Base de connaissances :**
- Articles d'aide par fonctionnalité
- Vidéos tutoriels
- Webinaires
- Documentation API
- Changelog et releases

#### 4.3.4 Facturation plateforme

**Facturation aux tenants :**
- Génération automatique mensuelle/annuelle
- Prélèvement automatique
- Relances impayés
- Suspension compte si défaut de paiement

**Reporting financier :**
- Revenus par plan
- Coûts d'infrastructure (AWS, services tiers)
- Marge par tenant
- Prévisions de revenus

---

## 5. MODULES SPÉCIFIQUES

### 5.1 Module multi-site

**Gestion centralisée :**
- Vue globale tous sites
- KPIs agrégés
- Comparaisons inter-sites
- Répartition de charge
- Transfert de clients entre sites

**Workflows inter-sites :**
- Réservation sur site A, modification vers site B
- Tarifs différents par site
- Promotions croisées
- Reporting consolidé

### 5.2 Module B2B (Entreprises)

**Fonctionnalités dédiées :**

```
Gestion compte entreprise :
- Stockage mutualisé (plusieurs box)
- Facturation centralisée
- Plusieurs utilisateurs autorisés
- Tarifs négociés
- Contrat-cadre

Services spécifiques :
- Archivage de documents (avec délais légaux)
- Stockage de matériel professionnel
- Gestion de flotte (déménagements fréquents)
- Reporting usage pour contrôle interne
```

### 5.3 Module Marketplace

**Écosystème de partenaires :**

```
Services tiers intégrables :
- Assurances complémentaires
- Déménageurs professionnels
- Garde-meubles temporaires
- Vente de fournitures
- Domiciliation

Fonctionnement :
- Catalogue de services
- Commission sur ventes
- API d'intégration
- Contrats partenaires
```

### 5.4 Module Analytics avancé

**Business Intelligence :**

```
Analyses disponibles :
- Prévisions d'occupation (ML)
- Optimisation tarifaire dynamique
- Segmentation clients (RFM)
- Détection de fraude
- Prédiction de churn
- Recommandations personnalisées

Visualisations :
- Dashboards interactifs (Tableau-like)
- Exports vers outils BI externes
- Rapports automatisés (hebdo, mensuel)
```

---

## 6. ASPECTS TECHNIQUES

### 6.1 Stack technologique

**Backend :**

```
Framework : Laravel 11.x
- Architecture MVC
- API RESTful + GraphQL (optionnel)
- Queue Jobs (Redis)
- Broadcasting (Pusher/Laravel Echo)
- Scheduler pour tâches récurrentes

Base de données :
- PostgreSQL 15+ (principal)
- Redis (cache + queues + sessions)
- Elasticsearch (recherche full-text)

Serveur web :
- Nginx + PHP 8.3 FPM
- Supervisor pour queues
- Cron pour scheduler
```

**Frontend :**

```
Framework : Vue.js 3 + Inertia.js
- Composition API
- TypeScript
- Tailwind CSS 3
- Headless UI components

Build :
- Vite
- Hot Module Replacement (HMR)
- Asset bundling optimisé

Admin :
- Laravel Nova (optionnel) ou custom
- Interface responsive
- Dark mode
```

**Infrastructure :**

```
Hosting : AWS / DigitalOcean / OVH
- Load Balancer (ALB)
- Auto-scaling EC2 ou Kubernetes
- RDS PostgreSQL Multi-AZ
- ElastiCache Redis
- S3 pour stockage fichiers
- CloudFront CDN

CI/CD :
- GitHub Actions / GitLab CI
- Tests automatisés
- Déploiement blue-green
- Rollback automatique si erreur

Monitoring :
- Sentry (erreurs)
- New Relic / DataDog (performance)
- UptimeRobot (disponibilité)
- Logs centralisés (ELK Stack)
```

### 6.2 Architecture applicative

**Design patterns :**

```
- Repository Pattern (abstraction données)
- Service Layer (logique métier)
- Event-Driven Architecture
- Queue Jobs pour traitements longs
- API Resources pour serialization
- Form Requests pour validation
```

**Modules Laravel :**

```php
app/
├── Models/
│   ├── Tenant/
│   ├── Site/
│   ├── Box/
│   ├── Contract/
│   ├── Customer/
│   ├── Invoice/
│   └── Payment/
├── Services/
│   ├── TenantService
│   ├── BookingService
│   ├── PricingService
│   ├── BillingService
│   └── AccessControlService
├── Repositories/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   ├── Admin/
│   │   └── Public/
│   ├── Middleware/
│   └── Requests/
├── Events/
├── Listeners/
├── Jobs/
└── Observers/
```

### 6.3 Packages Laravel recommandés

```
- spatie/laravel-permission : Gestion rôles/permissions
- spatie/laravel-multitenancy : Multi-tenancy
- spatie/laravel-medialibrary : Gestion fichiers
- spatie/laravel-backup : Sauvegardes automatiques
- barryvdh/laravel-dompdf : Génération PDF
- maatwebsite/excel : Import/export Excel
- laravel/sanctum : API authentication
- laravel/horizon : Monitoring queues
- laravel/telescope : Debugging (dev only)
- archtechx/tenancy : Alternative multi-tenant
```

### 6.4 API publique

**Endpoints principaux :**

```
Authentification :
POST /api/v1/auth/login
POST /api/v1/auth/register
POST /api/v1/auth/refresh

Recherche box :
GET /api/v1/sites/{siteId}/boxes
GET /api/v1/boxes/{boxId}
GET /api/v1/boxes/available

Réservations :
POST /api/v1/bookings
GET /api/v1/bookings/{bookingId}
PUT /api/v1/bookings/{bookingId}
DELETE /api/v1/bookings/{bookingId}

Paiements :
POST /api/v1/payments/intent
POST /api/v1/payments/confirm
GET /api/v1/invoices

Webhooks :
POST /webhooks/stripe
POST /webhooks/docusign
POST /webhooks/access-control

Versioning : /api/v1, /api/v2
Rate limiting : 100 req/min (authentifié), 20 req/min (public)
Documentation : Swagger/OpenAPI
```

### 6.5 Sécurité

**Mesures de sécurité :**

```
Application :
- CSRF protection
- XSS prevention
- SQL injection prevention (ORM)
- Input validation stricte
- Output encoding
- Rate limiting
- CORS configuré

Authentification :
- Bcrypt/Argon2 pour passwords
- 2FA optionnelle (TOTP)
- Session timeout
- Force HTTPS
- Secure cookies

Données :
- Encryption at rest (AES-256)
- Encryption in transit (TLS 1.3)
- Secrets dans environment variables
- Clés de chiffrement rotatives

Conformité :
- RGPD by design
- Droit à l'oubli
- Portabilité des données
- Consentements tracés
- DPO intégré

Audits :
- Logs d'accès
- Logs de modifications
- Tests de pénétration annuels
- Veille CVE
- Mises à jour régulières
```

### 6.6 Performance

**Optimisations :**

```
Backend :
- Query caching (Redis)
- Eager loading (N+1 prevention)
- Database indexing optimal
- Queue jobs pour traitements longs
- API pagination
- Response caching

Frontend :
- Lazy loading images
- Code splitting
- Asset compression (gzip/brotli)
- CDN pour statics
- Service Worker (PWA)
- Skeleton screens

Base de données :
- Indexes composites
- Partitioning si gros volumes
- Read replicas
- Connection pooling
```

**Objectifs de performance :**
- Time to First Byte < 200ms
- First Contentful Paint < 1s
- Time to Interactive < 3s
- Lighthouse score > 90

---

## 7. CONFORMITÉ RÉGLEMENTAIRE EUROPÉENNE

### 7.1 RGPD (Règlement Général sur la Protection des Données)

**Principes appliqués :**

```
Licéité du traitement :
- Consentement explicite clients
- Exécution du contrat
- Obligation légale
- Intérêt légitime documenté

Minimisation des données :
- Collecter uniquement le nécessaire
- Pas de données excessives
- Durée de conservation limitée

Droits des personnes :
- Droit d'accès
- Droit de rectification
- Droit à l'effacement (droit à l'oubli)
- Droit à la portabilité
- Droit d'opposition
- Droit à la limitation du traitement

Implémentation technique :
- Export données personnelles (JSON/XML)
- Anonymisation des données (après suppression)
- Pseudonymisation si possible
- Chiffrement
- Traçabilité des consentements
- Journal des accès aux données
```

**Fonctionnalités RGPD :**

```
Espace client :
- Télécharger mes données
- Supprimer mon compte
- Gérer mes consentements
- Historique d'utilisation

Back-office admin :
- Registre des traitements
- Gestion des demandes RGPD
- Rapport d'impact (DPIA)
- Notifications violations (72h)

Mentions légales :
- Politique de confidentialité
- Cookies banner conforme
- DPO identifié
- Durées de conservation affichées
```

### 7.2 Facturation électronique

**Obligations par pays :**

```
France (2024-2026) :
- Facturation électronique B2B obligatoire
- Transmission via plateforme Chorus Pro
- Format Factur-X ou UBL
- e-Reporting pour B2C

Italie :
- Sistema di Interscambio (SDI)
- Format FatturaPA
- Obligatoire depuis 2019

Allemagne, Espagne, Belgique :
- En cours de déploiement
- Formats XML harmonisés

Implémentation :
- Génération multi-format
- Connexion aux plateformes nationales
- Archivage légal
- Horodatage
```

### 7.3 Accessibilité (Directive UE 2016/2102)

**Standards WCAG 2.1 AA :**

```
- Contrastes de couleurs suffisants
- Navigation clavier complète
- Lecteurs d'écran supportés
- Sous-titres pour vidéos
- Alternatives textuelles images
- Formulaires accessibles
- Pas de saisie dépendante du temps seul

Tests :
- WAVE / axe DevTools
- Audit manuel
- Tests utilisateurs en situation de handicap
```

### 7.4 Directive Services de Paiement 2 (DSP2)

**Strong Customer Authentication :**

```
- 3D Secure v2 obligatoire
- Authentification à 2 facteurs
- Exemptions possibles (low-risk)
- Monitoring des transactions
```

### 7.5 Réglementation stockage

**Spécificités par pays :**

```
France :
- Loi Hamon (résiliation)
- Code civil (droit de rétention)
- Procédure Dailly (recouvrement)

Belgique :
- Loi sur les contrats de consommation
- Médiateur de la consommation

Allemagne :
- BGB (Code civil)
- Délais de préavis spécifiques

Clauses obligatoires :
- Inventaire des biens
- Valeur déclarée
- Assurance
- Conditions d'accès
- Résiliation et préavis
- Droit de rétention
- Procédure impayés
```

---

## 8. SÉCURITÉ ET DONNÉES

### 8.1 Sauvegarde et disaster recovery

**Stratégie de backup :**

```
Fréquences :
- Base de données : toutes les heures (incrémentielles)
- Full backup : quotidien (3h du matin)
- Snapshots serveurs : quotidien
- Fichiers uploadés : temps réel (S3 versioning)

Rétention :
- Horaire : 24h
- Quotidien : 30 jours
- Hebdomadaire : 12 semaines
- Mensuel : 12 mois
- Annuel : 7 ans (légal)

Stockage :
- Primaire : même région
- Secondaire : région différente (DR)
- Tertiaire : offsite froid (compliance)

Tests de restauration :
- Mensuel : restauration complète environnement de test
- Trimestriel : test disaster recovery complet
- Documentation procédures à jour
```

**RTO / RPO :**
- Recovery Time Objective : < 4h
- Recovery Point Objective : < 1h

### 8.2 Gestion des fichiers

**Types de fichiers :**

```
Documents clients :
- Pièces d'identité (CNI, passeport)
- Justificatifs domicile
- Contrats signés
- Factures
- Photos box (état des lieux)

Documents système :
- Exports comptables
- Logs système
- Sauvegardes
- Certificats SSL

Stockage :
- Amazon S3 ou équivalent
- Buckets séparés par tenant
- Versioning activé
- Lifecycle policies (archivage Glacier)
- Chiffrement server-side (AES-256)

Upload :
- Taille max : 10 MB par fichier
- Formats autorisés : PDF, JPG, PNG, DOCX
- Scan antivirus automatique
- Redimensionnement images automatique
```

### 8.3 Chiffrement

**Données en transit :**
- TLS 1.3 minimum
- Certificats SSL Let's Encrypt (auto-renew)
- HSTS activé
- Perfect Forward Secrecy

**Données au repos :**
- Base de données chiffrée (PostgreSQL encryption)
- Fichiers S3 chiffrés (SSE-S3 ou SSE-KMS)
- Secrets dans AWS Secrets Manager / HashiCorp Vault
- Clés de chiffrement rotatives

**Données sensibles :**
- PII hashées ou chiffrées
- Numéros de carte bancaire jamais stockés (tokens Stripe)
- Logs anonymisés
- Exports chiffrés (AES-256)

---

## 9. INTÉGRATIONS TIERCES

### 9.1 Paiement

**Stripe (priorité) :**
```
- Payment Intents API
- Payment Methods
- Subscriptions
- Mandats SEPA Direct Debit
- Webhooks (payment succeeded, failed, etc.)
- Stripe Connect (marketplace - phase 2)
- SCA compliance
```

**Adyen (alternative) :**
```
- Support multi-devises
- Optimisation taux d'acceptation
- Local payment methods
- Recurring billing
```

### 9.2 Signature électronique

**DocuSign / Yousign / Universign :**
```
- API de signature
- Templates de documents
- Signature à distance
- Signature sur tablette
- Webhooks (document signé, refusé)
- Archivage certifié
```

### 9.3 Contrôle d'accès

**Intégration avec systèmes :**

```
Protocoles supportés :
- API REST
- MQTT (IoT)
- Webhooks entrants

Fabricants compatibles :
- Salto Systems
- ASSA ABLOY
- Nuki Smart Locks
- Solutions custom

Fonctionnalités :
- Création codes PIN à distance
- Révocation instantanée
- Logs d'accès temps réel
- Gestion badges RFID
```

### 9.4 Communication

**Emails transactionnels :**
```
- SendGrid / Mailgun / Amazon SES
- Templates MJML
- Tracking opens/clicks
- SPF/DKIM/DMARC configurés
```

**SMS :**
```
- Twilio / Vonage
- Envoi international
- Numéros courts par pays
- Opt-in/opt-out gestion
```

**Push notifications :**
```
- Firebase Cloud Messaging
- OneSignal
- Segmentation
- A/B testing
```

### 9.5 Comptabilité

**Export vers :**
```
- Sage
- QuickBooks
- Cegid
- EBP
- Format FEC
- Format CSV personnalisable
```

### 9.6 Maps et localisation

**Google Maps Platform :**
```
- Maps JavaScript API
- Places API (autocomplete adresses)
- Geocoding API
- Distance Matrix API
- Street View (visite virtuelle centre)
```

### 9.7 Analytics

**Google Analytics 4 :**
```
- Tracking conversions
- Événements personnalisés
- E-commerce enhanced
- User-ID tracking
```

**Google Tag Manager :**
```
- Gestion centralisée des tags
- Pixels publicitaires
- A/B testing tools
```

---

## 10. ROADMAP ET PHASES

### 10.1 Phase 1 : MVP (Mois 1-4)

**Objectif :** Lancer une version minimale fonctionnelle

**Fonctionnalités incluses :**

```
✅ Multi-tenancy basique
✅ Gestion 1 site par tenant
✅ Gestion box (CRUD simple)
✅ Réservation en ligne simple
✅ Espace client basique
✅ Gestion des contrats
✅ Facturation manuelle
✅ Paiement CB (Stripe)
✅ Tableau de bord simple
✅ 3 langues : FR, EN, NL
✅ Responsive mobile

Livrables :
- Application fonctionnelle
- Documentation technique
- Guide utilisateur
- 3 tenants pilotes
```

**Ressources :**
- 1 Lead Developer Full-Stack (Laravel + Vue.js)
- 1 Developer Full-Stack
- 1 UI/UX Designer
- 1 Product Owner
- 1 QA Tester (part-time)

**Budget estimé :** 80 000 - 100 000 €

### 10.2 Phase 2 : Fonctionnalités avancées (Mois 5-8)

**Objectif :** Enrichir l'offre et automatiser

**Fonctionnalités ajoutées :**

```
✅ Multi-sites (illimité)
✅ Facturation automatique récurrente
✅ Prélèvement SEPA
✅ Signature électronique
✅ CRM complet
✅ Gestion des impayés automatisée
✅ Contrôle d'accès (intégration 1er système)
✅ Application mobile client (React Native)
✅ Module B2B
✅ Marketing automation
✅ 10 langues supplémentaires
✅ Analytics avancé

Livrables :
- Features déployées en production
- App mobile iOS + Android
- Intégrations opérationnelles
- 20 clients actifs
```

**Ressources :**
- Équipe Phase 1
- +1 Mobile Developer
- +1 DevOps Engineer

**Budget estimé :** 100 000 - 120 000 €

### 10.3 Phase 3 : Scale et Intelligence (Mois 9-12)

**Objectif :** Scaler et introduire l'IA

**Fonctionnalités ajoutées :**

```
✅ Tarification dynamique (ML)
✅ Prévisions d'occupation (ML)
✅ Chatbot IA (support client)
✅ Reconnaissance d'objets (inventaire par photo)
✅ API publique v1
✅ Marketplace partenaires
✅ Module franchises
✅ Intégrations multiples contrôle d'accès
✅ Vidéosurveillance intégrée
✅ IoT (capteurs température, humidité)
✅ White-labeling complet

Livrables :
- Fonctionnalités IA en production
- API publique documentée
- Marketplace lancée
- 50+ clients actifs
```

**Ressources :**
- Équipe Phase 2
- +1 Data Scientist
- +1 ML Engineer

**Budget estimé :** 120 000 - 150 000 €

### 10.4 Phase 4 : Expansion et consolidation (Mois 13-18)

**Objectif :** Devenir leader européen

**Axes de développement :**

```
✅ Expansion géographique (27 pays UE)
✅ Conformité locale totale
✅ Partenariats stratégiques
✅ Programme de revente (revendeurs agréés)
✅ Fonctionnalités entreprise avancées
✅ Modules verticaux (vin, archives, etc.)
✅ Intégration ERP externes (SAP, etc.)
✅ SSO entreprise (SAML, LDAP)
✅ Haute disponibilité (99.95%)
✅ Support 24/7

Objectifs business :
- 200+ clients
- 5000+ centres gérés
- 100 000+ clients finaux actifs
- Break-even opérationnel
```

**Ressources :**
- Équipe Phase 3
- +2 Sales
- +3 Support
- +1 Customer Success Manager

**Budget estimé :** 200 000 - 250 000 €

---

## ANNEXES

### A. Glossaire

```
Tenant : Client de la plateforme (opérateur de centre(s))
Site : Centre de stockage physique
Box : Unité de stockage louable
Contrat : Accord de location entre opérateur et client final
SaaS : Software as a Service
Multi-tenant : Architecture permettant de servir plusieurs clients indépendants
RGPD : Règlement Général sur la Protection des Données
SEPA : Single Euro Payments Area
CRM : Customer Relationship Management
ERP : Enterprise Resource Planning
API : Application Programming Interface
ML : Machine Learning
IoT : Internet of Things
```

### B. Références réglementaires

```
- RGPD : Règlement (UE) 2016/679
- DSP2 : Directive (UE) 2015/2366
- Facturation électronique : Directive 2014/55/UE
- Accessibilité : Directive (UE) 2016/2102
- Code de la consommation (FR)
- Code civil (par pays)
```

### C. Concurrents analysés

```
Logiciels :
- Buxida (FR) - cible petits opérateurs
- Kinnovis (FR) - moderne avec IA
- Win Box Manager (FR)
- Space Manager (UK)
- SiteLink (US/Monde) - leader mondial
- Syrasoft (US)
- Empower (US)

Opérateurs :
- Shurgard (EU)
- Une Pièce en Plus (FR)
- HOMEBOX (FR/BE)
- Big Yellow (UK)
- Safestore (UK/FR/ES)
```

### D. Stack technique détaillée

**Backend :**
```
- PHP 8.3+
- Laravel 11.x
- PostgreSQL 15+
- Redis 7+
- Elasticsearch 8+
```

**Frontend :**
```
- Vue.js 3
- TypeScript 5
- Tailwind CSS 3
- Vite 5
- Inertia.js
```

**Mobile :**
```
- React Native 0.73+
- Expo
```

**Infrastructure :**
```
- Docker + Kubernetes
- Nginx
- AWS (EC2, RDS, S3, CloudFront)
- GitHub Actions
```

**Monitoring :**
```
- Sentry
- New Relic / DataDog
- UptimeRobot
- ELK Stack
```

---

## CONCLUSION

Ce cahier des spécifications décrit une plateforme SaaS complète et moderne de gestion de self-stockage destinée au marché européen. L'architecture multi-tenant choisie permet de servir efficacement des centaines d'opérateurs tout en garantissant l'isolation et la sécurité des données.

Le choix de Laravel comme framework principal est judicieux pour ce projet car :
- Écosystème mature et riche
- Excellente documentation
- Large communauté
- Packages nombreux et de qualité
- Performance éprouvée
- Sécurité robuste
- Support long-terme

Le développement par phases permet de :
- Valider rapidement le product-market fit
- Générer des revenus dès le MVP
- Itérer en fonction des retours utilisateurs
- Maîtriser les investissements
- Construire une base technique solide

**Prochaines étapes recommandées :**
1. Validation de ce cahier des charges avec les parties prenantes
2. Maquettes UI/UX détaillées
3. Architecture technique détaillée
4. Choix des prestataires/intégrations
5. Constitution de l'équipe de développement
6. Démarrage Phase 1

**Contact :**
Pour toute question ou précision sur ce document, contactez l'équipe projet.

---

**Version :** 1.0  
**Date :** 15 novembre 2025  
**Auteur :** Équipe Produit  
**Statut :** Validé pour développement
