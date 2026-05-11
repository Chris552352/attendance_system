@echo off
chcp 65001 >nul
echo.
echo --- Pare-feu Windows : Apache port 80 (tous profils : privé, public, domaine) ---
echo Clic droit ^> Exécuter en tant qu'administrateur
echo.

set RULE=WAMP Apache HTTP 80 (LAN)

netsh advfirewall firewall delete rule name="%RULE%" >nul 2>&1

REM profile=any = private + public + domain (évite timeout si Wi-Fi classé "public")
netsh advfirewall firewall add rule name="%RULE%" dir=in action=allow protocol=TCP localport=80 profile=any

if %errorlevel% neq 0 (
  echo ECHEC. Lancez ce fichier en administrateur.
  pause
  exit /b 1
)

echo OK — règle ajoutée (profils tous réseaux).
echo Paramètres Windows : Wi-Fi du PC peut être défini en "Réseau privé" pour plus de simplicité.
echo Redémarrez WAMP si besoin, coupez le VPN sur le téléphone, retestez.
pause
