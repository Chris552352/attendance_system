<?php
// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Vérifier la structure de la table etudiants
echo "<h2>Structure de la table etudiants</h2>";
$structure = db_query("DESCRIBE etudiants");
echo "<pre>";
print_r($structure);
echo "</pre>";

// Vérifier les données des u00e9tudiants
echo "<h2>Données des u00e9tudiants</h2>";
$etudiants = db_query("SELECT * FROM etudiants LIMIT 5");
echo "<pre>";
print_r($etudiants);
echo "</pre>";
?>
