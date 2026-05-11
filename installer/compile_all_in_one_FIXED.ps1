# Script de compilation automatique du All-In-One
# Usage: .\compile_all_in_one.ps1

Write-Host ""
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "        COMPILATION - Attendance System ALL-IN-ONE" -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""

# ============== VERIFICATIONS ==============

Write-Host "1  Verification de NSIS..." -ForegroundColor Yellow

$nsisPath = "C:\Program Files (x86)\NSIS\makensis.exe"
if (-not (Test-Path $nsisPath)) {
    Write-Host "ERREUR: NSIS n'a pas ete trouve!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Solution:" -ForegroundColor Yellow
    Write-Host "  1. Telechargez NSIS: https://nsis.sourceforge.io/Download" -ForegroundColor White
    Write-Host "  2. Installez NSIS" -ForegroundColor White
    Write-Host "  3. Relancez ce script" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host "OK NSIS trouve" -ForegroundColor Green
Write-Host ""

# ============== VERIFIER LES FICHIERS SOURCE ==============

Write-Host "2  Verification des fichiers source..." -ForegroundColor Yellow

$files = @(
    "C:\wamp64\bin\apache",
    "C:\wamp64\bin\mysql",
    "C:\wamp64\bin\php",
    "C:\wamp64\www\attendance_system",
    "C:\wamp64\www\attendance_system\database\mysql_schema.sql"
)

$allExists = $true
foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "  OK $file" -ForegroundColor Green
    } else {
        Write-Host "  ERREUR $file NOT FOUND" -ForegroundColor Red
        $allExists = $false
    }
}

if (-not $allExists) {
    Write-Host ""
    Write-Host "ERREUR: Certains fichiers source sont manquants!" -ForegroundColor Red
    exit 1
}

Write-Host ""

# ============== CALCULER LA TAILLE ==============

Write-Host "3  Calcul de la taille..." -ForegroundColor Yellow

$wampSize = (Get-ChildItem -Path "C:\wamp64\bin" -Recurse | Measure-Object -Property Length -Sum).Sum / 1GB
$appSize = (Get-ChildItem -Path "C:\wamp64\www\attendance_system" -Recurse | Measure-Object -Property Length -Sum).Sum / 1GB

Write-Host "  WAMP: $([math]::Round($wampSize, 1)) GB" -ForegroundColor Cyan
Write-Host "  Application: $([math]::Round($appSize, 1)) GB" -ForegroundColor Cyan
Write-Host "  Total estime: $([math]::Round($wampSize + $appSize + 0.5, 1)) GB" -ForegroundColor Cyan
Write-Host ""

# ============== AVERTISSEMENT ==============

Write-Host "TEMPS DE COMPILATION" -ForegroundColor Yellow
Write-Host "  Environ 30 minutes a 1 heure" -ForegroundColor Cyan
Write-Host "  Prenez un cafe!" -ForegroundColor White
Write-Host ""

# ============== CONFIRMATION ==============

Write-Host "Confirmer la compilation? (O/N)" -ForegroundColor Yellow
$confirm = Read-Host
if ($confirm -ne "O" -and $confirm -ne "o") {
    Write-Host "Annule." -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host "DEMARRAGE DE LA COMPILATION..." -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host ""

# ============== LANCER LA COMPILATION ==============

$nsiFile = "C:\wamp64\www\attendance_system\installer\installer_allinone.nsi"
$outputDir = "C:\wamp64\www\attendance_system\installer"

Write-Host "Fichier NSI: $nsiFile" -ForegroundColor Cyan
Write-Host "Dossier output: $outputDir" -ForegroundColor Cyan
Write-Host ""

Write-Host "Lancement de NSIS..." -ForegroundColor Yellow
& $nsisPath /V2 $nsiFile

Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host ""

# ============== VERIFIER LE RESULTAT ==============

Write-Host "4  Verification du resultat..." -ForegroundColor Yellow

$exeFile = "$outputDir\Attendance_System_AllInOne_Setup.exe"

if (Test-Path $exeFile) {
    $exeSize = (Get-Item $exeFile).Length / 1GB
    Write-Host "OK COMPILATION REUSSIE!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Fichier cree: $exeFile" -ForegroundColor Cyan
    Write-Host "Taille: $([math]::Round($exeSize, 1)) GB" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "========================================================" -ForegroundColor Green
    Write-Host "PRET A DISTRIBUER!" -ForegroundColor Green
    Write-Host "========================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Prochaines etapes:" -ForegroundColor Yellow
    Write-Host "  1. Testez le .exe sur un autre PC" -ForegroundColor White
    Write-Host "  2. Si tout marche: distribuer via cle USB ou drive" -ForegroundColor White
    Write-Host "  3. Verifiez la taille (~2GB)" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host "ERREUR: Le fichier .exe n'a pas ete cree!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Verifiez:" -ForegroundColor Yellow
    Write-Host "  * NSIS est bien installe" -ForegroundColor White
    Write-Host "  * Tous les fichiers sources existent" -ForegroundColor White
    Write-Host "  * Vous avez les permissions admin" -ForegroundColor White
    Write-Host "  * Espace disque suffisant" -ForegroundColor White
    Write-Host ""
    exit 1
}
