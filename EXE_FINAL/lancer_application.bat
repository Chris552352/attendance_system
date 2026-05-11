@echo off
REM Système de Présence - Launcher
REM Lance WAMP et ouvre l'application

echo Demarrage du Systeme de Presence...
echo.

REM Ouvrir WAMP (ajuster le chemin si nécessaire)
start "" "C:\wamp64\wampmanager.exe"

timeout /t 5

REM Ouvrir le navigateur à l'application
start http://localhost/attendance_system/

echo Application lancee!
pause

