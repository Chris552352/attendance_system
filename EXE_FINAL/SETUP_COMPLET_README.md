# 🎉 SETUP COMPLET - ATTENDANCE SYSTEM

## 📦 QU'EST-CE QUI Y A DANS CET INSTALLATION?

### **INSTALL_COMPLET.bat** ⭐ RECOMMANDÉ

**C'est quoi?** Un script qui installe automatiquement:
- ✅ Configuration de WAMP
- ✅ Copie de l'application PHP
- ✅ Import automatique de la base de données
- ✅ Lancement de l'application

**Comment l'utiliser?**
1. **Double-cliquez** sur `INSTALL_COMPLET.bat`
2. **Attendez** l'installation (~30 secondes)
3. **L'application s'ouvre** automatiquement
4. **C'est fini!** ✨

**Avantages:**
- 🚀 Ultra simple
- ⚡ Rapide
- 🎯 Fonctionne du premier coup
- 📱 Pas de configuration requise

---

## ⚙️ PRÉREQUIS

Avant de lancer `INSTALL_COMPLET.bat`, vous DEVEZ avoir:

### **✅ WAMP INSTALLÉ**
```
Téléchargez et installez WAMP:
→ https://wampserver.com/
```

### **✅ PERMISSIONS ADMIN**
```
Cliquez droit sur INSTALL_COMPLET.bat
→ "Exécuter en tant qu'administrateur"
```

---

## 🚀 ÉTAPES D'INSTALLATION

### **Étape 1: Préparez WAMP**
```
1. Lancez WAMP (icône W en bas à droite)
2. Vérifiez que tout est vert ✅
3. Attendez 5 secondes
```

### **Étape 2: Lancez l'installation**
```
1. Double-cliquez: INSTALL_COMPLET.bat
2. Cliquez "Oui" si demandé (permissions admin)
3. Lisez les messages (informatifs)
```

### **Étape 3: Attendez**
```
Le script va:
[1/5] Vérifier WAMP
[2/5] Lancer WAMP
[3/5] Attendre la BD
[4/5] Importer la BD
[5/5] Tester la connexion

Durée: ~30 secondes
```

### **Étape 4: C'est fini!**
```
✅ Application lancée
✅ Identifiants affichés
✅ Prêt à utiliser!
```

---

## 📝 IDENTIFIANTS PAR DÉFAUT

Après installation, vous pouvez vous connecter avec:

### **Enseignant**
```
Email: chris552352@gmail.com
Mot de passe: 552352
```

### **Admin**
```
Même identifiants qu'enseignant
(role: admin)
```

### **Créer un étudiant**
```
Via le formulaire d'inscription
ou via QR code
```

---

## 🔍 SI QUELQUE CHOSE NE MARCHE PAS

### **Problème: "Accès refusé"**
```
Solution:
1. Cliquez droit sur INSTALL_COMPLET.bat
2. "Exécuter en tant qu'administrateur"
3. Relancez
```

### **Problème: "WAMP non trouvé"**
```
Solution:
1. Installez WAMP: https://wampserver.com/
2. Relancez l'installation
```

### **Problème: "MySQL n'a pas pu être trouvé"**
```
Solution (manuelle):
1. Ouvrez http://localhost/phpmyadmin
2. Créez une base: "attendance_system"
3. Allez à "Importer"
4. Importez: C:\wamp64\www\attendance_system\database\mysql_schema.sql
5. Relancez l'app
```

### **Problème: Page blanche**
```
Solution:
1. Attendez 30 secondes
2. Rafraîchissez la page (F5)
3. Vérifiez que WAMP est vert
```

---

## 📂 CE QUI EST INSTALLÉ OÙ

Après installation:
```
C:\wamp64\
├── bin/
│   ├── apache/     ← Serveur web
│   ├── mysql/      ← Base de données
│   └── php/        ← Interpréteur PHP
├── www/
│   └── attendance_system/
│       ├── index.php
│       ├── database/
│       │   └── mysql_schema.sql (importé)
│       ├── includes/
│       ├── config/
│       └── ... (toute l'application)
└── wampmanager.exe
```

---

## 💾 APRÈS L'INSTALLATION

### **Lancer l'application**
```
Deux options:

1. Bureau:
   → Double-cliquez le raccourci
   → (Attention: dépend de Electron)

2. Navigateur (recommandé):
   → Ouvrez: http://localhost/attendance_system/
   → Dans la barre d'adresse
```

### **Arrêter l'application**
```
1. Cliquez l'icône WAMP (bas à droite)
2. "Stop All Services"
3. C'est arrêté!
```

### **Relancer**
```
WAMP → "Start All Services"
puis ouvrez l'URL
```

---

## 🎯 CAS D'USAGE

### **Pour la soutenance**
```
1. Lancez INSTALL_COMPLET.bat (10 min avant)
2. Attendez que tout soit vert
3. Montrez directement l'application
4. Très professionnel! ✨
```

### **Pour partager avec quelqu'un**
```
1. Donnez-lui le dossier complet EXE_FINAL
2. Il lance INSTALL_COMPLET.bat
3. Ça marche directement (s'il a WAMP)
```

### **Pour une clé USB**
```
1. Copiez tout sur la clé
2. Sur l'autre PC:
   - Installez WAMP
   - Lancez INSTALL_COMPLET.bat de la clé
3. Ça marche!
```

---

## ✨ CARACTÉRISTIQUES

✅ Installation entièrement automatisée
✅ Base de données importée auto
✅ WAMP configuré
✅ Application prête à l'emploi
✅ Identifiants pré-configurés
✅ Fonctionne 100% en local
✅ Pas d'internet requis

---

## 🔐 SÉCURITÉ

⚠️ **Important:**
```
Cette installation:
- Utilise des identifiants par défaut (OK pour local)
- Ne nécessite pas de mot de passe MySQL (OK pour local)
- N'ouvre aucun port externe (OK pour local)

JAMAIS utiliser sur internet public!
```

---

## 📞 SUPPORT

### **Questions courantes**

**Q: Je peux désinstaller?**
R: Oui, juste supprimez le dossier ou utilisez Ajout/Suppression de programmes

**Q: Ça prend combien de place?**
R: WAMP: ~1 GB, Application: ~100 MB, BD: <10 MB

**Q: Je peux modifier l'app après?**
R: Oui! Les fichiers sont dans C:\wamp64\www\attendance_system\

**Q: Je peux mettre plusieurs versions?**
R: Oui, installez dans des dossiers différents

---

## 🚀 C'EST PRÊT!

```
╔════════════════════════════════════════════════════════════╗
║  Vous avez maintenant une installation COMPLÈTE et        ║
║  AUTOMATISÉE du Système de Présence!                      ║
║                                                            ║
║  Prochaine étape:                                          ║
║  → Double-cliquez INSTALL_COMPLET.bat                     ║
║                                                            ║
║  Et c'est tout! ✨                                         ║
╚════════════════════════════════════════════════════════════╝
```

**Bonne chance! 💪**

