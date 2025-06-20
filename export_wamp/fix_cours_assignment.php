<?php
/**
 * Script pour vérifier et corriger l'attribution des cours aux enseignants
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

// Vérifier les enseignants et leurs cours
echo "<h2>Enseignants et leurs cours</h2>";
$enseignants = db_query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'enseignant' ORDER BY nom, prenom");

foreach ($enseignants as $enseignant) {
    // Vérifier les cours de l'enseignant
    $cours = db_query("SELECT c.id, c.nom, c.code FROM cours c WHERE c.enseignant_id = ?", [$enseignant['id']]);
    
    echo "<h3>{$enseignant['nom']} {$enseignant['prenom']} ({$enseignant['email']})</h3>";
    
    if (empty($cours)) {
        show_message("Aucun cours attribué à cet enseignant.", 'warning');
    } else {
        echo "<table class='table'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Code</th></tr>";
        foreach ($cours as $c) {
            echo "<tr>";
            echo "<td>{$c['id']}</td>";
            echo "<td>{$c['nom']}</td>";
            echo "<td>{$c['code']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
