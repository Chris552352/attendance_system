# Configuration Réseau Local - Système de Présence QR Code

## 🔧 Configuration pour Soutenance DUT

### 1. **Préparer le Point d'Accès**

#### Option A : Partage de connexion Windows
```
1. Paramètres → Réseau et Internet → Point d'accès mobile
2. Activer le partage de connexion
3. Noter l'IP attribuée (généralement 192.168.137.1)
```

#### Option B : Point d'accès téléphone
```
1. Activer le point d'accès sur votre téléphone
2. Connecter l'ordinateur au réseau créé
3. Noter l'IP de la passerelle (souvent 192.168.43.1)
```

### 2. **Configurer l'Application**

1. Ouvrir le fichier `config_reseau.php`
2. Modifier la ligne :
   ```php
   define('IP_POINT_ACCES', '192.168.1.10');
   ```
   Remplacer `192.168.1.10` par votre IP réelle

3. Exemples d'IP courantes :
   - Windows Hotspot : `192.168.137.1`
   - Android Hotspot : `192.168.43.1`
   - Réseau local : `192.168.1.X` ou `192.168.0.X`

### 3. **Lancer le Système**

1. Démarrer le serveur PHP sur port 5000
2. Accéder à l'admin : `http://VOTRE_IP:5000`
3. Se connecter avec : `chris552352@gmail.com` / `password`

### 4. **Test avec les Étudiants**

1. **Connexion étudiants** :
   - Email : `angesimo@gmail.com`
   - Mot de passe : `password`

2. **Processus de validation** :
   - Enseignant génère un QR code
   - Étudiants scannent avec leur téléphone
   - Validation automatique de présence

### 5. **Sécurité Implémentée**

✅ **Authentification obligatoire** : Chaque étudiant doit se connecter
✅ **Sessions sécurisées** : $_SESSION['etudiant_id'] pour chaque validation
✅ **QR Code temporisé** : Expiration automatique (5-60 minutes)
✅ **Validation unique** : Impossible de valider deux fois la même séance
✅ **Vérification d'identité** : Affichage nom/email avant validation

### 6. **Dépannage**

#### Problème : Étudiants ne peuvent pas accéder
```
1. Vérifier que tous sont sur le même réseau Wi-Fi
2. Tester l'accès avec : http://VOTRE_IP:5000
3. Désactiver pare-feu temporairement si nécessaire
```

#### Problème : QR Code ne fonctionne pas
```
1. Vérifier l'IP dans config_reseau.php
2. S'assurer que le port 5000 est ouvert
3. Tester l'URL manuellement dans un navigateur
```

### 7. **Démonstration Soutenance**

1. **Préparer 2-3 comptes étudiants** avec mots de passe simples
2. **Créer une séance test** avec QR code
3. **Montrer la validation** en temps réel
4. **Présenter les rapports** de présence
5. **Démontrer la sécurité** (double validation impossible)

Le système est maintenant configuré pour fonctionner entièrement en local sans internet !