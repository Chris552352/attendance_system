<?php
// Script de nettoyage total de la base attendance_system
require_once 'config/database.php';
$tables = ['presences','inscriptions','seances','cours','etudiants','comptes_etudiants','utilisateurs'];
foreach ($tables as $t) {
    try {
        db_exec("DROP TABLE IF EXISTS `$t` CASCADE;");
        echo "Suppression: $t<br>";
    } catch(Exception $e) {
        echo "Erreur suppression $t: ".htmlspecialchars($e->getMessage())."<br>";
    }
}
echo "<b>Nettoyage terminé.</b>";
