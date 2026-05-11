<?php
// Script de vérification des extensions PHP nécessaires pour MySQL
header('Content-Type: text/plain; charset=utf-8');
echo "Vérification des extensions PHP nécessaires (mysqli, pdo_mysql) :\n";
echo "mysqli : ";
echo extension_loaded('mysqli') ? 'OK' : 'ABSENT';
echo "\n";
echo "pdo_mysql : ";
echo extension_loaded('pdo_mysql') ? 'OK' : 'ABSENT';
echo "\n\n";

if (!extension_loaded('mysqli') || !extension_loaded('pdo_mysql')) {
    echo "ERREUR : Les extensions PHP nécessaires ne sont pas chargées.\n";
    echo "Corrigez votre configuration WAMP/XAMPP (php.ini) pour activer :\n";
    echo "  - extension=php_mysqli.dll\n  - extension=php_pdo_mysql.dll\n";
    echo "Redémarrez ensuite tous les services Apache/WAMP.\n";
    exit(1);
} else {
    echo "Toutes les extensions nécessaires sont chargées.\n";
}
