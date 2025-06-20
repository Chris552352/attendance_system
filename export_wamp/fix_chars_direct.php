<?php
// Script pour remplacer directement les caractères encodés dans tous les fichiers

// Fonction pour remplacer les caractères encodés
function fix_encoded_chars($content) {
    // Tableau de correspondance des caractères encodés
    $replacements = [
        'u00e0' => 'à',
        'u00e1' => 'á',
        'u00e2' => 'â',
        'u00e3' => 'ã',
        'u00e4' => 'ä',
        'u00e5' => 'å',
        'u00e6' => 'æ',
        'u00e7' => 'ç',
        'u00e8' => 'è',
        'u00e9' => 'é',
        'u00ea' => 'ê',
        'u00eb' => 'ë',
        'u00ec' => 'ì',
        'u00ed' => 'í',
        'u00ee' => 'î',
        'u00ef' => 'ï',
        'u00f0' => 'ð',
        'u00f1' => 'ñ',
        'u00f2' => 'ò',
        'u00f3' => 'ó',
        'u00f4' => 'ô',
        'u00f5' => 'õ',
        'u00f6' => 'ö',
        'u00f8' => 'ø',
        'u00f9' => 'ù',
        'u00fa' => 'ú',
        'u00fb' => 'û',
        'u00fc' => 'ü',
        'u00fd' => 'ý',
        'u00ff' => 'ÿ',
        'u00c0' => 'À',
        'u00c1' => 'Á',
        'u00c2' => 'Â',
        'u00c3' => 'Ã',
        'u00c4' => 'Ä',
        'u00c5' => 'Å',
        'u00c6' => 'Æ',
        'u00c7' => 'Ç',
        'u00c8' => 'È',
        'u00c9' => 'É',
        'u00ca' => 'Ê',
        'u00cb' => 'Ë',
        'u00cc' => 'Ì',
        'u00cd' => 'Í',
        'u00ce' => 'Î',
        'u00cf' => 'Ï',
        'u00d0' => 'Ð',
        'u00d1' => 'Ñ',
        'u00d2' => 'Ò',
        'u00d3' => 'Ó',
        'u00d4' => 'Ô',
        'u00d5' => 'Õ',
        'u00d6' => 'Ö',
        'u00d8' => 'Ø',
        'u00d9' => 'Ù',
        'u00da' => 'Ú',
        'u00db' => 'Û',
        'u00dc' => 'Ü',
        'u00dd' => 'Ý',
        'u00df' => 'ß',
        'u0152' => 'Œ',
        'u0153' => 'œ',
    ];
    
    // Remplacer tous les caractères encodés
    foreach ($replacements as $encoded => $char) {
        // Remplacer les motifs comme "Franu00e7ais"
        $content = str_replace($encoded, $char, $content);
    }
    
    return $content;
}

// Fonction pour parcourir récursivement un répertoire
function process_directory($dir) {
    $modified_files = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Récursion pour les sous-répertoires
            $sub_modified = process_directory($path);
            $modified_files = array_merge($modified_files, $sub_modified);
        } else {
            // Traiter tous les fichiers PHP
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $content = file_get_contents($path);
                $new_content = fix_encoded_chars($content);
                
                if ($content !== $new_content) {
                    file_put_contents($path, $new_content);
                    $modified_files[] = $path;
                }
            }
        }
    }
    
    return $modified_files;
}

// Afficher l'en-tête HTML
echo "<!DOCTYPE html>\n<html>\n<head>\n    <title>Correction des caractères français</title>\n    <meta charset='UTF-8'>\n    <style>\n        body { font-family: Arial, sans-serif; margin: 20px; }\n        h1 { color: #2c3e50; }\n        .success { color: #27ae60; }\n        .file-list { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }\n        ul { list-style-type: none; padding-left: 10px; }\n        li { margin-bottom: 5px; }\n    </style>\n</head>\n<body>\n    <h1>Correction des caractères français</h1>\n    <p>Remplacement des caractères encodés (comme u00e9) par leurs équivalents français (comme é)...</p>";

// Traiter tous les fichiers PHP du projet
$root_dir = __DIR__;
$modified_files = process_directory($root_dir);

// Afficher les résultats
if (count($modified_files) > 0) {
    echo "<h2 class='success'>" . count($modified_files) . " fichiers ont été modifiés</h2>\n    <div class='file-list'>\n        <ul>";
    
    foreach ($modified_files as $file) {
        $relative_path = str_replace($root_dir . '/', '', $file);
        echo "<li>" . htmlspecialchars($relative_path) . "</li>";
    }
    
    echo "</ul>\n    </div>";
} else {
    echo "<h2>Aucun fichier n'a été modifié</h2>\n    <p>Tous les caractères sont déjà correctement formatés.</p>";
}

// Afficher un lien pour retourner à l'accueil
echo "<p><a href='index.php'>Retour à l'accueil</a></p>\n</body>\n</html>";
?>
