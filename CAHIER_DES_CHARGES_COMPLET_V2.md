# CAHIER DES CHARGES - SYSTÈME DE GESTION DE PRÉSENCE UNIVERSITAIRE

**Version:** 2.0  
**Date:** 26/04/2026  
**Statut:** Production Ready  
**Auteur:** Équipe Développement  

---

## TABLE DES MATIÈRES

1. [Vue d'Ensemble Exécutive](#vue-densemble-exécutive)
2. [Architecture Technique](#architecture-technique)
3. [Spécifications Fonctionnelles](#spécifications-fonctionnelles)
4. [Spécifications Techniques](#spécifications-techniques)
5. [Base de Données](#base-de-données)
6. [API REST](#api-rest)
7. [Sécurité](#sécurité)
8. [Déploiement](#déploiement)
9. [Maintenance](#maintenance)

---

## 1. VUE D'ENSEMBLE EXÉCUTIVE

### 1.1 Objectif Global
Créer une solution moderne de gestion des présences universitaires combinant :
- **Interface web** pour l'administration et les enseignants
- **Application mobile** Flutter pour les étudiants
- **Système de QR codes** pour le marquage simplifié
- **Authentification biométrique** native sur mobile
- **Rapports et statistiques** en temps réel

### 1.2 Bénéfices Attendus
- ✅ Marquage de présence en < 10 secondes
- ✅ Zéro saisie manuelle (système automatisé)
- ✅ Traçabilité complète de la présence
- ✅ Justifications d'absences dématérialisées
- ✅ Rapports statistiques en temps réel
- ✅ Support multilingue (au moins 2 langues)

### 1.3 Utilisateurs Cibles

| Profil | Rôle | Responsabilités |
|--------|------|-----------------|
| **Admin** | Administrateur | Gestion globale, utilisateurs, rapports |
| **Enseignant** | Créateur de séances | Créer séances, générer QR, consulter présences |
| **Étudiant** | Marqueur de présence | Scanner QR, marquer présence, justifier absence |

---

## 2. ARCHITECTURE TECHNIQUE

### 2.1 Architecture 3-Tiers

```
┌─────────────────────────────────────────────────────┐
│ TIER 1: PRÉSENTATION                                │
├─────────────────────┬───────────────────────────────┤
│ Frontend Web (PHP)  │ Mobile App (Flutter)          │
└─────────────────────┴───────────────────────────────┘
                        ↓ HTTP/HTTPS
┌─────────────────────────────────────────────────────┐
│ TIER 2: LOGIQUE MÉTIER                              │
├─────────────────────────────────────────────────────┤
│ Backend PHP 8.x | API REST | Gestion Sessions      │
└─────────────────────────────────────────────────────┘
                        ↓ MySQLi/PDO
┌─────────────────────────────────────────────────────┐
│ TIER 3: PERSISTANCE                                 │
├─────────────────────────────────────────────────────┤
│ MySQL 8.x | InnoDB | Transactions ACID             │
└─────────────────────────────────────────────────────┘
```

### 2.2 Composants Principaux

#### Frontend Web
- **Technologie:** PHP 7.4+, HTML5, CSS3, JavaScript ES6+
- **Framework:** Aucun (vanilla PHP) - simplicité
- **Responsif:** Bootstrap 5 ou Tailwind CSS
- **Authentification:** Sessions PHP sécurisées
- **Pages Principales:**
  - Dashboard administrateur
  - Interface enseignant
  - Gestion des séances et QR codes
  - Rapports et statistiques
  - Gestion des justifications

#### Application Mobile
- **Framework:** Flutter 3.x+
- **Langages:** Dart 2.19+
- **Cibles:** Android 11+ (API 30+), iOS 12+
- **Stockage Local:** SQLite + SharedPreferences
- **Authentification:** Biométrique (fingerprint/face)
- **Fonctionnalités Clés:**
  - Scanner QR code (avec caméra)
  - Authentification biométrique
  - Historique des présences
  - Profil étudiant
  - Notifications locales

#### Backend PHP
- **Version:** PHP 8.0+
- **Type:** REST API + Logique métier
- **Base de données:** MySQLi/PDO
- **Sécurité:** 
  - Hash Bcrypt pour mots de passe
  - Token de session 24h pour mobile
  - CORS Headers configurés
  - Rate limiting
  - Input validation/sanitization

#### Base de Données
- **SGBD:** MySQL 8.0+
- **Engine:** InnoDB
- **Encodage:** utf8mb4_unicode_ci
- **Pool de connexions:** 20-50 connexions max
- **Backups:** Quotidiens + Incrémentaux
- **Transactions:** ACID obligatoire

### 2.3 Infrastructure de Déploiement

**Développement:**
- WAMP (Windows Apache MySQL PHP) local
- PHP 8.x en mode debug
- Xdebug activé
- Flutter beta channel

**Production:**
- Hébergement PHP/MySQL professionnel
- HTTPS/SSL (Let's Encrypt)
- CDN pour actifs statiques
- Firewall applicatif (WAF)
- Monitoring 24/7
- Logs centralisés (ELK Stack optionnel)

---

## 3. SPÉCIFICATIONS FONCTIONNELLES

### 3.1 Cas d'Usage Administrateur

#### UC-ADM-001: Gestion des Utilisateurs
- **Acteur:** Administrateur
- **Préconditions:** Connecté avec rôle admin
- **Flux Principal:**
  1. Accès à "Gestion Utilisateurs"
  2. Créer/Modifier/Supprimer utilisateurs
  3. Assigner rôles (admin, enseignant)
  4. Activer/Désactiver comptes
- **Postconditions:** Utilisateur créé dans la base de données

#### UC-ADM-002: Gestion des Classes
- **Fonctionnalités:**
  - CRUD classes (créer, lister, modifier, supprimer)
  - Assigner étudiants à une classe
  - Gérer niveaux LMD (L1, L2, L3, M1, M2)
  - Gérer filières
  - Activer/Désactiver classes

#### UC-ADM-003: Rapport Administratif
- **Disponibles:**
  - Résumé présences par étudiant
  - Résumé présences par cours
  - Résumé présences par classe
  - Taux de présence (% global)
  - Liste absences non justifiées
- **Export:** CSV, PDF, Excel

### 3.2 Cas d'Usage Enseignant

#### UC-PROF-001: Créer une Séance
- **Acteur:** Enseignant
- **Flux:**
  1. Sélectionner cours
  2. Cliquer "Nouvelle Séance"
  3. Système génère:
     - Token unique UUID
     - QR code encodé
     - Expiration 24h
  4. QR projetable immédiatement
  5. Afficher QR pendant la séance

#### UC-PROF-002: Consulter Présences
- **Affichage:**
  - Liste étudiants inscrits au cours
  - Statut présence (Present/Absent/Retard/Excusé)
  - Date et heure marquage
  - Justifications attachées
- **Filtres:** Par classe, par date, par statut
- **Actions:**
  - Approuver justifications
  - Générer certificats de présence

#### UC-PROF-003: Résultats Séance
- **Affichage en Temps Réel:**
  - Nombre présents
  - Nombre absents
  - Nombre retards
  - Nombre excusés
  - Taux de présence globale
- **Graphiques:** Camembert, Histogramme

### 3.3 Cas d'Usage Étudiant Mobile

#### UC-ETU-001: Authentification
- **Processus:**
  1. Lancer app
  2. Entrer email/matricule
  3. Authentification biométrique (fingerprint/face)
  4. Token session créé (24h validité)
  5. App fonctionne offline avec data locale

#### UC-ETU-002: Scanner QR Séance
- **Prérequis:** Auth biométrique réussie
- **Flux:**
  1. Bouton "Scanner QR"
  2. Ouvrir caméra
  3. Scanner QR code
  4. Validation server-side du token
  5. Marquage présence dans DB
  6. Confirmation visuelle + sonore
  7. Sauvegarde locale
- **Délai:** < 5 secondes

#### UC-ETU-003: Justifier Absence
- **Flux:**
  1. Consulter historique
  2. Cliquer sur absence
  3. Fournir raison et documents
  4. Envoyer justification au serveur
  5. Status "En attente d'approbation"
  6. Notification approbation/rejet

#### UC-ETU-004: Consulter Historique
- **Affichage:**
  - Tous présences/absences/retards
  - Filtrés par cours ou par période
  - Statut justification
  - Taux de présence par cours
- **Grafiques:** Statistiques mensuelles

#### UC-ETU-005: Gestion Profil
- **Éditable:**
  - Numéro de téléphone
  - Adresse email
  - Photo profil
  - Langue préférée
- **Non-éditable:**
  - Matricule
  - Nom/Prénom
  - Classe

---

## 4. SPÉCIFICATIONS TECHNIQUES

### 4.1 Stack Technologique

| Composant | Technologie | Version |
|-----------|-------------|---------|
| **Web Server** | Apache | 2.4+ |
| **Backend** | PHP | 8.0+ |
| **Database** | MySQL | 8.0+ |
| **Mobile** | Flutter | 3.x+ |
| **Mobile OS** | Android/iOS | 11+/12+ |
| **API Format** | REST JSON | - |
| **Security** | Bcrypt/JWT | - |
| **Encoding** | UTF-8 MB4 | - |

### 4.2 Performances

| Métrique | Objectif | Seuil Critique |
|----------|----------|----------------|
| **Login Mobile** | < 2s | 5s |
| **Scan QR** | < 5s | 10s |
| **API Response** | < 200ms | 500ms |
| **Page Load Web** | < 2s | 5s |
| **Uptime** | 99.9% | 99% |
| **Concurrence** | 500 users | 1000 users |

### 4.3 Compatibilité

**Mobile:**
- Android: 11 (API 30) ~ 14 (API 34)
- iOS: 12 ~ 17

**Web:**
- Chrome 95+
- Firefox 95+
- Safari 15+
- Edge 95+

### 4.4 Stockage

**Serveur:**
- Structure: `/var/www/html/attendance_system/`
- Database: `/var/lib/mysql/attendance_system/`
- Backups: `/backups/attendance_system/`
- Space Required: 50 GB (avec 5 ans historique)

**Mobile (Per Device):**
- App Size: 80-120 MB
- Cache Local: 50-200 MB
- SQLite DB: 10-50 MB

---

## 5. BASE DE DONNÉES

### 5.1 Schéma Complet (11 Tables)

#### Table: utilisateurs
```sql
CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL (Bcrypt),
    role ENUM('admin', 'enseignant') NOT NULL,
    telephone VARCHAR(20),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL,
    UNIQUE KEY email_idx (email)
);
```

#### Table: etudiants
```sql
CREATE TABLE etudiants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(20),
    classe_id INT NOT NULL,
    photo_path VARCHAR(255) NULL,
    date_inscription DATE NOT NULL,
    actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (classe_id) REFERENCES classes_etablissement(id),
    KEY classe_idx (classe_id)
);
```

#### Table: comptes_etudiants
```sql
CREATE TABLE comptes_etudiants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id INT UNIQUE NOT NULL,
    email VARCHAR(150) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL (Bcrypt),
    biometric_data TEXT NULL (Hash),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL,
    actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE
);
```

#### Table: classes_etablissement
```sql
CREATE TABLE classes_etablissement (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(40) UNIQUE NOT NULL,
    nom VARCHAR(180) NOT NULL,
    niveau VARCHAR(80) NOT NULL (L1, L2, L3, M1, M2),
    filiere VARCHAR(120) NOT NULL,
    description TEXT,
    capacite INT DEFAULT 100,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY code_idx (code)
);
```

#### Table: cours
```sql
CREATE TABLE cours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    enseignant_id INT NOT NULL,
    classe_id INT NOT NULL,
    nombre_credits INT DEFAULT 3,
    heure_par_semaine INT DEFAULT 3,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (classe_id) REFERENCES classes_etablissement(id),
    KEY enseignant_idx (enseignant_id),
    KEY classe_idx (classe_id)
);
```

#### Table: inscriptions
```sql
CREATE TABLE inscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id INT NOT NULL,
    cours_id INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
    UNIQUE KEY etudiant_cours (etudiant_id, cours_id),
    KEY cours_idx (cours_id)
);
```

#### Table: seances
```sql
CREATE TABLE seances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cours_id INT NOT NULL,
    enseignant_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL (UUID),
    qr_code_data LONGTEXT NOT NULL (JSON encoded),
    date_seance DATETIME NOT NULL,
    expiration TIMESTAMP NOT NULL (NOW() + 24h),
    statut ENUM('active', 'closed', 'expired') DEFAULT 'active',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cours_id) REFERENCES cours(id),
    FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id),
    KEY token_idx (token),
    KEY cours_date_idx (cours_id, date_seance),
    INDEX expiration_idx (expiration)
);
```

#### Table: presences
```sql
CREATE TABLE presences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id INT NOT NULL,
    seance_id INT NOT NULL,
    cours_id INT NOT NULL,
    date_presence DATE NOT NULL,
    heure_marquage TIME NOT NULL,
    statut ENUM('present', 'absent', 'retard', 'excusé') DEFAULT 'present',
    justification_id INT NULL,
    justifie BOOLEAN DEFAULT FALSE,
    enregistre_par INT NOT NULL (enseignant ou système),
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_appareil VARCHAR(45) NULL (IPv4 or IPv6),
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (seance_id) REFERENCES seances(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id) REFERENCES cours(id),
    FOREIGN KEY (justification_id) REFERENCES justifications(id),
    KEY etudiant_idx (etudiant_id),
    KEY cours_idx (cours_id),
    KEY date_idx (date_presence),
    UNIQUE KEY presence_unique (etudiant_id, seance_id)
);
```

#### Table: justifications
```sql
CREATE TABLE justifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    presence_id INT NOT NULL UNIQUE,
    etudiant_id INT NOT NULL,
    raison TEXT NOT NULL,
    document_path VARCHAR(255) NULL,
    statut ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approuve_par INT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_approbation TIMESTAMP NULL,
    remarques TEXT NULL,
    FOREIGN KEY (presence_id) REFERENCES presences(id) ON DELETE CASCADE,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id),
    FOREIGN KEY (approuve_par) REFERENCES utilisateurs(id),
    KEY statut_idx (statut),
    KEY etudiant_idx (etudiant_id)
);
```

#### Table: sessions_mobiles
```sql
CREATE TABLE sessions_mobiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    device_info JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP NULL,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    KEY token_idx (token),
    INDEX expires_idx (expires_at)
);
```

#### Table: logs_systeme
```sql
CREATE TABLE logs_systeme (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NULL,
    action VARCHAR(255) NOT NULL,
    table_affectee VARCHAR(100) NULL,
    ancien_valeur JSON NULL,
    nouveau_valeur JSON NULL,
    ip_address VARCHAR(45) NULL,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    KEY utilisateur_idx (utilisateur_id),
    KEY date_idx (date_action)
);
```

### 5.2 Relations et Cardinalités

```
utilisateurs (1) ---> (N) cours (enseigne)
utilisateurs (1) ---> (N) seances (crée)
utilisateurs (1) ---> (N) presences (enregistre)
utilisateurs (1) ---> (N) justifications (approuve)

classes_etablissement (1) ---> (N) etudiants (contient)
etudiants (1) --> (1) comptes_etudiants (possède)

cours (1) ---> (N) seances (génère)
cours (1) ---> (N) inscriptions (a)

etudiants (M) <---> (N) cours (via inscriptions)
inscriptions (N) ---> (1) etudiants
inscriptions (N) ---> (1) cours

seances (1) ---> (N) presences (génère)
etudiants (1) ---> (N) presences (marque)
presences (N) ---> (1) justifications (peut_avoir)
```

### 5.3 Indexes de Performance

```sql
-- Indexes critiques pour recherches rapides
CREATE INDEX idx_seances_token ON seances(token);
CREATE INDEX idx_seances_expiration ON seances(expiration);
CREATE INDEX idx_presences_etudiant_date ON presences(etudiant_id, date_presence);
CREATE INDEX idx_presences_cours ON presences(cours_id);
CREATE INDEX idx_inscriptions_etudiant ON inscriptions(etudiant_id);
CREATE INDEX idx_cours_enseignant ON cours(enseignant_id);
```

### 5.4 Procédures Stockées Recommandées

```sql
-- Marquer présence automatique
PROCEDURE mark_attendance_auto()

-- Archiver données anciennes (> 2 ans)
PROCEDURE archive_old_data()

-- Calculer taux présence
PROCEDURE calculate_attendance_rates()

-- Nettoyer sessions expirées
PROCEDURE cleanup_expired_sessions()
```

---

## 6. API REST

### 6.1 Authentification API

#### POST /api/v1/auth/login_mobile
**Description:** Authentifier étudiant sur mobile

**Request:**
```json
{
    "email": "etudiant@univ.com",
    "password": "***",
    "device_info": {
        "type": "Android",
        "os_version": "13",
        "app_version": "1.0.0"
    }
}
```

**Response (200):**
```json
{
    "success": true,
    "token": "uuid-token-24h",
    "expires_in": 86400,
    "user": {
        "id": 1,
        "matricule": "ETU2024001",
        "nom": "Dupont",
        "prenom": "Jean"
    }
}
```

**Errors:**
- 401: Credentials invalides
- 403: Compte désactivé

---

#### POST /api/v1/auth/logout
**Description:** Déconnecter utilisateur mobile

**Request:**
```json
{
    "token": "uuid-token"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Session terminée"
}
```

---

### 6.2 Gestion Étudiant

#### GET /api/v1/student/profile
**Description:** Récupérer profil étudiant

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "id": 1,
    "matricule": "ETU2024001",
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean@univ.com",
    "classe": {
        "id": 5,
        "code": "L2-INFO",
        "nom": "Licence 2 Informatique"
    },
    "taux_presence_global": 87.5
}
```

---

#### GET /api/v1/student/courses
**Description:** Lister cours de l'étudiant

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "INF201",
            "nom": "Algorithmes",
            "enseignant": "M. Martin",
            "nb_inscrits": 45,
            "taux_presence": 92.0
        }
    ]
}
```

---

#### GET /api/v1/student/presences?course_id=1&month=4
**Description:** Historique présences de l'étudiant

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 100,
            "cours": "Algorithmes",
            "date": "2024-04-15",
            "heure": "09:30",
            "statut": "present",
            "justifie": false
        }
    ]
}
```

---

### 6.3 Présences

#### POST /api/v1/attendance/mark
**Description:** Marquer présence via QR scan

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
    "token_seance": "uuid-seance",
    "timestamp": "2024-04-15T09:35:00Z"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Présence marquée avec succès",
    "seance": {
        "id": 1,
        "cours": "Algorithmes",
        "enseignant": "M. Martin"
    }
}
```

**Errors:**
- 400: Token invalide/expiré
- 409: Déjà marqué pour cette séance

---

#### POST /api/v1/attendance/justify
**Description:** Soumettre justification d'absence

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
    "presence_id": 100,
    "raison": "Maladie",
    "document_base64": "iVBORw0KGgo..."
}
```

**Response (200):**
```json
{
    "success": true,
    "justification_id": 50,
    "statut": "pending"
}
```

---

### 6.4 Séances (Enseignant)

#### POST /api/v1/sessions/create
**Description:** Créer nouvelle séance

**Headers:** `Authorization: Bearer {admin-token}`

**Request:**
```json
{
    "cours_id": 1,
    "date_seance": "2024-04-15T09:00:00Z"
}
```

**Response (200):**
```json
{
    "success": true,
    "seance": {
        "id": 1,
        "token": "550e8400-e29b-41d4-a716-446655440000",
        "qr_code_base64": "iVBORw0KGgo...",
        "expires_at": "2024-04-16T09:00:00Z"
    }
}
```

---

### 6.5 Rapports

#### GET /api/v1/reports/attendance?course_id=1&period=month
**Description:** Rapport présence par cours

**Headers:** `Authorization: Bearer {admin-token}`

**Response (200):**
```json
{
    "success": true,
    "report": {
        "cours": "Algorithmes",
        "total_seances": 12,
        "presences": {
            "present": 480,
            "absent": 45,
            "retard": 15,
            "excusé": 30
        },
        "taux_global": 88.9,
        "etudiants": [
            {
                "matricule": "ETU001",
                "nom": "Dupont",
                "taux": 95.0
            }
        ]
    }
}
```

---

### 6.6 Statuts HTTP Standardisés

| Code | Signification | Cas d'Usage |
|------|---------------|-----------|
| 200 | OK | Succès général |
| 201 | Created | Ressource créée |
| 400 | Bad Request | Données invalides |
| 401 | Unauthorized | Pas authentifié |
| 403 | Forbidden | Pas autorisé |
| 404 | Not Found | Ressource absente |
| 409 | Conflict | Conflit (ex: doublon) |
| 422 | Unprocessable | Données incompatibles |
| 429 | Too Many Requests | Rate limit atteint |
| 500 | Server Error | Erreur serveur |

---

### 6.7 Rate Limiting

```
- 100 requêtes par minute par utilisateur
- 1000 requêtes par minute par IP
- Retry-After header en cas de dépassement
```

---

## 7. SÉCURITÉ

### 7.1 Authentification

**Web (Enseignant/Admin):**
- Sessions PHP sécurisées
- Durée: 30 minutes d'inactivité
- Encryption: HTTPS obligatoire
- Cookie: HttpOnly + Secure flags

**Mobile (Étudiant):**
- Authentification biométrique (fingerprint/face)
- Token JWT 24h
- Refresh token (7j)
- Rotation token automatique

### 7.2 Autorisation

**RBAC (Role-Based Access Control):**
```
Admin:
  - Gérer utilisateurs (CRUD)
  - Gérer classes (CRUD)
  - Approuver justifications
  - Générer rapports
  - Accès logs système

Enseignant:
  - Créer séances
  - Consulter présences (ses cours)
  - Approuver justifications (ses cours)
  - Générer rapports (ses cours)

Étudiant:
  - Consulter profil (sien)
  - Scanner QR (app)
  - Justifier absence (ses absences)
  - Consulter présences (siennes)
```

### 7.3 Chiffrement

**Mots de Passe:**
```php
// Hashing
password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verification
password_verify($password, $hash);
```

**Données Sensibles:**
- Nunéros téléphone: hasher (SHA-256)
- Données biométriques: hasher seulement (PBKDF2)
- Documents: encrypter en transit (HTTPS)

**URLs/Tokens:**
- UUID v4 pour tous les tokens
- Minimum 256 bits d'entropy

### 7.4 Validation Entrées

**Frontend:**
- Validation type/format côté client
- Restriction upload fichiers (max 5 MB, formats autorisés)

**Backend:**
```php
// Sanitization
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { error(); }

// Prepared Statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### 7.5 Protection CSRF

**Tokens CSRF:**
```php
// Générer
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Valider
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    die('CSRF token invalide');
}
```

### 7.6 Protection XSS

**Output Encoding:**
```php
// Pour HTML
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// Pour JSON
echo json_encode($data);

// Pour URLs
echo urlencode($data);
```

### 7.7 CORS

**Configuration:**
```php
header('Access-Control-Allow-Origin: https://app.example.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 3600');
```

### 7.8 Audit & Logging

**Events Loggés:**
- Tous les login/logout
- Modifications données critiques
- Approuvations/Rejets justifications
- Erreurs système
- Tentatives accès non autorisés

**Format Log:**
```json
{
    "timestamp": "2024-04-15T10:30:45Z",
    "user_id": 1,
    "action": "create_session",
    "resource": "seances",
    "result": "success",
    "ip": "192.168.1.100"
}
```

**Rétention:** 2 ans minimum

### 7.9 Compliance

- ✅ RGPD: Droit à l'oubli implémenté
- ✅ OWASP Top 10: Tous les risques adressés
- ✅ Data Backup: Quotidien + Copie géographique
- ✅ Incident Response: Plan 24/7

---

## 8. DÉPLOIEMENT

### 8.1 Environnements

**Dev:**
- Machine locale ou VM
- WAMP + Flutter Emulator
- Base test (100 users)
- Xdebug activé

**Staging:**
- Serveur test VPS
- Configuration production-like
- SSL auto-signé OK
- Data anonymisée

**Production:**
- Hébergement professionnel
- Load balancer optionnel
- CDN pour assets
- HTTPS (certificat public)

### 8.2 Processus Déploiement Web

```bash
# 1. Cloner repository
git clone https://repo.com/attendance.git

# 2. Installer dépendences PHP
composer install --no-dev --optimize-autoloader

# 3. Copy config production
cp config/config.example.php config/config.php

# 4. Permissions
chmod 755 /var/www/attendance_system
chmod 777 /var/www/attendance_system/uploads
chmod 777 /var/www/attendance_system/logs

# 5. Database migrations
php cli/migrate.php

# 6. Clear cache
php cli/cache:clear

# 7. Test
curl https://api.example.com/api/v1/health
```

### 8.3 Processus Déploiement Mobile

**Android:**
1. Build APK/AAB: `flutter build apk --release`
2. Signer avec keystore
3. Upload Google Play Console
4. Tests internes (1 semaine)
5. Tests bêta (1 semaine)
6. Release public

**iOS:**
1. Build IPA: `flutter build ios --release`
2. Archive Xcode
3. Upload TestFlight
4. Tests bêta (1 semaine)
5. Submit App Store
6. Approbation Apple (~24h)

### 8.4 Configuration Serveur

**Apache VirtualHost:**
```apache
<VirtualHost *:443>
    ServerName api.attendance.com
    DocumentRoot /var/www/attendance_system
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/cert.pem
    SSLCertificateKeyFile /etc/ssl/private/key.pem
    
    <Directory /var/www/attendance_system>
        RewriteEngine On
        RewriteRule ^api/(.*)$ index.php [QSA,L]
    </Directory>
    
    ErrorLog /var/log/apache2/attendance_error.log
    AccessLog /var/log/apache2/attendance_access.log
</VirtualHost>
```

**PHP Configuration:**
```ini
max_execution_time = 30
memory_limit = 256M
post_max_size = 50M
upload_max_filesize = 50M
```

**MySQL Configuration:**
```ini
[mysqld]
max_connections = 100
innodb_buffer_pool_size = 1G
slow_query_log = 1
```

### 8.5 Backup & Recovery

**Stratégie Backup:**
- **Quotidien:** Full backup à 2h du matin
- **Incrémental:** Toutes les 6 heures
- **Rétention:** 30 jours
- **Stockage:** On-site + Cloud

**Restore Procedure:**
```bash
# Restore database
mysql -u root -p attendance_system < backup.sql

# Restore files
rsync -av /backup/files/ /var/www/attendance_system/

# Verify
php cli/health:check
```

**RTO/RPO:**
- RTO: 1 heure (recovery time)
- RPO: 6 heures (data loss acceptable)

---

## 9. MAINTENANCE

### 9.1 Monitoring

**Métriques Clés:**
- CPU usage < 80%
- RAM usage < 80%
- Disk space > 20% libre
- Database response < 200ms
- API uptime > 99.9%
- Error rate < 0.1%

**Outils:**
- Nagios / Zabbix pour infrastructure
- ELK Stack (Elasticsearch, Logstash, Kibana) pour logs
- New Relic / DataDog pour APM

### 9.2 Maintenance Préventive

**Hebdomadaire:**
- Vérifier logs erreurs
- Nettoyer storage temporaire
- Vérifier espace disque

**Mensuel:**
- Optimiser base de données
- Vérifier certificats SSL
- Revoir utilisation ressources
- Test backup restoration

**Trimestriel:**
- Audit sécurité (OWASP)
- Review performances
- Mise à jour dépendances
- Analyse utilisation données

### 9.3 Support Utilisateurs

**Canaux Support:**
- Email: support@attendance.com
- Chat: Pendant heures travail
- Ticketing: Portal self-service
- Documentation: Wiki interne

**SLA Cibles:**
- Critical: 1h response, 4h resolution
- High: 4h response, 24h resolution
- Medium: 1 day response, 2 days resolution
- Low: 2 days response, 5 days resolution

### 9.4 Plan Changements

**Processus:**
1. **Documentation:** Décrire changement détail
2. **Test:** Valider en staging 48h
3. **Approbation:** Manager + Tech Lead
4. **Planning:** Choisir fenêtre maintenance
5. **Execution:** Déployer + Monitor
6. **Validation:** Tests post-déploiement
7. **Rollback:** Plan reversal si erreur

**Fenêtres:** Mercredi 22h-23h (faible utilisation)

### 9.5 Versioning

**Frontend Web:** Semantic versioning (Major.Minor.Patch)
- Major: Changements API ou interface
- Minor: Nouvelles fonctionnalités
- Patch: Corrections bugs

**Format Release:**
- Version: v1.2.3
- Branch: release/1.2.3
- Tag: git tag v1.2.3
- Changelog: CHANGELOG.md

---

## 10. CONFORMANCE ET STANDARDS

### 10.1 Standards Respectés

- ✅ REST API Design Guidelines
- ✅ OAuth 2.0 pour autorisation
- ✅ JSON API Specification
- ✅ SOLID Principles (Backend)
- ✅ Clean Code practices
- ✅ WCAG 2.1 accessibility (AA niveau)

### 10.2 Documentation

**Développeur:**
- Installation setup guide
- Architecture overview
- API documentation (Swagger/OpenAPI)
- Database schema diagram
- Deployment guide

**Utilisateur Final:**
- User guide web (PDF)
- Mobile app tutorial (in-app)
- FAQ / Knowledge base
- Video tutorials

### 10.3 Estimation Ressources

**Équipe:**
- 1 Project Manager
- 2 Backend developers
- 1 Frontend developer
- 1 Mobile developer (Flutter)
- 1 DevOps / System admin
- 1 QA Tester
- 1 Database admin (part-time)

**Durée Implémentation:**
- Initial deployment: 8-12 semaines
- Phase 1 (Core): 4 semaines
- Phase 2 (Mobile): 4 semaines
- Phase 3 (Polishing): 2 semaines
- Phase 4 (Testing): 2 semaines

---

## 11. BUDGÉTISATION

### 11.1 Infrastructure Serveur (Annuel)

| Item | Coût |
|------|------|
| Hosting (VPS 4GB RAM) | 1,200 € |
| SSL Certificate (wildcard) | 150 € |
| CDN (10 TB/mois) | 600 € |
| Email (100 users) | 400 € |
| Backups (Cloud) | 300 € |
| **Total** | **2,650 €** |

### 11.2 Licenses & Outils

| Item | Coût |
|------|------|
| JetBrains IDE (5 licences) | 500 € |
| GitHub Premium (Team) | 300 € |
| Monitoring (New Relic) | 400 € |
| Database tool (Navicat) | 200 € |
| **Total** | **1,400 €** |

### 11.3 Maintenance & Support (Annuel)

| Item | Coût |
|------|------|
| 1 FTE DevOps (50%) | 20,000 € |
| Support utilisateurs | 10,000 € |
| Training & documentation | 5,000 € |
| **Total** | **35,000 €** |

---

## 12. ROADMAP FONCTIONALITÉS FUTURES

### Phase 2 (v2.0, +3 mois)
- 📱 Synchronisation offline améliorer
- 📊 Dashboards analytique avancée
- 🔔 Notifications push (Firebase)
- 🌍 Support multilangue complet

### Phase 3 (v3.0, +6 mois)
- 🔗 Intégration avec SIS (Student Information System)
- 📧 Export rapports automatique
- 🖼️ Reconnaissance faciale (ML)
- ⚙️ Configuration interface customizable

### Phase 4 (v4.0, +12 mois)
- 🤖 AI-powered attendance analytics
- 📈 Prédiction taux abandons
- 🔐 Blockchain audit trail (optional)
- 🌐 Multi-site federation

---

## 13. VERSIONING & HISTORIQUE

| Version | Date | Statut | Notes |
|---------|------|--------|-------|
| v1.0 | 2024-04-26 | ✅ Production | Release initiale |
| v2.0 | 2024-07-26 | 📋 Prévu | Améliorations analytique |
| v3.0 | 2025-01-26 | 📋 Prévu | Intégration SIS |

---

**Document signé électroniquement par: Équipe Développement**  
**Date validité:** 26/04/2026 - 26/04/2027  
**Prochain review:** 26/07/2026


