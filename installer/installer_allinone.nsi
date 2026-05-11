; NSIS Installer Script - Attendance System All-In-One
; ====================================================
; Ce script crée un installateur complet qui contient:
;   - WAMP complet (Apache + MySQL + PHP)
;   - L'application PHP
;   - La base de données
;   - Configuration automatique
;
; Taille finale: ~2 GB
; Installation: Entièrement automatique
; Utilisateur final: N'a besoin de RIEN d'autre
;
; Compilation: makensis.exe installer_allinone.nsi
; Résultat: Attendance_System_AllInOne_Setup.exe (~2GB)

!include "MUI2.nsh"
!include "x64.nsh"

; ============== CONFIGURATION ==============

Name "Attendance System - Installation Complète v1.0"
OutFile "Attendance_System_AllInOne_Setup.exe"
InstallDir "$APPDATA\AttendanceSystem"
RequestExecutionLevel admin

; Icône (optionnel)
; Icon "icon.ico"

; ============== PAGES ==============

!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

!insertmacro MUI_LANGUAGE "French"

; ============== INSTALL SECTION ==============

Section "Installation Complète"

  SetOutPath "$INSTDIR"

  ; -------- ÉTAPE 1: WAMP --------
  DetailPrint "Installation de WAMP..."
  DetailPrint "Copie d'Apache..."
  SetOverwrite try
  File /r /x "docs" /x "manual" "C:\wamp64\bin\apache\*.*"

  DetailPrint "Copie de MySQL..."
  File /r /x "docs" /x "mysql-test" "C:\wamp64\bin\mysql\*.*"

  DetailPrint "Copie de PHP..."
  File /r "C:\wamp64\bin\php\*.*"

  DetailPrint "Copie des fichiers WAMP..."
  File /r "C:\wamp64\apps\*.*"
  File /r "C:\wamp64\scripts\*.*"
  File "C:\wamp64\wampmanager.exe"
  File "C:\wamp64\wampmanager.conf"
  File "C:\wamp64\README.txt"

  ; -------- ÉTAPE 2: APPLICATION --------
  DetailPrint "Installation de l'application..."
  CreateDirectory "$INSTDIR\wamp64\www\attendance_system"
  SetOutPath "$INSTDIR\wamp64\www\attendance_system"
  File /r /x "node_modules" /x ".git" /x "electron" /x "installer" "C:\wamp64\www\attendance_system\*.*"

  ; -------- ÉTAPE 3: CONFIGURATION --------
  DetailPrint "Configuration de la base de données..."
  SetOutPath "$INSTDIR"

  ; Copier le script d'installation
  File "C:\wamp64\www\attendance_system\installer\install_all_in_one.bat"
  File "C:\wamp64\www\attendance_system\database\mysql_schema.sql"

  ; -------- ÉTAPE 4: RACCOURCIS --------
  DetailPrint "Création des raccourcis..."
  CreateDirectory "$SMPROGRAMS\AttendanceSystem"
  CreateShortCut "$SMPROGRAMS\AttendanceSystem\Démarrer Attendance System.lnk" "$INSTDIR\wampmanager.exe"
  CreateShortCut "$SMPROGRAMS\AttendanceSystem\Désinstaller.lnk" "$INSTDIR\Uninstall.exe"
  CreateShortCut "$DESKTOP\Attendance System.lnk" "$INSTDIR\wampmanager.exe"

  ; -------- ÉTAPE 5: ENREGISTREMENT ==============
  DetailPrint "Enregistrement du système..."
  WriteRegStr HKLM "Software\AttendanceSystem" "InstallPath" "$INSTDIR"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem" "DisplayName" "Attendance System v1.0"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem" "UninstallString" "$INSTDIR\Uninstall.exe"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem" "InstallLocation" "$INSTDIR"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem" "DisplayVersion" "1.0.0"

  ; -------- ÉTAPE 6: EXÉCUTION DU SCRIPT ==============
  DetailPrint "Finalisation de l'installation..."
  ExecWait '"$INSTDIR\install_all_in_one.bat"'

  DetailPrint "Installation terminée!"

SectionEnd

; ============== UNINSTALL SECTION ==============

Section "Uninstall"

  DetailPrint "Arrêt des services..."
  ExecWait '"$INSTDIR\wampmanager.exe"' ; Essayer d'arrêter proprement

  DetailPrint "Suppression des fichiers..."
  RMDir /r "$INSTDIR"

  DetailPrint "Suppression des raccourcis..."
  RMDir /r "$SMPROGRAMS\AttendanceSystem"
  Delete "$DESKTOP\Attendance System.lnk"

  DetailPrint "Suppression de la base de données..."
  ; La BD reste dans %APPDATA% pour les données utilisateur

  DetailPrint "Nettoyage du registre..."
  DeleteRegKey HKLM "Software\AttendanceSystem"
  DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem"

  DetailPrint "Désinstallation terminée!"

SectionEnd

