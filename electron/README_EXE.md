# Guide de Build .EXE avec Electron

## Installation rapide

### 1. Installer Node.js (si pas encore fait)
Téléchargez depuis: https://nodejs.org/
Choisissez la version LTS

### 2. Ouvrir le terminal dans le dossier `electron`
```powershell
cd C:\wamp64\www\attendance_system\electron
```

### 3. Installer les dépendances
```powershell
npm install
```

### 4. Générer le .EXE
```powershell
npm run build
```

Le fichier .exe sera créé dans: `C:\wamp64\www\attendance_system\electron\dist\`

## Points importants

✅ **WAMP doit être en cours d'exécution** pour que l'app fonctionne
✅ L'application ouvre automatiquement http://localhost/attendance_system/
✅ Vous pouvez partager le fichier .exe sans installer Node.js

## Fichiers créés
- `main.js` - Fichier principal Electron
- `preload.js` - Configuration de sécurité
- `package.json` - Configuration du projet

## Build personnalisé
- **Installer** (nsis): `dist/Attendance System Setup.exe`
- **Portable** (exe simple): `dist/Attendance System.exe`

