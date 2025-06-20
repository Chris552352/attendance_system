<?php
/**
 * Script pour vérifier l'attribution des cours à l'enseignant Nze
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

// 1. Récupérer les informations de l'enseignant Nze
$enseignant = db_query_single("SELECT id, nom, prenom, email FROM utilisateurs WHERE nom = 'Nze' AND role = 'enseignant'");

if (!$enseignant) {
    show_message("Enseignant Nze non trouvé dans la base de données.", 'danger');
    die();
}

// 2. Récupérer tous les cours de l'enseignant Nze
$cours = db_query("SELECT c.*, COUNT(DISTINCT i.etudiant_id) as nb_etudiants
                FROM cours c
                LEFT JOIN inscriptions i ON c.id = i.cours_id
                WHERE c.enseignant_id = ?
                GROUP BY c.id, c.nom, c.code, c.description
                ORDER BY c.nom", [$enseignant['id']]);

// 3. Afficher les résultats
echo "<h2>Cours de l'enseignant Nze</h2>";
echo "<div class='alert alert-info'>";
echo "Enseignant: {$enseignant['nom']} {$enseignant['prenom']} ({$enseignant['email']})";
echo "</div>";

echo "<table class='table'>";
echo "<tr><th>ID</th><th>Nom</th><th>Code</th><th>Description</th><th>Étudiants</th></tr>";
foreach ($cours as $c) {
    echo "<tr>";
    echo "<td>{$c['id']}</td>";
    echo "<td>{$c['nom']}</td>";
    echo "<td>{$c['code']}</td>";
    echo "<td>{$c['description']}</td>";
    echo "<td>{$c['nb_etudiants']}</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Afficher un résumé
show_message("Vérification terminée. Les cours de l'enseignant Nze sont maintenant correctement affichés.");

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
