<?php
/**
 * Script pour ajouter la colonne justification u00e0 la table presences
 */

// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion directe u00e0 la base de données
$host = 'localhost';
$dbname = 'attendance_system';
$user = 'root';
$password = '';

echo "<h1>Ajout de la colonne justification u00e0 la table presences</h1>";

try {
    // Connexion u00e0 la base de données
    $mysqli = new mysqli($host, $user, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erreur de connexion u00e0 la base de données: " . $mysqli->connect_error);
    }
    
    echo "<p>Connexion u00e0 la base de données réussie.</p>";
    
    // Vérifier si la colonne existe déjà
    $result = $mysqli->query("SHOW COLUMNS FROM presences LIKE 'justification'");
    
    if ($result->num_rows > 0) {
        echo "<p>La colonne 'justification' existe déjà dans la table presences.</p>";
    } else {
        // Ajouter la colonne justification
        $sql = "ALTER TABLE presences ADD COLUMN justification TEXT NULL AFTER statut";
        
        if ($mysqli->query($sql) === TRUE) {
            echo "<p style='color:green'>La colonne 'justification' a u00e9té ajoutée avec succès u00e0 la table presences.</p>";
        } else {
            echo "<p style='color:red'>Erreur lors de l'ajout de la colonne: " . $mysqli->error . "</p>";
        }
    }
    
    // Vérifier si la colonne justifie existe déjà
    $result = $mysqli->query("SHOW COLUMNS FROM presences LIKE 'justifie'");
    
    if ($result->num_rows > 0) {
        echo "<p>La colonne 'justifie' existe déjà dans la table presences.</p>";
    } else {
        // Ajouter la colonne justifie
        $sql = "ALTER TABLE presences ADD COLUMN justifie BOOLEAN DEFAULT FALSE AFTER justification";
        
        if ($mysqli->query($sql) === TRUE) {
            echo "<p style='color:green'>La colonne 'justifie' a u00e9té ajoutée avec succès u00e0 la table presences.</p>";
        } else {
            echo "<p style='color:red'>Erreur lors de l'ajout de la colonne: " . $mysqli->error . "</p>";
        }
    }
    
    // Afficher la structure actuelle de la table
    $result = $mysqli->query("DESCRIBE presences");
    
    if ($result->num_rows > 0) {
        echo "<h2>Structure actuelle de la table presences</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["Field"] . "</td>";
            echo "<td>" . $row["Type"] . "</td>";
            echo "<td>" . $row["Null"] . "</td>";
            echo "<td>" . $row["Key"] . "</td>";
            echo "<td>" . $row["Default"] . "</td>";
            echo "<td>" . $row["Extra"] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Aucun résultat pour la structure de la table.</p>";
    }
    
    // Fermer la connexion
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Retour u00e0 l'accueil</a></p>";
?>
