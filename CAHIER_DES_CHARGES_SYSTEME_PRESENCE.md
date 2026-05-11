# Cahier des Charges - Système de Gestion de Présence Universitaire

## Référence du Document
- **Nom du projet** : Système de Gestion de Présence Universitaire
- **Version** : 1.0
- **Date** : 25 avril 2026
- **Auteur** : Étudiant en Informatique (L2/L3)

---

## 1. Vue d'ensemble et philosophie du système

### 1.1 Contexte et problématique

En tant qu'étudiant, j'ai remarqué que beaucoup de profs passent trop de temps à faire l'appel manuellement. C'est vraiment pas efficace ! Les feuilles de papier, ça se perd, c'est difficile à compter, et puis on peut facilement tricher en signant pour un copain.

J'ai donc imaginé un système qui utilise les technologies qu'on utilise tous les jours : nos smartphones. Avec les QR codes, plus besoin de faire la queue pour signer, il suffit de scanner. C'est rapide, fiable, et le système enregistre tout automatiquement avec l'heure exacte. Fini les fraudes !

### 1.2 Architecture globale et philosophie technique

Pour mon projet, j'ai choisi une architecture client-serveur classique, c'est ce qu'on apprend en cours et ça marche super bien :

**Le Backend PHP/MySQL** : C'est le serveur qui fait tout le travail. J'ai pris PHP parce que c'est ce qu'on voit le plus en cours, c'est facile à trouver des hébergeurs qui supportent, et y'a plein de documentation. MySQL pour la base de données, c'est le combo classique qu'on apprend en L2. Le moteur InnoDB, c'est important pour les relations entre les tables et pour éviter que les données se corrompent.

**Le Frontend Web** : C'est l'interface pour les admins et les profs. J'ai fait du PHP simple avec un peu de JavaScript, pas besoin de frameworks compliqués. L'important c'est que ce soit clair et facile à utiliser. Les profs doivent pouvoir créer une séance en 2 clics, pas besoin de formation compliquée.

**L'Application Mobile Flutter** : Pour les étudiants, c'est plus pratique d'avoir une app. J'ai choisi Flutter parce qu'on l'a vu en cours et ça permet de faire une seule app pour Android et iOS. C'est super pratique, plus besoin de développer deux fois ! L'app utilise la caméra pour scanner les QR codes et l'authentification par empreinte pour que personne ne puisse pointer à notre place.

### 1.3 Pourquoi ce projet est utile

**Finis les papiers !** : Plus besoin de feuilles d'émargement qui se perdent. Le système enregistre tout automatiquement avec l'heure exacte. Personne peut tricher en signant pour un autre, chaque scan est unique.

**Les profs gagnent du temps** : Au lieu de passer 10 minutes à faire l'appel, le prof lance une séance, affiche le QR code et c'est tout. Il peut se concentrer sur son cours. Le système vérifie tout seul qui est inscrit et enregistre les présences.

**Des stats fiables** : Tout est enregistré dans la base de données, on peut pas modifier les présences après coup. Les admin peuvent voir exactement qui était là, à quelle heure, et générer des rapports pour n'importe quelle période.

**C'est plus simple pour nous** : En tant qu'étudiants, on scanne le QR code en 2 secondes et c'est bon. Plus besoin de faire la queue. L'app permet aussi de voir son historique, de justifier une absence si on était malade, etc.

**Aide pour l'administration** : Avec les stats, les responsables peuvent voir les cours où il y a beaucoup d'absences, identifier les problèmes, et prendre des décisions pour améliorer la réussite des étudiants.

### 1.4 Pour que ça marche même avec beaucoup de monde

J'ai pensé à faire un système qui puisse supporter un grand nombre d'étudiants. Même si y'a 1000 étudiants qui scannent en même temps au début d'un cours, le système doit tenir. Pour ça, j'ai mis des index dans la base de données sur les champs qu'on utilise le plus, et j'ai optimisé les requêtes.

Le code est organisé en modules, donc si plus tard on veut ajouter des notifications push ou connecter le système à d'autres applications, c'est facile. L'API REST que j'ai faite permet à d'autres systèmes de récupérer les données de présence si besoin.

---

## 2. Les détails techniques de mon projet

### 2.1 Pourquoi j'ai choisi ces technologies

Pour mon projet, j'ai voulu utiliser des technologies qu'on voit en cours et qui sont fiables. Chaque choix a été fait pour que le système soit robuste, sécurisé et facile à maintenir.

#### 2.1.1 Le Backend PHP - où tout se passe

J'ai choisi PHP 8.x parce que c'est ce qu'on apprend en cours et c'est bien documenté. En plus, sur les PC du campus on trouve souvent déjà WAMP/LAMP, donc c’est facile à installer pour tester vite.

**Organisation du code** : je n’ai pas fait un framework MVC complet. J’ai plutôt organisé le projet en **pages PHP + fichiers include** (auth, fonctions, connexion DB, etc.). Ça reste simple à comprendre et à maintenir pour un projet étudiant.

**API mobile (JSON)** : pour que l’app Flutter parle avec le serveur, j’ai fait des endpoints PHP qui renvoient du **JSON**. Dans la pratique on utilise surtout **POST** avec un body JSON, parce que c’est plus simple à gérer.

**Session mobile** : au lieu d’un JWT “complet”, le serveur renvoie un **token signé** qui contient l’ID étudiant + une date d’expiration (environ 24h). Quand ça expire, l’étudiant se reconnecte.

#### 2.1.2 Base de données MySQL - Le socle de persistance

Pour la base de données, j'ai pris MySQL 8.x avec le moteur InnoDB. C'est ce qu'on nous a dit d'utiliser en cours de base de données. InnoDB c'est bien parce qu'il gère les transactions et les clés étrangères, donc les données restent cohérentes même si y'a des problèmes.

J'ai mis l'encodage utf8mb4_unicode_ci pour supporter tous les caractères, même les accents et les caractères spéciaux. C'est important pour les noms d'étudiants qui peuvent avoir des accents.

Pour le schéma, j'ai fait des tables normalisées comme on a appris en cours. Chaque table a une clé primaire qui s'incrémente toute seule, j'ai mis des index sur les champs qu'on utilise souvent pour les recherches, et des contraintes d'unicité pour éviter les doublons.

#### 2.1.3 Frontend Web - L'interface d'administration

L’interface web est faite en **PHP + HTML/CSS** (et un peu de JavaScript quand il faut). L’objectif c’est surtout que ce soit **clair et rapide** pour les enseignants/admins : créer une séance, afficher le QR, consulter les listes, etc.

Pour l’authentification web, on utilise les **sessions PHP** (admin / enseignant). Je garde ça simple : connexion, contrôle d’accès par rôle, et déconnexion.

#### 2.1.4 Application Mobile Flutter - L'expérience utilisateur moderne

**Flutter** permet de faire une app propre sur Android (et iOS si besoin) avec une seule base de code. C’est aussi ce qu’on commence à voir en cours, donc ça colle au contexte.

**Organisation de l’app** :
- Écrans (widgets) pour l’interface
- Des **services** pour appeler l’API (login, profil, cours, présences…)
- Du **stockage local** pour garder la session (auth_token, serveur)

**L'internationalisation (i18n)** est gérée nativement par Flutter avec le système ARB (Application Resource Bundle). L'application supporte le français et l'anglais avec la possibilité d'étendre à d'autres langues facilement. Le choix de la langue est persisté localement et l'interface s'adapte dynamiquement sans nécessiter un redémarrage.

**La biométrie** (empreinte/visage/PIN) est utilisée pour éviter qu’un étudiant pointe à la place d’un autre quand il emprunte le téléphone. Sur certains téléphones ça peut ne pas être disponible, donc l’app gère aussi ce cas.

### 2.2 Flux de communication et protocoles

#### 2.2.1 Architecture de communication globale

Le système est en **client-serveur** :

**Flux principal Application Mobile → API REST → Base de données** :
L’app mobile appelle des endpoints PHP qui renvoient du JSON. Après connexion, l’app envoie un **auth_token** (dans le JSON) pour prouver que l’étudiant est bien connecté.

**Flux secondaire Interface Web → Backend PHP → Base de données** :
L'interface web utilise des requêtes HTTP traditionnelles (souvent POST pour les formulaires) avec authentification par session PHP. Le backend traite ces requêtes de manière similaire à l'API mais retourne des pages HTML complètes plutôt que du JSON.

#### 2.2.2 Protocoles et formats de données

**HTTP/HTTPS** : en local (WAMP), on travaille souvent en HTTP. Si on met en production, l’idée c’est de passer en **HTTPS**.

**JSON** : Le format JSON a été choisi pour les échanges API en raison de sa légèreté, sa lisibilité et son support natif par JavaScript et Dart. Les objets JSON suivent une structure cohérente avec des champs prévisibles pour faciliter le parsing et la validation côté client.

**Token de session** : le serveur renvoie un token signé qui expire. Ça évite que quelqu’un réutilise un ancien token indéfiniment.

#### 2.2.3 Sécurité des communications

À notre niveau (projet étudiant), la sécurité se fait surtout par :
- mots de passe hashés côté serveur
- contrôle des rôles (admin / enseignant)
- token de session côté mobile (expire)
- validation côté serveur (séance active, étudiant inscrit au cours, pas de double pointage, etc.)

### 2.3 Infrastructure et déploiement

#### 2.3.1 Environnements de déploiement

**Environnement de développement** : Configuration WAMP/LAMP locale avec PHP 8.x, MySQL 8.x et Apache 2.4. L'environnement inclut Xdebug pour le débogage, Composer pour la gestion des dépendances et Git pour le versioning.

**Environnement de production (idée)** : un hébergement simple qui supporte PHP + MySQL (et HTTPS). Pour un vrai déploiement plus sérieux, on pourra améliorer plus tard, mais l’objectif ici c’est que ça marche bien en local et en démo.

#### 2.3.2 Monitoring et logging

**Logs** : on garde au moins des logs d’erreurs (pour comprendre quand ça bug) et quelques traces utiles (connexion, actions importantes).

#### 2.3.3 Sauvegarde et récupération

**Sauvegardes** : en pratique on peut exporter la base (phpMyAdmin) et garder une copie du dossier du projet. En production, on mettrait des backups réguliers, mais pour le projet la priorité c’est la fonctionnalité.

---

## 3. Modèle de données - Fondation du système académique

### 3.1 Philosophie de conception des données

J’ai fait une base qui suit la logique de l’université : **promotions (niveau + filière + groupe)**, étudiants, cours, inscriptions, séances et présences. L’idée c’est d’éviter les doublons (ex : un étudiant inscrit deux fois au même cours) et de garder des relations propres entre les tables (clés étrangères).

### 3.2 Entités fondamentales du système académique

#### 3.2.1 Utilisateurs administratifs (utilisateurs)

La table `utilisateurs` représente le **personnel administratif et enseignant** qui gère le système. Cette entité est fondamentale car elle définit qui peut accéder au système et avec quels droits.

**Structure détaillée** :
- `id` : Clé primaire auto-incrémentée, identifiant unique interne
- `nom` : Nom de famille de l'utilisateur, stocké en UTF-8 pour supporter tous les caractères
- `prenom` : Prénom de l'utilisateur, essentiel pour l'interface utilisateur personnalisée
- `email` : Adresse email professionnelle, utilisée pour l'authentification et les communications
- `mot_de_passe` : Hash bcrypt du mot de passe (jamais stocké en clair pour des raisons de sécurité)
- `role` : Énumération ('admin', 'enseignant') définissant les permissions de l'utilisateur
- `date_creation` : Timestamp de création du compte, utile pour les audits et les statistiques
- `derniere_connexion` : Timestamp de la dernière connexion, pour le suivi d'activité

**Contraintes et validations** :
- L'email doit être unique pour éviter les doublons et garantir l'identité
- Le rôle est obligatoire et limité aux valeurs prédéfinies
- Le mot de passe suit des règles de complexité minimales

**Logique métier associée** :
- Les administrateurs ont accès à toutes les fonctionnalités du système
- Les enseignants ne peuvent gérer que leurs propres cours et séances
- Le mot de passe est automatiquement hashé avec bcrypt lors de la création/modification

#### 3.2.2 Étudiants (etudiants)

La table `etudiants` est au cœur du système car elle représente les **bénéficiaires principaux** du service de suivi de présence. Chaque étudiant est une entité unique avec des informations académiques et personnelles.

**Structure détaillée** :
- `id` : Identifiant unique interne, clé primaire
- `matricule` : Identifiant académique unique de l'étudiant, souvent formaté par l'établissement
- `nom` : Nom de famille de l'étudiant, essentiel pour les listes et rapports
- `prenom` : Prénom de l'étudiant, utilisé dans les interfaces personnalisées
- `email` : Email académique de l'étudiant, utilisé pour les communications et l'authentification mobile
- `classe_id` : Clé étrangère vers la table classes_etablissement, obligatoire pour le modèle LMD
- `date_inscription` : Date d'inscription dans le système, pour le suivi administratif

**Contraintes et validations** :
- Le matricule doit être unique dans tout le système
- L'email doit être unique pour éviter les conflits d'authentification
- La classe_id est obligatoire, garantissant que chaque étudiant appartient à une promotion
- Le matricule suit un format spécifique à l'établissement (ex: UDS24-L1IA-01)

**Logique métier associée** :
- Un étudiant ne peut appartenir qu'à une seule classe/promotion à la fois
- L'email académique est utilisé pour créer automatiquement le compte mobile
- Le matricule sert d'identifiant alternatif pour l'authentification

#### 3.2.3 Classes et Promotions (classes_etablissement)

Cette table implémente le **modèle LMD (Licence-Master-Doctorat)** qui est la norme dans l'enseignement supérieur. Elle permet d'organiser les étudiants selon une structure académique hiérarchique.

**Structure détaillée** :
- `id` : Identifiant unique de la classe/promotion
- `code` : Code unique et lisible (ex: "L2-INFO-A") pour les interfaces et références
- `nom` : Nom descriptif complet (ex: "Licence 2 Informatique Groupe A")
- `niveau` : Niveau académique (Licence 1, Licence 2, Master 1, etc.)
- `filiere` : Domaine d'études (Informatique, Gestion, Droit, etc.)
- `description` : Texte descriptif optionnel pour plus de détails
- `actif` : Booléen indiquant si la promotion est actuellement active
- `date_creation` : Date de création de la promotion dans le système

**Contraintes et validations** :
- Le code doit être unique pour éviter les confusions
- Le niveau et la filière sont obligatoires pour le modèle LMD
- Le format du code suit une convention établie : NIVEAU-FILIERE-GROUPE

**Logique métier associée** :
- Une promotion inactive ne peut plus recevoir de nouveaux étudiants
- La suppression n'est possible que si aucun étudiant n'y est rattaché
- Le niveau et la filière permettent des filtrages et statistiques avancées

#### 3.2.4 Cours académiques (cours)

La table `cours` représente les **matières enseignées** dans l'établissement. Chaque cours est une entité académique distincte avec des caractéristiques pédagogiques spécifiques.

**Structure détaillée** :
- `id` : Identifiant unique du cours
- `code` : Code académique unique du cours (ex: "CS103", "FR835")
- `nom` : Nom complet et descriptif du cours
- `enseignant_id` : Clé étrangère vers l'enseignant responsable (nullable)
- `description` : Texte détaillé décrivant le contenu et objectifs du cours

**Contraintes et validations** :
- Le code du cours doit être unique pour éviter les conflits
- L'enseignant_id peut être null temporairement (cours sans responsable assigné)
- Le nom du cours est obligatoire et doit être significatif

**Logique métier associée** :
- Un enseignant peut être responsable de plusieurs cours
- Les étudiants s'inscrivent aux cours via la table de liaison inscriptions
- Le code du cours est utilisé dans les interfaces et rapports académiques

#### 3.2.5 Inscriptions aux cours (inscriptions)

Cette table de liaison réalise la **relation many-to-many** entre les étudiants et les cours. Elle est essentielle pour définir qui suit quel cours et ainsi valider les marquages de présence.

**Structure détaillée** :
- `id` : Identifiant unique de l'inscription
- `etudiant_id` : Référence à l'étudiant inscrit
- `cours_id` : Référence au cours suivi
- `date_inscription` : Date à laquelle l'étudiant s'est inscrit au cours

**Contraintes et validations** :
- Contrainte d'unicité sur (etudiant_id, cours_id) pour éviter les doublons
- Clés étrangères vers etudiants et cours avec intégrité référentielle
- Les deux IDs sont obligatoires pour une inscription valide

**Logique métier associée** :
- Un étudiant ne peut être inscrit qu'une seule fois au même cours
- L'inscription est prérequise pour pouvoir marquer sa présence
- La date d'inscription permet de suivre l'évolution des inscriptions

#### 3.2.6 Séances de présence (seances)

La table `seances` est au cœur du **mécanisme de marquage de présence**. Chaque séance représente une session de cours spécifique avec un QR code unique pour la validation des présences.

**Structure détaillée** :
- `id` : Identifiant unique de la séance
- `nom_cours` : Nom du cours pour la séance (reprise pour performance)
- `enseignant_id` : Référence à l'enseignant qui crée/gère la séance
- `token` : Token UUID unique pour le QR code, essentiel pour la sécurité
- `expiration` : Timestamp d'expiration du token pour limiter la durée de validité
- `date_creation` : Date de création de la séance
- `active` : Booléen indiquant si la séance est actuellement active

**Contraintes et validations** :
- Le token doit être unique dans tout le système
- L'expiration est obligatoire pour garantir la sécurité
- L'enseignant_id est obligatoire pour la traçabilité

**Logique métier associée** :
- Chaque séance génère un QR code unique avec son token
- Le token expire automatiquement après la durée configurée (défaut 2 heures)
- Une séance inactive ne peut plus accepter de marquages de présence
- Le token UUID garantit l'impossibilité de deviner les tokens valides

#### 3.2.7 Présences enregistrées (presences)

Cette table enregistre **chaque marquage de présence** effectué par les étudiants. Elle est la source de vérité pour toutes les statistiques et rapports de présence.

**Structure détaillée** :
- `id` : Identifiant unique de l'enregistrement de présence
- `etudiant_id` : Référence à l'étudiant qui marque sa présence
- `cours_id` : Référence au cours concerné
- `seance_id` : Référence à la séance spécifique (nullable pour présences manuelles)
- `date_presence` : Date du jour de la présence
- `statut` : Énumération ('present', 'absent', 'retard', 'excusé')
- `justification` : Texte de justification si applicable
- `justifie` : Booléen indiquant si l'absence a été justifiée
- `enregistre_par` : Référence à l'utilisateur qui a enregistré (null pour auto-pointage)
- `date_enregistrement` : Timestamp précis de l'enregistrement

**Contraintes et validations** :
- Contrainte d'unicité sur (etudiant_id, seance_id) pour éviter les doublons
- Le statut est obligatoire et limité aux valeurs prédéfinies
- La date_presence est obligatoire pour le suivi temporel

**Logique métier associée** :
- Un étudiant ne peut marquer sa présence qu'une seule fois par séance
- Le statut peut être modifié par un enseignant (absent → retard par exemple)
- Les justifications peuvent être ajoutées après coup par les étudiants
- L'enregistre_par permet de différencier l'auto-pointage du marquage manuel

#### 3.2.8 Comptes mobiles étudiants (comptes_etudiants)

Cette table gère **l'authentification mobile** des étudiants de manière séparée des données académiques. Cette séparation améliore la sécurité et la flexibilité du système.

**Structure détaillée** :
- `id` : Identifiant unique du compte mobile
- `etudiant_id` : Référence unique à l'étudiant propriétaire du compte
- `email` : Email utilisé pour l'authentification mobile
- `mot_de_passe` : Hash bcrypt du mot de passe mobile
- `date_creation` : Date de création du compte mobile
- `derniere_connexion` : Timestamp de dernière connexion mobile
- `actif` : Booléen indiquant si le compte est actif

**Contraintes et validations** :
- L'etudiant_id est unique (un étudiant = un compte mobile)
- L'email doit être unique pour éviter les conflits d'authentification
- Le mot de passe suit les mêmes règles de sécurité que les autres comptes

**Logique métier associée** :
- Le compte est créé automatiquement lors de la première connexion mobile
- Le mot de passe par défaut peut être utilisé pour l'activation initiale
- Un compte désactivé ne peut plus se connecter via l'application mobile
- La séparation avec la table etudiants permet une gestion fine des accès mobiles

### 3.3 Relations et contraintes d'intégrité

#### 3.3.1 Diagramme des relations principales

```
classes_etablissement
    ↓ (1,N)
etudiants ←→ (1,1) comptes_etudiants
    ↓ (1,N)
inscriptions
    ↓ (N,1)
cours ← (1,N) utilisateurs (enseignants)
    ↓ (1,N)
presences ← (1,N) seances ← (1,N) utilisateurs (enseignants)
```

**Explication des relations** :

- **classes_etablissement → etudiants** : Une promotion peut contenir plusieurs étudiants, mais chaque étudiant appartient à une seule promotion.
- **etudiants → comptes_etudiants** : Chaque étudiant a exactement un compte mobile, et chaque compte mobile appartient à un seul étudiant.
- **etudiants ←→ inscriptions ←→ cours** : Relation many-to-many gérée par la table inscriptions. Un étudiant peut suivre plusieurs cours, et un cours peut avoir plusieurs étudiants.
- **utilisateurs → cours** : Un enseignant peut être responsable de plusieurs cours, mais chaque cours a un seul enseignant responsable.
- **utilisateurs → seances** : Un enseignant peut créer plusieurs séances, chaque séance étant créée par un seul enseignant.
- **seances → presences** : Une séance peut avoir plusieurs enregistrements de présence, mais chaque présence est liée à une seule séance.
- **etudiants → presences** : Un étudiant peut avoir plusieurs enregistrements de présence, chaque enregistrement appartenant à un seul étudiant.

#### 3.3.2 Contraintes d'intégrité référentielle

**Contraintes de clé étrangère** :
- `etudiants.classe_id` référence `classes_etablissement.id` (RESTRICT ON DELETE)
- `cours.enseignant_id` référence `utilisateurs.id` (SET NULL ON DELETE)
- `inscriptions.etudiant_id` référence `etudiants.id` (CASCADE ON DELETE)
- `inscriptions.cours_id` référence `cours.id` (CASCADE ON DELETE)
- `seances.enseignant_id` référence `utilisateurs.id` (CASCADE ON DELETE)
- `presences.etudiant_id` référence `etudiants.id` (CASCADE ON DELETE)
- `presences.cours_id` référence `cours.id` (CASCADE ON DELETE)
- `presences.seance_id` référence `seances.id` (SET NULL ON DELETE)
- `comptes_etudiants.etudiant_id` référence `etudiants.id` (CASCADE ON DELETE)

**Stratégies de suppression** :
- **CASCADE** : Supprime automatiquement les enregistrements dépendants (inscriptions, présences, comptes)
- **RESTRICT** : Empêche la suppression si des enregistrements dépendants existent (classes)
- **SET NULL** : Met le champ à NULL si l'enregistrement parent est supprimé (enseignant_id)

Cette approche garantit la cohérence des données tout en permettant une gestion flexible des suppressions selon le contexte métier.

---

## 4. Fonctionnalités détaillées - Le cœur opérationnel du système

### 4.1 Module Administration Web - Le centre de contrôle académique

#### 4.1.1 Gestion des utilisateurs administratifs et enseignants

La gestion des utilisateurs constitue la **fondation de la sécurité et du contrôle d'accès** du système. Cette fonctionnalité permet aux administrateurs de définir précisément qui peut accéder au système et avec quels niveaux de permission.

**Interface de gestion complète** :
Le module offre une interface intuitive pour créer, modifier, supprimer et consulter les comptes utilisateurs. Chaque utilisateur est présenté avec ses informations essentielles : nom, prénom, email, rôle, date de création et dernière connexion. Les administrateurs peuvent rapidement identifier les comptes inactifs ou les utilisateurs qui n'ont pas encore configuré leur accès.

**Processus de création d'utilisateur** :
Lors de la création d'un nouvel utilisateur, l'administrateur remplit un formulaire structuré avec validation en temps réel. L'email doit être unique et valide, le mot de passe doit respecter des critères de complexité minimale (8 caractères, mélange de majuscules, minuscules, chiffres et symboles), et le rôle doit être clairement défini. Le système génère automatiquement un hash bcrypt du mot de passe pour garantir la sécurité.

**Système de rôles et permissions** :
Deux rôles principaux structurent les permissions :
- **Administrateur** : Accès total à toutes les fonctionnalités du système, y compris la gestion des utilisateurs, des promotions, des étudiants, des cours, et l'accès aux statistiques globales.
- **Enseignant** : Accès limité à ses propres cours, ses séances, et les étudiants inscrits à ses cours. Peut créer des séances, générer des QR codes, et consulter les statistiques de présence de ses cours uniquement.

**Sécurité et audit des accès** :
Chaque connexion est enregistrée avec l'adresse IP, le navigateur utilisé et l'horodatage précis. Les administrateurs peuvent consulter l'historique des connexions pour détecter des activités suspectes. En cas de compromission d'un compte, l'administrateur peut immédiatement désactiver le compte ou réinitialiser le mot de passe.

#### 4.1.2 Gestion des promotions académiques (modèle LMD)

Ce module implémente la **structure organisationnelle fondamentale** des établissements d'enseignement supérieur selon le modèle LMD (Licence-Master-Doctorat). Il permet d'organiser les étudiants de manière cohérente et hiérarchique.

**Création et configuration des promotions** :
L'interface de création guide l'administrateur à travers les étapes de définition d'une promotion. Le niveau académique (Licence 1, Licence 2, Master 1, etc.) et la filière (Informatique, Gestion, Droit, etc.) sont des champs obligatoires qui garantissent la cohérence du modèle académique. Le groupe (A, B, C...) permet de diviser les promotions volumineuses en sous-groupes plus gérables.

**Génération automatique des codes** :
Le système génère automatiquement un code unique et standardisé pour chaque promotion selon le format "NIVEAU-FILIERE-GROUPE" (ex: "L2-INFO-A"). Ce code est utilisé dans toutes les interfaces et rapports pour identifier rapidement une promotion. L'administrateur peut personnaliser ce code si nécessaire, mais le système valide son unicité.

**Gestion du cycle de vie des promotions** :
Les promotions peuvent être marquées comme actives ou inactives. Une promotion inactive continue d'exister dans le système pour l'historique des données, mais ne peut plus recevoir de nouveaux étudiants. La suppression d'une promotion n'est autorisée que si aucun étudiant n'y est actuellement rattaché, prévenant ainsi la perte de données académiques.

**Impact sur les autres modules** :
Chaque modification de promotion affecte immédiatement les modules connexes. Les listes déroulantes des formulaires étudiants sont mises à jour automatiquement, les statistiques sont recalculées, et les rapports reflètent les nouvelles structures. Cette intégration garantit la cohérence globale du système.

#### 4.1.3 Gestion des infrastructures physiques (salles)

Ce module permet de **cartographier et gérer les ressources physiques** de l'établissement. Une bonne gestion des salles est essentielle pour la planification efficace des séances et la prévention des conflits d'horaire.

**Catalogue complet des infrastructures** :
Chaque salle est enregistrée avec des informations détaillées : nom descriptif (ex: "Amphi A101", "Labo B205"), bâtiment ou bloc d'appartenance, capacité maximale d'accueil, liste des équipements disponibles (projecteur, tableau blanc, ordinateurs, connexion internet), et état actif/inactif.

**Planification intelligente des séances** :
Lors de la création d'une séance, le système vérifie automatiquement la disponibilité des salles et prévient les conflits. Si une salle est déjà utilisée pour une autre séance au même moment, le système alerte l'enseignant et suggère des alternatives disponibles. Cette fonctionnalité élimine les double-réservations et optimise l'utilisation des infrastructures.

**Gestion du cycle de vie des salles** :
Les salles peuvent être temporairement désactivées (travaux, maintenance) ou définitivement supprimées (démolition, réaffectation). Une salle désactivée n'apparaît plus dans les options de planification mais conserve son historique pour les rapports passés. La suppression n'est possible que si aucune séance future n'est planifiée dans cette salle.

**Intégration avec les statistiques** :
Le système peut générer des rapports sur l'utilisation des salles, identifiant les espaces les plus sollicités et ceux qui sont sous-utilisés. Ces informations aident les administrateurs à optimiser l'allocation des ressources physiques et à planifier les développements futurs.

#### 4.1.4 Gestion des étudiants - Le cœur académique du système

Ce module central gère l'ensemble de la **population étudiante** de l'établissement. Il combine la flexibilité de la création manuelle avec l'efficacité de l'importation en masse pour s'adapter à tous les scénarios opérationnels.

**Création individuelle d'étudiants** :
Pour des ajouts ponctuels, l'interface de création manuelle offre un formulaire complet avec validation en temps réel. L'administrateur saisit les informations essentielles : matricule (généré automatiquement si nécessaire), nom, prénom, email académique, et sélection de la promotion d'appartenance. Le système vérifie l'unicité du matricule et de l'email pour éviter les doublons.

**Importation massive par fichiers CSV** :
Pour les inscriptions de groupe (début d'année, transferts), le système supporte l'importation via fichiers CSV. L'administrateur télécharge un modèle préformaté, le remplit avec les informations des étudiants, et le système traite le fichier en lot. Le processus inclut une phase de validation qui identifie les erreurs potentielles avant l'insertion, et un rapport détaillé des succès et échecs.

**Gestion des inscriptions aux cours** :
Après la création de l'étudiant, l'administrateur peut immédiatement l'inscrire aux cours appropriés. L'interface présente la liste des cours disponibles avec filtres par niveau et filière. L'administrateur peut sélectionner plusieurs cours simultanément, et le système vérifie automatiquement que l'étudiant est bien éligible (prérequis, capacité, etc.).

**Validation et cohérence des données** :
Le système implémente des validations strictes pour garantir la qualité des données :
- Le matricule suit le format de l'établissement et est unique
- L'email académique est valide et unique
- Chaque étudiant doit appartenir à une promotion active
- Les inscriptions respectent les règles académiques (prérequis, etc.)

#### 4.1.5 Gestion des cours académiques

Ce module structure l'**offre pédagogique** de l'établissement. Chaque cours représente une unité d'enseignement avec ses caractéristiques pédagogiques et administratives.

**Création et configuration des cours** :
L'interface de création permet de définir tous les aspects d'un cours : code académique unique (ex: "CS103"), nom complet et descriptif, description détaillée des objectifs et contenu, et assignation d'un enseignant responsable. Le code du cours est utilisé dans toutes les interfaces et documents académiques, il doit donc être significatif et suivre les conventions de l'établissement.

**Assignation des enseignants** :
Chaque cours doit avoir un enseignant responsable qui gère les séances et les présences. L'administrateur peut sélectionner l'enseignant parmi la liste des utilisateurs avec le rôle "enseignant". Le système vérifie automatiquement la disponibilité de l'enseignant et prévient les surcharges.

**Gestion des inscriptions étudiantes** :
Le module offre une vue complète sur les étudiants inscrits à chaque cours. L'administrateur peut ajouter ou retirer des étudiants, consulter les statistiques d'inscription, et exporter les listes. Le système maintient un historique des changements d'inscription pour l'audit et le suivi.

**Évolution et cycle de vie des cours** :
Les cours peuvent être activés ou désactivés selon les périodes académiques. Un cours désactivé conserve son historique et les données de présence passées, mais n'apparaît plus dans les options d'inscription nouvelles. Cette approche permet de maintenir la continuité académique tout en adaptant l'offre pédagogique.

### 4.2 Module Enseignant (Web)

#### 4.2.1 Gestion des séances
- **Création** : Séance avec QR code
- **Types** : CM/TD/TP/Examen/Contrôle/Conférence
- **Salles** : Sélection avec vérification des conflits
- **Modes** : QR, Manuel, Enseignant seul

#### 4.2.2 Génération QR
- **Token unique** : UUID avec expiration
- **Affichage** : Écran projectable
- **Validation** : Contrôle serveur

#### 4.2.3 Feuille d'émargement
- **Mode avancé** : Établissement supérieur
- **États séance** : Planifiée, en cours, terminée, annulée
- **Marquage manuel** : Pour examens/contrôles

#### 4.2.4 Statistiques
- **Taux de présence** : Par cours/période
- **Historique** : Suivi détaillé
- **Export** : Rapports CSV

### 4.3 API Mobile (REST)

#### 4.3.1 Authentification
- **Endpoint** : `/api/login_mobile.php`
- **Méthode** : POST
- **Paramètres** : email/matricule + mot_de_passe
- **Retour** : auth_token + informations étudiant

#### 4.3.2 Profil étudiant
- **Endpoint** : `/api/student_profile.php`
- **Lecture** : GET avec auth_token
- **Mise à jour** : POST avec auth_token

#### 4.3.3 Cours et présences
- **Cours** : `/api/student_courses.php`
- **Présences** : `/api/student_presences.php`
- **Marquage** : `/api/mark_presence_mobile.php`

#### 4.3.4 Justifications
- **Envoi** : `/api/student_justify_absence.php`
- **Fichiers** : Upload supporté
- **Messages** : Communication admin/étudiant

### 4.4 Application Mobile (Flutter)

#### 4.4.1 Authentification
- **Connexion** : Email + mot de passe
- **Session** : Stockage local sécurisé
- **Configuration** : URL serveur adaptable

#### 4.4.2 Interface principale
- **Accueil** : Informations étudiant + bouton scan
- **Scan QR** : Caméra avec validation
- **Cours** : Liste des cours inscrits
- **Présences** : Historique détaillé
- **Profil** : Gestion informations

#### 4.4.3 Sécurité locale
- **Biométrie** : Empreinte/visage/ PIN
- **Confirmation** : Actions sensibles
- **Stockage** : Chiffrement local

#### 4.4.4 Internationalisation
- **Langues** : Français/Anglais
- **Configuration** : Sauvegarde locale
- **Détection** : Langue système par défaut

---

## 5. Flux de données et processus

### 5.1 Processus de marquage de présence

#### 5.1.1 Création séance (Enseignant)
```
1. Enseignant sélectionne cours + type + salle
2. Système vérifie les conflits (salle/horaire)
3. Génération token unique avec expiration
4. Affichage QR code projectable
5. Séance marquée "en cours"
```

#### 5.1.2 Scan QR (Étudiant mobile)
```
1. Étudiant scanne QR code
2. App parse seance_id + token
3. App vérifie validité séance (API)
4. Confirmation biométrique locale
5. Envoi marquage au serveur
6. Confirmation affichée
```

#### 5.1.3 Validation serveur
```
1. Vérifier token et expiration
2. Vérifier inscription étudiant au cours
3. Vérifier absence de doublon
4. Enregistrer présence avec timestamp
5. Retourner succès/échec
```

### 5.2 Processus de justification

#### 5.2.1 Demande étudiant
```
1. Étudiant sélectionne absence à justifier
2. Rédaction message + pièce jointe
3. Envoi via API mobile
4. Stockage en base avec statut "en attente"
```

#### 5.2.2 Traitement administration
```
1. Admin reçoit notification
2. Examen des pièces justificatives
3. Décision : accepter/refuser
4. Message de retour à l'étudiant
5. Mise à jour statut présence
```

---

## 6. Sécurité

### 6.1 Authentification
- **Web** : Sessions PHP (admin/enseignant)
- **Mobile** : Token de session signé (expire ~24h)
- **Mots de passe** : stockés en hash (bcrypt)
- **Biométrie** : confirmation locale dans l’app (si disponible)

### 6.2 Contrôle d'accès
- **Rôles** : Admin > Enseignant > Étudiant
- **Vérifications** : Permissions par action
- **Tokens** : Validation systématique

### 6.3 Validation des données
- **Input** : Filtrage et échappement
- **SQL** : Requêtes préparées
- **XSS** : Échappement automatique
- **CSRF** : Tokens de session

### 6.4 QR Codes
- **Tokens** : UUID v4 uniques
- **Expiration** : Configurable (défaut 2h)
- **Validation** : Contrôle serveur systématique

---

## 7. Performance et scalabilité

### 7.1 Base de données
- **Indexation** : clés primaires/étrangères + index sur les champs souvent utilisés
- **Requêtes préparées** : pour éviter l’injection et améliorer la stabilité
- **Objectif terrain** : que la base tienne quand un amphi scanne presque en même temps

### 7.2 Application
- **Côté web** : pages simples, formulaires clairs, pas de traitement lourd inutile.
- **Côté API** : endpoints courts (login, cours, présences, marquage), réponses JSON légères.

### 7.3 Mobile
- **Stockage local** : sauvegarde de la session + préférences (langue, serveur).
- **Réseau** : si le serveur n’est pas joignable, l’app affiche un message et propose de réessayer.

---

## 8. Déploiement et infrastructure

### 8.1 Environnement de développement
- **Serveur web** : Apache 2.4+
- **PHP** : 8.x avec extensions requises
- **MySQL** : 8.x
- **Flutter** : 3.x avec Dart 3.x

### 8.2 Production
- **Web** : un hébergement PHP/MySQL classique (ou serveur campus)
- **HTTPS** : recommandé si on publie sur Internet
- **Backup** : export régulier de la base + copie du projet (au minimum)

### 8.3 Mobile
- **Build** : APK/IPA signés
- **Store** : Google Play/App Store
- **Mises à jour** : Déploiement progressif

---

## 9. Tests et qualité

### 9.1 Tests unitaires
- **Backend/API** : tests manuels avec des cas simples (bon token / mauvais token, séance expirée, étudiant non inscrit, etc.)
- **Mobile** : tests manuels sur téléphone/émulateur (connexion, scan, profil, cours, présences)

### 9.2 Tests d'intégration
- **Flux complets** : Marquage présence
- **Authentification** : Tous les rôles
- **Données** : Import/export CSV

### 9.3 Tests de sécurité
- **Injection** : requêtes préparées + échappement HTML
- **Accès** : contrôle des rôles (admin/enseignant) + token mobile expirant

---

## 10. Maintenance et évolutivité

### 10.1 Monitoring
- **Logs** : Centralisés et rotatifs
- **Métriques** : Performance et erreurs
- **Alertes** : Notifications automatiques

### 10.2 Mises à jour
- **Schema** : Scripts de migration
- **Code** : Déploiement sans interruption
- **Mobile** : Mises à jour incrémentales

### 10.3 Évolutions prévues
- **Multi-établissement** : Architecture scalable
- **Notifications push** : Real-time alerts
- **Analytics avancés** : Tableaux de bord
- **Intégrations** : SIS externe

---

## 11. Livrables

### 11.1 Code source
- **Backend PHP** : Complet avec documentation
- **Frontend Web** : Templates et assets
- **Application Flutter** : Code source multi-plateforme
- **Base de données** : Schema + scripts migration

### 11.2 Documentation
- **Manuel utilisateur** : Tous les rôles
- **Guide installation** : Déploiement complet
- **API documentation** : Endpoints détaillés
- **Code comments** : Inline documentation

### 11.3 Déploiement
- **Scripts** : Installation automatisée
- **Configuration** : Environnements dev/prod
- **Monitoring** : Outils intégrés

---

## 12. Contraintes et exigences

### 12.1 Exigences fonctionnelles
- **Objectif disponibilité** : que ça tourne bien pendant les cours (et que le prof n’attende pas).
- **Objectif performance** : quand les étudiants scannent au début du cours, il ne faut pas que ça bloque (réponse rapide, même si le Wi‑Fi est moyen).
- **Capacité** : prévu pour une promo/un établissement avec beaucoup d’étudiants (ex : amphi plein), mais on reste réaliste : ça dépend aussi de la machine serveur et du réseau.

### 12.2 Exigences non-fonctionnelles
- **Sécurité (niveau projet)** : mots de passe hashés, contrôles d’accès, token mobile qui expire, pas de double pointage.
- **Utilisabilité** : interface simple, pas besoin d’être “informaticien” pour utiliser.
- **Langues** : Français/Anglais
- **Mobile** : Android (principalement), iOS possible si besoin.

### 12.3 Contraintes techniques
- **Hébergement** : PHP/MySQL compatible
- **Mobile** : Flutter SDK requis
- **Navigateurs** : Chrome/Firefox (au moins) pour l’admin/prof.

---

## 13. Risques et mitigations

### 13.1 Risques techniques
- **Réseau campus instable** : parfois le Wi‑Fi coupe → prévoir des messages clairs dans l’app + possibilité de réessayer.
- **Téléphones variés** : certains n’ont pas biométrie / caméra floue → l’app doit rester utilisable et expliquer quoi faire.
- **Charge au début du cours** : tout le monde scanne en même temps → index DB + endpoints simples + éviter les requêtes lourdes.

### 13.2 Risques opérationnels
- **Adoption** : si l’interface est compliquée, les profs vont abandonner → garder des boutons simples (créer séance → QR).
- **Fraude** : un étudiant veut pointer pour l’autre → biométrie + vérifs serveur (séance active, étudiant inscrit, 1 scan).
- **Données mal saisies** : d’où l’intérêt du CSV et des contrôles (email unique, promotion obligatoire, etc.).

---

## 14. Limites et perspectives (en mode projet étudiant)

Ce que je reconnais comme limites (normal pour un projet étudiant) :
- En local (WAMP), on est souvent en HTTP. En production, il faudra mettre HTTPS proprement.
- Les performances réelles dépendent beaucoup du serveur et du réseau (surtout sur campus).
- Certaines améliorations peuvent venir plus tard : notifications, export PDF plus complet, filtre par semestre, etc.

---

## 14. Conclusion

Au final, ce système règle un problème qu’on vit vraiment : l’appel qui prend du temps et les feuilles qui se perdent. Avec le QR + l’app, le pointage devient rapide, et tout est enregistré automatiquement.

Le système sépare bien les rôles (admin / enseignant / étudiant) et garde un minimum de sécurité (token qui expire, biométrie, validations côté serveur). Ça reste simple à déployer et à expliquer pour une soutenance.

---

**Fin du document**
