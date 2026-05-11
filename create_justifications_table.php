<?php
/**
 * Script pour créer la table justifications directement
 */

require_once 'config/database.php';

// Créer la table justifications
$sql = "CREATE TABLE IF NOT EXISTS justifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    presence_id INT NOT NULL,
    contenu TEXT NOT NULL,
    date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'validee', 'rejetee') DEFAULT 'en_attente',
    validee_par INT NULL,
    date_validation DATETIME NULL,
    commentaire TEXT NULL,
    FOREIGN KEY (presence_id) REFERENCES presences(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$result = db_exec($sql);

if ($result) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>La table 'justifications' a été créée avec succès.</h3>";
    echo "<p>Vous pouvez maintenant utiliser le système de justification d'absences.</p>";
    echo "<a href='gestion_justifications.php' style='display: inline-block; background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Aller à la gestion des justifications</a>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>Erreur lors de la création de la table 'justifications'.</h3>";
    echo "<p>" . db_error() . "</p>";
    echo "<a href='index.php' style='display: inline-block; background-color: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Retour à l'accueil</a>";
    echo "</div>";
}
?>
