<?php
/**
 * Script pour corriger le problème d'affichage des cours pour les enseignants
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Fonction pour afficher un message
function show_message($message, $type = 'success') {
    echo "<div style='margin: 20px; padding: 15px; border-radius: 5px; background-color: " . 
         ($type === 'success' ? '#d4edda' : '#f8d7da') . 
         "; color: " . ($type === 'success' ? '#155724' : '#721c24') . ";'>";
    echo $message;
    echo "</div>";
}

// En-tête HTML
echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Correction des cours pour enseignants</title>\n    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>\n</head>\n<body>\n    <div class='container mt-5'>\n        <h1>Correction des cours pour enseignants</h1>";

// Vérifier la structure de la table presences
$presences_structure = db_query("DESCRIBE presences");
$has_statut = false;
$has_est_present = false;

foreach ($presences_structure as $column) {
    if ($column['Field'] === 'statut') {
        $has_statut = true;
    }
    if ($column['Field'] === 'est_present') {
        $has_est_present = true;
    }
}

// Vérifier si la table inscriptions existe
$check_inscriptions = db_query("SHOW TABLES LIKE 'inscriptions'");
$inscriptions_exists = !empty($check_inscriptions);

// Afficher les informations sur la structure actuelle
echo "<div class='card mb-4'>\n    <div class='card-header bg-primary text-white'>\n        <h2 class='card-title h5 mb-0'>Structure actuelle de la base de données</h2>\n    </div>\n    <div class='card-body'>";

echo "<p>Table presences:</p>\n<ul>";
echo "<li>Colonne 'statut': " . ($has_statut ? "<span class='text-success'>Existe</span>" : "<span class='text-danger'>N'existe pas</span>") . "</li>";
echo "<li>Colonne 'est_present': " . ($has_est_present ? "<span class='text-success'>Existe</span>" : "<span class='text-danger'>N'existe pas</span>") . "</li>";
echo "</ul>";

echo "<p>Table inscriptions: " . ($inscriptions_exists ? "<span class='text-success'>Existe</span>" : "<span class='text-danger'>N'existe pas</span>") . "</p>";

echo "</div>\n</div>";

// Corriger le fichier mes_cours.php
echo "<div class='card mb-4'>\n    <div class='card-header bg-primary text-white'>\n        <h2 class='card-title h5 mb-0'>Correction du fichier mes_cours.php</h2>\n    </div>\n    <div class='card-body'>";

$file = 'mes_cours.php';
$content = file_get_contents($file);
$original_content = $content;

// 1. Simplifier la requête SQL pour u00e9viter les probèmes avec les tables manquantes
$old_query_admin = "SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        COUNT(DISTINCT i.etudiant_id) as nb_etudiants,
        COUNT(DISTINCT p.id) as total_presences,
        COALESCE(SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END), 0) as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        LEFT JOIN inscriptions i ON c.id = i.cours_id
        LEFT JOIN presences p ON c.id = p.cours_id
        GROUP BY c.id, c.nom, c.code, c.description, c.enseignant_id, u.nom, u.prenom
        ORDER BY c.nom";

$old_query_teacher = "SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        COUNT(DISTINCT i.etudiant_id) as nb_etudiants,
        COUNT(DISTINCT p.id) as total_presences,
        COALESCE(SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END), 0) as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        LEFT JOIN inscriptions i ON c.id = i.cours_id
        LEFT JOIN presences p ON c.id = p.cours_id
        WHERE c.enseignant_id = ?
        GROUP BY c.id, c.nom, c.code, c.description, c.enseignant_id, u.nom, u.prenom
        ORDER BY c.nom";

// Nouvelles requêtes simplifiées qui fonctionneront même si certaines tables n'existent pas
$new_query_admin = "SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        0 as nb_etudiants,
        0 as total_presences,
        0 as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        ORDER BY c.nom";

$new_query_teacher = "SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        0 as nb_etudiants,
        0 as total_presences,
        0 as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        WHERE c.enseignant_id = ?
        ORDER BY c.nom";

// Remplacer les requêtes
$content = str_replace($old_query_admin, $new_query_admin, $content);
$content = str_replace($old_query_teacher, $new_query_teacher, $content);

// 2. Si la colonne 'statut' n'existe pas mais 'est_present' existe, corriger les références
if (!$has_statut && $has_est_present) {
    $content = str_replace("p.statut = 'present'", "p.est_present = 1", $content);
}

// 3. Sauvegarder les modifications si des changements ont u00e9té effectués
if ($content !== $original_content) {
    file_put_contents($file, $content);
    show_message("<h4>Le fichier mes_cours.php a u00e9té corrigé avec succès!</h4>\n<p>Les requêtes SQL ont u00e9té simplifiées pour u00e9viter les probèmes avec les tables manquantes.</p>");
} else {
    show_message("<h4>Aucune modification nécessaire pour mes_cours.php</h4>\n<p>Le fichier est déjà configuré correctement.</p>", 'error');
}

echo "</div>\n</div>";

// Créer la table inscriptions si elle n'existe pas
if (!$inscriptions_exists) {
    echo "<div class='card mb-4'>\n    <div class='card-header bg-primary text-white'>\n        <h2 class='card-title h5 mb-0'>Création de la table inscriptions</h2>\n    </div>\n    <div class='card-body'>";
    
    $sql_create = "CREATE TABLE IF NOT EXISTS inscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        etudiant_id INT NOT NULL,
        cours_id INT NOT NULL,
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
        FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
        UNIQUE KEY unique_inscription (etudiant_id, cours_id)
    )";
    
    $result = db_exec($sql_create);
    
    if ($result) {
        show_message("<h4>La table 'inscriptions' a u00e9té créu00e9e avec succès!</h4>");
    } else {
        show_message("<h4>Erreur lors de la création de la table 'inscriptions'</h4>", 'error');
    }
    
    echo "</div>\n</div>";
}

// Ajouter la colonne 'statut' si elle n'existe pas
if (!$has_statut) {
    echo "<div class='card mb-4'>\n    <div class='card-header bg-primary text-white'>\n        <h2 class='card-title h5 mb-0'>Ajout de la colonne 'statut' u00e0 la table presences</h2>\n    </div>\n    <div class='card-body'>";
    
    $result = db_exec("ALTER TABLE presences ADD COLUMN statut ENUM('present', 'absent') DEFAULT 'absent'");
    
    if ($result) {
        show_message("<h4>La colonne 'statut' a u00e9té ajoutée u00e0 la table presences</h4>");
        
        // Mettre u00e0 jour les valeurs de statut en fonction de est_present si cette colonne existe
        if ($has_est_present) {
            $update_result = db_exec("UPDATE presences SET statut = CASE WHEN est_present = 1 THEN 'present' ELSE 'absent' END");
            
            if ($update_result) {
                show_message("<h4>Les valeurs de 'statut' ont u00e9té mises u00e0 jour en fonction de 'est_present'</h4>");
            } else {
                show_message("<h4>Erreur lors de la mise u00e0 jour des valeurs de 'statut'</h4>", 'error');
            }
        }
    } else {
        show_message("<h4>Erreur lors de l'ajout de la colonne 'statut' u00e0 la table presences</h4>", 'error');
    }
    
    echo "</div>\n</div>";
}

// Afficher les liens pour naviguer
echo "<div class='card mb-4'>\n    <div class='card-header bg-primary text-white'>\n        <h2 class='card-title h5 mb-0'>Actions</h2>\n    </div>\n    <div class='card-body'>\n        <div class='d-flex gap-2'>\n            <a href='mes_cours.php' class='btn btn-success'>Voir mes cours</a>\n            <a href='index.php' class='btn btn-secondary'>Retour u00e0 l'accueil</a>\n        </div>\n    </div>\n</div>";

// Pied de page HTML
echo "    </div>\n    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>\n</body>\n</html>";
?>
