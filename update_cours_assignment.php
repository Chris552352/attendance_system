<?php
/**
 * Script pour mettre à jour l'attribution des cours aux enseignants
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

// 2. Récupérer tous les cours actuels
$cours = db_query("SELECT id, nom, code, enseignant_id FROM cours ORDER BY nom");

// 3. Identifier les cours qui devraient être attribués à Nze
$cours_nze = [];
foreach ($cours as $c) {
    // Ici, vous devriez spécifier quels cours appartiennent à Nze
    // Par exemple, si vous savez que Nze donne ces cours :
    $cours_nze_codes = ['INFO101', 'INFO102', 'INFO201']; // Remplacez par les codes réels des cours de Nze
    
    if (in_array($c['code'], $cours_nze_codes)) {
        $cours_nze[] = $c;
    }
}

// 4. Mettre à jour les cours
if (!empty($cours_nze)) {
    foreach ($cours_nze as $c) {
        // Vérifier si le cours n'est pas déjà attribué à Nze
        if ($c['enseignant_id'] != $enseignant_nze['id']) {
            $sql = "UPDATE cours SET enseignant_id = ? WHERE id = ?";
            if (db_exec($sql, [$enseignant_nze['id'], $c['id']])) {
                show_message("Cours " . htmlspecialchars($c['nom']) . " (" . $c['code'] . ") attribué à l'enseignant Nze avec succès.", 'success');
            } else {
                show_message("Erreur lors de l'attribution du cours " . htmlspecialchars($c['nom']) . ": " . db_error(), 'danger');
            }
        } else {
            show_message("Le cours " . htmlspecialchars($c['nom']) . " (" . $c['code'] . ") est déjà attribué à l'enseignant Nze.", 'info');
        }
    }
} else {
    show_message("Aucun cours trouvé pour l'enseignant Nze.", 'warning');
}

// 5. Afficher un résumé final
show_message("Mise à jour terminée. Vous pouvez maintenant vérifier les cours dans la page 'Mes Cours'.");

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
