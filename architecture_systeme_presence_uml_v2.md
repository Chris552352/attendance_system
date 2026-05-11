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

' ===== ARCHITECTURE GLOBALE =====

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

' ===== MODÈLE DE DONNÉES (DIAGRAMME DE CLASSE SÉPARÉ) =====

package "Base de Données MySQL" <<Database>> {
    
    entity utilisateurs {
        * id: int
        --
        +nom: varchar(100)
        +prenom: varchar(100)
        +email: varchar(150)
        +mot_de_passe: varchar(255)
        +role: enum('admin','enseignant')
        +date_creation: timestamp
        +derniere_connexion: timestamp
    }
    
    entity etudiants {
        * id: int
        --
        +matricule: varchar(20)
        +nom: varchar(100)
        +prenom: varchar(100)
        +email: varchar(150)
        +classe_id: int
        +date_inscription: timestamp
    }
    
    entity classes_etablissement {
        * id: int
        --
        +code: varchar(40)
        +nom: varchar(180)
        +niveau: varchar(80)
        +filiere: varchar(120)
        +description: text
        +actif: boolean
        +date_creation: timestamp
    }
    
    entity cours {
        * id: int
        --
        +code: varchar(20)
        +nom: varchar(150)
        +enseignant_id: int
        +description: text
    }
    
    entity inscriptions {
        * id: int
        --
        +etudiant_id: int
        +cours_id: int
        +date_inscription: timestamp
    }
    
    entity comptes_etudiants {
        * id: int
        --
        +etudiant_id: int
        +email: varchar(150)
        +mot_de_passe: varchar(255)
        +date_creation: timestamp
        +derniere_connexion: timestamp
        +actif: boolean
    }
    
    entity seances {
        * id: int
        --
        +nom_cours: varchar(150)
        +enseignant_id: int
        +token: varchar(255)
        +expiration: timestamp
        +date_creation: timestamp
        +active: boolean
    }
    
    entity presences {
        * id: int
        --
        +etudiant_id: int
        +cours_id: int
        +seance_id: int
        +date_presence: date
        +statut: enum('present','absent','retard','excusé')
        +justification: text
        +justifie: boolean
        +enregistre_par: int
        +date_enregistrement: timestamp
    }
    
    ' Relations avec cardinalités
    utilisateurs ||--o{ cours : "enseigne"
    utilisateurs ||--o{ seances : "crée"
    utilisateurs ||--o{ presences : "enregistre"
    
    classes_etablissement ||--o{ etudiants : "contient"
    etudiants ||--|| comptes_etudiants : "possède"
    
    cours }o--o{ etudiants : "via inscriptions"
    inscriptions }o--|| etudiants : "étudiant"
    inscriptions }o--|| cours : "cours"
    
    seances ||--o{ presences : "génère"
    etudiants ||--o{ presences : "marque"
    cours ||--o{ presences : "concerne"
}

' ===== API REST ENDPOINTS =====

package "API REST Mobile" <<API>> {
    
    rectangle "Authentification" <<API>> {
        rectangle "POST /api/login_mobile.php" <<API>>
        rectangle "Token Session (24h)" <<API>>
        rectangle "Validation JWT" <<API>>
    }
    
    rectangle "Gestion Étudiant" <<API>> {
        rectangle "GET /api/student_profile.php" <<API>>
        rectangle "POST /api/student_profile_update.php" <<API>>
        rectangle "GET /api/student_courses.php" <<API>>
        rectangle "GET /api/student_presences.php" <<API>>
    }
    
    rectangle "Présences" <<API>> {
        rectangle "POST /api/mark_presence_mobile.php" <<API>>
        rectangle "POST /api/student_justify_absence.php" <<API>>
        rectangle "GET /api/seance_info.php" <<API>>
    }
    
    rectangle "Utilitaires" <<API>> {
        rectangle "GET /api/stats_seance.php" <<API>>
        rectangle "POST /api/terminer_seance.php" <<API>>
        rectangle "POST /api/justification_messages.php" <<API>>
    }
}

' ===== FLUX DE COMMUNICATION =====

package "Flux Principaux" {
    
    rectangle "Flux Web" <<Frontend>> {
        rectangle "Connexion Admin/Enseignant" <<Frontend>>
        rectangle "Création Séances" <<Frontend>>
        rectangle "Génération QR" <<Frontend>>
        rectangle "Consultation Stats" <<Frontend>>
    }
    
    rectangle "Flux Mobile" <<Mobile>> {
        rectangle "Authentification Biométrique" <<Mobile>>
        rectangle "Scan QR Code" <<Mobile>>
        rectangle "Marquage Présence" <<Mobile>>
        rectangle "Consultation Historique" <<Mobile>>
    }
}

' ===== SÉCURITÉ MULTICOUCHE =====

package "Sécurité" {
    
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

' ===== INFRASTRUCTURE TECHNIQUE =====

package "Infrastructure" {
    
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

' ===== CONNEXIONS FINALES =====

"Flux Web" --> "Sécurité Web"
"Flux Mobile" --> "Sécurité Mobile"
"API REST Mobile" --> "Sécurité API"

"Flux Web" --> "API REST Mobile"
"Flux Mobile" --> "API REST Mobile"
"API REST Mobile" --> "Base de Données MySQL"

"Serveur Web" --> "Base de Données"
"Application Mobile" --> "Serveur Web"

@enduml
```

# Architecture Détaillée du Système

## 🏗️ **Vue d'Ensemble (3 Tiers)**

### **Tier 1: Présentation**
- **🌐 Frontend Web** (PHP/HTML/CSS/JS)
  - Interface administration complète
  - Gestion QR codes projectables
  - Tableaux de bord en temps réel
  
- **📱 Application Mobile** (Flutter)
  - Authentification biométrique
  - Scan QR codes natif
  - Historique des présences
  - Support multilingue

### **Tier 2: Logique Métier**
- **🔧 Backend PHP 8.x**
  - Architecture modulaire
  - API REST complète
  - Gestion sessions sécurisées
  - Token de session mobile (24h)

### **Tier 3: Persistance**
- **🗄️ MySQL 8.x avec InnoDB**
  - Modèle LMD académique
  - Relations intégrité référentielle
  - Encodage UTF-8 complet
  - Transactions ACID

## 📊 **Base de Données - 8 Tables Principales**

### **Tables Utilisateurs**
1. **utilisateurs** - Admins et enseignants
2. **etudiants** - Données académiques
3. **comptes_etudiants** - Auth mobile séparée

### **Tables Académiques**
4. **classes_etablissement** - Promotions LMD
5. **cours** - Matières enseignées
6. **inscriptions** - Liaison many-to-many

### **Tables Présence**
7. **seances** - Sessions QR uniques
8. **presences** - Enregistrements de présence

## 🔐 **Sécurité Multicouche**

### **Couche Web**
- Hash Bcrypt mots de passe
- Sessions PHP sécurisées
- Contrôle d'accès par rôle
- Validation systématique

### **Couche Mobile**
- Auth biométrique locale
- Token session signé
- Stockage sécurisé
- Validation QR serveur

### **Couche API**
- Headers CORS configurés
- Rate limiting implémenté
- Validation token obligatoire
- Sanitization inputs

## 🔄 **Flux de Communication**

### **Flux Principal**
```
Mobile: App → API REST → Backend → MySQL
Web: Interface → Backend → MySQL
```

### **Protocoles**
- HTTP/HTTPS universel
- JSON pour API mobile
- Sessions PHP pour web
- Token UUID pour QR

## 📱 **Architecture Mobile Flutter**

### **Structure App**
```
lib/
├── screens/          # Écrans UI
├── models/           # Modèles données
├── services/        # Services API
├── l10n/            # Internationalisation
└── widgets/         # Composants réutilisables
```

### **Services Clés**
- ApiService (communication)
- BiometricAuthService (auth)
- SessionStorage (local)
- QRCodeScanner (scan)

## 🚀 **Infrastructure Déploiement**

### **Développement**
- WAMP local complet
- MySQL 8.x intégré
- Flutter debug mode
- Xdebug activé

### **Production**
- Hébergement PHP/MySQL
- HTTPS SSL obligatoire
- Backups automatisés
- Monitoring actif

## 🎯 **Processus Métier**

1. **Création Séance** → QR unique généré
2. **Scan QR** → Validation token serveur
3. **Auth Biométrique** → Marquage présence
4. **Enregistrement Auto** → Historique immédiat
5. **Stats Temps Réel** → Tableaux de bord
6. **Justifications** → Workflow admin

Cette architecture assure une gestion moderne, sécurisée et évolutive des présences universitaires avec une expérience utilisateur optimisée.
