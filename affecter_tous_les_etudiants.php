<?php
// Script pour inscrire tous les étudiants à tous les cours existants
require_once 'config/database.php';

$etudiants = db_query("SELECT id, nom, prenom, email FROM etudiants");
$cours = db_query("SELECT id, nom, code FROM cours");

$count = 0;
foreach ($etudiants as $etud) {
    foreach ($cours as $c) {
        $deja = db_query_single("SELECT id FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?", [$etud['id'], $c['id']]);
        if (!$deja) {
            db_exec("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)", [$etud['id'], $c['id']]);
            echo "Inscription : {$etud['prenom']} {$etud['nom']} à {$c['nom']} ({$c['code']})<br>\n";
            $count++;
        }
    }
}
if ($count === 0) {
    echo "Aucune nouvelle inscription (tout est déjà à jour).<br>\n";
}
echo "<hr>Script terminé. Total nouvelles inscriptions : $count";
