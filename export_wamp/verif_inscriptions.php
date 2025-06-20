<?php
// Script de vérification des inscriptions étudiants à un cours donné
require_once 'includes/auth.php';
require_once 'config/database.php';
require_auth();

$cours_id = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : 0;

if ($cours_id <= 0) {
    echo "<form method='get' style='margin:2em;'>";
    echo "<label>Entrez l'ID du cours : <input type='number' name='cours_id' min='1' required></label> ";
    echo "<button type='submit'>Vérifier</button>";
    echo "</form>";
    exit;
}

$cours = db_query_single("SELECT * FROM cours WHERE id = ?", [$cours_id]);
if (!$cours) {
    echo "<div style='color:red;font-weight:bold;margin:2em;'>Cours introuvable (id=$cours_id)</div>";
    exit;
}

$étudiants = db_query("SELECT e.* FROM étudiants e JOIN inscriptions i ON e.id = i.étudiant_id WHERE i.cours_id = ? ORDER BY e.nom, e.prénom", [$cours_id]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification des inscriptions étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Inscriptions pour le cours <b><?= htmlspecialchars($cours['nom']) ?></b> (ID <?= $cours_id ?>)</h4>
        </div>
        <div class="card-body">
            <?php if (empty($étudiants)): ?>
                <div class="alert alert-warning">Aucun étudiant inscrit à ce cours.</div>
            <?php else: ?>
                <table class="table table-striped">
                    <thead><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Matricule</th><th>Email</th><th>Téléphone</th></tr></thead>
                    <tbody>
                    <?php foreach ($étudiants as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><?= htmlspecialchars($e['nom']) ?></td>
                            <td><?= htmlspecialchars($e['prénom']) ?></td>
                            <td><?= htmlspecialchars($e['matricule']) ?></td>
                            <td><?= htmlspecialchars($e['email']) ?></td>
                            <td><?= htmlspecialchars($e['téléphone']) ?></td>
<td><?= htmlspecialchars($e['telephone']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <a href="?" class="btn btn-secondary mt-3">Vérifier un autre cours</a>
        </div>
    </div>
</div>
</body>
</html>
