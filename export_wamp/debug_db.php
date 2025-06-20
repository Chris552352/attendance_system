<?php
/**
 * Script de débogage pour vérifier la structure de la base de données
 */

require_once 'config/database.php';

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Débogage de la base de données</h1>";

// Vérifier la connexion u00e0 la base de données
echo "<h2>Test de connexion u00e0 la base de données</h2>";
if ($db_type === 'mysql' && $mysqli) {
    echo "<p style='color:green'>Connexion MySQL réussie!</p>";
} elseif ($db_type === 'postgresql' && $pdo) {
    echo "<p style='color:green'>Connexion PostgreSQL réussie!</p>";
} else {
    echo "<p style='color:red'>Erreur de connexion u00e0 la base de données!</p>";
}

// Vérifier la structure de la table utilisateurs
echo "<h2>Structure de la table utilisateurs</h2>";
try {
    $result = db_query("DESCRIBE utilisateurs");
    if ($result === false) {
        echo "<p style='color:red'>Erreur lors de la récupération de la structure de la table utilisateurs.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
    
    // Essayer une autre approche pour MySQL
    try {
        $result = db_query("SHOW COLUMNS FROM utilisateurs");
        if ($result === false) {
            echo "<p style='color:red'>Erreur lors de la récupération de la structure de la table utilisateurs avec SHOW COLUMNS.</p>";
        } else {
            echo "<table border='1'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
            foreach ($result as $row) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e2) {
        echo "<p style='color:red'>Exception avec SHOW COLUMNS: " . $e2->getMessage() . "</p>";
    }
}

// Vérifier les utilisateurs existants
echo "<h2>Utilisateurs existants</h2>";
try {
    $users = db_query("SELECT id, nom, prenom, email, role FROM utilisateurs");
    if ($users === false || empty($users)) {
        echo "<p style='color:orange'>Aucun utilisateur trouvé dans la base de données.</p>";
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
    echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
}

// Tester l'insertion d'un utilisateur
echo "<h2>Test d'insertion d'un utilisateur</h2>";
try {
    // Vérifier si l'utilisateur de test existe déjà
    $test_email = 'test_enseignant@example.com';
    $existing_user = db_query_single("SELECT id FROM utilisateurs WHERE email = ?", [$test_email]);
    
    if ($existing_user) {
        echo "<p style='color:orange'>L'utilisateur de test existe déjà (ID: {$existing_user['id']}). Suppression pour réessayer...</p>";
        $delete_result = db_exec("DELETE FROM utilisateurs WHERE email = ?", [$test_email]);
        if ($delete_result) {
            echo "<p style='color:green'>Utilisateur de test supprimé avec succès.</p>";
        } else {
            echo "<p style='color:red'>Échec de la suppression de l'utilisateur de test.</p>";
        }
    }
    
    // Tenter d'insérer un utilisateur de test
    $password_hash = password_hash('testpassword', PASSWORD_DEFAULT);
    $insert_result = db_exec(
        "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)",
        ['Test', 'Enseignant', $test_email, $password_hash, 'enseignant']
    );
    
    if ($insert_result) {
        $new_id = db_last_insert_id();
        echo "<p style='color:green'>Insertion réussie! Nouvel ID: {$new_id}</p>";
    } else {
        echo "<p style='color:red'>Échec de l'insertion.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exception lors du test d'insertion: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
?>
