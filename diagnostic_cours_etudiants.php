<?php
// Diagnostic complet des cours et inscriptions étudiants
require_once 'config/database.php';

// 1. Afficher tous les cours avec leur ID, nom, enseignant et nombre d'inscrits
$cours = db_query("SELECT c.id, c.nom, c.code, c.enseignant_id, (SELECT COUNT(*) FROM inscriptions i WHERE i.cours_id = c.id) as nb_inscrits FROM cours c ORDER BY c.id");

if (empty($cours)) {
    echo "<h2 style='color:red'>Aucun cours trouvé dans la base.</h2>";
    exit;
}

// Afficher le tableau des cours

echo "<h1>Liste des cours</h1>";
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nom</th><th>Code</th><th>Enseignant ID</th><th>Nb inscrits</th></tr>";
foreach ($cours as $c) {
    echo "<tr><td>".htmlspecialchars($c['id'])."</td><td>".htmlspecialchars($c['nom'])."</td><td>".htmlspecialchars($c['code'])."</td><td>".htmlspecialchars($c['enseignant_id'])."</td><td>".htmlspecialchars($c['nb_inscrits'])."</td></tr>";
}
echo "</table>";

// 2. Pour chaque cours, afficher les étudiants inscrits
foreach ($cours as $c) {
    echo "<h2>Étudiants inscrits au cours ID ".$c['id']." (".htmlspecialchars($c['nom']).")</h2>";
    $etudiants = db_query("SELECT e.id, e.nom, e.prenom, e.matricule FROM etudiants e JOIN inscriptions i ON e.id = i.etudiant_id WHERE i.cours_id = ? ORDER BY e.nom, e.prenom", [$c['id']]);
    if (empty($etudiants)) {
        echo "<div style='color:red'>Aucun étudiant inscrit à ce cours.</div>";
    } else {
        echo "<table border='1' cellpadding='4'><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Matricule</th></tr>";
        foreach ($etudiants as $e) {
            echo "<tr><td>".htmlspecialchars($e['id'])."</td><td>".htmlspecialchars($e['nom'])."</td><td>".htmlspecialchars($e['prenom'])."</td><td>".htmlspecialchars($e['matricule'])."</td></tr>";
        }
        echo "</table>";
    }
}

// 3. Afficher tous les étudiants existants
$all_etudiants = db_query("SELECT id, nom, prenom, matricule FROM etudiants ORDER BY nom, prenom");
echo "<h1>Liste complète des étudiants</h1>";
echo "<table border='1' cellpadding='4'><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Matricule</th></tr>";
foreach ($all_etudiants as $e) {
    echo "<tr><td>".htmlspecialchars($e['id'])."</td><td>".htmlspecialchars($e['nom'])."</td><td>".htmlspecialchars($e['prenom'])."</td><td>".htmlspecialchars($e['matricule'])."</td></tr>";
}
echo "</table>";

// 4. Afficher tous les enseignants
$enseignants = db_query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'enseignant' ORDER BY nom, prenom");
echo "<h1>Liste des enseignants</h1>";
echo "<table border='1' cellpadding='4'><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th></tr>";
foreach ($enseignants as $ens) {
    echo "<tr><td>".htmlspecialchars($ens['id'])."</td><td>".htmlspecialchars($ens['nom'])."</td><td>".htmlspecialchars($ens['prenom'])."</td><td>".htmlspecialchars($ens['email'])."</td></tr>";
}
echo "</table>";

// 5. Afficher les 10 dernières lignes de la table inscriptions
$last_inscr = db_query("SELECT * FROM inscriptions ORDER BY id DESC LIMIT 10");
echo "<h1>10 dernières inscriptions (toutes matières)</h1>";
echo "<table border='1' cellpadding='4'><tr><th>ID</th><th>etudiant_id</th><th>cours_id</th></tr>";
foreach ($last_inscr as $row) {
    echo "<tr><td>".htmlspecialchars($row['id'])."</td><td>".htmlspecialchars($row['etudiant_id'])."</td><td>".htmlspecialchars($row['cours_id'])."</td></tr>";
}
echo "</table>";
