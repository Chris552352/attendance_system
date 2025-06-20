<?php
/**
 * Script direct pour ajouter un enseignant
 * Ce script ajoute directement un enseignant sans formulaire
 */

// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure la configuration de la base de données
require_once 'config/database.php';

// Données de l'enseignant u00e0 ajouter
$nom = 'Nouvel';
$prenom = 'Enseignant';
$email = 'nouvel.enseignant@example.com';
$password = '12345678'; // Mot de passe simple pour le test
$password_hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h1>Ajout direct d'un enseignant</h1>";

// Vérifier si l'enseignant existe déjà
$sql_check = "SELECT id FROM utilisateurs WHERE email = ?";
$params_check = [$email];

try {
    $existing = false;
    
    // Vérification avec MySQLi directement
    if ($db_type === 'mysql' && $mysqli) {
        $stmt = $mysqli->prepare($sql_check);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $existing = true;
            }
            $stmt->close();
        }
    }
    // Vérification avec PDO pour PostgreSQL
    else if ($db_type === 'postgresql' && $pdo) {
        $stmt = $pdo->prepare($sql_check);
        $stmt->execute($params_check);
        if ($stmt->rowCount() > 0) {
            $existing = true;
        }
    }
    
    if ($existing) {
        echo "<p style='color:orange'>Un enseignant avec l'email '$email' existe déjà. Suppression pour réessayer...</p>";
        
        // Supprimer l'utilisateur existant
        $sql_delete = "DELETE FROM utilisateurs WHERE email = ?";
        $params_delete = [$email];
        
        if ($db_type === 'mysql' && $mysqli) {
            $stmt = $mysqli->prepare($sql_delete);
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                echo "<p>Utilisateur supprimé.</p>";
                $stmt->close();
            }
        }
        else if ($db_type === 'postgresql' && $pdo) {
            $stmt = $pdo->prepare($sql_delete);
            $stmt->execute($params_delete);
            echo "<p>Utilisateur supprimé.</p>";
        }
    }
    
    // Insérer le nouvel enseignant
    $sql_insert = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'enseignant')";
    $params_insert = [$nom, $prenom, $email, $password_hash];
    
    $inserted = false;
    $insert_id = 0;
    
    // Insertion avec MySQLi
    if ($db_type === 'mysql' && $mysqli) {
        echo "<h2>Tentative d'insertion avec MySQLi</h2>";
        $stmt = $mysqli->prepare($sql_insert);
        if ($stmt) {
            $stmt->bind_param("ssss", $nom, $prenom, $email, $password_hash);
            if ($stmt->execute()) {
                $inserted = true;
                $insert_id = $mysqli->insert_id;
            } else {
                echo "<p style='color:red'>Erreur lors de l'insertion : " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color:red'>Erreur de préparation de la requête : " . $mysqli->error . "</p>";
        }
    }
    // Insertion avec PDO pour PostgreSQL
    else if ($db_type === 'postgresql' && $pdo) {
        echo "<h2>Tentative d'insertion avec PDO</h2>";
        $stmt = $pdo->prepare($sql_insert);
        if ($stmt->execute($params_insert)) {
            $inserted = true;
            $insert_id = $pdo->lastInsertId();
        } else {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            L'insertion a échoué.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Exception : " . $e->getMessage() . "
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
        echo "<p><em>Veuillez noter ces informations de connexion pour les communiquer u00e0 l'enseignant.</em></p>";
        echo "</div>";
    } else {
        echo "<p style='color:red'>L'insertion a u00e9choué.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Exception : " . $e->getMessage() . "</p>";
}

// Afficher les informations de débogage sur la base de données
echo "<h2>Informations sur la base de données</h2>";
echo "<p><strong>Type de base de données:</strong> $db_type</p>";

if ($db_type === 'mysql') {
    echo "<p><strong>Version MySQL:</strong> " . $mysqli->server_info . "</p>";
    echo "<p><strong>Jeu de caractères:</strong> " . $mysqli->character_set_name() . "</p>";
} else if ($db_type === 'postgresql') {
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "<p><strong>Version PostgreSQL:</strong> $version</p>";
}

echo "<p><a href='enseignants.php'>Retour u00e0 la liste des enseignants</a></p>";
echo "<p><a href='index.php'>Retour u00e0 l'accueil</a></p>";
?>
