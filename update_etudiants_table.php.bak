<?php
/**
 * Script pour mettre à jour la structure de la table étudiants
 */

require_once 'config/database.php';

// Sélectionner la base de données
$sql0 = "USE attendance_system";
db_exec($sql0);

// Vérifier si les colonnes existent déjà
$result = db_query("DESCRIBE etudiants");
$existing_fields = [];

foreach ($result as $field) {
    $existing_fields[] = $field['Field'];
}

// Ajouter les colonnes manquantes
$columns_to_add = [
    'telephone' => "ALTER TABLE etudiants ADD COLUMN telephone VARCHAR(20) NULL AFTER email",
    'date_naissance' => "ALTER TABLE etudiants ADD COLUMN date_naissance DATE NULL AFTER telephone",
    'adresse' => "ALTER TABLE etudiants ADD COLUMN adresse TEXT NULL AFTER date_naissance"
];

$success = true;
$messages = [];

foreach ($columns_to_add as $column => $sql) {
    if (!in_array($column, $existing_fields)) {
        $result = db_exec($sql);
        if ($result) {
            $messages[] = "La colonne '$column' a été ajoutée avec succès.";
        } else {
            $success = false;
            $messages[] = "Erreur lors de l'ajout de la colonne '$column': " . db_error();
        }
    } else {
        $messages[] = "La colonne '$column' existe déjà.";
    }
}

// Afficher les résultats
echo "<h2>Mise à jour de la table étudiants</h2>";

if ($success) {
    echo "<p style='color:green'>La structure de la table étudiants a été mise à jour avec succès.</p>";
} else {
    echo "<p style='color:red'>Des erreurs se sont produites lors de la mise à jour de la table étudiants.</p>";
}

echo "<ul>";
foreach ($messages as $message) {
    echo "<li>$message</li>";
}
echo "</ul>";

echo "<p><a href='etudiants.php'>Retour à la liste des étudiants</a></p>";
?>
