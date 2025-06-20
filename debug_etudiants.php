<?php
/**
 * Script de diagnostic pour la table etudiants
 */

require_once 'config/database.php';

// Afficher les erreurs pour le diagnostic
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic de la table etudiants</h1>";

// 1. Vérifier si la table existe
echo "<h2>1. Vérification de l'existence de la table</h2>";
$tables = db_query("SHOW TABLES LIKE 'etudiants'");
if (count($tables) > 0) {
    echo "<p style='color:green'>La table 'etudiants' existe.</p>";
} else {
    echo "<p style='color:red'>La table 'etudiants' n'existe pas!</p>";
    exit;
}

// 2. Vérifier la structure de la table
echo "<h2>2. Structure de la table</h2>";
$structure = db_query("DESCRIBE etudiants");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
foreach ($structure as $column) {
    echo "<tr>";
    foreach ($column as $key => $value) {
        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// 3. Compter le nombre d'u00e9tudiants
echo "<h2>3. Nombre d'u00e9tudiants</h2>";
$count = db_query_single("SELECT COUNT(*) as total FROM etudiants");
echo "<p>Nombre total d'u00e9tudiants: " . $count['total'] . "</p>";

// 4. Afficher les 10 premiers u00e9tudiants
echo "<h2>4. Liste des 10 premiers u00e9tudiants</h2>";
$etudiants = db_query("SELECT * FROM etudiants ORDER BY id DESC LIMIT 10");

if (count($etudiants) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    foreach (array_keys($etudiants[0]) as $key) {
        echo "<th>" . htmlspecialchars($key) . "</th>";
    }
    echo "</tr>";
    
    foreach ($etudiants as $etudiant) {
        echo "<tr>";
        foreach ($etudiant as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>Aucun u00e9tudiant trouvé dans la base de données!</p>";
}

// 5. Vérifier les erreurs lors de l'ajout d'un u00e9tudiant de test
echo "<h2>5. Test d'ajout d'un u00e9tudiant</h2>";

// Générer un matricule unique
$matricule = "TEST-" . time();
$email = "test" . time() . "@example.com";

// Essayer d'ajouter un u00e9tudiant de test
$sql = "INSERT INTO etudiants (nom, prenom, matricule, email) VALUES (?, ?, ?, ?)";
$params = ["Test", "Diagnostic", $matricule, $email];

$success = db_exec($sql, $params);

if ($success) {
    $id = db_last_insert_id();
    echo "<p style='color:green'>Ajout réussi! ID de l'u00e9tudiant: $id</p>";
    
    // Vérifier si l'u00e9tudiant est bien dans la base
    $etudiant = db_query_single("SELECT * FROM etudiants WHERE id = ?", [$id]);
    
    if ($etudiant) {
        echo "<p>L'u00e9tudiant a bien u00e9té ajouté u00e0 la base de données:</p>";
        echo "<pre>";
        print_r($etudiant);
        echo "</pre>";
        
        // Supprimer l'u00e9tudiant de test
        db_exec("DELETE FROM etudiants WHERE id = ?", [$id]);
        echo "<p>L'u00e9tudiant de test a u00e9té supprimé.</p>";
    } else {
        echo "<p style='color:red'>ERREUR: L'u00e9tudiant a u00e9té ajouté mais n'est pas retrouvable dans la base!</p>";
    }
} else {
    echo "<p style='color:red'>ERREUR lors de l'ajout: " . db_error() . "</p>";
}

// 6. Vérifier le code de la page etudiants.php
echo "<h2>6. Analyse du code de etudiants.php</h2>";

$file = 'etudiants.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    
    // Extraire la reqête SQL
    if (preg_match('/db_query\(\s*"([^"]+)"\s*\)/s', $content, $matches)) {
        echo "<p>Reqête SQL trouvée:</p>";
        echo "<pre>" . htmlspecialchars($matches[1]) . "</pre>";
    } else {
        echo "<p style='color:red'>Impossible de trouver la reqête SQL dans le fichier.</p>";
    }
} else {
    echo "<p style='color:red'>Le fichier $file n'existe pas.</p>";
}

echo "<p><a href='etudiants.php'>Retour u00e0 la liste des u00e9tudiants</a></p>";
?>
