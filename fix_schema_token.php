<?php
// Script pour corriger le schéma de la table 'seances' :
// supprime la clé UNIQUE redondante sur 'token' si elle existe
require_once 'config/database.php';

try {
    // Vérifier si la clé existe
    $sql = "SHOW INDEX FROM seances WHERE Key_name = 'token' AND Non_unique = 0";
    $result = db_query($sql);
    if ($result && count($result) > 0) {
        // Supprimer la clé UNIQUE
        db_exec("ALTER TABLE seances DROP INDEX token");
        echo "<b>Clé UNIQUE 'token' supprimée de la table 'seances'.</b><br>";
    } else {
        echo "<b>Aucune clé UNIQUE nommée 'token' à supprimer.</b><br>";
    }
} catch(Exception $e) {
    echo "<b>Erreur :</b> ".htmlspecialchars($e->getMessage());
}

echo '<a href="reset_database.php">Retour à la réinitialisation</a>';
