<?php
// Script pour corriger la table 'etudiants' en ajoutant les colonnes manquantes
require_once 'config/database.php';

// Script pour corriger la table 'etudiants' en ajoutant les colonnes manquantes (version PDO)
require_once 'config/database.php';

// Colonnes à ajouter
$columns = [
    'telephone' => "VARCHAR(30) DEFAULT NULL",
    'date_naissance' => "DATE DEFAULT NULL",
    'adresse' => "VARCHAR(255) DEFAULT NULL"
];

foreach ($columns as $col => $def) {
    try {
        $col = preg_replace('/[^a-zA-Z0-9_]/', '', $col); // Sécurité minimale
        $sqlCheck = "SHOW COLUMNS FROM etudiants LIKE '$col'";
        $exists = $pdo->query($sqlCheck)->fetch();
        if (!$exists) {
            $sql = "ALTER TABLE etudiants ADD COLUMN `$col` $def;";
            $pdo->exec($sql);
            echo "Colonne '$col' ajoutée avec succès.<br>";
        } else {
            echo "Colonne '$col' existe déjà.<br>";
        }
    } catch (PDOException $e) {
        echo "Erreur lors de l'ajout de la colonne '$col' : " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

echo '<br>Correction terminée. Vous pouvez supprimer ce script.';

