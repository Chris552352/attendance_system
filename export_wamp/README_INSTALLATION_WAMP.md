# Installation WAMP - Système de Gestion de Présence

## Guide complet pour votre soutenance DUT

### 1. Prérequis
- WAMP Server installé et fonctionnel
- Navigateur web moderne
- Réseau Wi-Fi ou point d'accès configuré

### 2. Configuration Base de Données

#### Étape 1: Créer la base de données
1. Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
2. Cliquer sur "Nouvelle base de données"
3. Nom : `attendance_system`
4. Interclassement : `utf8mb4_unicode_ci`
5. Cliquer "Créer"

#### Étape 2: Importer le schéma
1. Sélectionner la base `attendance_system`
2. Onglet "Importer"
3. Choisir le fichier : `database/mysql_schema.sql`
4. Cliquer "Exécuter"

### 3. Installation Application

#### Étape 1: Copier les fichiers
1. Copier tout le dossier du projet dans : `C:\wamp64\www\attendance_system\`
2. Vérifier que tous les fichiers sont présents

#### Étape 2: Configuration réseau
1. Trouver votre IP locale :
   - Ouvrir cmd
   - Taper : `ipconfig`
   - Noter l'adresse IPv4 (ex: 192.168.1.10)

2. Modifier `config_reseau.php` :
   ```php
   define('IP_POINT_ACCES', '192.168.1.XX'); // Remplacer XX par votre IP
   ```

### 4. Autoriser l'Accès Réseau

#### Méthode 1: Interface WAMP
1. Clic droit sur l'icône WAMP (barre des tâches)
2. "Mettre en ligne"
3. Redémarrer tous les services

#### Méthode 2: Configuration manuelle
1. Ouvrir : `C:\wamp64\bin\apache\apache2.4.XX\conf\httpd.conf`
2. Rechercher : `<Directory "c:/wamp64/www/">`
3. Remplacer `Require local` par `Require all granted`
4. Redémarrer Apache

### 5. Test Installation

#### Test local
- URL : `http://localhost/attendance_system/test_wamp.php`
- Vérifier que tous les tests sont verts

#### Test réseau
- URL : `http://VOTRE_IP/attendance_system/`
- Tester depuis un téléphone connecté au même réseau

### 6. Comptes de Connexion

#### Comptes Enseignants
- **Administrateur** : `chris552352@gmail.com` / `552352`
- **Enseignant 1** : `marie.dupont@example.com` / `password`
- **Enseignant 2** : `jean.martin@example.com` / `password`

#### Comptes Étudiants (exemples)
- `angesimo@gmail.com` / `sim045` (Simo Ange - matricule 1045)
- `Ateba@gmail.com` / `ate022` (Ateba Jean - matricule 1022)
- `Cindy90@gmail.com` / `tch087` (Tchouche Cindy - matricule 1087)

**Règle de mot de passe étudiant** : 3 premières lettres du nom + 3 derniers chiffres du matricule

### 7. Création de Comptes Étudiants

#### Option 1: Auto-génération
1. Aller sur : `http://localhost/attendance_system/creer_comptes_etudiants.php`
2. Les comptes sont créés automatiquement avec la règle de mot de passe

#### Option 2: Inscription manuelle
1. Les étudiants peuvent créer leur compte via : `inscription_etudiant.php`
2. Ils utilisent leur matricule et email officiel
3. Ils choisissent leur propre mot de passe

### 8. Configuration Point d'Accès

#### Windows 10/11
1. Paramètres → Réseau et Internet
2. Point d'accès mobile → Activer
3. Noter l'IP (généralement 192.168.137.1)
4. Modifier `config_reseau.php` avec cette IP

#### Alternative réseau existant
1. Connecter tous les appareils au même réseau Wi-Fi
2. Utiliser l'IP de votre ordinateur sur ce réseau

### 9. Fonctionnalités Sécurisées Conservées

✅ **Authentification robuste**
- Séparation enseignants/étudiants
- Mots de passe hashés (bcrypt)
- Sessions sécurisées

✅ **QR Code temporisé**
- Codes uniques par session
- Expiration automatique
- Protection contre la réutilisation

✅ **Validation des présences**
- Vérification unique par étudiant/cours/date
- Justification des absences
- Historique complet

✅ **Gestion complète**
- Dashboard enseignant
- Rapports de présence
- Gestion des cours et étudiants

### 10. Dépannage

#### Problème de connexion base de données
- Vérifier que WAMP est démarré (icône verte)
- Vérifier le nom de la base : `attendance_system`
- Réimporter le schéma SQL si nécessaire

#### Problème d'accès réseau
- Vérifier l'IP dans `config_reseau.php`
- Désactiver le pare-feu temporairement
- Vérifier que WAMP est "en ligne"

#### Problème de permissions
- Vérifier les droits d'écriture sur le dossier
- Redémarrer WAMP en tant qu'administrateur

### 11. Démonstration Soutenance

1. **Connexion enseignant** : Montrer le dashboard et la création de QR
2. **Connexion étudiant** : Scanner le QR code, marquer présence
3. **Gestion des absences** : Justifier une absence
4. **Rapports** : Afficher les statistiques de présence
5. **Sécurité** : Montrer l'expiration des QR codes

### Support
En cas de problème, vérifier le fichier `test_wamp.php` qui diagnostique automatiquement la configuration.

---
*Système développé pour DUT - Toutes les fonctionnalités sécurisées sont conservées*