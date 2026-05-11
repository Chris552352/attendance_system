<?php
// Script pour corriger les justifications vides dans la table 'justifications'
require_once 'config/database.php';

// Mettre à jour les justifications vides
$sql = "UPDATE justifications SET contenu = 'Aucun motif saisi' WHERE contenu IS NULL OR TRIM(contenu) = ''";
$result = db_exec($sql);

if ($result) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>Correction terminée</h3>";
    echo "<p>Toutes les justifications vides ont été remplacées par 'Aucun motif saisi'.</p>";
    echo "<a href='gestion_justifications.php' style='display: inline-block; background-color: #1976d2; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Retour à la gestion des justifications</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>Erreur lors de la correction</h3>";
    echo "<p>" . db_error() . "</p>";
    echo "<a href='index.php' style='display: inline-block; background-color: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Retour à l'accueil</a>";
    echo "</div>";
} 