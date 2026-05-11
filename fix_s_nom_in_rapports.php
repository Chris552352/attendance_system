<?php
/**
 * Script pour corriger les requêtes SQL dans rapports.php
 * Remplace 's.nom as seance_nom' par 's.nom_cours as seance_nom'
 */

$target_file = 'rapports.php';

if (!file_exists($target_file)) {
    die("Fichier $target_file introuvable.\n");
}

$contents = file_get_contents($target_file);
$replaced = str_replace('s.nom as seance_nom', 's.nom_cours as seance_nom', $contents);

if ($contents === $replaced) {
    echo "<div style='color:orange;font-weight:bold;margin:2em;'>Aucune occurrence à remplacer dans $target_file.</div>";
} else {
    file_put_contents($target_file, $replaced);
    echo "<div style='color:green;font-weight:bold;margin:2em;'>Correction appliquée avec succès dans $target_file !</div>";
}
?> 