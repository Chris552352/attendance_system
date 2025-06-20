-- MySQL Schema for Attendance System
-- For local WAMP installation

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: attendance_system
-- Collation: utf8mb4_unicode_ci

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

-- Table: etudiants
CREATE TABLE `etudiants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricule` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  UNIQUE KEY `email` (`email`)
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

-- Table: presences
CREATE TABLE `presences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `etudiant_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `date_presence` date NOT NULL,
  `statut` enum('present','absent') COLLATE utf8mb4_unicode_ci DEFAULT 'present',
  `justification` text COLLATE utf8mb4_unicode_ci,
  `justifie` tinyint(1) DEFAULT '0',
  `enregistre_par` int(11) NOT NULL,
  `date_enregistrement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `etudiant_cours_date` (`etudiant_id`,`cours_id`,`date_presence`),
  KEY `cours_id` (`cours_id`),
  KEY `enregistre_par` (`enregistre_par`),
  CONSTRAINT `presences_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`),
  CONSTRAINT `presences_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`),
  CONSTRAINT `presences_ibfk_3` FOREIGN KEY (`enregistre_par`) REFERENCES `utilisateurs` (`id`)
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
  UNIQUE KEY `token` (`token`),
  KEY `enseignant_id` (`enseignant_id`),
  CONSTRAINT `seances_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`) VALUES
(1, 'Admin', 'Chris', 'chris552352@gmail.com', '$2y$10$/f3glrQ1ygJljOKN9s99L.OMvsaS/pnPVCDTKOQmfRPod7NZeT3dK', 'admin'),
(372, 'Dupont', 'Marie', 'marie.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant'),
(732, 'Martin', 'Jean', 'jean.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant');

INSERT INTO `cours` (`id`, `code`, `nom`, `enseignant_id`, `description`) VALUES
(3, 'CS103', 'Réseaux Informatiques', 372, 'Principes de communication, protocoles réseaux et configuration des équipements'),
(4, 'FR835', 'Français', 732, 'La lettre formelles et la rédaction d\'un rapport');

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

INSERT INTO `etudiants` (`id`, `matricule`, `nom`, `prenom`, `email`) VALUES
(6, 'CMD-UDS-23IUT1045', 'Simo', 'Ange', 'angesimo@gmail.com'),
(7, 'CMD-UDS-23IUT1022', 'Ateba', 'Jean', 'Ateba@gmail.com'),
(8, 'CMD-UDS-23IUT1087', 'Tchouche', 'Cindy', 'Cindy90@gmail.com'),
(9, 'CMD-UDS-23IUT1098', 'Manga', 'Patrick', 'Manga45@gmail.com'),
(10, 'CMD-UDS-IUTBJN01', 'Nguekam', 'Karl', 'nguekam.karl@gmail.com'),
(11, 'CMD-UDS-IUTDLA02', 'Mbouombouo', 'Diane', 'mbouombouo.diane@icloud.com'),
(12, 'CMD-UDS-IUTYDE03', 'Fomekong', 'Richard', 'fomekong.richard@gmail.com'),
(13, 'CMD-UDS-IUTBJN04', 'Kamgang', 'Inès', 'kamgang.ines@icloud.com'),
(14, 'CMD-UDS-IUTBDA05', 'Fokou', 'Donald', 'fokou.donald@gmail.com'),
(15, 'CMD-UDS-IUTDLA06', 'Tchameni', 'Sandrine', 'tchameni.sandrine@icloud.com');

-- Création automatique des comptes étudiants avec mot de passe par défaut
INSERT INTO `comptes_etudiants` (`etudiant_id`, `email`, `mot_de_passe`) VALUES
(6, 'angesimo@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(7, 'Ateba@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(8, 'Cindy90@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(9, 'Manga45@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(10, 'nguekam.karl@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(11, 'mbouombouo.diane@icloud.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(12, 'fomekong.richard@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(13, 'kamgang.ines@icloud.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(14, 'fokou.donald@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(15, 'tchameni.sandrine@icloud.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Set AUTO_INCREMENT values
ALTER TABLE `utilisateurs` AUTO_INCREMENT=733;
ALTER TABLE `cours` AUTO_INCREMENT=5;
ALTER TABLE `etudiants` AUTO_INCREMENT=16;
ALTER TABLE `inscriptions` AUTO_INCREMENT=1;
ALTER TABLE `presences` AUTO_INCREMENT=1;

COMMIT;