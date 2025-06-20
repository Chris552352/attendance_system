<?php
// Script simple et direct pour corriger les caractères encodés

// Fonction pour remplacer directement les caractères encodés
function fix_french_chars($content) {
    // Remplacer les caractères encodés courants
    $content = str_replace('u00e0', 'à', $content); // à
    $content = str_replace('u00e2', 'â', $content); // â
    $content = str_replace('u00e7', 'ç', $content); // ç
    $content = str_replace('u00e8', 'è', $content); // è
    $content = str_replace('u00e9', 'é', $content); // é
    $content = str_replace('u00ea', 'ê', $content); // ê
    $content = str_replace('u00eb', 'ë', $content); // ë
    $content = str_replace('u00ee', 'î', $content); // î
    $content = str_replace('u00ef', 'ï', $content); // ï
    $content = str_replace('u00f4', 'ô', $content); // ô
    $content = str_replace('u00f9', 'ù', $content); // ù
    $content = str_replace('u00fb', 'û', $content); // û
    $content = str_replace('u00fc', 'ü', $content); // ü
    $content = str_replace('u00c0', 'À', $content); // À
    $content = str_replace('u00c7', 'Ç', $content); // Ç
    $content = str_replace('u00c9', 'É', $content); // É
    $content = str_replace('u00ca', 'Ê', $content); // Ê
    
    return $content;
}

// Exemple de fichier à corriger
$example_file = 'justifier_absence.php';

if (file_exists($example_file)) {
    $content = file_get_contents($example_file);
    $fixed_content = fix_french_chars($content);
    file_put_contents($example_file, $fixed_content);
    echo "Le fichier $example_file a été corrigé.";
} else {
    echo "Le fichier $example_file n'existe pas.";
}

// Corriger tous les fichiers PHP dans le répertoire courant
$files = glob('*.php');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $fixed_content = fix_french_chars($content);
    
    if ($content !== $fixed_content) {
        file_put_contents($file, $fixed_content);
        $count++;
    }
}

echo "<br>$count fichiers ont été corrigés.";
?>
