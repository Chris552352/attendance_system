<?php
// Script de vérification des inscriptions étudiants à un cours donné
require_once 'config/database.php';

$cours_id = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : 0;
if ($cours_id <= 0) {
    echo "<h3 style='color:red'>Veuillez fournir un cours_id valide dans l'URL, ex: ?cours_id=1</h3>";
    exit;
}

// Vérifier la table inscriptions
$inscriptions = db_query("SELECT * FROM inscriptions WHERE cours_id = ?", [$cours_id]);
echo "<h2>Inscriptions pour le cours ID $cours_id</h2>";
if (empty($inscriptions)) {
    echo "<div style='color:red'>Aucune inscription trouvée pour ce cours.</div>";
} else {
    echo "<table border='1' cellpadding='4'><tr><th>ID</th><th>etudiant_id</th><th>cours_id</th></tr>";
    foreach ($inscriptions as $row) {
        echo "<tr><td>" . htmlspecialchars($row['id']) . "</td><td>" . htmlspecialchars($row['etudiant_id']) . "</td><td>" . htmlspecialchars($row['cours_id']) . "</td></tr>";
    }
    echo "</table>";
}

// Vérifier les étudiants réellement inscrits
$etudiants = db_query("SELECT e.* FROM etudiants e JOIN inscriptions i ON e.id = i.etudiant_id WHERE i.cours_id = ? ORDER BY e.nom, e.prenom", [$cours_id]);
echo "<h2>Étudiants inscrits à ce cours</h2>";
if (empty($etudiants)) {
    echo "<div style='color:red'>Aucun étudiant trouvé pour ce cours.</div>";
} else {
    echo "<table border='1' cellpadding='4'><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Matricule</th></tr>";
    foreach ($etudiants as $etudiant) {
        echo "<tr><td>" . htmlspecialchars($etudiant['id']) . "</td><td>" . htmlspecialchars($etudiant['nom']) . "</td><td>" . htmlspecialchars($etudiant['prenom']) . "</td><td>" . htmlspecialchars($etudiant['matricule']) . "</td></tr>";
    }
    echo "</table>";
}
