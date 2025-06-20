# Démarrage Rapide - Point d'Accès Wi-Fi

## Configuration en 5 Minutes

### 1. Installation WAMP (2 min)
```bash
# Télécharger et installer WAMP64
# Copier le projet dans: C:\wamp64\www\attendance_system\
# Démarrer WAMP (icône verte)
```

### 2. Configuration Base de Données (1 min)
```bash
# Ouvrir: http://localhost/phpmyadmin
# Créer base: attendance_system (utf8mb4_unicode_ci)
# Importer: database/mysql_schema.sql
```

### 3. Configuration Réseau WAMP (30 sec)
```apache
# Éditer: C:\wamp64\bin\apache\apache2.4.xx\conf\httpd.conf
# Chercher: <Directory "c:/wamp64/www/">
# Remplacer: Require local
# Par: Require all granted
# Redémarrer WAMP
```

### 4. Activation Point d'Accès (1 min)
```
Windows: Paramètres → Réseau → Point d'accès mobile
- Nom réseau: IUT-Presence
- Mot de passe: presence2024
- IP automatique: 192.168.137.1
```

### 5. Test Final (30 sec)
```bash
# PC: http://localhost/attendance_system/test_wamp.php
# Noter l'IP détectée
# Téléphone: Se connecter au Wi-Fi "IUT-Presence"
# Téléphone: http://IP_DETECTEE/attendance_system/
```

## Utilisation Soutenance

### Connexion Enseignant
- Email: `chris552352@gmail.com`
- Mot de passe: `552352`

### Génération QR Code
1. Dashboard → Générer QR Code
2. Sélectionner cours
3. Afficher QR sur écran/projecteur

### Scan Mobile Étudiant
1. Connexion Wi-Fi "IUT-Presence"
2. Scan QR avec appareil photo
3. Création compte si nécessaire
4. Validation sécurisée

## Dépannage Express

### QR ne fonctionne pas
```bash
# Vérifier IP: test_wamp.php
# Regénérer QR avec nouvelle IP
# Tester URL manuellement
```

### Téléphone n'accède pas
```bash
# Vérifier connexion Wi-Fi
# Désactiver pare-feu temporairement
# Redémarrer point d'accès
```

### Base de données
```bash
# WAMP icône verte ?
# Réimporter mysql_schema.sql
# Vérifier localhost/phpmyadmin
```

## IPs Courantes
- Point d'accès Windows: `192.168.137.1`
- Routeur domestique: `192.168.1.x`
- Box opérateur: `192.168.0.x`

Configuration automatique détecte l'IP correcte.