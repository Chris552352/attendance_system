<?php
/**
 * Script pour corriger les problèmes liés u00e0 l'affichage du téléphone
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Vérifier si la colonne téléphone existe
$check_telephone = db_query("SHOW COLUMNS FROM etudiants LIKE 'telephone'");

if (empty($check_telephone)) {
    // La colonne n'existe pas, on la crée
    echo "<p>La colonne téléphone n'existe pas dans la table etudiants. Création en cours...</p>";
    
    $sql = "ALTER TABLE etudiants ADD COLUMN telephone VARCHAR(20) NULL AFTER email";
    
    if (db_exec($sql)) {
        echo "<p style='color: green;'>Colonne téléphone ajoutée avec succès.</p>";
    } else {
        echo "<p style='color: red;'>Erreur lors de l'ajout de la colonne téléphone: " . db_error() . "</p>";
    }
} else {
    echo "<p>La colonne téléphone existe déjà dans la table etudiants.</p>";
}

// Vérifier les données des u00e9tudiants
echo "<h2>Vérification des données des u00e9tudiants</h2>";

$etudiants = db_query("SELECT id, nom, prenom, email, telephone FROM etudiants LIMIT 10");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Téléphone existe</th></tr>";

foreach ($etudiants as $etudiant) {
    echo "<tr>";
    echo "<td>{$etudiant['id']}</td>";
    echo "<td>{$etudiant['nom']}</td>";
    echo "<td>{$etudiant['prenom']}</td>";
    echo "<td>{$etudiant['email']}</td>";
    echo "<td>" . (isset($etudiant['telephone']) ? htmlspecialchars($etudiant['telephone']) : 'NULL') . "</td>";
    echo "<td>" . (array_key_exists('telephone', $etudiant) ? 'Oui' : 'Non') . "</td>";
    echo "</tr>";
}

echo "</table>";

// Corriger les valeurs NULL pour le téléphone
echo "<h2>Correction des valeurs NULL pour le téléphone</h2>";

$sql = "UPDATE etudiants SET telephone = '' WHERE telephone IS NULL";

if (db_exec($sql)) {
    echo "<p style='color: green;'>Les valeurs NULL pour le téléphone ont u00e9té remplacées par des chaînes vides.</p>";
} else {
    echo "<p style='color: red;'>Erreur lors de la mise u00e0 jour des valeurs NULL pour le téléphone: " . db_error() . "</p>";
}

// Vérifier u00e0 nouveau les données des u00e9tudiants après correction
echo "<h2>Vérification des données des u00e9tudiants après correction</h2>";

$etudiants = db_query("SELECT id, nom, prenom, email, telephone FROM etudiants LIMIT 10");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Téléphone existe</th></tr>";

foreach ($etudiants as $etudiant) {
    echo "<tr>";
    echo "<td>{$etudiant['id']}</td>";
    echo "<td>{$etudiant['nom']}</td>";
    echo "<td>{$etudiant['prenom']}</td>";
    echo "<td>{$etudiant['email']}</td>";
    echo "<td>" . (isset($etudiant['telephone']) ? htmlspecialchars($etudiant['telephone']) : 'NULL') . "</td>";
    echo "<td>" . (array_key_exists('telephone', $etudiant) ? 'Oui' : 'Non') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<p><a href='etudiants.php' class='btn btn-primary'>Retour u00e0 la liste des u00e9tudiants</a></p>";
?>
