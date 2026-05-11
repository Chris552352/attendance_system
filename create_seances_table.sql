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
