<?php
// Script pour corriger les textes en franu00e7ais dans un fichier spu00e9cifique

// Fonction pour remplacer les textes spu00e9cifiques
function fix_specific_texts($content) {
    // Textes u00e0 remplacer (format: 'texte original' => 'texte corrigu00e9')
    $replacements = [
        // Exemples de textes u00e0 corriger
        'Franu00e7ais' => 'Franu00e7ais',
        'Ru00e9seaux Informatiques' => 'Ru00e9seaux Informatiques',
        'u00e9tu00e9' => 'u00e9tu00e9',
        'ajoutu00e9' => 'ajoutu00e9',
        'succu00e8s' => 'succu00e8s',
        'ultu00e9rieurement' => 'ultu00e9rieurement',
        'u00e0 l\'enseignant' => 'u00e0 l\'enseignant',
        'u00e9tu00e9 ajoutu00e9' => 'u00e9tu00e9 ajoutu00e9',
        'u00e9tu00e9 corrigu00e9' => 'u00e9tu00e9 corrigu00e9',
        'terminu00e9' => 'terminu00e9',
        'modifiu00e9' => 'modifiu00e9',
        'traitu00e9s' => 'traitu00e9s',
        'Ru00e9pertoire' => 'Ru00e9pertoire',
        'ru00e9sultats' => 'ru00e9sultats',
        'exu00e9cution' => 'exu00e9cution',
        'Ru00e9cursion' => 'Ru00e9cursion',
        'ru00e9pertoires' => 'ru00e9pertoires',
        'Vu00e9rifier' => 'Vu00e9rifier',
        'apportu00e9es' => 'apportu00e9es',
        'u00c9crire' => 'u00c9crire',
        'modifiu00e9s' => 'modifiu00e9s',
        'Du00e9but' => 'Du00e9but',
        'En-tu00eate' => 'En-tu00eate',
        'caractu00e8res' => 'caractu00e8res',
        'encodu00e9s' => 'encodu00e9s',
        'franu00e7ais' => 'franu00e7ais',
        'ru00e9guliu00e8re' => 'ru00e9guliu00e8re',
        'ou00f9' => 'ou00f9',
        'u00e9tu00e9' => 'u00e9tu00e9'
    ];
    
    // Appliquer les remplacements
    foreach ($replacements as $original => $corrected) {
        $content = str_replace($original, $corrected, $content);
    }
    
    return $content;
}

// Fichier u00e0 corriger (vous pouvez modifier cette valeur)
$file_to_fix = isset($_GET['file']) ? $_GET['file'] : '';

// Afficher le formulaire si aucun fichier n'est spu00e9cifiu00e9
if (empty($file_to_fix)) {
    echo "<!DOCTYPE html>\n<html>\n<head>\n    <title>Correction des textes en franu00e7ais</title>\n    <meta charset='UTF-8'>\n    <style>\n        body { font-family: Arial, sans-serif; margin: 20px; }\n        h1 { color: #2c3e50; }\n        .form { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }\n        input[type=text] { width: 300px; padding: 5px; }\n        button { padding: 5px 10px; background-color: #3498db; color: white; border: none; border-radius: 3px; cursor: pointer; }\n        button:hover { background-color: #2980b9; }\n        .files { margin-top: 20px; }\n        .files a { display: block; margin-bottom: 5px; }\n    </style>\n</head>\n<body>\n    <h1>Correction des textes en franu00e7ais</h1>\n    \n    <div class='form'>\n        <form method='get'>\n            <label for='file'>Fichier u00e0 corriger:</label>\n            <input type='text' id='file' name='file' placeholder='Exemple: justifier_absence.php'>\n            <button type='submit'>Corriger</button>\n        </form>\n    </div>\n    \n    <div class='files'>\n        <h3>Fichiers PHP disponibles:</h3>";
    
    // Lister les fichiers PHP disponibles
    $php_files = glob('*.php');
    foreach ($php_files as $php_file) {
        echo "<a href='?file=" . urlencode($php_file) . "'>" . htmlspecialchars($php_file) . "</a>\n";
    }
    
    echo "</div>\n</body>\n</html>";
    exit;
}

// Vu00e9rifier si le fichier existe
if (!file_exists($file_to_fix)) {
    die("Le fichier '$file_to_fix' n'existe pas.");
}

// Lire le contenu du fichier
$content = file_get_contents($file_to_fix);

// Corriger le contenu
$fixed_content = fix_specific_texts($content);

// Sauvegarder le fichier original
$backup_file = $file_to_fix . '.bak';
file_put_contents($backup_file, $content);

// u00c9crire le contenu corrigu00e9
file_put_contents($file_to_fix, $fixed_content);

// Afficher un message de confirmation
echo "<!DOCTYPE html>\n<html>\n<head>\n    <title>Correction des textes en franu00e7ais</title>\n    <meta charset='UTF-8'>\n    <style>\n        body { font-family: Arial, sans-serif; margin: 20px; }\n        h1 { color: #2c3e50; }\n        .success { color: #27ae60; }\n        .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }\n        pre { background-color: #f1f1f1; padding: 10px; border-radius: 3px; overflow: auto; }\n        .back { margin-top: 20px; }\n        .back a { text-decoration: none; color: #3498db; }\n    </style>\n</head>\n<body>\n    <h1>Correction des textes en franu00e7ais</h1>\n    \n    <h2 class='success'>Le fichier '$file_to_fix' a u00e9tu00e9 corrigu00e9 avec succu00e8s!</h2>\n    <p>Une sauvegarde a u00e9tu00e9 cru00e9u00e9e: '$backup_file'</p>\n    \n    <div class='info'>\n        <h3>Modifications effectuu00e9es:</h3>\n        <pre>" . htmlspecialchars(substr($fixed_content, 0, 1000)) . (strlen($fixed_content) > 1000 ? '...' : '') . "</pre>\n    </div>\n    \n    <div class='back'>\n        <a href='correct_text.php'>&laquo; Retour</a> | \n        <a href='" . $file_to_fix . "'>Voir le fichier corrigu00e9</a>\n    </div>\n</body>\n</html>";
?>
