<?php
// Script pour inscrire tous les étudiants à tous les cours
require_once 'config/database.php';

$etudiants = db_query("SELECT id FROM etudiants");
$cours = db_query("SELECT id FROM cours");

if (empty($etudiants) || empty($cours)) {
    echo "<h3 style='color:red'>Aucun étudiant ou aucun cours trouvé.</h3>";
    exit;
}

$nb_insert = 0;
foreach ($etudiants as $etudiant) {
    foreach ($cours as $cours_item) {
        // Vérifier si déjà inscrit
        $exists = db_query_single("SELECT id FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?", [$etudiant['id'], $cours_item['id']]);
        if (!$exists) {
            db_exec("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)", [$etudiant['id'], $cours_item['id']]);
            $nb_insert++;
        }
    }
}
echo "<h2 style='color:green'>Tous les étudiants ont été inscrits à tous les cours.</h2>";
echo "<p>$nb_insert inscriptions ajoutées.</p>";
