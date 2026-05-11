# Autorise les autres appareils (téléphone) à joindre Apache WAMP sur le PC.
# Clic droit > Exécuter avec PowerShell en administrateur
# Ou lancez : autoriser_acces_reseau_wamp.bat (admin)

$ErrorActionPreference = 'Stop'
$httpd = 'C:\wamp64\bin\apache\apache2.4.62.1\bin\httpd.exe'

Write-Host ''
Write-Host '--- Autorisation acces reseau WAMP ---' -ForegroundColor Cyan

if (-not (Test-Path -LiteralPath $httpd)) {
    Write-Host "ERREUR: httpd.exe introuvable : $httpd" -ForegroundColor Red
    Write-Host 'Adaptez le chemin dans ce script si votre version Apache est differente.'
    exit 1
}

# 1) Wi-Fi en "reseau prive" (le profil Public bloque beaucoup de connexions entrantes)
$wifi = Get-NetConnectionProfile | Where-Object { $_.InterfaceAlias -like '*Wi-Fi*' -or $_.InterfaceAlias -like '*WLAN*' }
foreach ($p in $wifi) {
    if ($p.NetworkCategory -ne 'Private') {
        Write-Host "Wi-Fi ""$($p.Name)"" : passage en Reseau prive (etait $($p.NetworkCategory))..."
        Set-NetConnectionProfile -InterfaceIndex $p.InterfaceIndex -NetworkCategory Private
    } else {
        Write-Host "Wi-Fi ""$($p.Name)"" : deja Reseau prive."
    }
}

# 2) Pare-feu : autoriser Apache (plus fiable qu'un simple port 80)
$ruleNames = @('WAMP Apache httpd.exe (LAN)', 'WAMP Apache TCP 80 (LAN)')
foreach ($n in $ruleNames) {
    Remove-NetFirewallRule -DisplayName $n -ErrorAction SilentlyContinue
}

New-NetFirewallRule -DisplayName 'WAMP Apache httpd.exe (LAN)' `
    -Direction Inbound -Action Allow -Program $httpd `
    -Profile Domain, Private, Public | Out-Null
Write-Host 'Pare-feu : regle pour httpd.exe (tous profils).' -ForegroundColor Green

New-NetFirewallRule -DisplayName 'WAMP Apache TCP 80 (LAN)' `
    -Direction Inbound -Action Allow -Protocol TCP -LocalPort 80 `
    -Profile Domain, Private, Public | Out-Null
Write-Host 'Pare-feu : regle TCP port 80 (tous profils).' -ForegroundColor Green

Write-Host ''
Write-Host 'OK. Redemarrez Apache (WAMP) puis testez depuis le telephone.' -ForegroundColor Green
Write-Host 'Si ca echoue encore : le Wi-Fi (ecole / invite) peut interdire les appareils entre eux.' -ForegroundColor Yellow
Write-Host ''
