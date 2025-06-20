<?php
/**
 * Script pour convertir les caractères encodés en hexadécimal (u00xx) en caractères français normaux
 */

// Fonction pour convertir les caractères encodés en caractères normaux
function convert_encoded_chars($content) {
    // Tableau de correspondance des caractères encodés courants en français
    $encoded_map = [
        'u00e0' => 'à', // a accent grave
        'u00e1' => 'á', // a accent aigu
        'u00e2' => 'â', // a accent circonflexe
        'u00e3' => 'ã', // a tilde
        'u00e4' => 'ä', // a tréma
        'u00e5' => 'å', // a rond en chef
        'u00e6' => 'æ', // ae ligature
        'u00e7' => 'ç', // c cédille
        'u00e8' => 'è', // e accent grave
        'u00e9' => 'é', // e accent aigu
        'u00ea' => 'ê', // e accent circonflexe
        'u00eb' => 'ë', // e tréma
        'u00ec' => 'ì', // i accent grave
        'u00ed' => 'í', // i accent aigu
        'u00ee' => 'î', // i accent circonflexe
        'u00ef' => 'ï', // i tréma
        'u00f0' => 'ð', // eth
        'u00f1' => 'ñ', // n tilde
        'u00f2' => 'ò', // o accent grave
        'u00f3' => 'ó', // o accent aigu
        'u00f4' => 'ô', // o accent circonflexe
        'u00f5' => 'õ', // o tilde
        'u00f6' => 'ö', // o tréma
        'u00f8' => 'ø', // o barré
        'u00f9' => 'ù', // u accent grave
        'u00fa' => 'ú', // u accent aigu
        'u00fb' => 'û', // u accent circonflexe
        'u00fc' => 'ü', // u tréma
        'u00fd' => 'ý', // y accent aigu
        'u00ff' => 'ÿ', // y tréma
        'u00c0' => 'À', // A accent grave
        'u00c1' => 'Á', // A accent aigu
        'u00c2' => 'Â', // A accent circonflexe
        'u00c3' => 'Ã', // A tilde
        'u00c4' => 'Ä', // A tréma
        'u00c5' => 'Å', // A rond en chef
        'u00c6' => 'Æ', // AE ligature
        'u00c7' => 'Ç', // C cédille
        'u00c8' => 'È', // E accent grave
        'u00c9' => 'É', // E accent aigu
        'u00ca' => 'Ê', // E accent circonflexe
        'u00cb' => 'Ë', // E tréma
        'u00cc' => 'Ì', // I accent grave
        'u00cd' => 'Í', // I accent aigu
        'u00ce' => 'Î', // I accent circonflexe
        'u00cf' => 'Ï', // I tréma
        'u00d0' => 'Ð', // Eth
        'u00d1' => 'Ñ', // N tilde
        'u00d2' => 'Ò', // O accent grave
        'u00d3' => 'Ó', // O accent aigu
        'u00d4' => 'Ô', // O accent circonflexe
        'u00d5' => 'Õ', // O tilde
        'u00d6' => 'Ö', // O tréma
        'u00d8' => 'Ø', // O barré
        'u00d9' => 'Ù', // U accent grave
        'u00da' => 'Ú', // U accent aigu
        'u00db' => 'Û', // U accent circonflexe
        'u00dc' => 'Ü', // U tréma
        'u00dd' => 'Ý', // Y accent aigu
        'u00df' => 'ß', // eszett
        'u0152' => 'Œ', // OE ligature majuscule
        'u0153' => 'œ', // oe ligature minuscule
    ];
    
    // Remplacer tous les caractères encodés par leurs équivalents normaux
    foreach ($encoded_map as $encoded => $normal) {
        $content = str_replace($encoded, $normal, $content);
    }
    
    return $content;
}

// Fonction pour parcourir récursivement un répertoire et traiter tous les fichiers
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
        } else {
            // Traiter tous les fichiers (pas seulement PHP)
            $content = file_get_contents($path);
            
            // Rechercher les motifs comme "Français"
            if (preg_match('/[A-Za-z]+u00[a-f0-9]{2}/', $content)) {
                // Convertir les caractères encodés
                $new_content = $content;
                
                // Remplacer les motifs comme "Français" par "Français"
                $new_content = preg_replace_callback('/([A-Za-z]+)(u00[a-f0-9]{2})/', function($matches) {
                    $prefix = $matches[1]; // Ex: "Fran"
                    $encoded = $matches[2]; // Ex: "u00e7"
                    
                    // Convertir le caractère encodé
                    $char = convert_encoded_chars($encoded);
                    
                    return $prefix . $char;
                }, $new_content);
                
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
                }
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
echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Conversion des caractères français</title>\n    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>\n    <style>\n        body { padding: 20px; }\n        .card { margin-bottom: 20px; }\n        .modified { background-color: #d1e7dd; }\n    </style>\n</head>\n<body>\n    <div class='container'>\n        <h1 class='mb-4'>Conversion des caractères encodés en caractères français</h1>";

// Répertoire racine du projet
$root_dir = __DIR__;

// Traiter tous les fichiers du projet
echo "<div class='alert alert-info'>\n    <p>Recherche des caractères encodés comme 'Français' pour les convertir en 'Français'...</p>\n</div>";

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

echo "<div class='alert alert-success'>\n    <h4>Traitement terminé</h4>\n    <p>$modified_count fichiers modifiés sur $total_count fichiers traités.</p>\n</div>";

// Afficher la liste des fichiers modifiés
if ($modified_count > 0) {
    echo "<h2>Fichiers modifiés</h2>\n    <div class='card'>\n        <div class='card-header'>\n            <h5 class='card-title mb-0'>Liste des fichiers où des caractères encodés ont été convertis</h5>\n        </div>\n        <div class='card-body'>\n            <div class='table-responsive'>\n                <table class='table table-striped'>\n                    <thead>\n                        <tr>\n                            <th>Fichier</th>\n                            <th>Statut</th>\n                        </tr>\n                    </thead>\n                    <tbody>";
    
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
