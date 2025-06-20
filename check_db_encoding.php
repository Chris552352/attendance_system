<?php
/**
 * Script pour vu00e9rifier et corriger l'encodage de la base de donnu00e9es
 */

// Inclure les fichiers nu00e9cessaires
require_once 'config/database.php';

// Fonction pour afficher un message
function show_message($message, $type = 'info') {
    $class = $type === 'success' ? 'alert-success' : ($type === 'danger' ? 'alert-danger' : 'alert-info');
    echo "<div class='alert $class'>$message</div>";
}

// En-tu00eate HTML
echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Vu00e9rification de l'encodage de la base de donnu00e9es</title>\n    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>\n    <style>\n        body { padding: 20px; }\n        .card { margin-bottom: 20px; }\n    </style>\n</head>\n<body>\n    <div class='container'>\n        <h1 class='mb-4'>Vu00e9rification de l'encodage de la base de donnu00e9es</h1>";

// Vu00e9rifier l'encodage actuel de la base de donnu00e9es
$db_info = db_query("SHOW VARIABLES LIKE 'character_set%'");
$collation_info = db_query("SHOW VARIABLES LIKE 'collation%'");

echo "<div class='card'>\n    <div class='card-header bg-primary text-white'>\n        <h5 class='card-title mb-0'>Configuration actuelle de MySQL</h5>\n    </div>\n    <div class='card-body'>\n        <h6>Jeux de caractu00e8res:</h6>\n        <table class='table table-striped'>\n            <thead>\n                <tr>\n                    <th>Variable</th>\n                    <th>Valeur</th>\n                </tr>\n            </thead>\n            <tbody>";

foreach ($db_info as $info) {
    echo "<tr>\n                <td>{$info['Variable_name']}</td>\n                <td>{$info['Value']}</td>\n            </tr>";
}

echo "</tbody>\n        </table>\n        \n        <h6>Collations:</h6>\n        <table class='table table-striped'>\n            <thead>\n                <tr>\n                    <th>Variable</th>\n                    <th>Valeur</th>\n                </tr>\n            </thead>\n            <tbody>";

foreach ($collation_info as $info) {
    echo "<tr>\n                <td>{$info['Variable_name']}</td>\n                <td>{$info['Value']}</td>\n            </tr>";
}

echo "</tbody>\n        </table>\n    </div>\n</div>";

// Vu00e9rifier l'encodage des tables
$tables = db_query("SHOW TABLES");
$tables_info = [];

foreach ($tables as $table_row) {
    $table = reset($table_row);
    $table_info = db_query_single("SHOW TABLE STATUS LIKE ?", [$table]);
    $tables_info[] = [
        'name' => $table,
        'collation' => $table_info['Collation']
    ];
}

echo "<div class='card'>\n    <div class='card-header bg-primary text-white'>\n        <h5 class='card-title mb-0'>Encodage des tables</h5>\n    </div>\n    <div class='card-body'>\n        <table class='table table-striped'>\n            <thead>\n                <tr>\n                    <th>Table</th>\n                    <th>Collation</th>\n                    <th>Statut</th>\n                </tr>\n            </thead>\n            <tbody>";

foreach ($tables_info as $info) {
    $is_utf8 = (strpos($info['collation'], 'utf8') === 0);
    $status_class = $is_utf8 ? 'text-success' : 'text-danger';
    $status_text = $is_utf8 ? 'OK' : 'Probu00e8me d\'encodage';
    
    echo "<tr>\n                <td>{$info['name']}</td>\n                <td>{$info['collation']}</td>\n                <td class='$status_class'>$status_text</td>\n            </tr>";
}

echo "</tbody>\n        </table>\n    </div>\n</div>";

// Proposer des corrections
echo "<div class='card'>\n    <div class='card-header bg-primary text-white'>\n        <h5 class='card-title mb-0'>Corriger l'encodage</h5>\n    </div>\n    <div class='card-body'>";

// Vu00e9rifier si une action a u00e9tu00e9 demandu00e9e
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'fix_db_connection') {
        // Modifier le fichier de configuration de la base de donnu00e9es
        $db_file = 'config/database.php';
        $db_content = file_get_contents($db_file);
        
        // Vu00e9rifier si le fichier contient du00e9ju00e0 des instructions pour l'encodage
        if (strpos($db_content, 'SET NAMES utf8') === false) {
            // Chercher la fonction de connexion
            $pattern = '/function\s+db_connect\s*\(\s*\)\s*\{[^}]*\}/s';
            
            if (preg_match($pattern, $db_content, $matches)) {
                $old_function = $matches[0];
                
                // Ajouter l'instruction SET NAMES utf8
                $new_function = str_replace(
                    'return $pdo;',
                    '$pdo->exec("SET NAMES utf8");\n    return $pdo;',
                    $old_function
                );
                
                $db_content = str_replace($old_function, $new_function, $db_content);
                file_put_contents($db_file, $db_content);
                
                show_message("La connexion u00e0 la base de donnu00e9es a u00e9tu00e9 modifiu00e9e pour utiliser l'encodage UTF-8.", 'success');
            } else {
                show_message("Impossible de trouver la fonction de connexion u00e0 la base de donnu00e9es.", 'danger');
            }
        } else {
            show_message("La connexion u00e0 la base de donnu00e9es utilise du00e9ju00e0 l'encodage UTF-8.", 'info');
        }
    } elseif ($action === 'fix_tables') {
        // Convertir toutes les tables en UTF-8
        $success = true;
        
        foreach ($tables_info as $info) {
            if (strpos($info['collation'], 'utf8') !== 0) {
                $result = db_exec("ALTER TABLE {$info['name']} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                if (!$result) {
                    $success = false;
                    show_message("Erreur lors de la conversion de la table {$info['name']}.", 'danger');
                    break;
                }
            }
        }
        
        if ($success) {
            show_message("Toutes les tables ont u00e9tu00e9 converties en UTF-8 avec succu00e8s.", 'success');
        }
    }
}

// Formulaire pour corriger la connexion u00e0 la base de donnu00e9es
echo "<form method='post' class='mb-4'>\n            <input type='hidden' name='action' value='fix_db_connection'>\n            <p>Ajouter l'instruction <code>SET NAMES utf8</code> u00e0 la connexion u00e0 la base de donnu00e9es pour assurer que les donnu00e9es sont correctement encodu00e9es.</p>\n            <button type='submit' class='btn btn-primary'>Corriger la connexion u00e0 la base de donnu00e9es</button>\n        </form>";

// Formulaire pour convertir les tables en UTF-8
echo "<form method='post'>\n            <input type='hidden' name='action' value='fix_tables'>\n            <p>Convertir toutes les tables en UTF-8 pour assurer que les donnu00e9es sont correctement encodu00e9es.</p>\n            <div class='alert alert-warning'>\n                <strong>Attention:</strong> Cette action va modifier la structure de vos tables. Assurez-vous d'avoir une sauvegarde de votre base de donnu00e9es avant de continuer.\n            </div>\n            <button type='submit' class='btn btn-danger'>Convertir les tables en UTF-8</button>\n        </form>";

echo "    </div>\n</div>";

// Conseils pour phpMyAdmin
echo "<div class='card'>\n    <div class='card-header bg-primary text-white'>\n        <h5 class='card-title mb-0'>Configuration de phpMyAdmin</h5>\n    </div>\n    <div class='card-body'>\n        <p>Si vous utilisez phpMyAdmin, vous pouvez u00e9galement configurer l'encodage dans l'interface:</p>\n        <ol>\n            <li>Connectez-vous u00e0 phpMyAdmin</li>\n            <li>Su00e9lectionnez votre base de donnu00e9es</li>\n            <li>Cliquez sur l'onglet 'Opu00e9rations'</li>\n            <li>Dans la section 'Collation', su00e9lectionnez 'utf8mb4_unicode_ci'</li>\n            <li>Cliquez sur 'Exu00e9cuter'</li>\n        </ol>\n        <p>Pour les nouvelles tables, assurez-vous de su00e9lectionner 'utf8mb4_unicode_ci' comme collation lors de leur cru00e9ation.</p>\n    </div>\n</div>";

// Pied de page HTML
echo "</div>\n    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>\n</body>\n</html>";
?>
