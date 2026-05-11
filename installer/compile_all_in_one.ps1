# Script de compilation automatique du All-In-One
# Usage: .\compile_all_in_one.ps1

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║        COMPILATION - Attendance System ALL-IN-ONE          ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# ============== VÉRIFICATIONS ==============

Write-Host "1️⃣  Vérification de NSIS..." -ForegroundColor Yellow

$nsisPath = "C:\Program Files (x86)\NSIS\makensis.exe"
if (-not (Test-Path $nsisPath)) {
    Write-Host "❌ NSIS n'a pas été trouvé!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Solution:" -ForegroundColor Yellow
    Write-Host "  1. Téléchargez NSIS: https://nsis.sourceforge.io/Download" -ForegroundColor White
    Write-Host "  2. Installez NSIS" -ForegroundColor White
    Write-Host "  3. Relancez ce script" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host "✅ NSIS trouvé" -ForegroundColor Green
Write-Host ""

# ============== VÉRIFIER LES FICHIERS SOURCE ==============

Write-Host "2️⃣  Vérification des fichiers source..." -ForegroundColor Yellow

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
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $file NOT FOUND" -ForegroundColor Red
        $allExists = $false
    }
}

if (-not $allExists) {
    Write-Host ""
    Write-Host "❌ Erreur: Certains fichiers source sont manquants!" -ForegroundColor Red
    exit 1
}

Write-Host ""

# ============== CALCULER LA TAILLE ==============

Write-Host "3️⃣  Calcul de la taille..." -ForegroundColor Yellow

$wampSize = (Get-ChildItem -Path "C:\wamp64\bin" -Recurse | Measure-Object -Property Length -Sum).Sum / 1GB
$appSize = (Get-ChildItem -Path "C:\wamp64\www\attendance_system" -Recurse | Measure-Object -Property Length -Sum).Sum / 1GB

Write-Host "  📦 WAMP: $([math]::Round($wampSize, 1)) GB" -ForegroundColor Cyan
Write-Host "  📦 Application: $([math]::Round($appSize, 1)) GB" -ForegroundColor Cyan
Write-Host "  📦 Total estimé: $([math]::Round($wampSize + $appSize + 0.5, 1)) GB" -ForegroundColor Cyan
Write-Host ""

# ============== AVERTISSEMENT ==============

Write-Host "⏱️  TEMPS DE COMPILATION" -ForegroundColor Yellow
Write-Host "  ⏳ Environ 30 minutes à 1 heure" -ForegroundColor Cyan
Write-Host "  ☕ Prenez un café!" -ForegroundColor White
Write-Host ""

# ============== CONFIRMATION ==============

Write-Host "Confirmer la compilation? (O/N)" -ForegroundColor Yellow
$confirm = Read-Host
if ($confirm -ne "O" -and $confirm -ne "o") {
    Write-Host "Annulé." -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "🚀 DÉMARRAGE DE LA COMPILATION..." -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
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
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""

# ============== VÉRIFIER LE RÉSULTAT ==============

Write-Host "4️⃣  Vérification du résultat..." -ForegroundColor Yellow

$exeFile = "$outputDir\Attendance_System_AllInOne_Setup.exe"

if (Test-Path $exeFile) {
    $exeSize = (Get-Item $exeFile).Length / 1GB
    Write-Host "✅ COMPILATION RÉUSSIE!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📦 Fichier créé: $exeFile" -ForegroundColor Cyan
    Write-Host "📊 Taille: $([math]::Round($exeSize, 1)) GB" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host "✨ PRÊT À DISTRIBUER!" -ForegroundColor Green
    Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host ""
    Write-Host "Prochaines étapes:" -ForegroundColor Yellow
    Write-Host "  1. Testez le .exe sur un autre PC" -ForegroundColor White
    Write-Host "  2. Si tout marche: distribuer via clé USB ou drive" -ForegroundColor White
    Write-Host "  3. Vérifiez la taille (~2GB)" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host "❌ ERREUR: Le fichier .exe n'a pas été créé!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Vérifiez:" -ForegroundColor Yellow
    Write-Host "  • NSIS est bien installé" -ForegroundColor White
    Write-Host "  • Tous les fichiers sources existent" -ForegroundColor White
    Write-Host "  • Vous avez les permissions admin" -ForegroundColor White
    Write-Host "  • Espace disque suffisant" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host "Appuyez sur une touche pour continuer..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

