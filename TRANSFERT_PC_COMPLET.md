# Transfert Complet du Projet sur Votre PC

## Méthode 1: Téléchargement Direct (Recommandée)

### Étape 1: Télécharger depuis Replit
1. **Ouvrir Replit** dans votre navigateur
2. **Aller dans votre projet** 
3. **Cliquer sur les 3 points** (menu) en haut à droite
4. **Sélectionner "Download as zip"**
5. **Sauvegarder** le fichier sur votre PC

### Étape 2: Extraction et Installation
```bash
# Extraire le ZIP téléchargé
# Copier le contenu vers: C:\wamp64\www\attendance_system\
```

## Méthode 2: Clonage Git (Alternative)

### Si votre projet est sur GitHub
```bash
# Ouvrir Git Bash dans C:\wamp64\www\
git clone https://github.com/VOTRE_USERNAME/VOTRE_REPO.git attendance_system
cd attendance_system
```

## Méthode 3: Copie Manuelle Fichier par Fichier

### Fichiers Essentiels à Copier
```
attendance_system/
├── index.php
├── login.php
├── login_etudiant.php
├── inscription_etudiant.php
├── accueil.php
├── generer_qr.php
├── afficher_qr.php
├── presence.php
├── cours.php
├── etudiants.php
├── enseignants.php
├── rapports.php
├── config/
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   └── sidebar.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── database/
    └── mysql_schema.sql
```

## Configuration WAMP Après Transfert

### 1. Installation WAMP
```bash
# Télécharger: https://www.wampserver.com/
# Installer WAMP64
# Démarrer tous les services (icône verte)
```

### 2. Configuration Base de Données
```sql
-- Ouvrir: http://localhost/phpmyadmin
-- Créer nouvelle base de données
-- Nom: attendance_system
-- Interclassement: utf8mb4_unicode_ci
-- Importer le fichier: database/mysql_schema.sql
```

### 3. Modification config/database.php
```php
<?php
// Remplacer le contenu par:
$host = 'localhost';
$dbname = 'attendance_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>
```

### 4. Configuration Réseau WAMP
```apache
# Éditer: C:\wamp64\bin\apache\apache2.4.xx\conf\httpd.conf
# Chercher: <Directory "c:/wamp64/www/">
# Remplacer ligne: Require local
# Par: Require all granted
# Sauvegarder et redémarrer WAMP
```

### 5. Configuration Point d'Accès Wi-Fi
```
Windows 10/11:
1. Paramètres → Réseau et Internet
2. Point d'accès mobile
3. Activer le partage de connexion
4. Noter l'IP (généralement 192.168.137.1)
```

## Test de Fonctionnement

### URLs de Test
```
Local (PC): http://localhost/attendance_system/
Mobile: http://192.168.137.1/attendance_system/
Test config: http://localhost/attendance_system/test_wamp.php
```

### Comptes de Connexion
```
Admin: chris552352@gmail.com / 552352
Étudiants: créer via inscription_etudiant.php
```

## Workflow Utilisation

### 1. Génération QR Code
```
1. Connexion enseignant
2. Sélectionner cours
3. Générer QR code
4. Afficher sur écran/projecteur
```

### 2. Scan Mobile Étudiant
```
1. Connecter téléphone au Wi-Fi
2. Scanner QR code
3. Créer compte si nécessaire
4. Valider présence
```

## Dépannage Courant

### Erreur Base de Données
```
- Vérifier WAMP démarré (icône verte)
- Vérifier base 'attendance_system' créée
- Réimporter mysql_schema.sql
```

### QR Code Ne Marche Pas
```
- Vérifier IP dans config_reseau.php
- Tester URL manuellement depuis téléphone
- Regénérer QR code
```

### Accès Réseau Impossible
```
- Vérifier "Require all granted" dans httpd.conf
- Redémarrer WAMP
- Désactiver pare-feu temporairement
```

Votre projet sera ainsi entièrement fonctionnel sur votre PC avec WAMP, MySQL et point d'accès Wi-Fi pour les QR codes mobiles.