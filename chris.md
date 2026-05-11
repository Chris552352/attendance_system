# Système de gestion de présence — Documentation (version Chris)

Ce document présente **tout le système** (site web + API mobile + application Flutter) et ses fonctionnalités, pour un usage **universitaire** (niveau LMD, filière, groupes/promo, salles…).

---

## 1) Vue d’ensemble

Le système est composé de :

- **Une plateforme web (PHP/MySQL)** pour l’administration et l’usage enseignant.
- **Des endpoints API (PHP/JSON)** consommés par l’application mobile.
- **Une application mobile Flutter** pour les étudiants (présence, cours, profil, justificatifs…).

Le cœur du système tourne autour de :

- **Étudiants** (profil + compte mobile)
- **Cours** (matières) + **inscriptions** (qui suit quel cours)
- **Séances** (sessions de présence, avec QR, horaires, type, salle…)
- **Présences** (présent/absent/retard/excusé) + justifications
- **Promotions universitaires** (niveau + filière + groupe) et **salles**

---

## 2) Modèle “Université” (obligatoire)

### 2.1 Promotions / groupes (niveau + filière)

On gère les promotions sous la forme :
- **Niveau** (Licence 1, Licence 2, … Master 2)
- **Filière / parcours** (Informatique, Gestion, Droit, etc.)
- **Groupe** (A, B, …)
- Un **code** unique (ex: `L2-INFO-A`)

Fonctionnellement :
- Chaque étudiant doit appartenir à **une promotion** (c’est obligatoire).
- Chaque promotion doit avoir **niveau** et **filière** (obligatoire).

Page web (admin) :
- `Promotions (LMD)` : ajout, désactivation/réactivation, suppression (si aucun étudiant rattaché).

### 2.2 Salles (infrastructures)

On gère aussi les **salles** de l’établissement :
- Nom (ex: Amphi A101)
- Bâtiment/bloc
- Capacité
- Équipements
- Actif / inactif

Page web (admin) :
- `Salles` : ajout, modification, désactivation/réactivation, suppression.

Pourquoi c’est utile :
- Quand l’établissement construit de nouvelles salles, on les ajoute et ça devient disponible pour organiser les séances.

---

## 3) Plateforme web (PHP) — fonctionnalités

### 3.1 Authentification & rôles

Deux profils principaux :
- **Admin**
- **Enseignant**

Accès :
- Admin : voit tout (étudiants, enseignants, cours, promotions, salles, imports…)
- Enseignant : accès à ses cours et aux actions de présence liées.

### 3.2 Gestion des étudiants (admin)

Fonctionnalités :
- Lister les étudiants
- Ajouter un étudiant
- Modifier un étudiant
- Supprimer un étudiant (avec suppression de ses inscriptions et présences associées)
- Affecter une **promotion** (obligatoire)
- Inscrire l’étudiant à un ou plusieurs **cours**

Points pratiques :
- Le **matricule** peut être généré automatiquement si nécessaire.
- L’email est unique (contrôle anti-duplication).

### 3.3 Gestion des cours (admin + enseignant)

Fonctionnalités :
- Créer / gérer les cours (code, nom, description)
- Assigner un enseignant à un cours
- Voir les étudiants inscrits à un cours

### 3.4 Présence & QR Code (usage enseignant)

Le principe :
- L’enseignant crée une **séance** (session de présence).
- Le système génère un **QR Code** pour cette séance.
- Les étudiants scannent le QR pour marquer leur présence.

Fonctions liées :
- Génération / affichage QR
- Validation côté serveur (séance active, token, etc.)
- Statistiques de séance

### 3.5 Feuille d’émargement (mode établissement supérieur)

En plus du scan QR, il y a un module “établissement supérieur” qui gère :
- Types de séances (CM/TD/TP/Examen/Contrôle/Conférence)
- Salles
- États de séance (planifiée, en cours, terminée, annulée)
- Données étendues sur les présences (ex: retard, excusé…)

### 3.6 Justifications d’absence (web)

Le système prévoit :
- Une gestion des **justifications** (dossier, fichiers joints, échanges)
- Suivi des statuts (en attente, accepté, refusé)

### 3.7 Statistiques & rapports (web)

Selon les pages disponibles :
- Tableau de bord stats présence
- Rapports par cours / par période
- Suivi des absences, retards, taux de présence

### 3.8 Imports CSV (admin)

Pour la collecte rapide (quand on a beaucoup d’étudiants), on a :

- **Import promotions (CSV)** : créer des promotions (niveau + filière + groupe).
- **Import étudiants (CSV)** : ajouter les étudiants en masse et les rattacher à une promotion.

Important :
- L’encodage recommandé est **UTF‑8**
- Séparateur accepté : `;` ou `,`
- On met d’abord les **promotions**, ensuite les **étudiants**.

Modèles disponibles :
- `assets/modeles/import_promotions_exemple.csv`
- `assets/modeles/import_etudiants_exemple.csv`

---

## 4) API mobile (PHP/JSON) — fonctionnalités

L’API mobile sert à l’application Flutter. Elle gère notamment :

### 4.1 Connexion étudiant

- Connexion via email (ou matricule) + mot de passe
- Création/activation automatique du compte étudiant si nécessaire (selon règles)
- Délivrance d’un **auth_token** (session mobile)
- Retour de l’identité + **promotion** (niveau/filière/groupe) si disponible

### 4.2 Profil étudiant

- Lecture du profil (nom, prénom, email, matricule, date d’inscription)
- Mise à jour du profil (nom/prénom/email)
- Retour de la promotion rattachée

### 4.3 Cours de l’étudiant

Retourne la liste des cours auxquels l’étudiant est inscrit, avec :
- Code cours, nom, description
- Enseignant (si assigné)

### 4.4 Présences & justificatifs

Selon endpoints présents :
- Liste des présences de l’étudiant
- Envoi d’une justification (texte + fichier)
- Historique de messages / traitement

### 4.5 Marquage de présence

Le marquage se fait via :
- QR (séance + token)
- Contrôles côté serveur
- Enregistrement dans la table `presences`

---

## 5) Application Flutter (étudiants) — fonctionnalités

### 5.1 Connexion & session

Fonctions :
- Saisie email + mot de passe
- Stockage local de la session (auth_token + identité + serveur)
- Possibilité de configurer l’URL serveur (utile sur Wi‑Fi campus)

### 5.2 Internationalisation (FR/EN)

L’app est prête en **français / anglais** :
- Traductions gérées par fichiers ARB
- Choix de la langue (Français / English / Langue système)
- Sauvegarde du choix localement

### 5.3 Accueil

L’accueil affiche :
- Nom/prénom
- Matricule
- Promotion (niveau · filière · groupe) si disponible
- Bouton de scan QR

### 5.4 Scan QR & présence

Workflow :
- Scanner le QR code d’une séance
- Confirmation de présence
- Envoi au serveur

### 5.5 Biométrie / sécurité

Après certaines actions sensibles, l’app peut demander une confirmation :
- empreinte / visage / code téléphone (selon appareil)

### 5.6 Mes cours

Fonctions :
- Liste des cours de l’étudiant
- Affichage enseignant (si disponible)
- Rafraîchissement

### 5.7 Mes présences & justificatifs

Fonctions :
- Voir l’historique des présences/absences
- Justifier une absence
- Suivre statut (en attente/accepté/refusé)

### 5.8 Profil

Fonctions :
- Voir ses infos
- Modifier nom/prénom/email
- Afficher la promotion rattachée

---

## 6) Scripts de mise à jour / maintenance (utile en local)

### 6.1 Migration schéma + promotions

- `maj_base_universite.php` : met à jour la base existante (tables/colonnes “avancées”, promotions, etc.).

### 6.2 “Université uniquement” (nettoyage + affectations)

- `maj_affectations_universite.php` : réaffecte les étudiants sur des promotions LMD, crée des cours universitaires de base, ajoute des inscriptions, et supprime les promos “secondaire”.

À utiliser une fois, puis **supprimer/protéger**.

---

## 7) Bonnes pratiques d’exploitation (terrain)

- **Toujours créer/importer les promotions avant les étudiants**, sinon les étudiants ne pourront pas être rattachés proprement.
- Si une promotion doit “disparaître”, le mieux c’est :
  - **réaffecter** les étudiants à une autre promotion,
  - ensuite supprimer la promotion (quand elle a 0 étudiant).
- Pour les nouvelles constructions : ajouter les **salles** au fur et à mesure, comme ça les séances peuvent être mieux organisées.
- Pour l’import CSV : privilégier **UTF‑8** et un format simple ; si un fichier passe mal, on corrige la ligne, on relance et ça continue.

---

## 8) Ce que l’utilisateur voit (résumé simple)

- Admin : gère tout (promotions, salles, étudiants, enseignants, cours, imports).
- Enseignant : gère ses cours, génère QR, fait l’émargement, suit stats.
- Étudiant : se connecte, voit sa promo, scanne pour présence, suit ses cours et ses présences, envoie les justificatifs.

