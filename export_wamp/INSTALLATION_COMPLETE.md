# Installation Complète WAMP - Système de Présence

## Package Export Complet

Ce package contient tous les fichiers nécessaires pour installer le système sur WAMP Server avec MySQL et phpMyAdmin.

## Contenu du Package

### Fichiers PHP Principaux
- `index.php` - Page d'accueil
- `login.php` - Connexion enseignants
- `login_etudiant.php` - Connexion étudiants
- `inscription_etudiant.php` - Inscription nouveaux étudiants
- `accueil.php` - Dashboard principal
- `generer_qr.php` - Génération QR codes
- `afficher_qr.php` - Affichage QR codes
- `presence.php` - Validation présence mobile
- `cours.php` - Gestion des cours
- `etudiants.php` - Gestion des étudiants
- `enseignants.php` - Gestion des enseignants
- `rapports.php` - Rapports de présence

### Configuration
- `config/database.php` - Configuration MySQL optimisée
- `config_reseau.php` - Détection automatique IP locale
- `includes/` - Fonctions et authentification
- `assets/` - CSS, JS et images

### Base de Données
- `database/mysql_schema.sql` - Schéma complet avec données test

### Documentation
- `README_INSTALLATION_WAMP.md` - Guide installation détaillé
- `GUIDE_POINT_ACCES.md` - Configuration Wi-Fi
- `DEMARRAGE_RAPIDE.md` - Setup rapide
- `SECURITE_COMPLETE.md` - Documentation sécurité

### Outils
- `test_wamp.php` - Diagnostic système
- `creer_comptes_etudiants.php` - Génération comptes

## Installation Rapide

### 1. WAMP Server
```bash
# Installer WAMP64 depuis wampserver.com
# Copier le dossier export_wamp vers C:\wamp64\www\attendance_system\
```

### 2. Base de Données
```sql
-- Ouvrir phpMyAdmin: http://localhost/phpmyadmin
-- Créer base: attendance_system
-- Interclassement: utf8mb4_unicode_ci
-- Importer: database/mysql_schema.sql
```

### 3. Configuration Réseau
```apache
# Éditer httpd.conf
# Remplacer "Require local" par "Require all granted"
# Redémarrer WAMP
```

### 4. Point d'Accès Wi-Fi
```
# Windows: Paramètres → Point d'accès mobile
# Activer partage connexion
# IP automatique: 192.168.137.1
```

## Comptes Configurés

### Enseignant/Admin
- Email: `chris552352@gmail.com`
- Mot de passe: `552352`

### Étudiants
- Inscription libre via interface
- Comptes pré-configurés avec mot de passe `password`

## URLs d'Accès

### Local (PC)
- `http://localhost/attendance_system/`
- `http://localhost/attendance_system/test_wamp.php`

### Mobile (Point d'Accès)
- `http://192.168.137.1/attendance_system/`
- QR codes générés automatiquement avec bonne IP

## Fonctionnalités Sécurisées

- Authentification individuelle avec mots de passe hashés
- Sessions PHP traçables
- QR codes temporisés (expiration automatique)
- Validation d'identité avant confirmation
- Prévention doublons de présence
- Base de données avec contraintes d'intégrité

## Support et Dépannage

Consulter `test_wamp.php` pour diagnostic automatique des problèmes de configuration réseau et base de données.

Le système détecte automatiquement votre configuration IP et génère les QR codes avec les bonnes adresses pour le scan mobile.

**Installation testée et validée pour soutenance DUT.**