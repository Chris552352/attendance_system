<?php
/**
 * Script de débogage pour identifier pourquoi les enseignants ne voient pas leurs cours
 */

require_once 'config/database.php';

// Afficher la structure de la table cours
echo "<h2>Structure de la table 'cours'</h2>";
$structure_cours = db_query("DESCRIBE cours");
echo "<pre>";
print_r($structure_cours);
echo "</pre>";

// Afficher la structure de la table utilisateurs
echo "<h2>Structure de la table 'utilisateurs'</h2>";
$structure_utilisateurs = db_query("DESCRIBE utilisateurs");
echo "<pre>";
print_r($structure_utilisateurs);
echo "</pre>";

// Afficher les enseignants dans la table utilisateurs
echo "<h2>Enseignants dans la table 'utilisateurs'</h2>";
$enseignants = db_query("SELECT id, nom, prenom, email, role FROM utilisateurs WHERE role = 'enseignant'");
echo "<pre>";
print_r($enseignants);
echo "</pre>";

// Afficher les cours avec leurs enseignants
echo "<h2>Cours avec leurs enseignants</h2>";
$cours_enseignants = db_query("SELECT c.id, c.nom, c.code, c.enseignant_id, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM cours c LEFT JOIN utilisateurs u ON c.enseignant_id = u.id");
echo "<pre>";
print_r($cours_enseignants);
echo "</pre>";

// Afficher les informations de session pour un enseignant connecté
echo "<h2>Informations de session</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    echo "<p>Utilisateur connecté: ID = {$_SESSION['user_id']}, Role = {$_SESSION['user_role']}</p>";
    
    // Vérifier les cours de cet enseignant
    if ($_SESSION['user_role'] === 'enseignant') {
        echo "<h3>Cours de l'enseignant connecté</h3>";
        $cours_enseignant = db_query("SELECT id, nom, code FROM cours WHERE enseignant_id = ?", [$_SESSION['user_id']]);
        echo "<pre>";
        print_r($cours_enseignant);
        echo "</pre>";
    }
} else {
    echo "<p>Aucun utilisateur connecté</p>";
}
?>
