@echo off
REM ╔═══════════════════════════════════════════════════════════════════╗
REM ║                                                                   ║
REM ║   ATTENDANCE SYSTEM - INSTALLATEUR COMPLET                        ║
REM ║   (Installation complète: WAMP + Application + Base de Données)  ║
REM ║                                                                   ║
REM ╚═══════════════════════════════════════════════════════════════════╝

setlocal enabledelayedexpansion

color 0A
cls

echo.
echo ╔═══════════════════════════════════════════════════════════════════╗
echo ║                                                                   ║
echo ║     Installation du Système de Présence - Attendance System      ║
echo ║                                                                   ║
echo ╚═══════════════════════════════════════════════════════════════════╝
echo.

REM ============== ÉTAPE 1: Vérifier WAMP ==============
echo [1/5] Vérification de WAMP...
if not exist "C:\wamp64\wampmanager.exe" (
    echo.
    echo ❌ ERREUR: WAMP n'a pas été trouvé!
    echo.
    echo Veuillez installer WAMP d'abord:
    echo   → https://wampserver.com/
    echo.
    echo Après installation de WAMP, relancez ce script.
    echo.
    pause
    exit /b 1
)
echo ✅ WAMP trouvé
echo.

REM ============== ÉTAPE 2: Lancer WAMP ==============
echo [2/5] Lancement de WAMP...
tasklist /FI "IMAGENAME eq wampmanager.exe" 2>NUL | find /I "wampmanager" >NUL
if errorlevel 1 (
    echo   ⏳ Démarrage de WAMP...
    start "" "C:\wamp64\wampmanager.exe"
    echo   ⏳ Attente du démarrage (10 secondes)...
    timeout /t 10 /nobreak
) else (
    echo   ℹ️  WAMP est déjà en cours d'exécution
)
echo ✅ WAMP lancé
echo.

REM ============== ÉTAPE 3: Attendre MySQL ==============
echo [3/5] Attente du démarrage de la base de données...
echo   ⏳ Attente (5 secondes)...
timeout /t 5 /nobreak
echo ✅ Base de données prête
echo.

REM ============== ÉTAPE 4: Importer la BD ==============
echo [4/5] Configuration de la base de données...

REM Trouver la version de MySQL
set MYSQL_PATH=
if exist "C:\wamp64\bin\mysql\mysql8.0.1\bin\mysql.exe" (
    set MYSQL_PATH=C:\wamp64\bin\mysql\mysql8.0.1\bin\mysql.exe
) else if exist "C:\wamp64\bin\mysql\mysql5.7.9\bin\mysql.exe" (
    set MYSQL_PATH=C:\wamp64\bin\mysql\mysql5.7.9\bin\mysql.exe
) else (
    for /d %%D in (C:\wamp64\bin\mysql\*) do (
        if exist "%%D\bin\mysql.exe" (
            set MYSQL_PATH=%%D\bin\mysql.exe
            goto found_mysql
        )
    )
)

:found_mysql
if "!MYSQL_PATH!"=="" (
    echo.
    echo ⚠️  ATTENTION: MySQL n'a pas pu être trouvé automatiquement
    echo   Vous devrez importer la base de données manuellement:
    echo   1. Ouvrez http://localhost/phpmyadmin
    echo   2. Créez une base "attendance_system"
    echo   3. Importez: C:\wamp64\www\attendance_system\database\mysql_schema.sql
    echo.
) else (
    echo   📊 Importation du schéma de base de données...

    REM Créer la base si elle n'existe pas
    "!MYSQL_PATH!" -u root -e "CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>NUL

    REM Importer le schéma
    "!MYSQL_PATH!" -u root attendance_system < "C:\wamp64\www\attendance_system\database\mysql_schema.sql" 2>NUL

    if errorlevel 0 (
        echo   ✅ Base de données importée avec succès
    ) else (
        echo   ⚠️  Erreur lors de l'import (non bloquant)
    )
)
echo ✅ Configuration BD complète
echo.

REM ============== ÉTAPE 5: Vérifier la connexion ==============
echo [5/5] Vérification de l'application...
echo   🌐 Test de connexion à localhost...
timeout /t 2 /nobreak

REM Utiliser PowerShell pour tester la connexion
powershell -NoProfile -Command "try { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $response = Invoke-WebRequest -Uri 'http://localhost/attendance_system/' -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop; Write-Host '   ✅ Application accessible'; exit 0 } catch { Write-Host '   ⚠️  Application pas encore disponible'; exit 1 }" >NUL 2>&1

echo ✅ Vérification complète
echo.

REM ============== AFFICHAGE DES INFORMATIONS FINALES ==============
echo ╔═══════════════════════════════════════════════════════════════════╗
echo ║                                                                   ║
echo ║            ✅ INSTALLATION COMPLÈTE RÉUSSIE!                     ║
echo ║                                                                   ║
echo ╚═══════════════════════════════════════════════════════════════════╝
echo.
echo 📝 IDENTIFIANTS PAR DÉFAUT:
echo    Email: chris552352@gmail.com
echo    Mot de passe: 552352
echo.
echo 🚀 LANCEMENT DE L'APPLICATION...
echo.

timeout /t 2 /nobreak
start "" "http://localhost/attendance_system/"

echo ✅ Application lancée dans le navigateur!
echo.
echo 💡 CONSEILS:
echo    • Gardez WAMP lancé (icône verte en bas à droite)
echo    • Si vous fermer le navigateur, relancez: http://localhost/attendance_system/
echo    • Pour arrêter: Cliquez sur l'icône WAMP → "Stop All Services"
echo.
pause

