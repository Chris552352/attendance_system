<?php
/**
 * Script pour mettre à jour la structure de la base de données
 */

require_once 'config/database.php';

// Sélectionner la base de données
$sql0 = "USE attendance_system";
db_exec($sql0);

// Ajouter les colonnes pour la justification d'absence
$sql1 = "ALTER TABLE presences ADD COLUMN IF NOT EXISTS justification TEXT NULL AFTER statut";
$sql2 = "ALTER TABLE presences ADD COLUMN IF NOT EXISTS justifie BOOLEAN DEFAULT FALSE AFTER justification";

// Exécuter les requêtes
$result1 = db_exec($sql1);
$result2 = db_exec($sql2);

if ($result1 && $result2) {
    echo "<p>La base de données a été mise à jour avec succès.</p>";
} else {
    echo "<p>Erreur lors de la mise à jour de la base de données.</p>";
}

/**
 * Script de mise à jour de la base de données
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Vérifier si les tables existent
$tables = db_query("SHOW TABLES");
$table_names = array_column($tables, 'Tables_in_' . $dbname);

if (!in_array('utilisateurs', $table_names)) {
    // Créer la table utilisateurs
    $sql = "CREATE TABLE utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        role ENUM('admin', 'enseignant') NOT NULL DEFAULT 'enseignant',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (db_exec($sql)) {
        echo "<p>Table utilisateurs créée avec succès.</p>";
    } else {
        echo "<p>Erreur lors de la mise à jour de la base de données.</p>";
    }
}

// Vérifier si la table étudiants existe
$tables = db_query("SHOW TABLES LIKE 'étudiants'");

if (empty($tables)) {
    // Créer la table étudiants
    $sql = "CREATE TABLE étudiants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matricule VARCHAR(20) NOT NULL,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        telephone VARCHAR(20) NULL,
        date_naissance DATE NULL,
        adresse TEXT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (db_exec($sql)) {
        echo "<p>Table étudiants créée avec succès.</p>";
    } else {
        echo "<p>Erreur lors de la création de la table étudiants: " . db_error() . "</p>";
    }
}

// Vérifier si la colonne téléphone existe dans la table étudiants
$check_telephone = db_query("SHOW COLUMNS FROM étudiants LIKE 'telephone'");

if (empty($check_telephone)) {
    // Ajouter la colonne téléphone
    $sql = "ALTER TABLE étudiants ADD COLUMN telephone VARCHAR(20) NULL AFTER email";
    
    if (db_exec($sql)) {
        echo "<p>Colonne téléphone ajoutée avec succès.</p>";
    } else {
        echo "<p>Erreur lors de l'ajout de la colonne téléphone: " . db_error() . "</p>";
    }
}

// Vérifier si la colonne date_naissance existe dans la table étudiants
$check_date_naissance = db_query("SHOW COLUMNS FROM étudiants LIKE 'date_naissance'");

if (empty($check_date_naissance)) {
    // Ajouter la colonne date_naissance
    $sql = "ALTER TABLE étudiants ADD COLUMN date_naissance DATE NULL AFTER telephone";
    
    if (db_exec($sql)) {
        echo "<p>Colonne date_naissance ajoutée avec succès.</p>";
    } else {
        echo "<p>Erreur lors de l'ajout de la colonne date_naissance: " . db_error() . "</p>";
    }
}

// Vérifier si la colonne adresse existe dans la table étudiants
$check_adresse = db_query("SHOW COLUMNS FROM étudiants LIKE 'adresse'");

if (empty($check_adresse)) {
    // Ajouter la colonne adresse
    $sql = "ALTER TABLE étudiants ADD COLUMN adresse TEXT NULL AFTER date_naissance";
    
    if (db_exec($sql)) {
        echo "<p>Colonne adresse ajoutée avec succès.</p>";
    } else {
        echo "<p>Erreur lors de l'ajout de la colonne adresse: " . db_error() . "</p>";
    }
}

// Vérifier si l'administrateur existe déjà
$admin = db_query_single("SELECT * FROM utilisateurs WHERE email = 'chris552352@gmail.com'");

if (!$admin) {
    // Créer l'administrateur s'il n'existe pas
    $password_hash = password_hash('552352', PASSWORD_DEFAULT);
    $result3 = db_exec(
        "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)",
        ['Nounamo', 'Chris', 'chris552352@gmail.com', $password_hash, 'admin']
    );
    
    if ($result3) {
        echo "<p>L'administrateur a été créé avec succès.</p>";
    } else {
        echo "<p>Erreur lors de la création de l'administrateur.</p>";
    }
} else {
    // Mettre à jour le rôle et le mot de passe de l'administrateur
    $password_hash = password_hash('552352', PASSWORD_DEFAULT);
    $result3 = db_exec(
        "UPDATE utilisateurs SET mot_de_passe = ?, role = ? WHERE email = ?",
        [$password_hash, 'admin', 'chris552352@gmail.com']
    );
    
    if ($result3) {
        echo "<p>Les informations de l'administrateur ont été mises à jour.</p>";
    } else {
        echo "<p>Erreur lors de la mise à jour des informations de l'administrateur.</p>";
    }
}

echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
?>
