; NSIS Installer - Attendance System Complete
; Génère un setup.exe qui installe tout automatiquement

!include "MUI2.nsh"

; ============== CONFIGURATION ==============
Name "Attendance System v1.0 - Setup Complet"
OutFile "Attendance_System_AllInOne.exe"
InstallDir "$INSTDIR"
RequestExecutionLevel admin
Icon "..\assets\icon.ico"

; Espace requis
InstallDirRegKey HKLM "Software\AttendanceSystem" "InstallPath"

; ============== PAGES D'INSTALLATION ==============
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

!insertmacro MUI_LANGUAGE "French"

; ============== INSTALL SECTION ==============
Section "Installation"
  SetOutPath "$INSTDIR"

  ; Messages d'installation
  DetailPrint "Préparation de l'installation..."
  DetailPrint "Créer le dossier d'installation..."

  ; Créer la structure des dossiers
  CreateDirectory "$INSTDIR"
  CreateDirectory "$INSTDIR\www"
  CreateDirectory "$INSTDIR\www\attendance_system"

  ; Copier les fichiers de l'application
  DetailPrint "Copie de l'application..."
  SetOverwrite try

  ; Copier récursivement depuis le dossier source
  ; NOTE: Adapter ces chemins selon votre configuration
  File /r /x "node_modules" /x ".git" /x "electron" "..\*.*"

  ; Enregistrer dans le registre
  WriteRegStr HKLM "Software\AttendanceSystem" "InstallPath" "$INSTDIR"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem" "DisplayName" "Attendance System"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem" "UninstallString" "$INSTDIR\Uninstall.exe"

  ; Créer un raccourci de lancement
  CreateDirectory "$SMPROGRAMS\AttendanceSystem"
  CreateShortCut "$SMPROGRAMS\AttendanceSystem\Lancer l'application.lnk" "http://localhost/attendance_system/"
  CreateShortCut "$DESKTOP\Attendance System.lnk" "http://localhost/attendance_system/"

  ; Lancer le script de finalisation
  DetailPrint "Finalisation de l'installation..."
  ExecWait '"$INSTDIR\installer\install_complete.bat"'

  DetailPrint "Installation terminée!"
SectionEnd

; ============== UNINSTALL SECTION ==============
Section "Uninstall"
  RMDir /r "$INSTDIR"
  RMDir /r "$SMPROGRAMS\AttendanceSystem"
  Delete "$DESKTOP\Attendance System.lnk"

  DeleteRegKey HKLM "Software\AttendanceSystem"
  DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\AttendanceSystem"
SectionEnd

