# 📦 CRÉER VOTRE FICHIER .EXE - GUIDE COMPLET

## ✅ Option 1: Utiliser le Launcher Simple (Rapide)

### Ce que c'est?
Un simple fichier `.bat` qui démarre WAMP + ouvre votre application

### Comment faire?
1. Double-cliquez sur: `lancer_application.bat`
2. C'est tout! ✨

### Où le trouver?
`C:\wamp64\www\attendance_system\lancer_application.bat`

**Avantages**: Rapide, simple, pas de compilation
**Inconvénients**: Dépend de WAMP

---

## ✅ Option 2: Build Electron EXE (Professionnel)

### Prérequis
- ✅ Node.js installé (déjà fait!)

### Étapes

#### 1. Ouvrir PowerShell dans le dossier electron
```powershell
cd C:\wamp64\www\attendance_system\electron
```

#### 2. Installer les dépendances
```powershell
npm install
```

#### 3. Générer l'EXE
```powershell
npm run build
```

#### 4. Récupérer votre EXE
Les fichiers seront dans: `C:\wamp64\www\attendance_system\electron\dist\`

- **Installeur**: `Attendance System Setup.exe` (recommandé)
- **Portable**: `Attendance System.exe` (pas d'installation)

---

## 📋 Comparaison

| Caractéristique | Launcher.bat | Electron EXE |
|---|---|---|
| Temps de création | ⚡ Immédiat | ⏱️ 2-5 minutes |
| Taille fichier | 📄 1 KB | 📦 ~150 MB |
| Partage facile | ❌ Non (WAMP requis) | ✅ Oui |
| Interface pro | ❌ Console | ✅ Fenêtre native |
| Dépend de WAMP | ✅ Oui | ✅ Oui |

---

## 🚀 Utilisation

### Avant de lancer l'EXE
1. **Démarrez WAMP** (icône vert) - Essentiel!
2. Attendez que la BD se charge
3. Lancez l'EXE

### Démarrage automatique
Si vous vérifiez "Exécuter WAMP au démarrage", l'EXE trouvera l'application!

---

## ⚠️ Dépannage

### "Connection refused"
→ WAMP n'est pas running
→ Cliquez sur l'icône WAMP en bas à droite

### "Fichier non trouvé"
→ Assurez-vous que vous êtes dans le bon dossier
→ Vérifiez que `attendance_system` existe dans `C:\wamp64\www\`

### L'EXE fait rien
→ Attendez 10 secondes, WAMP doit démarrer
→ Vérifiez les logs: `http://localhost/attendance_system/test_wamp.php`

---

## 📝 Configuration avancée (optionnel)

Si vous voulez modifier l'EXE:
1. Éditez `electron/main.js`
2. Changez l'URL ou les paramètres
3. Relancez: `npm run build`

---

**Besoin d'aide?** Consultez `electron/README_EXE.md`

