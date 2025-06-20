<?php
/**
 * Script pour mettre u00e0 jour la structure de la base de données
 * Création d'une table séparée pour les justifications
 */

require_once 'config/database.php';

// Sélectionner la base de données
$sql0 = "USE attendance_system";
db_exec($sql0);

// Créer la table justifications
$sql1 = "CREATE TABLE IF NOT EXISTS justifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    presence_id INT NOT NULL,
    contenu TEXT NOT NULL,
    date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'validee', 'rejetee') DEFAULT 'en_attente',
    validee_par INT NULL,
    date_validation DATETIME NULL,
    commentaire TEXT NULL,
    FOREIGN KEY (presence_id) REFERENCES presences(id) ON DELETE CASCADE,
    FOREIGN KEY (validee_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
)";

// Modifier la table presences pour utiliser un booléen pour justifie
$sql2 = "ALTER TABLE presences MODIFY COLUMN justifie BOOLEAN DEFAULT FALSE";

// Exécuter les requêtes
$result1 = db_exec($sql1);
$result2 = db_exec($sql2);

// Migrer les données existantes
$sql3 = "SELECT id, justification FROM presences WHERE justification IS NOT NULL AND justification != ''";
$presences_avec_justification = db_query($sql3);

if ($presences_avec_justification) {
    foreach ($presences_avec_justification as $presence) {
        $sql4 = "INSERT INTO justifications (presence_id, contenu, statut) VALUES (?, ?, 'validee')";
        db_exec($sql4, [$presence['id'], $presence['justification']]);
    }
}

// Afficher le résultat
if ($result1 && $result2) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>La base de données a u00e9té mise u00e0 jour avec succès.</h3>";
    echo "<p>La table 'justifications' a u00e9té créu00e9e et les données existantes ont u00e9té migrées.</p>";
    echo "<p>Vous pouvez maintenant utiliser le nouveau système de justification d'absences.</p>";
    echo "<a href='index.php' style='display: inline-block; background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Retour u00e0 l'accueil</a>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>Erreur lors de la mise u00e0 jour de la base de données.</h3>";
    echo "<p>Veuillez contacter l'administrateur système.</p>";
    echo "<a href='index.php' style='display: inline-block; background-color: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Retour u00e0 l'accueil</a>";
    echo "</div>";
}
?>
