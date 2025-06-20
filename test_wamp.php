<?php
/**
 * Test de configuration WAMP - Système de Gestion de Présence
 * Ce fichier vérifie la configuration et affiche les informations nécessaires
 */

// Configuration pour éviter les erreurs d'affichage
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Configuration WAMP - Gestion de Présence</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .code { background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; font-family: monospace; }
        h1 { color: #333; text-align: center; }
        h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .ip-box { background: #e9ecef; padding: 15px; border-radius: 4px; font-size: 18px; font-weight: bold; text-align: center; }
        .test-item { margin: 15px 0; padding: 10px; border-left: 4px solid #ccc; }
        .test-ok { border-left-color: #28a745; }
        .test-error { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test Configuration WAMP</h1>
        <h2>Système de Gestion de Présence DUT</h2>

        <?php
        // Fonction pour obtenir l'IP locale
        function getLocalIP() {
            $ips = [];
            
            // Méthode 1: $_SERVER
            if (!empty($_SERVER['SERVER_ADDR'])) {
                $ips[] = $_SERVER['SERVER_ADDR'];
            }
            
            // Méthode 2: Commande système
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('ipconfig');
                if (preg_match_all('/IPv4[^\:]*\:\s*([0-9\.]+)/', $output, $matches)) {
                    foreach ($matches[1] as $ip) {
                        if ($ip !== '127.0.0.1') {
                            $ips[] = $ip;
                        }
                    }
                }
            } else {
                $output = shell_exec('hostname -I');
                if ($output) {
                    $ips = array_merge($ips, explode(' ', trim($output)));
                }
            }
            
            // Filtrer les IPs locales valides
            $validIPs = [];
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false && 
                    filter_var($ip, FILTER_VALIDATE_IP) !== false && 
                    $ip !== '127.0.0.1') {
                    $validIPs[] = $ip;
                }
            }
            
            return array_unique($validIPs);
        }

        // Tests de configuration
        $tests = [];
        
        // Test PHP
        $tests['php'] = [
            'name' => 'Version PHP',
            'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'message' => 'PHP ' . PHP_VERSION . (version_compare(PHP_VERSION, '7.4.0', '>=') ? ' ✓' : ' (Minimum 7.4 requis)')
        ];

        // Test MySQL
        $tests['mysql'] = [
            'name' => 'Extension MySQL',
            'status' => extension_loaded('mysqli') || extension_loaded('pdo_mysql'),
            'message' => extension_loaded('mysqli') || extension_loaded('pdo_mysql') ? 'MySQL disponible ✓' : 'MySQL non disponible ✗'
        ];

        // Test écriture
        $tests['write'] = [
            'name' => 'Permissions écriture',
            'status' => is_writable(__DIR__),
            'message' => is_writable(__DIR__) ? 'Écriture autorisée ✓' : 'Pas de permission d\'écriture ✗'
        ];

        // Test session
        $tests['session'] = [
            'name' => 'Sessions PHP',
            'status' => function_exists('session_start'),
            'message' => function_exists('session_start') ? 'Sessions disponibles ✓' : 'Sessions non disponibles ✗'
        ];

        // Affichage des tests
        echo "<h2>🔍 Diagnostic Système</h2>";
        foreach ($tests as $test) {
            $class = $test['status'] ? 'test-ok success' : 'test-error error';
            echo "<div class='test-item $class'>";
            echo "<strong>{$test['name']}:</strong> {$test['message']}";
            echo "</div>";
        }

        // Obtenir les IPs
        $localIPs = getLocalIP();
        
        echo "<h2>🌐 Configuration Réseau</h2>";
        
        if (!empty($localIPs)) {
            echo "<div class='success'>";
            echo "<strong>IP(s) locale(s) détectée(s):</strong><br>";
            foreach ($localIPs as $ip) {
                echo "<div class='ip-box'>$ip</div>";
            }
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<strong>📋 Instructions de configuration:</strong><br>";
            echo "1. Copiez l'une des IP ci-dessus<br>";
            echo "2. Ouvrez le fichier <code>config_reseau.php</code><br>";
            echo "3. Remplacez la ligne: <code>define('IP_POINT_ACCES', '192.168.1.XX');</code><br>";
            echo "4. Par: <code>define('IP_POINT_ACCES', 'VOTRE_IP');</code>";
            echo "</div>";
        } else {
            echo "<div class='warning'>";
            echo "<strong>⚠️ Aucune IP locale détectée automatiquement</strong><br>";
            echo "Utilisez <code>ipconfig</code> (Windows) ou <code>ifconfig</code> (Linux/Mac) pour trouver votre IP.";
            echo "</div>";
        }

        // Test de connexion base de données
        echo "<h2>🗄️ Base de Données</h2>";
        
        try {
            // Essayer la connexion MySQL locale
            $host = 'localhost';
            $dbname = 'attendance_system';
            $username = 'root';
            $password = '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            echo "<div class='success'>✅ Connexion à la base de données réussie!</div>";
            
            // Vérifier les tables
            $tables = ['utilisateurs', 'cours', 'etudiants', 'inscriptions', 'presences'];
            $existing_tables = [];
            
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    $existing_tables[] = $table;
                }
            }
            
            if (count($existing_tables) == count($tables)) {
                echo "<div class='success'>✅ Toutes les tables sont présentes</div>";
            } else {
                echo "<div class='warning'>⚠️ Tables manquantes: " . implode(', ', array_diff($tables, $existing_tables)) . "</div>";
                echo "<div class='info'>Importez le fichier <code>database/mysql_schema.sql</code> dans phpMyAdmin</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</div>";
            echo "<div class='info'>";
            echo "<strong>Pour configurer la base de données:</strong><br>";
            echo "1. Ouvrir phpMyAdmin: <code>http://localhost/phpmyadmin</code><br>";
            echo "2. Créer une base de données nommée: <code>attendance_system</code><br>";
            echo "3. Choisir l'interclassement: <code>utf8mb4_unicode_ci</code><br>";
            echo "4. Importer le fichier: <code>database/mysql_schema.sql</code>";
            echo "</div>";
        }

        // URLs de test
        echo "<h2>🔗 URLs de Test</h2>";
        echo "<div class='info'>";
        echo "<strong>Accès local:</strong><br>";
        echo "• <a href='http://localhost/attendance_system/' target='_blank'>http://localhost/attendance_system/</a><br><br>";
        
        if (!empty($localIPs)) {
            echo "<strong>Accès réseau (pour les étudiants):</strong><br>";
            foreach ($localIPs as $ip) {
                echo "• <a href='http://$ip/attendance_system/' target='_blank'>http://$ip/attendance_system/</a><br>";
            }
        }
        echo "</div>";

        // Comptes de test
        echo "<h2>👥 Comptes de Test</h2>";
        echo "<div class='info'>";
        echo "<strong>Administrateur:</strong><br>";
        echo "• Email: <code>chris552352@gmail.com</code><br>";
        echo "• Mot de passe: <code>552352</code><br><br>";
        echo "<strong>Enseignants:</strong><br>";
        echo "• <code>marie.dupont@example.com</code> / <code>password</code><br>";
        echo "• <code>jean.martin@example.com</code> / <code>password</code><br><br>";
        echo "<strong>Étudiants (exemples):</strong><br>";
        echo "• <code>angesimo@gmail.com</code> / <code>sim045</code><br>";
        echo "• <code>Ateba@gmail.com</code> / <code>ate022</code><br>";
        echo "• Les étudiants peuvent créer leurs comptes via l'interface d'inscription<br>";
        echo "</div>";

        // Informations système
        echo "<h2>ℹ️ Informations Système</h2>";
        echo "<div class='code'>";
        echo "Système: " . PHP_OS . "<br>";
        echo "Serveur: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
        echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
        echo "Répertoire actuel: " . __DIR__ . "<br>";
        echo "Date/Heure: " . date('Y-m-d H:i:s');
        echo "</div>";
        ?>

        <div class="success" style="text-align: center; margin-top: 30px;">
            <h3>🎯 Système prêt pour votre soutenance DUT!</h3>
            <p>Toutes les fonctionnalités sécurisées sont conservées:<br>
            ✓ Authentification • ✓ QR Code temporisé • ✓ Validation unique • ✓ Gestion des présences</p>
        </div>
    </div>
</body>
</html>