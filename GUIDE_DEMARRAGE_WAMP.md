# 🚀 Guide de Démarrage WAMP - Système de Présence

## ❌ Problème actuel
- Le QR code ne fonctionne pas sur le téléphone
- Erreur "Not Found" sur l'URL
- Le serveur PHP intégré (`php -S localhost:8000`) n'est accessible que depuis le PC

## ✅ Solution : Utiliser WAMP

### 1. Arrêter le serveur PHP intégré
```bash
# Dans le terminal PowerShell, appuyez sur :
Ctrl + C
```

### 2. Démarrer WAMP
1. **Double-cliquez sur l'icône WAMP** dans le menu Démarrer
2. **Attendez que l'icône devienne verte** dans la barre des tâches
3. **Vérifiez qu'Apache est démarré** :
   - Clic droit sur l'icône WAMP
   - Apache → Service → Status doit être vert

### 3. Vérifier la structure des dossiers
```
C:/wamp64/www/attendance_system/
├── presence_qr.php
├── generer_qr.php
├── afficher_qr.php
└── ... (autres fichiers)
```

### 4. URLs correctes à utiliser

#### Sur le PC (localhost)
- **Accueil** : http://localhost/attendance_system/
- **Générer QR** : http://localhost/attendance_system/generer_qr.php
- **Test QR** : http://localhost/attendance_system/diagnostic_qr.php

#### Sur le téléphone (réseau)
- **Accueil** : http://192.168.137.1/attendance_system/
- **Page présence** : http://192.168.137.1/attendance_system/presence_qr.php?seance=XX&token=XXX
- **Test QR** : http://192.168.137.1/attendance_system/diagnostic_qr.php

### 5. Test de fonctionnement

#### Étape 1 : Test sur PC
1. Ouvrez : http://localhost/attendance_system/
2. Connectez-vous en tant qu'enseignant
3. Générez un QR code
4. Vérifiez que le QR code s'affiche

#### Étape 2 : Test sur téléphone
1. Connectez votre téléphone au point d'accès
2. Scannez le QR code généré
3. Vous devriez voir la page de présence
4. Connectez-vous avec un email étudiant + mot de passe 12345

### 6. URLs de test rapide

#### Test de connectivité
- PC : http://localhost/attendance_system/test_connectivite_reseau.php
- Téléphone : http://192.168.137.1/attendance_system/test_connectivite_reseau.php

#### Diagnostic QR
- PC : http://localhost/attendance_system/diagnostic_qr.php
- Téléphone : http://192.168.137.1/attendance_system/diagnostic_qr.php

#### Test complet du système
- PC : http://localhost/attendance_system/test_presence_complete_v2.php
- Téléphone : http://192.168.137.1/attendance_system/test_presence_complete_v2.php

### 7. Dépannage

#### Si WAMP ne démarre pas
1. Vérifiez qu'aucun autre serveur web n'utilise le port 80
2. Redémarrez WAMP
3. Vérifiez les logs dans `C:/wamp64/logs/`

#### Si l'accès réseau ne fonctionne pas
1. Vérifiez que le pare-feu Windows autorise Apache
2. Vérifiez que le téléphone est bien connecté au même réseau
3. Testez avec `ping 192.168.137.1` depuis le téléphone

#### Si le QR code ne s'affiche pas
1. Vérifiez que l'URL dans le QR contient `/attendance_system/`
2. Testez l'URL directement dans le navigateur
3. Vérifiez les logs d'erreur PHP

### 8. Commandes utiles

#### Vérifier l'IP locale
```bash
ipconfig
```

#### Tester la connectivité
```bash
ping 192.168.137.1
```

#### Vérifier les services WAMP
- Clic droit sur l'icône WAMP → Apache → Service → Status
- Clic droit sur l'icône WAMP → MySQL → Service → Status

### 9. Résumé des étapes

1. **Arrêtez** le serveur PHP intégré (Ctrl+C)
2. **Démarrez** WAMP (icône verte)
3. **Testez** sur PC : http://localhost/attendance_system/
4. **Testez** sur téléphone : http://192.168.137.1/attendance_system/
5. **Générez** un QR code et testez-le

### 10. Liens de test rapide

- **Accueil** : http://localhost/attendance_system/
- **Test connectivité** : http://localhost/attendance_system/test_connectivite_reseau.php
- **Diagnostic QR** : http://localhost/attendance_system/diagnostic_qr.php
- **Test présence** : http://localhost/attendance_system/test_presence_complete_v2.php

---

**Une fois WAMP démarré, tout devrait fonctionner sur PC ET téléphone !** 🎉 