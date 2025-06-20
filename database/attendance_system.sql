-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 06 mai 2025 u00e0 17:21
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `attendance_system`
--
CREATE DATABASE IF NOT EXISTS `attendance_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `attendance_system`;

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

DROP TABLE IF EXISTS `cours`;
CREATE TABLE IF NOT EXISTS `cours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enseignant_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `enseignant_id` (`enseignant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id`, `code`, `nom`, `enseignant_id`, `description`) VALUES
(3, 'CS103', 'Réseaux Informatiques', 372, 'Principes de communication, protocoles réseaux et configuration des u00e9quipements '),
(4, 'FR835', 'Français', 732, 'La lettre formelles et la rédaction d&#39;un rapport ');

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE IF NOT EXISTS `etudiants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id`, `matricule`, `nom`, `prenom`, `email`, `date_inscription`) VALUES
(6, 'CMD-UDS-23IUT1045', 'Simo', 'Ange', 'angesimo@gmail.com', '2025-04-30 22:32:52'),
(7, 'CMD-UDS-23IUT1022', 'Ateba', 'jean', 'Ateba@gmail.com', '2025-05-05 18:16:51'),
(8, 'CMD-UDS-23IUT1087', 'Tchouche', 'Cindy', 'Cindy90@gmail.com', '2025-05-05 18:18:52'),
(9, 'CMD-UDS-23IUT1098', 'Manga', 'Patrick', 'Manga45@gmail.com', '2025-05-05 18:21:12'),
(10, 'CMD-UDS-IUTBJN01', 'Nguekam', 'Karl', 'nguekam.karl@gmail.com', '2025-05-05 18:32:41'),
(11, 'CMD-UDS-IUTDLA02', 'Mbouombouo', 'Diane', 'mbouombouo.diane@icloud.com', '2025-05-05 18:32:41'),
(12, 'CMD-UDS-IUTYDE03', 'Fomekong', 'Richard', 'fomekong.richard@gmail.com', '2025-05-05 18:32:41'),
(13, 'CMD-UDS-IUTBJN04', 'Kamgang', 'Inès', 'kamgang.ines@icloud.com', '2025-05-05 18:32:41'),
(14, 'CMD-UDS-IUTBDA05', 'Fokou', 'Donald', 'fokou.donald@gmail.com', '2025-05-05 18:32:41'),
(15, 'CMD-UDS-IUTDLA06', 'Tchameni', 'Sandrine', 'tchameni.sandrine@icloud.com', '2025-05-05 18:32:41'),
(16, 'CMD-UDS-IUTBJN07', 'Moukoko', 'Jean', 'moukoko.jean@gmail.com', '2025-05-05 18:32:41'),
(17, 'CMD-UDS-IUTYDE08', 'Nkoué', 'Françoise', 'nkoue.francoise@icloud.com', '2025-05-05 18:32:41'),
(18, 'CMD-UDS-IUTBDA09', 'Koum', 'Serges', 'koum.serges@gmail.com', '2025-05-05 18:32:41'),
(19, 'CMD-UDS-IUTDLA10', 'Nana', 'Stéphanie', 'nana.stephanie@icloud.com', '2025-05-05 18:32:41');

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
CREATE TABLE IF NOT EXISTS `inscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `etudiant_id` int NOT NULL,
  `cours_id` int NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `etudiant_id` (`etudiant_id`,`cours_id`),
  KEY `cours_id` (`cours_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `presences`
--

DROP TABLE IF EXISTS `presences`;
CREATE TABLE IF NOT EXISTS `presences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `etudiant_id` int NOT NULL,
  `cours_id` int NOT NULL,
  `date_presence` date NOT NULL,
  `statut` enum('present','absent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `justification` TEXT NULL,
  `justifie` BOOLEAN DEFAULT FALSE,
  `enregistre_par` int NOT NULL,
  `date_enregistrement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `etudiant_id` (`etudiant_id`,`cours_id`,`date_presence`),
  KEY `cours_id` (`cours_id`),
  KEY `enregistre_par` (`enregistre_par`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','enseignant') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enseignant',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=733 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `date_creation`, `derniere_connexion`) VALUES
(237, 'Admin', 'System', 'admin@example.com', '$2y$10$lHj7gQX1Kd2x1RCBQKL0T.0d/dQFXnJL81U5aOrCQpBfCsqSg3U/m', 'admin', '2025-04-30 13:59:20', NULL),
(372, 'Kenmogne', 'Alfed', 'Kenmognealfred@gmail.com', '$2y$10$lHj7gQX1Kd2x1RCBQKL0T.0d/dQFXnJL81U5aOrCQpBfCsqSg3U/m', 'enseignant', '2025-04-30 13:59:20', NULL),
(732, 'Nounamo', 'Chris', 'chris552352@gmail.com', '$2y$10$.Gltio/0jwGtIIpIUXBOp./5QwJQe7FQ1ZIXCGCAFSOV8f3WXTLeu', 'admin', '2025-04-30 13:59:20', '2025-05-05 19:06:33');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `presences`
--
ALTER TABLE `presences`
  ADD CONSTRAINT `presences_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presences_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presences_ibfk_3` FOREIGN KEY (`enregistre_par`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
