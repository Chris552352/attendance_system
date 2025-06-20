<?php
/**
 * Script pour mettre à jour automatiquement les attributions des cours aux enseignants
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

// 1. Récupérer tous les enseignants
echo "<h2>Mise à jour des attributions des cours</h2>";
$enseignants = db_query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'enseignant' ORDER BY nom, prenom");

if (empty($enseignants)) {
    show_message("Aucun enseignant trouvé dans la base de données.", 'warning');
    die();
}

// 2. Pour chaque enseignant, identifier ses cours
foreach ($enseignants as $enseignant) {
    echo "<h3>Enseignant: {$enseignant['nom']} {$enseignant['prenom']} ({$enseignant['email']})</h3>";
    
    // Identifier les cours qui devraient être attribués à cet enseignant
    // Ici, nous allons utiliser le nom de l'enseignant pour identifier ses cours
    $nom_enseignant = strtolower($enseignant['nom']);
    
    // Rechercher les cours qui contiennent le nom de l'enseignant dans leur nom ou description
    $cours_potentiels = db_query("SELECT id, nom, code, description, enseignant_id 
                                FROM cours 
                                WHERE LOWER(nom) LIKE ? 
                                OR LOWER(description) LIKE ? 
                                OR LOWER(code) LIKE ?", 
                                ["%{$nom_enseignant}%", "%{$nom_enseignant}%", "%{$nom_enseignant}%"]);
    
    if (!empty($cours_potentiels)) {
        foreach ($cours_potentiels as $c) {
            // Vérifier si le cours n'est pas déjà attribué à cet enseignant
            if ($c['enseignant_id'] != $enseignant['id']) {
                // Mettre à jour l'attribution du cours
                $sql = "UPDATE cours SET enseignant_id = ? WHERE id = ?";
                if (db_exec($sql, [$enseignant['id'], $c['id']])) {
                    show_message("Cours " . htmlspecialchars($c['nom']) . " (" . $c['code'] . ") attribué à l'enseignant avec succès.", 'success');
                } else {
                    show_message("Erreur lors de l'attribution du cours " . htmlspecialchars($c['nom']) . ": " . db_error(), 'danger');
                }
            }
        }
    } else {
        show_message("Aucun cours trouvé pour cet enseignant.", 'warning');
    }
}

// 3. Vérifier les cours qui n'ont pas d'enseignant
$cours_sans_enseignant = db_query("SELECT id, nom, code, description 
                                FROM cours 
                                WHERE enseignant_id IS NULL 
                                OR enseignant_id = 0");

if (!empty($cours_sans_enseignant)) {
    echo "<h3>Cours sans enseignant</h3>";
    echo "<table class='table'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Code</th><th>Description</th></tr>";
    foreach ($cours_sans_enseignant as $c) {
        echo "<tr>";
        echo "<td>{$c['id']}</td>";
        echo "<td>{$c['nom']}</td>";
        echo "<td>{$c['code']}</td>";
        echo "<td>{$c['description']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Afficher un résumé final
show_message("Mise à jour terminée. Les cours ont été attribués automatiquement aux enseignants correspondants.");

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
