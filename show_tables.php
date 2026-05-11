<?php
require_once 'config/database.php';

try {
    $tables = db_query("SHOW TABLES");
    echo "<h2>Tables présentes dans la base attendance_system :</h2><ul>";
    foreach ($tables as $row) {
        foreach ($row as $table) {
            echo "<li>".htmlspecialchars($table)."</li>";
        }
    }
    echo "</ul>";
} catch(Exception $e) {
    echo "Erreur : ".htmlspecialchars($e->getMessage());
}
