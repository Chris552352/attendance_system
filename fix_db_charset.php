<?php
/**
 * Script pour corriger l'encodage de la base de donnu00e9es
 */

// Inclure les fichiers nu00e9cessaires
require_once 'config/database.php';

// Fonction pour afficher un message
function show_message($message, $type = 'info') {
    $class = $type === 'success' ? 'alert-success' : ($type === 'danger' ? 'alert-danger' : 'alert-info');
    echo "<div class='alert $class'>$message</div>";
}

// En-tu00eate HTML
echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Correction de l'encodage de la base de donnu00e9es</title>\n    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>\n    <style>\n        body { padding: 20px; }\n        .card { margin-bottom: 20px; }\n    </style>\n</head>\n<body>\n    <div class='container'>\n        <h1 class='mb-4'>Correction de l'encodage de la base de donnu00e9es</h1>";

// Vu00e9rifier si une action a u00e9tu00e9 demandu00e9e
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'fix_db_connection') {
        // Modifier le fichier de configuration de la base de donnu00e9es
        $db_file = 'config/database.php';
        if (file_exists($db_file)) {
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
        } else {
            show_message("Le fichier de configuration de la base de donnu00e9es n'a pas u00e9tu00e9 trouvu00e9.", 'danger');
        }
    } elseif ($action === 'fix_tables') {
        // Convertir toutes les tables en UTF-8
        $tables = [];
        try {
            $result = db_query("SHOW TABLES");
            foreach ($result as $row) {
                $tables[] = reset($row);
            }
        } catch (Exception $e) {
            show_message("Erreur lors de la ru00e9cupu00e9ration des tables: " . $e->getMessage(), 'danger');
        }
        
        $success = true;
        $converted = 0;
        
        foreach ($tables as $table) {
            try {
                db_exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $converted++;
            } catch (Exception $e) {
                $success = false;
                show_message("Erreur lors de la conversion de la table $table: " . $e->getMessage(), 'danger');
            }
        }
        
        if ($success) {
            show_message("$converted tables ont u00e9tu00e9 converties en UTF-8 avec succu00e8s.", 'success');
        }
    } elseif ($action === 'fix_data') {
        // Corriger les donnu00e9es avec des caractu00e8res encodu00e9s
        $tables = [];
        try {
            $result = db_query("SHOW TABLES");
            foreach ($result as $row) {
                $tables[] = reset($row);
            }
        } catch (Exception $e) {
            show_message("Erreur lors de la ru00e9cupu00e9ration des tables: " . $e->getMessage(), 'danger');
        }
        
        $success = true;
        $fixed_fields = 0;
        
        foreach ($tables as $table) {
            try {
                // Ru00e9cupu00e9rer les colonnes de type texte
                $columns = db_query("SHOW COLUMNS FROM `$table`");
                
                foreach ($columns as $column) {
                    $column_name = $column['Field'];
                    $column_type = $column['Type'];
                    
                    // Vu00e9rifier si c'est une colonne de type texte
                    if (strpos($column_type, 'char') !== false || 
                        strpos($column_type, 'text') !== false || 
                        strpos($column_type, 'varchar') !== false) {
                        
                        // Remplacer les caractu00e8res encodu00e9s
                        $replacements = [
                            'u00e0' => 'u00e0', // à
                            'u00e2' => 'u00e2', // â
                            'u00e7' => 'u00e7', // ç
                            'u00e8' => 'u00e8', // è
                            'u00e9' => 'u00e9', // é
                            'u00ea' => 'u00ea', // ê
                            'u00eb' => 'u00eb', // ë
                            'u00ee' => 'u00ee', // î
                            'u00ef' => 'u00ef', // ï
                            'u00f4' => 'u00f4', // ô
                            'u00f9' => 'u00f9', // ù
                            'u00fb' => 'u00fb', // û
                            'u00fc' => 'u00fc', // ü
                            'u00c0' => 'u00c0', // À
                            'u00c7' => 'u00c7', // Ç
                            'u00c9' => 'u00c9', // É
                            'u00ca' => 'u00ca', // Ê
                        ];
                        
                        foreach ($replacements as $encoded => $decoded) {
                            try {
                                $query = "UPDATE `$table` SET `$column_name` = REPLACE(`$column_name`, '$encoded', '$decoded') WHERE `$column_name` LIKE '%$encoded%'";
                                $affected = db_exec($query);
                                if ($affected > 0) {
                                    $fixed_fields += $affected;
                                }
                            } catch (Exception $e) {
                                // Ignorer les erreurs et continuer
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $success = false;
                show_message("Erreur lors de la correction des donnu00e9es dans la table $table: " . $e->getMessage(), 'danger');
            }
        }
        
        if ($success) {
            show_message("$fixed_fields champs ont u00e9tu00e9 corrigu00e9s avec succu00e8s.", 'success');
        }
    }
}

// Formulaires pour les diffu00e9rentes actions
echo "<div class='card'>\n    <div class='card-header bg-primary text-white'>\n        <h5 class='card-title mb-0'>Corriger l'encodage</h5>\n    </div>\n    <div class='card-body'>";

// Formulaire pour corriger la connexion u00e0 la base de donnu00e9es
echo "<form method='post' class='mb-4'>\n            <input type='hidden' name='action' value='fix_db_connection'>\n            <p>Ajouter l'instruction <code>SET NAMES utf8</code> u00e0 la connexion u00e0 la base de donnu00e9es pour assurer que les donnu00e9es sont correctement encodu00e9es.</p>\n            <button type='submit' class='btn btn-primary'>Corriger la connexion u00e0 la base de donnu00e9es</button>\n        </form>";

// Formulaire pour convertir les tables en UTF-8
echo "<form method='post' class='mb-4'>\n            <input type='hidden' name='action' value='fix_tables'>\n            <p>Convertir toutes les tables en UTF-8 pour assurer que les donnu00e9es sont correctement encodu00e9es.</p>\n            <div class='alert alert-warning'>\n                <strong>Attention:</strong> Cette action va modifier la structure de vos tables. Assurez-vous d'avoir une sauvegarde de votre base de donnu00e9es avant de continuer.\n            </div>\n            <button type='submit' class='btn btn-danger'>Convertir les tables en UTF-8</button>\n        </form>";

// Formulaire pour corriger les donnu00e9es avec des caractu00e8res encodu00e9s
echo "<form method='post'>\n            <input type='hidden' name='action' value='fix_data'>\n            <p>Corriger les donnu00e9es contenant des caractu00e8res encodu00e9s comme 'u00e9' au lieu de 'u00e9'.</p>\n            <div class='alert alert-warning'>\n                <strong>Attention:</strong> Cette action va modifier les donnu00e9es dans vos tables. Assurez-vous d'avoir une sauvegarde de votre base de donnu00e9es avant de continuer.\n            </div>\n            <button type='submit' class='btn btn-danger'>Corriger les donnu00e9es</button>\n        </form>";

echo "    </div>\n</div>";

// Conseils pour phpMyAdmin
echo "<div class='card'>\n    <div class='card-header bg-primary text-white'>\n        <h5 class='card-title mb-0'>Configuration de phpMyAdmin</h5>\n    </div>\n    <div class='card-body'>\n        <p>Si vous utilisez phpMyAdmin, vous pouvez u00e9galement configurer l'encodage dans l'interface:</p>\n        <ol>\n            <li>Connectez-vous u00e0 phpMyAdmin</li>\n            <li>Su00e9lectionnez votre base de donnu00e9es</li>\n            <li>Cliquez sur l'onglet 'Opu00e9rations'</li>\n            <li>Dans la section 'Collation', su00e9lectionnez 'utf8mb4_unicode_ci'</li>\n            <li>Cliquez sur 'Exu00e9cuter'</li>\n        </ol>\n        <p>Pour les nouvelles tables, assurez-vous de su00e9lectionner 'utf8mb4_unicode_ci' comme collation lors de leur cru00e9ation.</p>\n    </div>\n</div>";

// Pied de page HTML
echo "</div>\n    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>\n</body>\n</html>";
?>
