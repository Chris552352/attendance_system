<?php
/**
 * Script pour mettre à jour la structure de la table utilisateurs et ajouter le champ role
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['user_id']) || !est_admin()) {
    die("Accès non autorisé.");
}

// Fonction pour afficher un message
function show_message($message, $type = 'info') {
    $classes = $type === 'success' ? 'alert-success' : ($type === 'danger' ? 'alert-danger' : 'alert-info');
    echo "<div class='alert {$classes}'>{$message}</div>";
}

// 1. Vérifier si le champ role existe déjà
echo "<h2>Mise à jour de la structure de la base de données</h2>";

// Vérifier la structure actuelle de la table utilisateurs
$columns = db_query("SHOW COLUMNS FROM utilisateurs");
$has_role = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'role') {
        $has_role = true;
        break;
    }
}

if (!$has_role) {
    // Ajouter le champ role
    $sql = "ALTER TABLE utilisateurs ADD COLUMN role ENUM('admin', 'enseignant', 'etudiant') NOT NULL DEFAULT 'etudiant'";
    if (db_exec($sql)) {
        show_message("Champ role ajouté avec succès à la table utilisateurs.", 'success');
    } else {
        show_message("Erreur lors de l'ajout du champ role: " . db_error(), 'danger');
        die();
    }
} else {
    show_message("Le champ role existe déjà dans la table utilisateurs.", 'info');
}

// 2. Mettre à jour les rôles existants
// D'abord, vérifier les utilisateurs existants
$utilisateurs = db_query("SELECT id, email FROM utilisateurs");

if (empty($utilisateurs)) {
    show_message("Aucun utilisateur trouvé dans la base de données.", 'warning');
    die();
}

// Mettre à jour les rôles
foreach ($utilisateurs as $user) {
    // Par défaut, on met tous les utilisateurs comme étudiants
    $sql = "UPDATE utilisateurs SET role = 'etudiant' WHERE id = ?";
    if (db_exec($sql, [$user['id']])) {
        show_message("Rôle mis à jour pour l'utilisateur avec succès.", 'success');
    } else {
        show_message("Erreur lors de la mise à jour du rôle: " . db_error(), 'danger');
    }
}

// 3. Afficher un résumé final
show_message("<strong>Résumé de la mise à jour :</strong>", 'info');

// Compter les utilisateurs par rôle
$utilisateurs_par_role = db_query("SELECT role, COUNT(*) as nb FROM utilisateurs GROUP BY role");

echo "<h3>Nombre d'utilisateurs par rôle</h3>";
echo "<table class='table'>";
echo "<tr><th>Rôle</th><th>Nombre</th></tr>";
foreach ($utilisateurs_par_role as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
    echo "<td>" . $row['nb'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
