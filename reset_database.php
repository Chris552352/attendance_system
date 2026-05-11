<?php
// Script de réinitialisation complète de la base de données à partir du schéma SQL
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/database.php';

function drop_all_tables($pdo) {
    $tables = ['presences','inscriptions','seances','cours','etudiants','comptes_etudiants','utilisateurs'];
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$table` CASCADE;");
            echo "<li>Table supprimée : <b>$table</b></li>\n";
        } catch (Exception $e) {
            echo "<li style='color:red'>Erreur suppression $table : ".htmlspecialchars($e->getMessage())."</li>\n";
        }
    }
}

function import_schema($pdo, $schema_file) {
    $sql = file_get_contents($schema_file);
    if ($sql === false) {
        echo "<p style='color:red'>Impossible de lire le fichier $schema_file</p>";
        return false;
    }
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>Schéma importé avec succès depuis $schema_file</p>";
        return true;
    } catch (Exception $e) {
        echo "<p style='color:red'>Erreur lors de l'import du schéma : ".htmlspecialchars($e->getMessage())."</p>";
        return false;
    }
}

// Exécution
try {
    global $pdo;
    echo "<h2>Suppression des tables existantes...</h2><ul>";
    drop_all_tables($pdo);
    echo "</ul>";
    echo "<h2>Import du schéma mysql_schema.sql...</h2>";
    $schema_path = __DIR__ . '/database/mysql_schema.sql';
    import_schema($pdo, $schema_path);
    echo "<hr><b>Réinitialisation terminée.</b>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erreur critique : ".htmlspecialchars($e->getMessage())."</p>";
}
