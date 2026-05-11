<?php
/**
 * Script de vérification de la structure des cours et des enseignants
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';
require_once 'includes/auth.php';
require_auth();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

// Vérifier la structure de la table cours
echo "<h2>Structure de la table cours</h2>";
$structure = db_query("DESCRIBE cours");
echo "<table class='table'>";
echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
foreach ($structure as $field) {
    echo "<tr>";
    echo "<td>{$field['Field']}</td>";
    echo "<td>{$field['Type']}</td>";
    echo "<td>{$field['Null']}</td>";
    echo "<td>{$field['Key']}</td>";
    echo "<td>{$field['Default']}</td>";
    echo "<td>{$field['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Vérifier les données des cours
echo "<h2>Données des cours</h2>";
$cours = db_query("SELECT * FROM cours");
echo "<table class='table'>";
echo "<tr><th>ID</th><th>Nom</th><th>Code</th><th>Enseignant ID</th><th>Description</th></tr>";
foreach ($cours as $c) {
    echo "<tr>";
    echo "<td>{$c['id']}</td>";
    echo "<td>{$c['nom']}</td>";
    echo "<td>{$c['code']}</td>";
    echo "<td>{$c['enseignant_id']}</td>";
    echo "<td>{$c['description']}</td>";
    echo "</tr>";
}
echo "</table>";

// Vérifier les données des utilisateurs
echo "<h2>Données des utilisateurs</h2>";
$utilisateurs = db_query("SELECT id, nom, prenom, email, role FROM utilisateurs");
echo "<table class='table'>";
echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th></tr>";
foreach ($utilisateurs as $u) {
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td>{$u['nom']}</td>";
    echo "<td>{$u['prenom']}</td>";
    echo "<td>{$u['email']}</td>";
    echo "<td>{$u['role']}</td>";
    echo "</tr>";
}
echo "</table>";

// Vérifier les cours attribués à chaque enseignant
echo "<h2>Cours par enseignant</h2>";
$enseignants_cours = db_query("SELECT u.id as utilisateur_id, u.nom, u.prenom, c.id as cours_id, c.nom as cours_nom 
                            FROM utilisateurs u 
                            JOIN cours c ON u.id = c.enseignant_id 
                            WHERE u.role = 'enseignant'
                            ORDER BY u.nom, u.prenom");

echo "<table class='table'>";
echo "<tr><th>Enseignant</th><th>Cours</th></tr>";
$last_enseignant = null;
foreach ($enseignants_cours as $row) {
    if ($row['nom'] . ' ' . $row['prenom'] !== $last_enseignant) {
        if ($last_enseignant !== null) echo "</tr>";
        echo "<tr><td rowspan='1'>{$row['nom']} {$row['prenom']}</td><td>{$row['cours_nom']}</td></tr>";
        $last_enseignant = $row['nom'] . ' ' . $row['prenom'];
    } else {
        echo "<tr><td>{$row['cours_nom']}</td></tr>";
    }
}
if ($last_enseignant !== null) echo "</tr>";
echo "</table>";

// Liste tous les cours avec leur enseignant
$cours = db_query("SELECT c.id, c.nom, c.code, c.enseignant_id, u.nom as enseignant_nom, u.prenom as enseignant_prenom, u.email as enseignant_email FROM cours c LEFT JOIN utilisateurs u ON c.enseignant_id = u.id ORDER BY c.nom");

// Affichage
echo "<h2>Diagnostic des Cours et Assignation Enseignant</h2>";
echo "<table border='1' cellpadding='6' style='border-collapse:collapse;'>";
echo "<tr style='background:#1976d2;color:white;'><th>ID</th><th>Nom</th><th>Code</th><th>Enseignant</th><th>Email</th><th>Assigné à moi ?</th></tr>";
foreach ($cours as $c) {
    $is_mine = ($c['enseignant_id'] == $user_id) ? "<span style='color:green;font-weight:bold;'>OUI</span>" : "<span style='color:red;'>NON</span>";
    echo "<tr>";
    echo "<td>" . htmlspecialchars($c['id']) . "</td>";
    echo "<td>" . htmlspecialchars($c['nom']) . "</td>";
    echo "<td>" . htmlspecialchars($c['code']) . "</td>";
    echo "<td>" . htmlspecialchars($c['enseignant_nom'] . ' ' . $c['enseignant_prenom']) . "</td>";
    echo "<td>" . htmlspecialchars($c['enseignant_email']) . "</td>";
    echo "<td style='text-align:center;'>$is_mine</td>";
    echo "</tr>";
}
echo "</table>";

// Message explicatif
if ($role === 'enseignant') {
    echo "<p style='margin-top:2em;'><b>Seuls les cours marqués <span style='color:green;'>OUI</span> sont disponibles pour la génération de QR code avec votre compte.</b></p>";
} else {
    echo "<p style='margin-top:2em;'><b>Vous êtes administrateur, vous pouvez voir tous les cours.</b></p>";
}
?>
