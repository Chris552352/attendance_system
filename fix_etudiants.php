<?php
/**
 * Script pour corriger l'affichage des u00e9tudiants
 */

require_once 'config/database.php';

// Afficher les erreurs pour le diagnostic
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction de la page des u00e9tudiants</h1>";

// Vérifier si la redirection est trop rapide
echo "<h2>1. Vérification de la redirection</h2>";

$file = 'ajouter_etudiant.php';
$content = file_get_contents($file);

// Remplacer la redirection immédiate par une redirection avec délai
if (strpos($content, "header('Location: etudiants.php');") !== false) {
    echo "<p>Redirection immédiate détectée dans le fichier $file</p>";
    
    // Modification du fichier pour ajouter un délai
    $content_modified = str_replace(
        "// Redirection immédiate pour u00e9viter d'afficher les données dans la liste\n                header('Location: etudiants.php');\n                exit();",
        "// Redirection avec message de succès\n                alerte(\"L'u00e9tudiant a u00e9té ajouté avec succès.\", \"success\");\n                rediriger('etudiants.php');",
        $content
    );
    
    // Sauvegarder les modifications
    if ($content_modified !== $content) {
        file_put_contents($file, $content_modified);
        echo "<p style='color:green'>Correction appliquée: Remplacement de la redirection immédiate par une redirection standard.</p>";
    } else {
        echo "<p style='color:red'>Erreur: Impossible de modifier le fichier.</p>";
    }
} else {
    echo "<p>Aucune redirection immédiate détectée dans le fichier $file</p>";
}

// Corriger la requête SQL dans etudiants.php
echo "<h2>2. Correction de la requête SQL dans etudiants.php</h2>";

$file = 'etudiants.php';
$content = file_get_contents($file);

// Remplacer la requête SQL pour afficher tous les u00e9tudiants
if (strpos($content, "SELECT e.*\n    FROM etudiants e") !== false) {
    echo "<p>Requête SQL détectée dans le fichier $file</p>";
    
    // Modification du fichier pour ajouter un délai
    $content_modified = str_replace(
        "// Récupérer la liste des u00e9tudiants\n$etudiants = db_query(\"\n    SELECT e.*\n    FROM etudiants e\n    ORDER BY e.nom, e.prenom\n\");",
        "// Récupérer la liste des u00e9tudiants\n$etudiants = db_query(\"SELECT * FROM etudiants ORDER BY nom, prenom\");",
        $content
    );
    
    // Sauvegarder les modifications
    if ($content_modified !== $content) {
        file_put_contents($file, $content_modified);
        echo "<p style='color:green'>Correction appliquée: Simplification de la requête SQL.</p>";
    } else {
        echo "<p style='color:red'>Erreur: Impossible de modifier le fichier.</p>";
    }
} else {
    echo "<p>Requête SQL non détectée dans le fichier $file</p>";
}

// Ajouter un u00e9tudiant de test
echo "<h2>3. Ajout d'un u00e9tudiant de test</h2>";

// Générer un matricule unique
$matricule = "ETU-" . date('Y') . mt_rand(10000, 99999);
$email = "test" . time() . "@example.com";

// Essayer d'ajouter un u00e9tudiant de test
$sql = "INSERT INTO etudiants (nom, prenom, matricule, email) VALUES (?, ?, ?, ?)";
$params = ["Test", "Etudiant", $matricule, $email];

$success = db_exec($sql, $params);

if ($success) {
    $id = db_last_insert_id();
    echo "<p style='color:green'>Ajout réussi! ID de l'u00e9tudiant: $id</p>";
    echo "<p>Vérifiez maintenant si cet u00e9tudiant apparaît dans la liste.</p>";
} else {
    echo "<p style='color:red'>ERREUR lors de l'ajout: " . db_error() . "</p>";
}

echo "<p><a href='etudiants.php' class='btn btn-primary'>Voir la liste des u00e9tudiants</a></p>";
?>
