-- MySQL Schema for Attendance System
-- For local WAMP installation

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: attendance_system
-- Collation: utf8mb4_unicode_ci

-- Ordre de création des tables :
-- 1. utilisateurs (pas de dépendances)
-- 2. cours (dépend de utilisateurs)
-- 3. etudiants (pas de dépendances)
-- 4. comptes_etudiants (dépend de etudiants)
-- 5. inscriptions (dépend de etudiants, cours)
-- 6. seances (dépend de utilisateurs)
-- 7. presences (dépend de etudiants, cours, seances, utilisateurs)

-- Table: utilisateurs
CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','enseignant') COLLATE utf8mb4_unicode_ci DEFAULT 'enseignant',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cours
CREATE TABLE `cours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enseignant_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `enseignant_id` (`enseignant_id`),
  CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: classes_etablissement (promotions LMD : niveau + filière + groupe)
CREATE TABLE `classes_etablissement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveau` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filiere` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_unique` (`code`),
  KEY `idx_actif` (`actif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: etudiants
CREATE TABLE `etudiants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricule` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `classe_id` int(11) NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_etudiants_classe` (`classe_id`),
  CONSTRAINT `etudiants_classe_fk` FOREIGN KEY (`classe_id`) REFERENCES `classes_etablissement` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour les comptes étudiants (connexion séparée)
CREATE TABLE `comptes_etudiants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `etudiant_id` int(11) NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `etudiant_id` (`etudiant_id`),
  CONSTRAINT `comptes_etudiants_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: inscriptions
CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `etudiant_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `etudiant_cours` (`etudiant_id`,`cours_id`),
  KEY `cours_id` (`cours_id`),
  CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`),
  CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: seances (pour les QR codes)
CREATE TABLE `seances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_cours` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enseignant_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
  `expiration` timestamp NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `enseignant_id` (`enseignant_id`),
  CONSTRAINT `seances_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: presences
CREATE TABLE `presences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `etudiant_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `seance_id` int(11) DEFAULT NULL,
  `date_presence` date NOT NULL,
  `statut` enum('present','absent') COLLATE utf8mb4_unicode_ci DEFAULT 'present',
  `justification` text COLLATE utf8mb4_unicode_ci,
  `justifie` tinyint(1) DEFAULT '0',
  `enregistre_par` int(11) NOT NULL,
  `date_enregistrement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `etudiant_seance` (`etudiant_id`,`seance_id`),
  KEY `cours_id` (`cours_id`),
  KEY `seance_id` (`seance_id`),
  KEY `enregistre_par` (`enregistre_par`),
  CONSTRAINT `presences_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`),
  CONSTRAINT `presences_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`),
  CONSTRAINT `presences_ibfk_3` FOREIGN KEY (`enregistre_par`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `presences_ibfk_4` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Insert sample data
INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`) VALUES
(1, 'Admin', 'Chris', 'chris552352@gmail.com', '$2y$10$/f3glrQ1ygJljOKN9s99L.OMvsaS/pnPVCDTKOQmfRPod7NZeT3dK', 'admin'),
(372, 'Dupont', 'Marie', 'marie.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant'),
(732, 'Martin', 'Jean', 'jean.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant');

INSERT INTO `cours` (`id`, `code`, `nom`, `enseignant_id`, `description`) VALUES
(3, 'CS103', 'Réseaux Informatiques', 372, 'Principes de communication, protocoles réseaux et configuration des équipements'),
(4, 'FR835', 'Français', 732, 'La lettre formelles et la rédaction d''un rapport');

INSERT INTO `classes_etablissement` (`id`, `code`, `nom`, `niveau`, `filiere`, `actif`) VALUES
(1, 'L1-INFO-A', 'Groupe A', 'Licence 1', 'Informatique', 1),
(2, 'L1-INFO-B', 'Groupe B', 'Licence 1', 'Informatique', 1),
(3, 'L2-INFO-A', 'Groupe A', 'Licence 2', 'Informatique', 1),
(4, 'L3-INFO-A', 'Groupe A', 'Licence 3', 'Informatique', 1),
(5, 'M1-GEST-A', 'Groupe A', 'Master 1', 'Gestion', 1),
(6, 'M2-INFO-A', 'Groupe A', 'Master 2', 'Informatique', 1);

INSERT INTO `etudiants` (`id`, `matricule`, `nom`, `prenom`, `email`, `classe_id`) VALUES
(6, 'UDS24-L1IA-01', 'Kouam', 'Paul', 'paul.kouam24@etu.uds.cm', 1),
(7, 'UDS24-L1IA-02', 'Mbarga', 'Sarah', 'sarah.mbarga24@etu.uds.cm', 1),
(8, 'UDS24-L1IA-03', 'Ndongo', 'Léo', 'leo.ndongo24@etu.uds.cm', 1),
(9, 'UDS24-L1IB-01', 'Fotso', 'Marie', 'marie.fotso24@etu.uds.cm', 2),
(10, 'UDS24-L1IB-02', 'Tabi', 'Jean', 'jean.tabi24@etu.uds.cm', 2),
(11, 'UDS24-L2IA-01', 'Nguele', 'Grace', 'grace.nguele24@etu.uds.cm', 3),
(12, 'UDS24-L2IA-02', 'Owona', 'Marc', 'marc.owona24@etu.uds.cm', 3),
(13, 'UDS24-L2IA-03', 'Dissake', 'Alain', 'alain.dissake24@etu.uds.cm', 3),
(14, 'UDS24-L3IA-01', 'Mbiada', 'Iris', 'iris.mbiada24@etu.uds.cm', 4),
(15, 'UDS24-L3IA-02', 'Bilog', 'Lucas', 'lucas.bilog24@etu.uds.cm', 4),
(16, 'UDS24-M1GS-01', 'Essomba', 'Inès', 'ines.essomba24@etu.uds.cm', 5),
(17, 'UDS24-M1GS-02', 'Nana', 'Franck', 'franck.nana24@etu.uds.cm', 5),
(18, 'UDS24-M1GS-03', 'Moukoko', 'Sara', 'sara.moukoko24@etu.uds.cm', 5),
(19, 'UDS24-M2IA-01', 'Ateba', 'Théo', 'theo.ateba24@etu.uds.cm', 6),
(20, 'UDS24-M2IA-02', 'Fokou', 'Emma', 'emma.fokou24@etu.uds.cm', 6);

-- Création automatique des comptes étudiants avec mot de passe par défaut
INSERT INTO `comptes_etudiants` (`etudiant_id`, `email`, `mot_de_passe`) VALUES
(6, 'paul.kouam24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(7, 'sarah.mbarga24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(8, 'leo.ndongo24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(9, 'marie.fotso24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(10, 'jean.tabi24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(11, 'grace.nguele24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(12, 'marc.owona24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(13, 'alain.dissake24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(14, 'iris.mbiada24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(15, 'lucas.bilog24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(16, 'ines.essomba24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(17, 'franck.nana24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(18, 'sara.moukoko24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(19, 'theo.ateba24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(20, 'emma.fokou24@etu.uds.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inscriptions aux cours de démo (CS103 + FR835)
INSERT INTO `inscriptions` (`etudiant_id`, `cours_id`) VALUES
(6, 3), (6, 4), (7, 3), (7, 4), (8, 3), (8, 4), (9, 3), (9, 4), (10, 3), (10, 4),
(11, 3), (11, 4), (12, 3), (12, 4), (13, 3), (13, 4), (14, 3), (14, 4), (15, 3), (15, 4),
(16, 3), (16, 4), (17, 3), (17, 4), (18, 3), (18, 4), (19, 3), (19, 4), (20, 3), (20, 4);

-- Set AUTO_INCREMENT values
ALTER TABLE `utilisateurs` AUTO_INCREMENT=733;
ALTER TABLE `cours` AUTO_INCREMENT=5;
ALTER TABLE `classes_etablissement` AUTO_INCREMENT=7;
ALTER TABLE `etudiants` AUTO_INCREMENT=21;
ALTER TABLE `inscriptions` AUTO_INCREMENT=31;
ALTER TABLE `presences` AUTO_INCREMENT=1;
ALTER TABLE `seances` AUTO_INCREMENT=1;

COMMIT;