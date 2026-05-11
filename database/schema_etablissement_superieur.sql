-- Schema étendu pour établissement supérieur - Système de Présence Avancé
-- Mode local avec gestion multi-salles et types de séances

-- Tables nouvelles pour établissement supérieur

-- Types de séances (Cours, TD, TP, Examen, Conférence)
CREATE TABLE `types_seances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `duree_minutes` int(11) DEFAULT 60,
  `presence_obligatoire` tinyint(1) DEFAULT 1,
  `mode_marquage` enum('qr','manuel','enseignant_seul') COLLATE utf8mb4_unicode_ci DEFAULT 'qr',
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salles disponibles
CREATE TABLE `salles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batiment` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `capacite` int(11) DEFAULT 30,
  `equipements` text COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gestion des retards détaillés
CREATE TABLE `retards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `etudiant_id` int(11) NOT NULL,
  `seance_id` int(11) NOT NULL,
  `minutes_retard` int(11) NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci,
  `justifie` tinyint(1) DEFAULT 0,
  `date_enregistrement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `etudiant_id` (`etudiant_id`),
  KEY `seance_id` (`seance_id`),
  CONSTRAINT `retards_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `retards_ibfk_2` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seuils d'alerte par classe
CREATE TABLE `seuils_alerte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cours_id` int(11) NOT NULL,
  `type_alerte` enum('absence','retard') COLLATE utf8mb4_unicode_ci NOT NULL,
  `seuil` int(11) NOT NULL,
  `email_destinataire` varchar(150) COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `cours_id` (`cours_id`),
  CONSTRAINT `seuils_alerte_ibfk_1` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historique présence mensuel
CREATE TABLE `presence_mensuelle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `etudiant_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  `total_present` int(11) DEFAULT 0,
  `total_absent` int(11) DEFAULT 0,
  `total_retard` int(11) DEFAULT 0,
  `taux_presence` decimal(5,2) DEFAULT 0.00,
  `date_calcul` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `etudiant_cours_mois_annee` (`etudiant_id`,`cours_id`,`mois`,`annee`),
  KEY `cours_id` (`cours_id`),
  CONSTRAINT `presence_mensuelle_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presence_mensuelle_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modifications des tables existantes

-- Modifier table seances pour ajouter gestion multi-salles et types
ALTER TABLE `seances` 
  ADD COLUMN `salle_id` int(11) DEFAULT NULL AFTER `enseignant_id`,
  ADD COLUMN `type_seance_id` int(11) DEFAULT NULL AFTER `salle_id`,
  ADD COLUMN `heure_debut` time DEFAULT NULL AFTER `type_seance_id`,
  ADD COLUMN `heure_fin` time DEFAULT NULL AFTER `heure_debut`,
  ADD COLUMN `mode_marquage` enum('qr','manuel','enseignant_seul') COLLATE utf8mb4_unicode_ci DEFAULT 'qr' AFTER `heure_fin`,
  ADD COLUMN `statut_seance` enum('planifiee','en_cours','terminee','annulee') COLLATE utf8mb4_unicode_ci DEFAULT 'planifiee' AFTER `mode_marquage`,
  ADD KEY `salle_id` (`salle_id`),
  ADD KEY `type_seance_id` (`type_seance_id`),
  ADD CONSTRAINT `seances_ibfk_2` FOREIGN KEY (`salle_id`) REFERENCES `salles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seances_ibfk_3` FOREIGN KEY (`type_seance_id`) REFERENCES `types_seances` (`id`) ON DELETE SET NULL;

-- Modifier table presences pour ajouter détails avancés
ALTER TABLE `presences` 
  ADD COLUMN `heure_arrivee` time DEFAULT NULL AFTER `statut`,
  ADD COLUMN `heure_depart` time DEFAULT NULL AFTER `heure_arrivee`,
  ADD COLUMN `statut` enum('present','retard','absent','excuse') COLLATE utf8mb4_unicode_ci DEFAULT 'present' AFTER `date_presence`,
  ADD COLUMN `geolocalisation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `heure_depart`,
  ADD COLUMN `photo_profil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `geolocalisation`,
  ADD COLUMN `marque_par_enseignant` tinyint(1) DEFAULT 0 AFTER `photo_profil`;

-- Modifier table etudiants pour ajouter seuils individuels
ALTER TABLE `etudiants` 
  ADD COLUMN `seuil_absence` int(11) DEFAULT 3 AFTER `email`,
  ADD COLUMN `seuil_retard` int(11) DEFAULT 5 AFTER `seuil_absence`,
  ADD COLUMN `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `seuil_retard`,
  ADD COLUMN `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `telephone`;

-- Insertion des données initiales pour types de séances
INSERT INTO `types_seances` (`nom`, `code`, `couleur`, `duree_minutes`, `presence_obligatoire`, `mode_marquage`, `description`) VALUES
('Cours Magistral', 'CM', '#28a745', 90, 1, 'qr', 'Cours théorique obligatoire'),
('Travaux Dirigés', 'TD', '#007bff', 120, 1, 'qr', 'Exercices dirigés en groupe'),
('Travaux Pratiques', 'TP', '#fd7e14', 180, 1, 'qr', 'Manipulations pratiques'),
('Examen', 'EX', '#dc3545', 180, 1, 'enseignant_seul', 'Contrôle écrit - enseignant uniquement'),
('Conférence', 'CF', '#6f42c1', 60, 0, 'manuel', 'Conférence optionnelle');

-- Insertion des salles exemples
INSERT INTO `salles` (`nom`, `batiment`, `capacite`, `equipements`, `actif`) VALUES
('Amphi A101', 'Batiment A', 150, 'Projecteur, Tableau blanc, Micro', 1),
('Salle B205', 'Batiment B', 30, 'Tableau blanc, Vidéoprojecteur', 1),
('Lab TP C301', 'Batiment C', 25, 'Ordinateurs, Logiciels spécialisés', 1),
('Salle D150', 'Batiment D', 50, 'Tableau interactif, Projecteur', 1),
('Amphi E200', 'Batiment E', 200, 'Projecteur, Sonorisation, Micro', 1);

-- Insertion des seuils d'alerte par défaut
INSERT INTO `seuils_alerte` (`cours_id`, `type_alerte`, `seuil`, `email_destinataire`, `actif`) VALUES
(3, 'absence', 3, 'marie.dupont@example.com', 1),
(4, 'absence', 3, 'jean.martin@example.com', 1),
(3, 'retard', 5, 'marie.dupont@example.com', 1),
(4, 'retard', 5, 'jean.martin@example.com', 1);

-- Mise à jour des AUTO_INCREMENT
ALTER TABLE `types_seances` AUTO_INCREMENT=6;
ALTER TABLE `salles` AUTO_INCREMENT=6;
ALTER TABLE `retards` AUTO_INCREMENT=1;
ALTER TABLE `seuils_alerte` AUTO_INCREMENT=5;
ALTER TABLE `presence_mensuelle` AUTO_INCREMENT=1;
