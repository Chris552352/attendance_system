@echo off
chcp 65001 >nul
title Autoriser acces reseau WAMP
echo.
echo Ce script doit etre lance EN ADMINISTRATEUR (pare-feu + profil reseau).
echo.

net session >nul 2>&1
if %errorlevel% neq 0 (
  echo Clic droit sur ce fichier ^> Executer en tant qu'administrateur
  pause
  exit /b 1
)

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0autoriser_acces_reseau_wamp.ps1"
if %errorlevel% neq 0 pause
pause
