<?php
// Script de correction automatique des inscriptions étudiants-cours
require_once 'includes/auth.php';
require_once 'config/database.php';
require_auth();

// 1. Vérifier la cohérence des inscriptions pour chaque étudiant et chaque cours
$etudiants = db_query("SELECT id FROM etudiants");
$cours = db_query("SELECT id FROM cours");

// 2. Ajouter une inscription manquante pour chaque étudiant dans chaque cours de son enseignant
$nb_fixes = 0;
foreach ($cours as $c) {
    // Récupérer tous les étudiants qui devraient être inscrits à ce cours
    $inscrits = db_query("SELECT etudiant_id FROM inscriptions WHERE cours_id = ?", [$c['id']]);
    $inscrits_ids = array_column($inscrits, 'etudiant_id');
    foreach ($etudiants as $e) {
        // Vérifier s'il manque une inscription
        if (!in_array($e['id'], $inscrits_ids)) {
            // Optionnel : ici on ne force pas l'inscription, on pourrait le faire si besoin
            // db_exec("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)", [$e['id'], $c['id']]);
            // $nb_fixes++;
        }
    }
}

// 3. Diagnostiquer les cours sans étudiants inscrits
$cours_sans_etudiants = db_query("SELECT c.id, c.nom FROM cours c LEFT JOIN inscriptions i ON c.id = i.cours_id WHERE i.id IS NULL");

// 4. Diagnostiquer les étudiants sans inscription
$etudiants_sans_cours = db_query("SELECT e.id, e.nom, e.prenom FROM etudiants e LEFT JOIN inscriptions i ON e.id = i.etudiant_id WHERE i.id IS NULL");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Correction automatique des inscriptions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Diagnostic des inscriptions étudiants-cours</h4>
        </div>
        <div class="card-body">
            <h5>Cours sans étudiants inscrits :</h5>
            <?php if (empty($cours_sans_etudiants)): ?>
                <div class="alert alert-success">Tous les cours ont au moins un étudiant inscrit.</div>
            <?php else: ?>
                <ul>
                    <?php foreach ($cours_sans_etudiants as $c): ?>
                        <li><?= htmlspecialchars($c['nom']) ?> (ID <?= $c['id'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h5>Étudiants sans inscription à un cours :</h5>
            <?php if (empty($etudiants_sans_cours)): ?>
                <div class="alert alert-success">Tous les étudiants sont inscrits à au moins un cours.</div>
            <?php else: ?>
                <ul>
                    <?php foreach ($etudiants_sans_cours as $e): ?>
                        <li><?= htmlspecialchars($e['nom']) ?> <?= htmlspecialchars($e['prenom']) ?> (ID <?= $e['id'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <hr>
            <form method="post">
                <button type="submit" name="fix" class="btn btn-success">Corriger automatiquement (inscrire chaque étudiant à chaque cours)</button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix'])) {
                $nb_fixes = 0;
                foreach ($cours as $c) {
                    foreach ($etudiants as $e) {
                        $exists = db_query_single("SELECT id FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?", [$e['id'], $c['id']]);
                        if (!$exists) {
                            db_exec("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)", [$e['id'], $c['id']]);
                            $nb_fixes++;
                        }
                    }
                }
                echo '<div class="alert alert-info mt-3">' . $nb_fixes . ' inscriptions ajoutées.</div>';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
