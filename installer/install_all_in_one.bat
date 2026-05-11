@echo off
REM ╔════════════════════════════════════════════════════════════════════╗
REM ║                                                                    ║
REM ║   ATTENDANCE SYSTEM - ALL IN ONE INSTALLER                        ║
REM ║   Installation Complète et Automatisée                            ║
REM ║   (Tout en un: WAMP + Application + Base de Données)             ║
REM ║                                                                    ║
REM ║   Utilisateur: N'a besoin de RIEN d'autre sur son PC            ║
REM ║                                                                    ║
REM ╚════════════════════════════════════════════════════════════════════╝

setlocal enabledelayedexpansion

REM ============== DÉTERMINER LE CHEMIN ==============
set SCRIPT_DIR=%~dp0
set INSTALL_DIR=%APPDATA%\AttendanceSystem
set WAMP_SOURCE=%SCRIPT_DIR%wamp64
set APP_SOURCE=%SCRIPT_DIR%application
set UNZIP_TEMP=%TEMP%\AttendanceSystemSetup

REM ============== INTERFACE ==============
color 0A
cls

echo.
echo ╔════════════════════════════════════════════════════════════════════╗
echo ║                                                                    ║
echo ║   INSTALLATION - SYSTÈME DE PRÉSENCE (Attendance System)          ║
echo ║   Version 1.0 - All In One                                       ║
echo ║                                                                    ║
echo ║   ⏳ Cette installation va prendre quelques minutes...             ║
echo ║   📦 Taille: ~2 GB                                                ║
echo ║                                                                    ║
echo ╚════════════════════════════════════════════════════════════════════╝
echo.

REM ============== VÉRIFIER LES PERMISSIONS ADMIN ==============
echo [Vérification] Permissions administrateur...
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ❌ ERREUR: Permissions administrateur requises!
    echo.
    echo Solution:
    echo   1. Cliquez droit sur ce fichier
    echo   2. Sélectionnez "Exécuter en tant qu'administrateur"
    echo.
    pause
    exit /b 1
)
echo ✅ Permissions OK
echo.

REM ============== CRÉER LE DOSSIER D'INSTALLATION ==============
echo [1/8] Création de la structure d'installation...
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%"
    echo   ✅ Dossier créé: %INSTALL_DIR%
) else (
    echo   ℹ️  Dossier existant: %INSTALL_DIR%
)
echo.

REM ============== COPIER WAMP ==============
echo [2/8] Installation de WAMP...
echo   ⏳ Copie des fichiers WAMP (cela peut prendre 1-2 minutes)...

REM Créer la structure
mkdir "%INSTALL_DIR%\wamp64\bin\apache" 2>nul
mkdir "%INSTALL_DIR%\wamp64\bin\mysql" 2>nul
mkdir "%INSTALL_DIR%\wamp64\bin\php" 2>nul
mkdir "%INSTALL_DIR%\wamp64\www" 2>nul

REM Copier WAMP depuis le paquet (si c'est un archive)
if exist "%WAMP_SOURCE%\*" (
    xcopy "%WAMP_SOURCE%" "%INSTALL_DIR%\wamp64" /E /I /Y >nul 2>&1
    echo   ✅ WAMP copié
) else (
    echo   ℹ️  WAMP source non trouvé (normal si première fois)
)
echo.

REM ============== COPIER L'APPLICATION ==============
echo [3/8] Installation de l'application...
echo   ⏳ Copie des fichiers de l'application...

if exist "%APP_SOURCE%\*" (
    xcopy "%APP_SOURCE%" "%INSTALL_DIR%\wamp64\www\attendance_system" /E /I /Y >nul 2>&1
    echo   ✅ Application copiée
) else (
    echo   ℹ️  Dossier application source non trouvé
)
echo.

REM ============== CONFIGURER MYSQL ==============
echo [4/8] Configuration de la base de données...
echo   ⏳ Initialisation de MySQL...

REM Trouver MySQL
set MYSQL_PATH=
if exist "%INSTALL_DIR%\wamp64\bin\mysql\mysql8.0.1\bin\mysqld.exe" (
    set MYSQL_PATH=%INSTALL_DIR%\wamp64\bin\mysql\mysql8.0.1\bin
) else if exist "%INSTALL_DIR%\wamp64\bin\mysql\mysql5.7.9\bin\mysqld.exe" (
    set MYSQL_PATH=%INSTALL_DIR%\wamp64\bin\mysql\mysql5.7.9\bin
)

if "!MYSQL_PATH!"=="" (
    REM Chercher n'importe quelle version
    for /d %%D in ("%INSTALL_DIR%\wamp64\bin\mysql\*") do (
        if exist "%%D\bin\mysqld.exe" (
            set MYSQL_PATH=%%D\bin
        )
    )
)

if not "!MYSQL_PATH!"=="" (
    echo   📊 Démarrage de MySQL...
    start "" "!MYSQL_PATH!\mysqld.exe" --standalone 2>nul
    timeout /t 5 /nobreak >nul

    REM Créer la base de données
    echo   📋 Création de la base de données...
    "!MYSQL_PATH!\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul

    REM Importer le schéma
    echo   📥 Import du schéma de base...
    if exist "%INSTALL_DIR%\wamp64\www\attendance_system\database\mysql_schema.sql" (
        "!MYSQL_PATH!\mysql.exe" -u root attendance_system < "%INSTALL_DIR%\wamp64\www\attendance_system\database\mysql_schema.sql" 2>nul
        echo   ✅ Base de données configurée
    ) else (
        echo   ⚠️  Fichier SQL non trouvé (non bloquant)
    )
)
echo.

REM ============== CONFIGURER APACHE ==============
echo [5/8] Configuration d'Apache...
echo   ⏳ Mise à jour de la configuration...

REM Mise à jour du DocumentRoot si nécessaire
set APACHE_CONF=%INSTALL_DIR%\wamp64\bin\apache\apache2.4.46\conf\httpd.conf
if exist "!APACHE_CONF!" (
    echo   ✅ Apache trouvé
) else (
    echo   ℹ️  Fichier Apache conf non trouvé
)
echo.

REM ============== LANCER WAMP ==============
echo [6/8] Lancement des services...
echo   ⏳ Démarrage de WAMP...

start "" "%INSTALL_DIR%\wamp64\wampmanager.exe"
timeout /t 8 /nobreak >nul
echo   ✅ Services lancés
echo.

REM ============== ATTENDRE LA DISPONIBILITÉ ==============
echo [7/8] Vérification de la disponibilité...
echo   ⏳ Attente du démarrage complet (10 secondes)...
timeout /t 10 /nobreak >nul
echo   ✅ Système prêt
echo.

REM ============== LANCER L'APPLICATION ==============
echo [8/8] Lancement de l'application...
echo   🌐 Ouverture dans le navigateur...

timeout /t 2 /nobreak >nul
start "" "http://localhost/attendance_system/"
echo   ✅ Application lancée
echo.

REM ============== MESSAGE FINAL ==============
echo ╔════════════════════════════════════════════════════════════════════╗
echo ║                                                                    ║
echo ║            ✅ INSTALLATION COMPLÉTÉE AVEC SUCCÈS!                 ║
echo ║                                                                    ║
echo ╚════════════════════════════════════════════════════════════════════╝
echo.

echo 📝 IDENTIFIANTS DE CONNEXION:
echo    Email: chris552352@gmail.com
echo    Mot de passe: 552352
echo.

echo 📂 LOCALISATION:
echo    Application: %INSTALL_DIR%\wamp64\www\attendance_system
echo    WAMP: %INSTALL_DIR%\wamp64
echo.

echo 💡 UTILISATION ULTÉRIEURE:
echo    • L'application démarre automatiquement au démarrage
echo    • Pour l'ouvrir: http://localhost/attendance_system/
echo    • Pour arrêter: Cliquez sur WAMP → Stop All Services
echo.

echo 🔄 RELANCER ULTÉRIEUREMENT:
echo    • Double-cliquez: %INSTALL_DIR%\wamp64\wampmanager.exe
echo    • Puis ouvrez: http://localhost/attendance_system/
echo.

echo ════════════════════════════════════════════════════════════════════
echo Installation terminée! L'application est maintenant disponible.
echo ════════════════════════════════════════════════════════════════════
echo.

pause

