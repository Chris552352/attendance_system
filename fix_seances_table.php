<?php
require_once 'config/database.php';

// Utiliser l'objet $mysqli défini dans config/database.php
if (!isset($mysqli) || !$mysqli) {
    die("Erreur de connexion à la base de données.");
}

// Supprimer toute clé nommée 'token' dans la base (hors table seances)
$res = $mysqli->query("SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME='token'");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $table = $row['TABLE_NAME'];
        $mysqli->query("ALTER TABLE `" . $table . "` DROP INDEX `token`");
    }
}

// Supprimer la table 'seances' si elle existe
$sql_drop = "DROP TABLE IF EXISTS `seances`;";
$drop_ok = $mysqli->query($sql_drop);
if (!$drop_ok) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>Erreur DROP TABLE : ".$mysqli->error."</div>";
}

// Recréer la table
$sql_create = "CREATE TABLE `seances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_cours` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enseignant_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
  `expiration` timestamp NOT NULL,
  `date_heure` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seances_token` (`token`),
  KEY `enseignant_id` (`enseignant_id`),
  CONSTRAINT `seances_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$create_ok = $mysqli->query($sql_create);

if ($create_ok) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>La table 'seances' a été recréée avec succès.</h3>";
    echo "<a href='generer_qr.php' style='display: inline-block; background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Retour à la génération du QR</a>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>Erreur lors de la création de la table 'seances'.</h3>";
    echo "<p>" . db_error() . "</p>";
    echo "<a href='generer_qr.php' style='display: inline-block; background-color: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Retour à la génération du QR</a>";
    echo "</div>";
}
