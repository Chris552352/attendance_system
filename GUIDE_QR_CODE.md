# 🎯 Guide d'utilisation du système de QR Codes

## ✅ Problème résolu !

Le problème avec la génération de QR codes a été **corrigé**. Voici ce qui a été fait :

### 🔧 Corrections apportées :

1. **API Endroid QR Code** : Mise à jour vers la version 6.x
   - Ancien : `Builder::create()->writer()->data()->build()`
   - Nouveau : `new Builder(writer:, data:, ...)->build()`

2. **Base de données** : Correction des noms de colonnes
   - `actif = TRUE` → `active = TRUE`
   - `ce.actif = 1` → `ce.active = 1`

## 🚀 Comment utiliser le système maintenant :

### 1. **Accès à la page de génération**
```
http://localhost/attendance_system/generer_qr.php
```

### 2. **Authentification requise**
- Vous devez être connecté en tant qu'**enseignant**
- Si vous n'êtes pas connecté, vous serez redirigé vers la page de connexion

### 3. **Génération d'un QR Code**
1. Remplissez le formulaire :
   - **Nom du cours** : Ex: "Mathématiques - TD1"
   - **Durée de validité** : 5, 10, 15, 30 minutes ou 1 heure
2. Cliquez sur "Générer le QR Code"
3. Le QR code s'affichera automatiquement

### 4. **Utilisation par les étudiants**
1. Les étudiants scannent le QR code avec leur téléphone
2. Ils accèdent à la page de validation de présence
3. Ils se connectent avec leur email et le mot de passe universel (`12345`)
4. Leur présence est automatiquement enregistrée

## 🧪 Tests disponibles :

### Test de diagnostic complet :
```
http://localhost/attendance_system/diagnostic_qr.php
```

### Test direct sans authentification :
```
http://localhost/attendance_system/test_generer_qr_direct.php
```

### Test final :
```
http://localhost/attendance_system/test_generer_qr_final.php
```

## 📱 Configuration réseau :

### IP configurée :
- **IP locale** : `192.168.167.70`
- **URL de base** : `http://192.168.167.70`

### Pour les étudiants :
- Ils doivent être sur le même réseau Wi-Fi
- Ou connectés au point d'accès de votre ordinateur
- L'URL du QR code sera : `http://192.168.167.70/presence_qr.php?seance=X&token=XXX`

## 🔍 Dépannage :

### Si le QR code ne s'affiche pas :
1. Vérifiez que vous êtes connecté en tant qu'enseignant
2. Consultez le diagnostic : `diagnostic_qr.php`
3. Vérifiez les logs d'erreur PHP

### Si les étudiants ne peuvent pas accéder :
1. Vérifiez que l'IP est correcte dans `config_reseau.php`
2. Testez l'URL depuis un téléphone
3. Vérifiez la connectivité réseau

### Si la présence n'est pas enregistrée :
1. Vérifiez que la séance n'a pas expiré
2. Vérifiez que l'étudiant utilise le bon mot de passe (`12345`)
3. Consultez la table `presences` en base de données

## 📊 Structure de la base de données :

### Table `seances` :
- `id` : Identifiant unique
- `nom_cours` : Nom du cours/séance
- `enseignant_id` : ID de l'enseignant
- `token` : Token unique pour le QR code
- `expiration` : Date d'expiration
- `date_creation` : Date de création
- `active` : Statut actif (1/0)

### Table `presences` :
- `etudiant_id` : ID de l'étudiant
- `cours_id` : ID du cours
- `seance_id` : ID de la séance
- `date_presence` : Date de présence
- `statut` : 'present' ou 'absent'

## 🎉 Félicitations !

Votre système de gestion de présence par QR Code fonctionne maintenant correctement !

### Prochaines étapes :
1. Testez la génération de QR codes
2. Faites tester par vos étudiants
3. Vérifiez les rapports de présence
4. Personnalisez selon vos besoins

---

**Support** : Si vous rencontrez encore des problèmes, consultez les scripts de diagnostic ou contactez le support technique. 