# 🎨 Guide d'Utilisation - Gestionnaire de Plans Visuels

> **Créez et organisez vos sites de self-storage en quelques minutes !**

---

## 📋 Table des Matières

1. [Démarrage Rapide](#-démarrage-rapide-3-minutes)
2. [Créer Votre Infrastructure](#-créer-votre-infrastructure)
3. [Organiser Visuellement](#-organiser-visuellement-vos-boxes)
4. [Fonctionnalités Avancées](#-fonctionnalités-avancées)
5. [Trucs et Astuces](#-trucs-et-astuces)

---

## ⚡ Démarrage Rapide (3 minutes)

### 1. Exécuter la migration

```bash
cd /home/user/buxi/boxibox
php artisan migrate
```

### 2. Accéder au gestionnaire

```
Admin → Sites & Plans
```

### 3. Créer votre premier site

1. Cliquer "➕ Nouveau Site"
2. Remplir les informations
3. Sauvegarder

**C'est tout ! Votre infrastructure est prête.**

---

## 🏗️ Créer Votre Infrastructure

### Étape 1 : Créer un Site

**Navigation** : Admin → Sites & Plans → ➕ Nouveau Site

**Informations requises** :
- **Nom** : Ex. "Mon Self-Storage Paris"
- **Adresse** : Ex. "123 rue de la République"
- **Ville** : Ex. "Paris"
- **Code Postal** : Ex. "75001"
- **Téléphone** (optionnel)
- **Email** (optionnel)
- **Statut** : Actif / Inactif / Maintenance
- **Plan visuel** : ✅ Activé (recommandé)

**Temps** : 30 secondes

---

### Étape 2 : Ajouter un Bâtiment

**Navigation** : Site → ➕ Nouveau Bâtiment

**Informations requises** :
- **Nom** : Ex. "Bâtiment A", "Bâtiment Principal"
- **Description** (optionnel) : Ex. "Bâtiment principal avec accès direct"
- **Couleur** : Choisir une couleur pour identifier le bâtiment
- **Ordre d'affichage** : Automatique

**Temps** : 20 secondes

---

### Étape 3 : Créer un Étage

**Navigation** : Bâtiment → ➕ Nouvel Étage

**Informations requises** :
- **Nom** : Ex. "Rez-de-chaussée", "Étage 1", "Sous-sol"
- **Numéro d'étage** : 0 (RDC), 1, 2, -1 (sous-sol), etc.
- **Largeur du plan** : 1200 pixels (par défaut)
- **Hauteur du plan** : 800 pixels (par défaut)

**Temps** : 20 secondes

---

### Étape 4 : Ajouter des Boxes

#### **Option A : Création en Masse** ⚡ RECOMMANDÉ

**Navigation** : Étage → ➕ Création en Masse

**Exemple : Créer 50 boxes en 10 secondes**

```
Préfixe : RDC          (optionnel)
Numéro de départ : 1
Nombre : 50
Taille : 10 m²
Type : Standard
Prix : 100 €/mois
Auto-organisation : ✅ Grille
```

**Résultat** : Boxes RDC1, RDC2, RDC3... RDC50 créées et organisées !

**Temps** : 10 secondes

#### **Option B : Création Individuelle**

Pour un contrôle total sur chaque box.

**Temps** : 30 secondes par box

---

## 🎨 Organiser Visuellement Vos Boxes

### Ouvrir l'Éditeur de Plan

**Navigation** : Admin → Sites & Plans → Sélectionner Site → Bâtiment → Étage → 🎨 Ouvrir l'Éditeur

---

### Interface de l'Éditeur

```
┌─────────────────────────────────────────────────────────┐
│ [💾 Sauvegarder] [⚙️ Auto-Organiser] [🔍 Zoom]         │
│ 🟢 Disponibles: 20  🔴 Occupées: 15  🟠 Réservées: 5  │
├─────────────────────────────────────────┬───────────────┤
│                                         │               │
│   ┌───┐  ┌───┐  ┌───┐  ┌───┐          │  BOX A1      │
│   │A1 │  │A2 │  │A3 │  │A4 │          │              │
│   └───┘  └───┘  └───┘  └───┘          │  Taille: 10m²│
│                                         │  Type: Std   │
│   ┌───┐  ┌───┐  ┌───┐  ┌───┐          │  Prix: 100€  │
│   │A5 │  │A6 │  │A7 │  │A8 │          │              │
│   └───┘  └───┘  └───┘  └───┘          │  Position:   │
│                                         │  X: 50px     │
│  [GRILLE DE FOND AVEC REPÈRES]         │  Y: 50px     │
│                                         │              │
│                                         │  [Modifier]  │
└─────────────────────────────────────────┴───────────────┘
```

---

### Organisation Automatique

**1. Cliquer sur "⚙️ Auto-Organiser"**

**2. Choisir le mode** :

| Mode | Description | Idéal pour |
|------|-------------|------------|
| **🔲 Grille** | Disposition optimale en grille | La plupart des cas |
| **⬇️ Lignes** | Alignement vertical (colonnes) | Couloirs longs |
| **➡️ Colonnes** | Alignement horizontal (lignes) | Espaces larges |

**3. Les boxes se placent automatiquement !**

**Temps** : 2 secondes

---

### Organisation Manuelle

#### **Déplacer une Box**

1. **Cliquer** sur la box
2. **Glisser** à l'emplacement souhaité
3. **Relâcher**
4. ✅ **Sauvegarde automatique**

#### **Redimensionner**

1. Sélectionner la box
2. Modifier dans le panneau latéral
3. Sauvegarder

#### **Changer la Couleur**

1. Sélectionner la box
2. Choisir une couleur dans la palette
3. ✅ Sauvegarde automatique

**Couleurs par défaut selon le statut** :
- 🟢 **Vert** : Disponible
- 🔴 **Rouge** : Occupée
- 🟠 **Orange** : Réservée
- ⚫ **Gris** : Maintenance

---

### Zoom

| Action | Raccourci |
|--------|-----------|
| Zoom In | Clic sur 🔍+ |
| Zoom Out | Clic sur 🔍- |
| Reset | Clic sur 🔄 |

---

### Sauvegarder

**Sauvegarde automatique** : Après chaque déplacement de box

**Sauvegarde globale** : Cliquer sur "💾 Sauvegarder Tout"

---

## 🚀 Fonctionnalités Avancées

### Création en Masse avec Préfixe

**Exemples** :

```
Préfixe: A-RDC-
Numéro: 1
Nombre: 20
→ A-RDC-1, A-RDC-2... A-RDC-20

Préfixe: B1-
Numéro: 101
Nombre: 30
→ B1-101, B1-102... B1-130

Préfixe: (vide)
Numéro: 1
Nombre: 50
→ 1, 2, 3... 50
```

---

### Types de Boxes

| Type | Description | Prix suggéré |
|------|-------------|--------------|
| **Standard** | Box standard non climatisée | 100€/mois |
| **Climatisé** | Température contrôlée | 150€/mois |
| **Premium** | Accès privilégié, sécurité renforcée | 200€/mois |
| **Extérieur** | Container extérieur | 80€/mois |

---

### Statistiques en Temps Réel

L'éditeur affiche en permanence :
- Nombre de boxes disponibles
- Nombre de boxes occupées
- Nombre de boxes réservées
- Taux d'occupation global

---

### Navigation Hiérarchique

**Breadcrumb** : Toujours visible en haut de page

```
Sites → Mon Site → Bâtiment A → Rez-de-chaussée → Éditeur
```

Cliquez sur n'importe quel élément pour naviguer rapidement.

---

## 💡 Trucs et Astuces

### ⚡ Démarrage Ultra-Rapide

**Créer un site complet en 3 minutes** :

1. ✅ Créer le site (30s)
2. ✅ Ajouter un bâtiment (20s)
3. ✅ Créer un étage (20s)
4. ✅ Créer 50 boxes en masse (10s)
5. ✅ Auto-organiser en grille (2s)
6. ✅ **TERMINÉ !**

**Total : 82 secondes** 🚀

---

### 🎨 Personnalisation

**Astuce 1** : Utilisez les couleurs personnalisées pour identifier les boxes spéciales
- Bleu : Boxes premium
- Violet : Boxes climatisées
- Rose : Boxes avec promotion

**Astuce 2** : Organisez d'abord automatiquement, puis ajustez manuellement

**Astuce 3** : Utilisez le zoom pour les plans complexes avec beaucoup de boxes

---

### 🔄 Workflow Recommandé

```
1. Créer l'infrastructure (Site → Bâtiment → Étage)
2. Créer boxes en masse
3. Auto-organiser en grille
4. Ajuster manuellement les positions si besoin
5. Personnaliser les couleurs
6. Sauvegarder
```

---

### 📊 Calcul Rapide du ROI

**Exemple : 50 boxes @ 100€/mois**

```
Revenue mensuel : 50 × 100€ = 5,000€
Revenue annuel : 5,000€ × 12 = 60,000€
```

L'éditeur affiche automatiquement ces calculs lors de la création en masse.

---

### 🎯 Cas d'Usage

#### **Cas 1 : Petit Site (1 bâtiment, 1 étage, 20 boxes)**

```
1. Site : "Mon Mini Storage"
2. Bâtiment : "Bâtiment Unique"
3. Étage : "Rez-de-chaussée"
4. Boxes : Créer 20 en masse (A1-A20)
5. Auto-organiser : Grille
6. Temps total : 2 minutes
```

#### **Cas 2 : Site Moyen (2 bâtiments, 3 étages, 100 boxes)**

```
1. Site : "Centre Self-Storage"
2. Bâtiment A (50 boxes) + Bâtiment B (50 boxes)
3. Étages : RDC (30), Étage 1 (40), Étage 2 (30)
4. Auto-organiser chaque étage
5. Temps total : 10 minutes
```

#### **Cas 3 : Grand Site (5 bâtiments, 10 étages, 500 boxes)**

```
1. Site principal
2. 5 bâtiments avec identité visuelle (couleurs)
3. Création en masse par étage (50 boxes/étage)
4. Auto-organisation systématique
5. Temps total : 30 minutes
```

---

## 🆘 Dépannage

### Problème : Boxes qui se chevauchent

**Solution** : Cliquer sur "⚙️ Auto-Organiser → Grille"

### Problème : Box hors du canvas

**Solution** : Glisser la box vers le centre ou réorganiser automatiquement

### Problème : Impossible de sauvegarder

**Solution** : Vérifier que vous avez les droits admin

### Problème : Plan trop petit

**Solution** : Modifier les dimensions du plan dans les paramètres de l'étage

---

## 📝 Raccourcis

| Action | Méthode |
|--------|---------|
| **Sélectionner box** | Clic simple |
| **Déplacer box** | Glisser-déposer |
| **Désélectionner** | Clic sur zone vide |
| **Zoom rapide** | Molette (si disponible) |
| **Sauvegarder** | Automatique après déplacement |

---

## 🎉 Résumé

### Ce que vous pouvez faire

✅ **Créer** des sites, bâtiments, étages, boxes
✅ **Organiser** visuellement avec drag-and-drop
✅ **Créer en masse** jusqu'à 100 boxes en 10 secondes
✅ **Auto-organiser** en grille/lignes/colonnes
✅ **Personnaliser** couleurs et positions
✅ **Visualiser** l'occupation en temps réel
✅ **Naviguer** facilement dans la hiérarchie
✅ **Calculer** le ROI automatiquement

### Gains de temps

- **Sans le système** : 2h pour 50 boxes
- **Avec le système** : 3 minutes pour 50 boxes
- **Gain** : **97% de temps économisé** 🚀

---

## 📚 Ressources

- **Documentation technique** : README.md
- **Guide de déploiement** : DEPLOYMENT_GUIDE.md
- **Guide de démarrage rapide** : QUICK_START.md
- **Résumé du projet** : FINAL_SUMMARY.md

---

## 🎯 Support

En cas de problème :
1. Vérifier les logs : `storage/logs/laravel.log`
2. Activer le mode debug : `APP_DEBUG=true` dans `.env`
3. Consulter la documentation complète

---

**🎊 Vous êtes maintenant prêt à gérer vos sites de self-storage visuellement !**

*Documentation mise à jour : 19 Novembre 2025*
*Version : 1.0.0*
