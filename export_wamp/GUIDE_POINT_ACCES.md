# Configuration Point d'Accès Wi-Fi - Système de Présence

## Configuration Complète PC + Téléphone

### 1. Configuration WAMP Server

#### Installation et configuration
1. **Télécharger WAMP** : https://www.wampserver.com/
2. **Installer** dans `C:\wamp64\`
3. **Copier le projet** dans `C:\wamp64\www\attendance_system\`
4. **Démarrer WAMP** (icône verte dans la barre des tâches)

#### Configuration Apache pour accès réseau
1. **Clic droit** sur l'icône WAMP → **Apache** → **Fichiers de configuration** → **httpd.conf**
2. **Chercher** la ligne : `<Directory "c:/wamp64/www/">`
3. **Remplacer** :
   ```apache
   Require local
   ```
   **Par** :
   ```apache
   Require all granted
   ```
4. **Redémarrer** tous les services WAMP

### 2. Configuration Point d'Accès Wi-Fi

#### Option A: Point d'accès Windows 10/11
1. **Paramètres** → **Réseau et Internet** → **Point d'accès mobile**
2. **Activer** le partage de connexion
3. **Configurer** :
   - Nom du réseau : `IUT-Presence`
   - Mot de passe : `presence2024`
   - Bande : 2,4 GHz (meilleure compatibilité)
4. **Noter l'IP** assignée (généralement `192.168.137.1`)

#### Option B: Routeur Wi-Fi existant
1. **Connecter** le PC au routeur (câble Ethernet)
2. **Trouver l'IP du PC** :
   ```cmd
   ipconfig
   ```
3. **Noter** l'adresse IPv4 (ex: `192.168.1.15`)

### 3. Configuration Base de Données

#### Créer la base MySQL
1. **Ouvrir** : http://localhost/phpmyadmin
2. **Créer** base : `attendance_system`
3. **Interclassement** : `utf8mb4_unicode_ci`
4. **Importer** le fichier : `database/mysql_schema.sql`

### 4. Test de Configuration

#### Vérification réseau
1. **Ouvrir** : http://localhost/attendance_system/test_wamp.php
2. **Noter** l'IP détectée automatiquement
3. **Tester** depuis un téléphone connecté au Wi-Fi : http://IP_DETECTEE/attendance_system/

#### URLs de test
- **Local** : http://localhost/attendance_system/
- **Réseau** : http://192.168.137.1/attendance_system/ (point d'accès)
- **Réseau** : http://192.168.1.X/attendance_system/ (routeur existant)

### 5. Utilisation avec Téléphone

#### Connexion Wi-Fi
1. **Connecter** le téléphone au réseau `IUT-Presence`
2. **Mot de passe** : `presence2024`
3. **Vérifier** la connexion

#### Scan QR Code
1. **Connexion enseignant** : http://IP_PC/attendance_system/
   - Email : `chris552352@gmail.com`
   - Mot de passe : `552352`
2. **Générer QR code** depuis le dashboard
3. **Scanner** avec l'appareil photo du téléphone
4. **Redirection automatique** vers la page de présence

#### Création compte étudiant (téléphone)
1. **Ouvrir** : http://IP_PC/attendance_system/inscription_etudiant.php
2. **Saisir** :
   - Matricule : `CMD-UDS-23IUT1XXX`
   - Email : `etudiant@exemple.com`
   - Mot de passe : au choix
3. **Créer le compte**
4. **Se connecter** via : http://IP_PC/attendance_system/login_etudiant.php

### 6. Flux Complet de Présence

#### Côté Enseignant (PC)
1. **Connexion** au système
2. **Sélection** du cours
3. **Génération** QR code temporisé (30 min)
4. **Affichage** du QR à scanner

#### Côté Étudiant (Téléphone)
1. **Connexion** Wi-Fi au point d'accès
2. **Scan** du QR code avec l'appareil photo
3. **Ouverture** automatique du navigateur
4. **Connexion** au compte étudiant
5. **Validation** de l'identité affichée
6. **Confirmation** de présence

### 7. Adresses IP Typiques

#### Point d'accès Windows
- **IP PC** : `192.168.137.1`
- **Plage téléphones** : `192.168.137.2-254`
- **URL** : http://192.168.137.1/attendance_system/

#### Routeur domestique
- **IP PC** : `192.168.1.X` (variable)
- **Passerelle** : `192.168.1.1`
- **URL** : http://192.168.1.X/attendance_system/

#### Box opérateur
- **IP PC** : `192.168.0.X` ou `10.0.0.X`
- **URL** : http://IP_DETECTEE/attendance_system/

### 8. Résolution des Problèmes

#### QR code ne fonctionne pas
- **Vérifier** que l'IP dans le QR correspond à l'IP du PC
- **Tester** l'URL manuellement depuis le téléphone
- **Regénérer** le QR code

#### Téléphone ne peut pas accéder
- **Vérifier** la connexion Wi-Fi
- **Désactiver** temporairement le pare-feu Windows
- **Redémarrer** WAMP et régénérer le QR

#### Base de données inaccessible
- **Vérifier** que WAMP est démarré (icône verte)
- **Réimporter** le schéma SQL
- **Tester** : http://localhost/phpmyadmin

### 9. Optimisations pour Soutenance

#### Préparation
1. **Tester** toute la chaîne 24h avant
2. **Préparer** plusieurs comptes étudiants test
3. **Vérifier** la portée Wi-Fi dans la salle
4. **Avoir** un câble Ethernet de secours

#### Démonstration
1. **Démarrer** WAMP et le point d'accès
2. **Afficher** l'IP détectée via test_wamp.php
3. **Connecter** un téléphone au Wi-Fi
4. **Générer** et scanner un QR code
5. **Montrer** la validation sécurisée

### 10. Configuration Automatique

Le système détecte automatiquement l'IP locale et génère les QR codes avec la bonne adresse. Le fichier `config_reseau.php` s'adapte automatiquement à votre environnement réseau.

**Test final** : http://localhost/attendance_system/test_wamp.php affiche toutes les informations nécessaires pour votre configuration réseau.