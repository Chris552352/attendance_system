<?php
require_once 'config/database.php';

$cours = db_query("SELECT id, nom, code FROM cours ORDER BY id");
$etudiants = db_query("SELECT id, nom, prenom, email FROM etudiants ORDER BY id");
$inscriptions = db_query("SELECT i.*, e.nom as etudiant_nom, e.prenom as etudiant_prenom, e.email, c.nom as cours_nom, c.code as cours_code FROM inscriptions i JOIN etudiants e ON i.etudiant_id = e.id JOIN cours c ON i.cours_id = c.id ORDER BY c.id, e.nom");

?>
<h2>Liste des inscriptions</h2>
<table border="1" cellpadding="5">
<tr><th>Cours</th><th>Code</th><th>Étudiant</th><th>Email</th></tr>
<?php foreach ($inscriptions as $insc): ?>
<tr>
    <td><?= htmlspecialchars($insc['cours_nom']) ?></td>
    <td><?= htmlspecialchars($insc['cours_code']) ?></td>
    <td><?= htmlspecialchars($insc['etudiant_prenom'] . ' ' . $insc['etudiant_nom']) ?></td>
    <td><?= htmlspecialchars($insc['email']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<h3>Total inscriptions : <?= count($inscriptions) ?></h3>
