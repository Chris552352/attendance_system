<?php
// Script de test d'insertion d'un utilisateur

// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure la configuration de la base de données
require_once 'config/database.php';

echo "<h1>Test d'insertion d'un utilisateur</h1>";

// Informations de l'utilisateur de test
$nom = 'Test';
$prenom = 'Enseignant';
$email = 'test_' . time() . '@example.com'; // Email unique
$password = 'testpassword';
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'enseignant';

echo "<p>Tentative d'insertion d'un utilisateur avec les données suivantes :</p>";
echo "<ul>";
echo "<li>Nom : $nom</li>";
echo "<li>Prénom : $prenom</li>";
echo "<li>Email : $email</li>";
echo "<li>Rôle : $role</li>";
echo "</ul>";

try {
    // Tester l'insertion directe avec MySQLi si disponible
    if ($db_type === 'mysql' && $mysqli) {
        echo "<h2>Test avec MySQLi</h2>";
        
        // Préparer la requête
        $stmt = $mysqli->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            echo "<p style='color:red'>Erreur de préparation de la requête : " . $mysqli->error . "</p>";
        } else {
            // Lier les paramètres
            $stmt->bind_param("sssss", $nom, $prenom, $email, $password_hash, $role);
            
            // Exécuter la requête
            $result = $stmt->execute();
            
            if ($result) {
                echo "<p style='color:green'>Insertion réussie avec MySQLi! ID : " . $mysqli->insert_id . "</p>";
            } else {
                echo "<p style='color:red'>u00c9chec de l'insertion avec MySQLi : " . $stmt->error . "</p>";
            }
            
            $stmt->close();
        }
    } else {
        echo "<p>MySQLi n'est pas disponible.</p>";
    }
    
    // Tester l'insertion avec la fonction db_exec
    echo "<h2>Test avec db_exec</h2>";
    $email2 = 'test2_' . time() . '@example.com'; // Un autre email unique
    
    echo "<p>Tentative avec email : $email2</p>";
    
    $result = db_exec(
        "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)",
        [$nom, $prenom, $email2, $password_hash, $role]
    );
    
    if ($result) {
        echo "<p style='color:green'>Insertion réussie avec db_exec! ID : " . db_last_insert_id() . "</p>";
    } else {
        echo "<p style='color:red'>u00c9chec de l'insertion avec db_exec.</p>";
    }
    
    // Vérifier la structure de la table utilisateurs
    echo "<h2>Structure de la table utilisateurs</h2>";
    try {
        // Pour MySQL
        if ($db_type === 'mysql') {
            $columns = db_query("SHOW COLUMNS FROM utilisateurs");
            if ($columns !== false) {
                echo "<table border='1'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                foreach ($columns as $column) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color:red'>Impossible d'obtenir la structure de la table.</p>";
            }
        }
        // Pour PostgreSQL
        else if ($db_type === 'postgresql') {
            $columns = db_query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'utilisateurs'");
            if ($columns !== false) {
                echo "<table border='1'>";
                echo "<tr><th>Column</th><th>Type</th><th>Nullable</th></tr>";
                foreach ($columns as $column) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($column['column_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['data_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['is_nullable']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color:red'>Impossible d'obtenir la structure de la table.</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Exception lors de la vérification de la structure : " . $e->getMessage() . "</p>";
    }
    
    // Vérifier les utilisateurs existants
    echo "<h2>Utilisateurs existants</h2>";
    try {
        $users = db_query("SELECT id, nom, prenom, email, role FROM utilisateurs LIMIT 10");
        if ($users === false) {
            echo "<p style='color:red'>Erreur lors de la récupération des utilisateurs.</p>";
        } else if (empty($users)) {
            echo "<p>Aucun utilisateur trouvé.</p>";
        } else {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($user['prenom']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Exception lors de la récupération des utilisateurs : " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Exception principale : " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Retour u00e0 l'accueil</a></p>";
?>
