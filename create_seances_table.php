<?php
// Script pour créer la table 'seances' si elle n'existe pas
require_once 'config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS seances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_cours VARCHAR(255),
    enseignant_id INT,
    date_creation DATETIME,
    expiration DATETIME,
    token VARCHAR(64),
    active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    db_exec($sql);
    echo "<p style='color:green;'>Table 'seances' créée ou déjà existante.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erreur lors de la création de la table : " . htmlspecialchars($e->getMessage()) . "</p>";
}
