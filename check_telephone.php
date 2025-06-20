<?php
/**
 * Script de diagnostic pour le champ téléphone
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Vérifier la structure de la table etudiants
echo "<h2>Structure de la table etudiants</h2>";
$structure = db_query("DESCRIBE etudiants");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
foreach ($structure as $field) {
    echo "<tr>";
    echo "<td>{$field['Field']}</td>";
    echo "<td>{$field['Type']}</td>";
    echo "<td>{$field['Null']}</td>";
    echo "<td>{$field['Key']}</td>";
    echo "<td>{$field['Default']}</td>";
    echo "<td>{$field['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Vérifier les données des étudiants
echo "<h2>Données des étudiants</h2>";
$etudiants = db_query("SELECT * FROM etudiants LIMIT 10");
echo "<table border='1' cellpadding='5'>";
echo "<tr>";
foreach ($structure as $field) {
    echo "<th>{$field['Field']}</th>";
}
echo "</tr>";

foreach ($etudiants as $etudiant) {
    echo "<tr>";
    foreach ($structure as $field) {
        $fieldName = $field['Field'];
        echo "<td>" . (isset($etudiant[$fieldName]) ? htmlspecialchars($etudiant[$fieldName]) : 'NULL') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Vérifier le code qui récupère un étudiant
echo "<h2>Test de récupération d'un étudiant</h2>";
if (!empty($etudiants)) {
    $etudiant_test_id = $etudiants[0]['id'];
    echo "<p>Test avec l'étudiant ID: {$etudiant_test_id}</p>";
    
    $etudiant_test = db_query_single("SELECT * FROM etudiants WHERE id = ?", [$etudiant_test_id]);
    
    echo "<h3>Données brutes:</h3>";
    echo "<pre>";
    print_r($etudiant_test);
    echo "</pre>";
    
    echo "<h3>Vérification du champ téléphone:</h3>";
    echo "<p>Clé 'telephone' existe dans le tableau: " . (array_key_exists('telephone', $etudiant_test) ? 'Oui' : 'Non') . "</p>";
    echo "<p>Valeur du champ téléphone: " . (isset($etudiant_test['telephone']) ? htmlspecialchars($etudiant_test['telephone']) : 'NULL') . "</p>";
}

// Vérifier le code de modification d'un étudiant
echo "<h2>Simulation de modification d'un étudiant</h2>";
if (!empty($etudiants)) {
    $etudiant_test_id = $etudiants[0]['id'];
    $etudiant_test = db_query_single("SELECT * FROM etudiants WHERE id = ?", [$etudiant_test_id]);
    
    echo "<p>Champs qui seraient utilisés pour la mise à jour:</p>";
    $fields = ['nom', 'prenom', 'email'];
    $params = [$etudiant_test['nom'], $etudiant_test['prenom'], $etudiant_test['email']];
    
    // Simuler l'ajout des champs optionnels
    $optional_fields = ['telephone', 'date_naissance', 'adresse'];
    echo "<ul>";
    foreach ($optional_fields as $field) {
        // Vérifier si le champ existe dans la table
        $check = db_query_single("SHOW COLUMNS FROM etudiants LIKE ?", [$field]);
        echo "<li>Champ '{$field}': " . ($check ? 'Existe dans la table' : 'N\'existe pas dans la table') . "</li>";
        
        if ($check) {
            $fields[] = $field;
            $params[] = $etudiant_test[$field] ?? null;
            echo "<ul>";
            echo "<li>Valeur récupérée: " . (isset($etudiant_test[$field]) ? htmlspecialchars($etudiant_test[$field]) : 'NULL') . "</li>";
            echo "</ul>";
        }
    }
    echo "</ul>";
    
    echo "<p>Requête SQL qui serait générée:</p>";
    $set_clauses = array_map(function($field) { return "{$field} = ?"; }, $fields);
    $sql = "UPDATE etudiants SET " . implode(', ', $set_clauses) . " WHERE id = ?";
    echo "<pre>{$sql}</pre>";
    
    echo "<p>Paramètres qui seraient utilisés:</p>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";
}
