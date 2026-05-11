; NSIS Installer Script - Attendance System Complete Setup
; Compile avec: makensis.exe attendance_system_installer.nsi

!include "MUI2.nsh"
!include "x64.nsh"

; Configuration générale
Name "Attendance System - Système de Présence v1.0"
OutFile "Attendance_System_Complete_Setup.exe"
InstallDir "$PROGRAMFILES64\AttendanceSystem"

; MUI Settings
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

!insertmacro MUI_LANGUAGE "French"

; Sections
Section "Installation Complète"
  SetOutPath "$INSTDIR"

  ; Copier l'executable Electron
  File /r "C:\wamp64\www\attendance_system\electron\dist\win-unpacked\*.*"

  ; Copier l'application PHP
  SetOutPath "$INSTDIR\www\attendance_system"
  File /r /x "node_modules" /x "electron" /x ".git" "C:\wamp64\www\attendance_system\*.*"

  ; Créer les raccourcis
  SetOutPath "$INSTDIR"
  CreateDirectory "$SMPROGRAMS\AttendanceSystem"
  CreateShortCut "$SMPROGRAMS\AttendanceSystem\Attendance System.lnk" "$INSTDIR\Attendance System.exe"
  CreateShortCut "$DESKTOP\Attendance System.lnk" "$INSTDIR\Attendance System.exe"

  ; Exécuter le script de configuration
  ExecWait 'powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$INSTDIR\PostInstall.ps1"'

SectionEnd

; Désinstallation
Section "Uninstall"
  RMDir /r "$INSTDIR"
  RMDir /r "$SMPROGRAMS\AttendanceSystem"
  Delete "$DESKTOP\Attendance System.lnk"
SectionEnd

