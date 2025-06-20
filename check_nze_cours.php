<?php
/**
 * Script pour vérifier les cours de l'enseignant Nze
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['user_id']) || !est_admin()) {
    die("Accès non autorisé.");
}

// Fonction pour afficher un message
function show_message($message, $type = 'info') {
    $classes = $type === 'success' ? 'alert-success' : ($type === 'danger' ? 'alert-danger' : 'alert-info');
    echo "<div class='alert {$classes}'>{$message}</div>";
}

// 1. Récupérer l'ID de l'enseignant Nze
$enseignant_nze = db_query_single("SELECT id, nom, prenom FROM utilisateurs WHERE nom = 'Nze' AND role = 'enseignant'");

if (!$enseignant_nze) {
    show_message("Enseignant Nze non trouvé dans la base de données.", 'danger');
    die();
}

show_message("Enseignant trouvé: {$enseignant_nze['nom']} {$enseignant_nze['prenom']} (ID: {$enseignant_nze['id']})");

// 2. Afficher les cours actuels de Nze
echo "<h2>Cours actuels de l'enseignant Nze</h2>";
$cours_actuels = db_query("SELECT c.id, c.nom, c.code, c.description, 
                        COUNT(DISTINCT i.etudiant_id) as nb_etudiants
                        FROM cours c
                        LEFT JOIN inscriptions i ON c.id = i.cours_id
                        WHERE c.enseignant_id = ?
                        GROUP BY c.id, c.nom, c.code, c.description
                        ORDER BY c.nom", [$enseignant_nze['id']]);

echo "<table class='table'>";
echo "<tr><th>ID</th><th>Nom</th><th>Code</th><th>Description</th><th>Étudiants</th></tr>";
foreach ($cours_actuels as $c) {
    echo "<tr>";
    echo "<td>{$c['id']}</td>";
    echo "<td>{$c['nom']}</td>";
    echo "<td>{$c['code']}</td>";
    echo "<td>{$c['description']}</td>";
    echo "<td>{$c['nb_etudiants']}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Afficher tous les cours disponibles
echo "<h2>Tous les cours disponibles</h2>";
$cours_tous = db_query("SELECT id, nom, code, description, enseignant_id FROM cours ORDER BY nom");

echo "<table class='table'>";
echo "<tr><th>ID</th><th>Nom</th><th>Code</th><th>Description</th><th>Enseignant ID</th></tr>";
foreach ($cours_tous as $c) {
    echo "<tr>";
    echo "<td>{$c['id']}</td>";
    echo "<td>{$c['nom']}</td>";
    echo "<td>{$c['code']}</td>";
    echo "<td>{$c['description']}</td>";
    echo "<td>{$c['enseignant_id']}</td>";
    echo "</tr>";
}
echo "</table>";

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
