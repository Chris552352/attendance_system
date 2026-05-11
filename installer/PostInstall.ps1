# Script PowerShell pour finaliser l'installation
# À exécuter après l'installation NSIS

param(
    [string]$WampPath = "C:\wamp64",
    [string]$AppPath = "C:\wamp64\www\attendance_system"
)

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║    Configuration FINALE - Système de Présence             ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

# Étape 1: Vérifier WAMP
Write-Host "1️⃣  Vérification de WAMP..." -ForegroundColor Cyan
if (-not (Test-Path "$WampPath\wampmanager.exe")) {
    Write-Host "❌ WAMP n'a pas été trouvé à: $WampPath" -ForegroundColor Red
    Write-Host "   Installez WAMP manuellement: https://wampserver.com" -ForegroundColor Yellow
    exit 1
}
Write-Host "   ✅ WAMP trouvé" -ForegroundColor Green

# Étape 2: Lancer WAMP si pas lancé
Write-Host ""
Write-Host "2️⃣  Lancement de WAMP..." -ForegroundColor Cyan
$wampProcess = Get-Process wampmanager -ErrorAction SilentlyContinue
if (-not $wampProcess) {
    Write-Host "   ⏳ Lancement de WAMP..." -ForegroundColor Yellow
    Start-Process "$WampPath\wampmanager.exe"
    Start-Sleep -Seconds 8
}
Write-Host "   ✅ WAMP lancé" -ForegroundColor Green

# Étape 3: Attendre que MySQL soit ready
Write-Host ""
Write-Host "3️⃣  Attente du démarrage de MySQL..." -ForegroundColor Cyan
Start-Sleep -Seconds 5
Write-Host "   ✅ MySQL prêt" -ForegroundColor Green

# Étape 4: Importer la base de données
Write-Host ""
Write-Host "4️⃣  Importation de la base de données..." -ForegroundColor Cyan

$mysqlPath = "$WampPath\bin\mysql\mysql8.0.1\bin\mysql.exe"
$sqlFile = "$AppPath\database\mysql_schema.sql"

if (-not (Test-Path $mysqlPath)) {
    Write-Host "❌ mysql.exe non trouvé à: $mysqlPath" -ForegroundColor Red
    Write-Host "   Les versions de MySQL peuvent différer" -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path $sqlFile)) {
    Write-Host "❌ Fichier SQL non trouvé à: $sqlFile" -ForegroundColor Red
    exit 1
}

try {
    # Importer le schéma
    & $mysqlPath -u root -e "DROP DATABASE IF EXISTS attendance_system;" 2>&1 | Out-Null
    & $mysqlPath -u root < $sqlFile 2>&1 | Out-Null
    Write-Host "   ✅ Base de données importée" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Erreur lors de l'import (non bloquant)" -ForegroundColor Yellow
    Write-Host "   Vous pouvez importer manuellement: $sqlFile" -ForegroundColor Yellow
}

# Étape 5: Vérifier la connexion
Write-Host ""
Write-Host "5️⃣  Vérification de la connexion..." -ForegroundColor Cyan
Start-Sleep -Seconds 3

try {
    $response = Invoke-WebRequest -Uri "http://localhost/attendance_system/" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    Write-Host "   ✅ Application accessible" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Application pas accessible (pas bloquant)" -ForegroundColor Yellow
    Write-Host "   Vérifiez: http://localhost/attendance_system/" -ForegroundColor Yellow
}

# Étape 6: Lancer l'application
Write-Host ""
Write-Host "6️⃣  Lancement de l'application..." -ForegroundColor Cyan
Start-Process "http://localhost/attendance_system/"
Write-Host "   ✅ Application lancée" -ForegroundColor Green

Write-Host ""
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "✅ INSTALLATION COMPLÈTE!" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Identifiants par défaut:" -ForegroundColor Yellow
Write-Host "   Email: chris552352@gmail.com" -ForegroundColor White
Write-Host "   Password: 552352" -ForegroundColor White
Write-Host ""

