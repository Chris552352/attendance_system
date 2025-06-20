# Système de Sécurité - Gestion de Présence DUT

## Conformité aux 6 Critères de Sécurité

### ✅ 1. Connexion avec mot de passe personnel
**Implémentation:**
- Table `comptes_etudiants` : stockage sécurisé des identifiants
- Mots de passe hashés avec bcrypt (PHP `password_hash()`)
- Authentification par email + mot de passe unique
- Page d'inscription : `inscription_etudiant.php`
- Page de connexion : `login_etudiant.php`

**Vérification:**
```sql
SELECT email, LENGTH(mot_de_passe) as hash_length, actif 
FROM comptes_etudiants;
```

### ✅ 2. Session utilisateur PHP
**Implémentation:**
- Session automatique : `$_SESSION['etudiant_id']` 
- Gestion dans `includes/auth.php`
- Persistance durant toute la navigation
- Validation avant chaque action

**Code type:**
```php
session_start();
$_SESSION['etudiant_id'] = $etudiant_id;
```

### ✅ 3. QR Code à durée limitée
**Implémentation:**
- Table `seances` avec champs `token` et `expiration`
- Génération automatique de tokens uniques
- Vérification d'expiration avant validation
- Durée configurable (par défaut: 30 minutes)

**Format URL:**
```
http://IP_LOCALE/attendance_system/presence.php?seance=5&token=abc123xyz
```

### ✅ 4. Affichage d'identité avant validation
**Implémentation:**
- Page `presence.php` lit `$_SESSION['etudiant_id']`
- Affichage du nom complet et email
- Bouton de confirmation explicite
- Protection contre l'usurpation d'identité

**Exemple d'affichage:**
```
Vous êtes connecté en tant que : Marie Dupont (marie.dupont@iut.cm)
[Bouton: Confirmer ma présence]
```

### ✅ 5. Validation unique par séance
**Implémentation:**
- Contrainte UNIQUE sur (`etudiant_id`, `seance_id`) 
- Vérification avant insertion en base
- Message "Présence déjà enregistrée" si doublon
- Traçabilité complète des tentatives

### ✅ 6. Structure de base de données
**Tables conformes:**

```sql
-- Table etudiants (profils)
CREATE TABLE etudiants (
    id SERIAL PRIMARY KEY,
    matricule VARCHAR(20) UNIQUE,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(150) UNIQUE
);

-- Table comptes_etudiants (authentification)
CREATE TABLE comptes_etudiants (
    id SERIAL PRIMARY KEY,
    etudiant_id INTEGER REFERENCES etudiants(id),
    email VARCHAR(150) UNIQUE,
    mot_de_passe VARCHAR(255), -- Hash bcrypt
    actif BOOLEAN DEFAULT TRUE
);

-- Table seances (QR codes temporisés)
CREATE TABLE seances (
    id SERIAL PRIMARY KEY,
    nom_cours VARCHAR(150),
    enseignant_id INTEGER REFERENCES utilisateurs(id),
    token VARCHAR(255) UNIQUE,
    expiration TIMESTAMP,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table presences (validations)
CREATE TABLE presences (
    id SERIAL PRIMARY KEY,
    etudiant_id INTEGER REFERENCES etudiants(id),
    seance_id INTEGER REFERENCES seances(id),
    cours_id INTEGER REFERENCES cours(id),
    date_presence DATE,
    statut VARCHAR(10) DEFAULT 'present',
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(etudiant_id, seance_id) -- Prévention doublons
);
```

## Comptes de Test Configurés

### Enseignants
- **Administrateur:** `chris552352@gmail.com` / `552352`
- **Enseignant 1:** `marie.dupont@example.com` / `password`
- **Enseignant 2:** `jean.martin@example.com` / `password`

### Étudiants (exemples pré-configurés)
- `angesimo@gmail.com` / `password`
- `Ateba@gmail.com` / `password`
- `Cindy90@gmail.com` / `password`

**Note:** Les étudiants peuvent créer leurs propres comptes via l'interface d'inscription.

## Flux de Sécurité Complet

1. **Inscription étudiant:** Validation matricule + email → Création compte
2. **Connexion:** Email + mot de passe → Session active
3. **Génération QR:** Enseignant crée token temporisé
4. **Scan QR:** Redirection vers presence.php avec token
5. **Vérification:** Token valide + session active
6. **Confirmation:** Affichage identité + validation unique
7. **Enregistrement:** Présence tracée en base

## Tests de Sécurité

### Test d'authentification
```bash
# Page de test disponible
curl http://localhost:5000/test_securite.php
```

### Test de QR codes
1. Connexion enseignant → Génération QR
2. Tentative de scan après expiration → Erreur
3. Double scan avec même compte → Prévention

### Test d'usurpation
1. Connexion compte A
2. Tentative d'utiliser URL de scan → Identité compte A affichée
3. Impossibilité de valider pour un autre étudiant

## Sécurité Réseau

- **IP locale configurée:** Variable dans `config_reseau.php`
- **Port sécurisé:** 5000 (configurable)
- **HTTPS recommandé** pour production
- **Pare-feu:** Accès réseau local uniquement

## Conformité Soutenance DUT

Le système respecte intégralement les 6 critères de sécurité exigés:
- ✅ Authentification individuelle sécurisée
- ✅ Sessions PHP traçables
- ✅ QR codes temporisés et uniques
- ✅ Validation d'identité obligatoire
- ✅ Prévention des validations multiples
- ✅ Architecture de base de données robuste

**Prêt pour démonstration et évaluation technique.**