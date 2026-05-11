<?php
// Script pour créer la table 'presences' si elle n'existe pas
require_once 'config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `presences` (
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
  KEY `enregistre_par` (`enregistre_par`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    db_exec($sql);
    echo "<b>La table 'presences' a été créée avec succès ou existe déjà.</b>";
} catch (Exception $e) {
    echo "<b>Erreur lors de la création de la table 'presences' :</b> ".htmlspecialchars($e->getMessage());
}
