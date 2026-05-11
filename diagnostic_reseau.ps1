# Script de diagnostic réseau pour Attendance System
# Exécutez ce script pour identifier les problèmes de connectivité

Write-Host "=== DIAGNOSTIC RÉSEAU ATTENDANCE SYSTEM ===" -ForegroundColor Cyan
Write-Host ""

# 1. Vérifier l'adresse IP du PC
Write-Host "1. ADRESSE IP DU PC:" -ForegroundColor Yellow
$ipconfig = ipconfig
$ipconfig | Select-String "IPv4" | ForEach-Object {
    $ipLine = $_.ToString()
    if ($ipLine -match ":\s*([0-9\.]+)") {
        $ip = $matches[1]
        Write-Host "   IP détectée: $ip" -ForegroundColor Green
    }
}
Write-Host ""

# 2. Vérifier si Apache fonctionne
Write-Host "2. STATUT APACHE:" -ForegroundColor Yellow
$apacheProcesses = Get-Process | Where-Object { $_.ProcessName -like "*apache*" -or $_.ProcessName -like "*httpd*" }
if ($apacheProcesses) {
    Write-Host "   Apache est en cours d'exécution" -ForegroundColor Green
    $apacheProcesses | ForEach-Object {
        Write-Host "   - $($_.ProcessName) (PID: $($_.Id))"
    }
} else {
    Write-Host "   Apache n'est PAS en cours d'exécution" -ForegroundColor Red
    Write-Host "   ACTION: Démarrez WAMP" -ForegroundColor Yellow
}
Write-Host ""

# 3. Vérifier les ports d'écoute
Write-Host "3. PORTS D'ÉCOUTE:" -ForegroundColor Yellow
$netstat = netstat -ano | Select-String ":80"
if ($netstat) {
    Write-Host "   Port 80 est en écoute:" -ForegroundColor Green
    $netstat | ForEach-Object {
        Write-Host "   $_"
    }
} else {
    Write-Host "   Port 80 n'est PAS en écoute" -ForegroundColor Red
}
Write-Host ""

# 4. Tester la connexion locale
Write-Host "4. TEST CONNEXION LOCALE (localhost):" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost/attendance_system/" -UseBasicParsing -TimeoutSec 5
    Write-Host "   SUCCÈS: Le serveur répond en local" -ForegroundColor Green
    Write-Host "   Status: $($response.StatusCode)"
} catch {
    Write-Host "   ÉCHEC: Le serveur ne répond pas en local" -ForegroundColor Red
    Write-Host "   Erreur: $($_.Exception.Message)"
}
Write-Host ""

# 5. Tester la connexion externe
Write-Host "5. TEST CONNEXION EXTERNE (10.74.196.93):" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://10.74.196.93/attendance_system/" -UseBasicParsing -TimeoutSec 5
    Write-Host "   SUCCÈS: Le serveur répond sur l'IP externe" -ForegroundColor Green
    Write-Host "   Status: $($response.StatusCode)"
} catch {
    Write-Host "   ÉCHEC: Le serveur ne répond pas sur l'IP externe" -ForegroundColor Red
    Write-Host "   Erreur: $($_.Exception.Message)"
}
Write-Host ""

# 6. Vérifier le pare-feu
Write-Host "6. PARE-FEU WINDOWS:" -ForegroundColor Yellow
try {
    $firewallRules = Get-NetFirewallRule | Where-Object { $_.DisplayName -like "*Apache*" -or $_.DisplayName -like "*WAMP*" -or $_.DisplayName -like "*HTTP*" }
    if ($firewallRules) {
        Write-Host "   Règles de pare-feu trouvées:" -ForegroundColor Green
        $firewallRules | ForEach-Object {
            Write-Host "   - $($_.DisplayName) (Enabled: $($_.Enabled))"
        }
    } else {
        Write-Host "   Aucune règle de pare-feu spécifique trouvée" -ForegroundColor Yellow
        Write-Host "   ACTION: Le pare-feu peut bloquer le port 80" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   Impossible de vérifier le pare-feu" -ForegroundColor Yellow
}
Write-Host ""

# 7. Instructions de résolution
Write-Host "=== INSTRUCTIONS DE RÉSOLUTION ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Si Apache ne fonctionne pas:" -ForegroundColor Yellow
Write-Host "   1. Ouvrez WAMP Manager"
Write-Host "   2. Cliquez sur 'Start All Services'"
Write-Host "   3. Attendez que l'icône devienne verte"
Write-Host ""

Write-Host "Si le pare-feu bloque:" -ForegroundColor Yellow
Write-Host "   1. Panneau de configuration > Pare-feu Windows"
Write-Host "   2. Autoriser une application > Apache HTTP Server"
Write-Host "   3. Ou désactivez temporairement le pare-feu pour tester"
Write-Host ""

Write-Host "Pour accéder depuis le téléphone:" -ForegroundColor Yellow
Write-Host "   1. Connectez le téléphone au MÊME réseau Wi-Fi que le PC"
Write-Host "   2. Ouvrez le navigateur sur le téléphone"
Write-Host "   3. Tapez: http://10.74.196.93/attendance_system/"
Write-Host ""

Write-Host "=== FIN DU DIAGNOSTIC ===" -ForegroundColor Cyan
Write-Host "Appuyez sur une touche pour quitter..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
