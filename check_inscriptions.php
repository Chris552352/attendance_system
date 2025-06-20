<?php
/**
 * Script pour vérifier les inscriptions des étudiants
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

// 1. Récupérer tous les étudiants
$etudiants = db_query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'etudiant' ORDER BY nom, prenom");

if (empty($etudiants)) {
    show_message("Aucun étudiant trouvé dans la base de données.", 'warning');
    die();
}

// 2. Pour chaque étudiant, afficher ses inscriptions
echo "<h2>Inscriptions des étudiants</h2>";
foreach ($etudiants as $etudiant) {
    echo "<h3>" . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) . " (" . $etudiant['email'] . ")</h3>";
    
    // Récupérer les cours de l'étudiant
    $cours = db_query("SELECT c.* 
                      FROM cours c 
                      JOIN inscriptions i ON c.id = i.cours_id 
                      WHERE i.etudiant_id = ? 
                      ORDER BY c.nom", [$etudiant['id']]);
    
    if (empty($cours)) {
        show_message("Aucun cours trouvé pour cet étudiant.", 'warning');
        continue;
    }
    
    echo "<table class='table'>";
    echo "<tr><th>Code</th><th>Nom du Cours</th><th>Enseignant</th></tr>";
    
    foreach ($cours as $c) {
        // Récupérer le nom de l'enseignant
        $enseignant = db_query_single("SELECT nom, prenom FROM utilisateurs WHERE id = ?", [$c['enseignant_id']]);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($c['code']) . "</td>";
        echo "<td>" . htmlspecialchars($c['nom']) . "</td>";
        echo "<td>";
        if ($enseignant) {
            echo htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']);
        } else {
            echo "<span class='text-muted'>Non assigné</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// 3. Afficher un résumé des inscriptions
show_message("<strong>Résumé des inscriptions :</strong>", 'info');

// Compter les inscriptions par cours
$inscriptions_par_cours = db_query("SELECT c.nom, c.code, COUNT(*) as nb_etudiants 
                                  FROM cours c 
                                  JOIN inscriptions i ON c.id = i.cours_id 
                                  GROUP BY c.id, c.nom, c.code 
                                  ORDER BY c.nom");

echo "<h3>Nombre d'étudiants par cours</h3>";
echo "<table class='table'>";
echo "<tr><th>Code</th><th>Nom du Cours</th><th>Nombre d'Étudiants</th></tr>";
foreach ($inscriptions_par_cours as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['code']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
    echo "<td>" . $row['nb_etudiants'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
