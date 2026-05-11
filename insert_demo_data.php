<?php
// Script d'insertion de données de test pour le module de rapports
require_once 'config/database.php';

// 1. Créer un étudiant de test
$etudiant = db_query_single("SELECT * FROM etudiants WHERE matricule = 'TEST001'");
if (!$etudiant) {
    db_exec("INSERT INTO etudiants (nom, prenom, matricule, email) VALUES (?, ?, ?, ?)", [
        'Test', 'Etudiant', 'TEST001', 'test.etudiant@example.com'
    ]);
    $etudiant = db_query_single("SELECT * FROM etudiants WHERE matricule = 'TEST001'");
}

// 2. Créer un cours de test
$cours = db_query_single("SELECT * FROM cours WHERE code = 'COURSTEST'");
if (!$cours) {
    db_exec("INSERT INTO cours (nom, code, enseignant_id) VALUES (?, ?, ?)", [
        'Cours Démo', 'COURSTEST', 1
    ]);
    $cours = db_query_single("SELECT * FROM cours WHERE code = 'COURSTEST'");
}

// 3. Créer une séance de test
$seance = db_query_single("SELECT * FROM seances WHERE nom_cours = ? ORDER BY id DESC", [$cours['nom']]);
if (!$seance) {
    db_exec("INSERT INTO seances (nom_cours, enseignant_id, token, expiration) VALUES (?, ?, ?, ?)", [
        $cours['nom'], 1, bin2hex(random_bytes(8)), date('Y-m-d H:i:s', strtotime('+1 day'))
    ]);
    $seance = db_query_single("SELECT * FROM seances WHERE nom_cours = ? ORDER BY id DESC", [$cours['nom']]);
}

// 4. Insérer des présences de test (3 jours)
$dates = [
    date('Y-m-d', strtotime('-2 days')),
    date('Y-m-d', strtotime('-1 day')),
    date('Y-m-d')
];
$statuts = ['present', 'absent', 'present'];
foreach ($dates as $i => $date) {
    $exist = db_query_single("SELECT * FROM presences WHERE etudiant_id = ? AND cours_id = ? AND date_presence = ?", [
        $etudiant['id'], $cours['id'], $date
    ]);
    if (!$exist) {
        db_exec("INSERT INTO presences (etudiant_id, cours_id, seance_id, date_presence, statut, enregistre_par) VALUES (?, ?, ?, ?, ?, ?)", [
            $etudiant['id'], $cours['id'], $seance['id'], $date, $statuts[$i], 1
        ]);
    }
}

echo '<h2>Données de test insérées avec succès !</h2>';
echo '<a href="rapports.php">Voir les rapports</a>';
