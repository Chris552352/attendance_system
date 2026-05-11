# Architecture UML Complète - Système de Gestion de Présence Universitaire

```plantuml
@startuml
!theme cerulean-outline
title Architecture Globale du Système de Présence Universitaire
skinparam backgroundColor #F8F9FA
skinparam handwritten false

' ===== COULEURS POUR LA CLARTÉ =====
skinparam rectangle {
    BackgroundColor<<Backend>> #E3F2FD
    BorderColor<<Backend>> #1976D2
    FontColor<<Backend>> #0D47A1
}

skinparam rectangle {
    BackgroundColor<<Frontend>> #E8F5E8
    BorderColor<<Frontend>> #388E3C
    FontColor<<Frontend>> #1B5E20
}

skinparam rectangle {
    BackgroundColor<<Mobile>> #FFF3E0
    BorderColor<<Mobile>> #F57C00
    FontColor<<Mobile>> #E65100
}

skinparam rectangle {
    BackgroundColor<<Database>> #FCE4EC
    BorderColor<<Database>> #C2185B
    FontColor<<Database>> #880E4F
}

skinparam rectangle {
    BackgroundColor<<API>> #F3E5F5
    BorderColor<<API>> #7B1FA2
    FontColor<<API>> #4A148C
}

' ===== NIVEAU 1: ARCHITECTURE GLOBALE =====

package "Architecture Client-Serveur" {
    
    rectangle "🌐 FRONTEND WEB" <<Frontend>> {
        rectangle "Interface Administration" <<Frontend>>
        rectangle "Interface Enseignant" <<Frontend>>
        rectangle "Gestion QR Code" <<Frontend>>
        rectangle "Tableaux de Bord" <<Frontend>>
    }
    
    rectangle "📱 APPLICATION MOBILE" <<Mobile>> {
        rectangle "Authentification Biométrique" <<Mobile>>
        rectangle "Scan QR Code" <<Mobile>>
        rectangle "Historique Présences" <<Mobile>>
        rectangle "Profil Étudiant" <<Mobile>>
    }
    
    rectangle "🔧 BACKEND PHP" <<Backend>> {
        rectangle "API REST" <<Backend>>
        rectangle "Gestion Sessions" <<Backend>>
        rectangle "Logique Métier" <<Backend>>
        rectangle "Sécurité" <<Backend>>
    }
    
    rectangle "🗄️ BASE DE DONNÉES" <<Database>> {
        rectangle "MySQL 8.x" <<Database>>
        rectangle "InnoDB Engine" <<Database>>
        rectangle "Relations Académiques" <<Database>>
    }
}

' ===== CONNEXIONS PRINCIPALES =====

"🌐 FRONTEND WEB" --> "🔧 BACKEND PHP" : HTTP/HTTPS
"📱 APPLICATION MOBILE" --> "🔧 BACKEND PHP" : API REST (JSON)
"🔧 BACKEND PHP" --> "🗄️ BASE DE DONNÉES" : MySQL PDO

' ===== NIVEAU 2: DÉTAIL DES COMPOSANTS =====

package "Détail Backend PHP" <<Backend>> {
    
    rectangle "Gestion Utilisateurs" <<Backend>> {
        rectangle "Authentification Admin" <<Backend>>
        rectangle "Authentification Enseignant" <<Backend>>
        rectangle "Contrôle Rôles" <<Backend>>
        rectangle "Sessions PHP" <<Backend>>
    }
    
    rectangle "Gestion Académique" <<Backend>> {
        rectangle "Promotions LMD" <<Backend>>
        rectangle "Cours & Inscriptions" <<Backend>>
        rectangle "Séances & QR Codes" <<Backend>>
        rectangle "Présences" <<Backend>>
    }
    
    rectangle "API Mobile" <<API>> {
        rectangle "/api/login_mobile.php" <<API>>
        rectangle "/api/student_profile.php" <<API>>
        rectangle "/api/student_courses.php" <<API>>
        rectangle "/api/mark_presence_mobile.php" <<API>>
        rectangle "/api/student_presences.php" <<API>>
    }
}

' ===== NIVEAU 3: MODÈLE DE DONNÉES =====

package "Modèle de Données Complet" <<Database>> {
    
    class utilisateurs {
        +id: int
        +nom: varchar
        +prenom: varchar
        +email: varchar
        +mot_de_passe: varchar
        +role: enum
    }
    
    class etudiants {
        +id: int
        +matricule: varchar
        +nom: varchar
        +prenom: varchar
        +email: varchar
        +classe_id: int
    }
    
    class classes_etablissement {
        +id: int
        +code: varchar
        +nom: varchar
        +niveau: varchar
        +filiere: varchar
    }
    
    class cours {
        +id: int
        +code: varchar
        +nom: varchar
        +enseignant_id: int
        +description: text
    }
    
    class inscriptions {
        +id: int
        +etudiant_id: int
        +cours_id: int
        +date_inscription: timestamp
    }
    
    class comptes_etudiants {
        +id: int
        +etudiant_id: int
        +email: varchar
        +mot_de_passe: varchar
        +actif: boolean
    }
    
    class seances {
        +id: int
        +nom_cours: varchar
        +enseignant_id: int
        +token: varchar
        +expiration: timestamp
        +active: boolean
    }
    
    class presences {
        +id: int
        +etudiant_id: int
        +cours_id: int
        +seance_id: int
        +date_presence: date
        +statut: enum
    }
    
    ' Relations entre les tables
    utilisateurs "1" --> "N" cours : enseigne
    utilisateurs "1" --> "N" seances : crée
    utilisateurs "1" --> "N" presences : enregistre
    
    classes_etablissement "1" --> "N" etudiants : contient
    etudiants "1" --> "1" comptes_etudiants : possède
    
    cours "N" <-- "M" etudiants : via inscriptions
    etudiants "N" --> "M" cours : via inscriptions
    
    seances "1" --> "N" presences : génère
    etudiants "N" --> "N" presences : marque
    cours "N" --> "N" presences : concerne
}

' ===== NIVEAU 4: FLUX DE DONNÉES =====

package "Flux de Communication" {
    
    rectangle "Flux Web" <<Frontend>> {
        rectangle "Connexion Admin/Enseignant" <<Frontend>>
        rectangle "Création Séances" <<Frontend>>
        rectangle "Génération QR" <<Frontend>>
        rectangle "Consultation Stats" <<Frontend>>
    }
    
    rectangle "Flux Mobile" <<Mobile>> {
        rectangle "Authentification Mobile" <<Mobile>>
        rectangle "Scan QR Code" <<Mobile>>
        rectangle "Marquage Présence" <<Mobile>>
        rectangle "Consultation Historique" <<Mobile>>
    }
    
    rectangle "Flux API" <<API>> {
        rectangle "JSON Request/Response" <<API>>
        rectangle "Token Session (24h)" <<API>>
        rectangle "Validation Token" <<API>>
        rectangle "Retour Données" <<API>>
    }
}

' ===== CONNEXIONS DÉTAILLÉES =====

"Flux Web" --> "Gestion Utilisateurs" : Sessions PHP
"Flux Web" --> "Gestion Académique" : Requêtes HTTP
"Flux Mobile" --> "API Mobile" : HTTP POST/GET
"API Mobile" --> "Gestion Académique" : Appels Fonctions
"Gestion Académique" --> "Base MySQL" : PDO Prepared Statements

' ===== SÉCURITÉ =====

package "Couche Sécurité" {
    
    rectangle "Sécurité Web" <<Backend>> {
        rectangle "Hash Bcrypt Mots de Passe" <<Backend>>
        rectangle "Sessions PHP Sécurisées" <<Backend>>
        rectangle "Contrôle Rôles" <<Backend>>
        rectangle "Validation Entrées" <<Backend>>
    }
    
    rectangle "Sécurité Mobile" <<Mobile>> {
        rectangle "Authentification Biométrique" <<Mobile>>
        rectangle "Token Session Signé" <<Mobile>>
        rectangle "Stockage Sécurisé" <<Mobile>>
        rectangle "Validation QR Code" <<Mobile>>
    }
    
    rectangle "Sécurité API" <<API>> {
        rectangle "CORS Headers" <<API>>
        rectangle "Rate Limiting" <<API>>
        rectangle "Token Validation" <<API>>
        rectangle "Input Sanitization" <<API>>
    }
}

"Sécurité Web" --> "Gestion Utilisateurs"
"Sécurité Mobile" --> "API Mobile"
"Sécurité API" --> "API Mobile"

' ===== INFRASTRUCTURE =====

package "Infrastructure Technique" {
    
    rectangle "Serveur Web" <<Backend>> {
        rectangle "Apache 2.4" <<Backend>>
        rectangle "PHP 8.x" <<Backend>>
        rectangle "HTTPS (Production)" <<Backend>>
        rectangle "WAMP (Développement)" <<Backend>>
    }
    
    rectangle "Base de Données" <<Database>> {
        rectangle "MySQL 8.x" <<Database>>
        rectangle "InnoDB Engine" <<Database>>
        rectangle "utf8mb4_unicode_ci" <<Database>>
        rectangle "Transactions ACID" <<Database>>
    }
    
    rectangle "Application Mobile" <<Mobile>> {
        rectangle "Flutter Framework" <<Mobile>>
        rectangle "Android/iOS" <<Mobile>>
        rectangle "QR Code Scanner" <<Mobile>>
        rectangle "Biometric Auth" <<Mobile>>
    }
}

"Serveur Web" --> "Base de Données"
"Application Mobile" --> "Serveur Web"

' ===== DÉPLOIEMENT =====

package "Environnements" {
    
    rectangle "Développement" <<Frontend>> {
        rectangle "WAMP Local" <<Frontend>>
        rectangle "MySQL Local" <<Frontend>>
        rectangle "Flutter Debug" <<Frontend>>
        rectangle "Xdebug" <<Frontend>>
    }
    
    rectangle "Production" <<Backend>> {
        rectangle "Hébergement PHP/MySQL" <<Backend>>
        rectangle "HTTPS SSL" <<Backend>>
        rectangle "Backup Automatique" <<Backend>>
        rectangle "Monitoring" <<Backend>>
    }
}

"Développement" --> "Production" : Déploiement

@enduml
```

# Architecture Détaillée par Composants

## 🏗️ **Architecture Globale (3 Tiers)**

### **Tier 1: Présentation (Frontend)**
- **🌐 Interface Web Administration** (PHP/HTML/CSS/JavaScript)
  - Gestion utilisateurs, promotions, cours, étudiants
  - Tableaux de bord et statistiques
  - Génération QR codes projectables
  
- **📱 Application Mobile Étudiant** (Flutter)
  - Authentification biométrique (empreinte/visage/PIN)
  - Scan QR codes avec caméra
  - Historique des présences
  - Internationalisation (Français/Anglais)

### **Tier 2: Logique Métier (Backend)**
- **🔧 Backend PHP 8.x**
  - Architecture modulaire (includes + pages)
  - API REST pour communication mobile
  - Gestion sessions PHP (web)
  - Token de session mobile (24h signé)
  - Validation et sécurité

- **🟣 API REST**
  - Endpoints JSON pour mobile
  - Authentification par token
  - Validation serveur des QR codes
  - Gestion des justifications

### **Tier 3: Données (Database)**
- **🗄️ MySQL 8.x avec InnoDB**
  - Modèle LMD (Licence-Master-Doctorat)
  - Relations académiques complètes
  - Transactions ACID
  - Encodage utf8mb4_unicode_ci

## 📊 **Modèle de Données Complet**

### **Entités Principales**
1. **utilisateurs** (admin/enseignant)
2. **etudiants** (données académiques)
3. **comptes_etudiants** (authentification mobile)
4. **classes_etablissement** (promotions LMD)
5. **cours** (matières enseignées)
6. **inscriptions** (many-to-many étudiants-cours)
7. **seances** (sessions QR codes uniques)
8. **presences** (enregistrements de présence)

### **Relations Clés**
- classes_etablissement → etudiants (1,N)
- etudiants ←→ cours via inscriptions (N,M)
- utilisateurs → cours (enseigne)
- utilisateurs → seances (crée)
- seances → presences (génère)
- etudiants → presences (marque)

## 🔐 **Architecture de Sécurité**

### **Sécurité Web**
- Hash Bcrypt mots de passe
- Sessions PHP sécurisées
- Contrôle d'accès par rôle
- Validation entrées serveur

### **Sécurité Mobile**
- Authentification biométrique locale
- Token de session signé (24h)
- Stockage sécurisé local
- Validation QR code serveur

### **Sécurité API**
- Headers CORS
- Rate limiting
- Validation token systématique
- Sanitization inputs

## 🔄 **Flux de Communication**

### **Flux Principal Mobile**
```
App Flutter → API REST → Backend PHP → MySQL
```

### **Flux Principal Web**
```
Interface Web → Backend PHP → MySQL
```

### **Protocoles**
- HTTP/HTTPS pour tout
- JSON pour API mobile
- Sessions PHP pour web
- Token UUID pour QR codes

## 🚀 **Infrastructure de Déploiement**

### **Développement**
- WAMP local (Windows/Apache/MySQL/PHP)
- MySQL 8.x local
- Flutter en mode debug
- Xdebug pour débogage

### **Production**
- Hébergement PHP/MySQL mutualisé
- HTTPS avec SSL
- Backups automatiques
- Monitoring basique

## 📱 **Architecture Mobile Flutter**

### **Structure App**
```
lib/
├── screens/          # Écrans UI
├── models/           # Modèles de données
├── services/        # Services API
├── l10n/            # Internationalisation
└── widgets/         # Composants réutilisables
```

### **Services Principaux**
- ApiService (communication API)
- BiometricAuthService (auth biométrique)
- SessionStorage (stockage local)
- QRCodeScanner (scan codes)

## 🎯 **Points Forts de l'Architecture**

1. **Séparation claire des responsabilités**
2. **Scalabilité horizontale possible**
3. **Sécurité multicouche**
4. **Support multi-langues natif**
5. **API REST standardisée**
6. **Base de données normalisée**
7. **Authentification moderne (biométrie)**
8. **Gestion complète du cycle académique**

## 🔄 **Processus Métier Implémentés**

1. **Création séance** → Génération QR code unique
2. **Scan QR code** → Validation token serveur
3. **Authentification biométrique** → Marquage présence
4. **Enregistrement automatique** → Historique immédiat
5. **Statistiques** → Tableaux de bord en temps réel
6. **Justifications** → Workflow administratif

Cette architecture complète assure une gestion moderne, sécurisée et efficace des présences universitaires avec une expérience utilisateur optimisée pour tous les acteurs (administrateurs, enseignants, étudiants).
