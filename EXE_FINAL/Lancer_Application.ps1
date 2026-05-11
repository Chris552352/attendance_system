# Script de Lancement - Attendance System
# Click droit > Exécuter avec PowerShell

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SYSTÈME DE PRÉSENCE - LAUNCHER" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si WAMP est running
Write-Host "Vérification de WAMP..." -ForegroundColor Yellow
$wampProcess = Get-Process wampmanager -ErrorAction SilentlyContinue

if ($wampProcess) {
    Write-Host "✅ WAMP est déjà lancé" -ForegroundColor Green
} else {
    Write-Host "⏳ Lancement de WAMP..." -ForegroundColor Yellow
    Start-Process "C:\wamp64\wampmanager.exe" -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 8
    Write-Host "✅ WAMP lancé" -ForegroundColor Green
}

Write-Host ""
Write-Host "⏳ Attente du démarrage de la base de données..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

# Vérifier la connexion à localhost
Write-Host "Vérification de la connexion..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost/attendance_system/" -UseBasicParsing -TimeoutSec 5
    Write-Host "✅ Connexion établie" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Impossible de se connecter à localhost" -ForegroundColor Yellow
    Write-Host "   Attendez quelques secondes et relancez le script" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🌐 Ouverture de l'application..." -ForegroundColor Cyan
Start-Process "http://localhost/attendance_system/"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ Application lancée!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Identifiants:"
Write-Host "  Email: chris552352@gmail.com"
Write-Host "  Password: 552352"
Write-Host ""

