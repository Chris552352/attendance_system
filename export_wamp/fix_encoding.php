<?php
/**
 * Script pour convertir les caractères Unicode encodés (\u00xx) en caractères français normaux
 */

// Fonction pour convertir les caractères Unicode encodés en caractères normaux
function convert_unicode_to_normal($content) {
    // Tableau de correspondance des caractères Unicode courants en français
    $unicode_map = [
        '\à' => 'à', // a accent grave
        '\á' => 'á', // a accent aigu
        '\â' => 'â', // a accent circonflexe
        '\ã' => 'ã', // a tilde
        '\ä' => 'ä', // a tréma
        '\å' => 'å', // a rond en chef
        '\æ' => 'æ', // ae ligature
        '\ç' => 'ç', // c cédille
        '\è' => 'è', // e accent grave
        '\é' => 'é', // e accent aigu
        '\ê' => 'ê', // e accent circonflexe
        '\ë' => 'ë', // e tréma
        '\ì' => 'ì', // i accent grave
        '\í' => 'í', // i accent aigu
        '\î' => 'î', // i accent circonflexe
        '\ï' => 'ï', // i tréma
        '\ð' => 'ð', // eth
        '\ñ' => 'ñ', // n tilde
        '\ò' => 'ò', // o accent grave
        '\ó' => 'ó', // o accent aigu
        '\ô' => 'ô', // o accent circonflexe
        '\õ' => 'õ', // o tilde
        '\ö' => 'ö', // o tréma
        '\ø' => 'ø', // o barré
        '\ù' => 'ù', // u accent grave
        '\ú' => 'ú', // u accent aigu
        '\û' => 'û', // u accent circonflexe
        '\ü' => 'ü', // u tréma
        '\ý' => 'ý', // y accent aigu
        '\ÿ' => 'ÿ', // y tréma
        '\À' => 'À', // A accent grave
        '\Á' => 'Á', // A accent aigu
        '\Â' => 'Â', // A accent circonflexe
        '\Ã' => 'Ã', // A tilde
        '\Ä' => 'Ä', // A tréma
        '\Å' => 'Å', // A rond en chef
        '\Æ' => 'Æ', // AE ligature
        '\Ç' => 'Ç', // C cédille
        '\È' => 'È', // E accent grave
        '\É' => 'É', // E accent aigu
        '\Ê' => 'Ê', // E accent circonflexe
        '\Ë' => 'Ë', // E tréma
        '\Ì' => 'Ì', // I accent grave
        '\Í' => 'Í', // I accent aigu
        '\Î' => 'Î', // I accent circonflexe
        '\Ï' => 'Ï', // I tréma
        '\Ð' => 'Ð', // Eth
        '\Ñ' => 'Ñ', // N tilde
        '\Ò' => 'Ò', // O accent grave
        '\Ó' => 'Ó', // O accent aigu
        '\Ô' => 'Ô', // O accent circonflexe
        '\Õ' => 'Õ', // O tilde
        '\Ö' => 'Ö', // O tréma
        '\Ø' => 'Ø', // O barré
        '\Ù' => 'Ù', // U accent grave
        '\Ú' => 'Ú', // U accent aigu
        '\Û' => 'Û', // U accent circonflexe
        '\Ü' => 'Ü', // U tréma
        '\Ý' => 'Ý', // Y accent aigu
        '\ß' => 'ß', // eszett
        '\Œ' => 'Œ', // OE ligature majuscule
        '\œ' => 'œ', // oe ligature minuscule
        '\’' => "'", // apostrophe
        '\–' => '–', // tiret demi-cadratin
        '\—' => '—', // tiret cadratin
        '\«' => '«', // guillemet français ouvrant
        '\»' => '»', // guillemet français fermant
    ];
    
    // Remplacer tous les caractères Unicode par leurs équivalents normaux
    foreach ($unicode_map as $unicode => $normal) {
        $content = str_replace($unicode, $normal, $content);
    }
    
    // Utiliser une expression régulière pour capturer les caractères Unicode qui ne seraient pas dans notre liste
    $content = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($matches) {
        return html_entity_decode('&#x' . $matches[1] . ';', ENT_QUOTES, 'UTF-8');
    }, $content);
    
    return $content;
}

// Fonction pour parcourir récursivement un répertoire et traiter tous les fichiers PHP
function process_directory($dir) {
    $results = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Récursion pour les sous-répertoires
            $sub_results = process_directory($path);
            $results = array_merge($results, $sub_results);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            // Traiter uniquement les fichiers PHP
            $content = file_get_contents($path);
            $new_content = convert_unicode_to_normal($content);
            
            // Vérifier si des modifications ont été apportées
            if ($content !== $new_content) {
                // Sauvegarder le fichier original
                $backup_path = $path . '.bak';
                file_put_contents($backup_path, $content);
                
                // Écrire le contenu modifié
                file_put_contents($path, $new_content);
                
                $results[] = [
                    'path' => $path,
                    'modified' => true,
                    'backup' => $backup_path
                ];
            } else {
                $results[] = [
                    'path' => $path,
                    'modified' => false
                ];
            }
        }
    }
    
    return $results;
}

// Début du script
$start_time = microtime(true);

// En-tête HTML
echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Conversion des caractères Unicode</title>\n    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>\n    <style>\n        body { padding: 20px; }\n        .card { margin-bottom: 20px; }\n        .modified { background-color: #d1e7dd; }\n    </style>\n</head>\n<body>\n    <div class='container'>\n        <h1 class='mb-4'>Conversion des caractères Unicode en caractères français</h1>";

// Répertoire racine du projet
$root_dir = __DIR__;

// Traiter tous les fichiers PHP du projet
echo "<div class='alert alert-info'>\n    <p>Traitement des fichiers en cours...</p>\n</div>";

// Vider le buffer de sortie pour afficher le message de traitement
ob_flush();
flush();

// Traiter les fichiers
$results = process_directory($root_dir);

// Afficher les résultats
$modified_count = 0;
$total_count = count($results);

foreach ($results as $result) {
    if ($result['modified']) {
        $modified_count++;
    }
}

echo "<div class='alert alert-success'>\n    <h4>Traitement terminé</h4>\n    <p>$modified_count fichiers modifiés sur $total_count fichiers PHP traités.</p>\n</div>";

// Afficher la liste des fichiers modifiés
if ($modified_count > 0) {
    echo "<h2>Fichiers modifiés</h2>\n    <div class='card'>\n        <div class='card-header'>\n            <h5 class='card-title mb-0'>Liste des fichiers où des caractères Unicode ont été convertis</h5>\n        </div>\n        <div class='card-body'>\n            <div class='table-responsive'>\n                <table class='table table-striped'>\n                    <thead>\n                        <tr>\n                            <th>Fichier</th>\n                            <th>Statut</th>\n                        </tr>\n                    </thead>\n                    <tbody>";
    
    foreach ($results as $result) {
        if ($result['modified']) {
            $relative_path = str_replace($root_dir . '/', '', $result['path']);
            echo "<tr class='modified'>\n                <td>$relative_path</td>\n                <td><span class='badge bg-success'>Modifié</span></td>\n            </tr>";
        }
    }
    
    echo "</tbody>\n                </table>\n            </div>\n        </div>\n    </div>";
}

// Afficher le temps d'exécution
$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);

echo "<div class='alert alert-info'>\n    <p>Temps d'exécution: $execution_time secondes</p>\n</div>";

// Liens de navigation
echo "<div class='mt-4'>\n    <a href='index.php' class='btn btn-primary'>Retour à l'accueil</a>\n</div>";

// Pied de page HTML
echo "</div>\n    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>\n</body>\n</html>";
?>
