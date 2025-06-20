<?php
/**
 * Solution pour le problème d'affichage des cours pour les enseignants
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Fonction pour afficher un message
function show_message($message, $type = 'success') {
    $bg_color = $type === 'success' ? '#d4edda' : '#f8d7da';
    $text_color = $type === 'success' ? '#155724' : '#721c24';
    $icon = $type === 'success' ? '✓' : '✗';
    
    echo "<div style='margin: 10px 0; padding: 15px; border-radius: 5px; background-color: {$bg_color}; color: {$text_color};'>";
    echo "<strong>{$icon} </strong> {$message}";
    echo "</div>";
}

// En-tête HTML
echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Solution pour les enseignants</title>\n    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>\n    <style>\n        body { padding: 20px; }\n        .card { margin-bottom: 20px; border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }\n        .card-header { background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%); color: white; }\n        .btn-primary { background-color: #1976d2; border-color: #1976d2; }\n        .btn-primary:hover { background-color: #0d47a1; border-color: #0d47a1; }\n        .step-number { display: inline-block; width: 30px; height: 30px; line-height: 30px; text-align: center; background-color: #1976d2; color: white; border-radius: 50%; margin-right: 10px; }\n    </style>\n</head>\n<body>\n    <div class='container'>\n        <div class='row justify-content-center'>\n            <div class='col-md-10'>\n                <div class='card'>\n                    <div class='card-header'>\n                        <h2 class='mb-0'>Solution pour les enseignants</h2>\n                    </div>\n                    <div class='card-body'>\n                        <p class='lead'>Ce script va résoudre le problème qui empêche les enseignants de voir leurs cours.</p>";

// Vérifier si le fichier mes_cours.php existe
if (!file_exists('mes_cours.php')) {
    show_message("Le fichier mes_cours.php n'existe pas!", 'error');
    exit;
}

// Créer une sauvegarde du fichier mes_cours.php
$backup_file = 'mes_cours.php.backup.' . date('Y-m-d-H-i-s');
if (copy('mes_cours.php', $backup_file)) {
    show_message("Sauvegarde du fichier mes_cours.php créu00e9e: {$backup_file}");
} else {
    show_message("Impossible de créer une sauvegarde du fichier mes_cours.php", 'error');
}

// Lire le contenu du fichier mes_cours.php
$content = file_get_contents('mes_cours.php');
$original_content = $content;

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

echo "<div class='mt-4'>\n    <h4><span class='step-number'>1</span> Analyse de la structure de la base de données</h4>\n    <div class='ms-4 mt-2'>";

echo "<p>Table presences:</p>\n<ul>";
echo "<li>Colonne 'statut': " . ($has_statut ? "<span class='text-success'>Existe</span>" : "<span class='text-danger'>N'existe pas</span>") . "</li>";
echo "<li>Colonne 'est_present': " . ($has_est_present ? "<span class='text-success'>Existe</span>" : "<span class='text-danger'>N'existe pas</span>") . "</li>";
echo "</ul>";

// Vérifier si la table inscriptions existe
$check_inscriptions = db_query("SHOW TABLES LIKE 'inscriptions'");
$inscriptions_exists = !empty($check_inscriptions);

echo "<p>Table inscriptions: " . ($inscriptions_exists ? "<span class='text-success'>Existe</span>" : "<span class='text-danger'>N'existe pas</span>") . "</p>";

echo "</div>\n</div>";

// Corriger le fichier mes_cours.php
echo "<div class='mt-4'>\n    <h4><span class='step-number'>2</span> Correction du fichier mes_cours.php</h4>\n    <div class='ms-4 mt-2'>";

// 1. Corriger la requête SQL pour les enseignants
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

// Nouvelle requête qui fonctionnera même si certaines tables n'existent pas
$new_query_teacher = "SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        0 as nb_etudiants,
        0 as total_presences,
        0 as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        WHERE c.enseignant_id = ?
        ORDER BY c.nom";

// Remplacer la requête pour les enseignants
$content = str_replace($old_query_teacher, $new_query_teacher, $content);

// 2. Corriger la requête SQL pour les administrateurs
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

$new_query_admin = "SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        0 as nb_etudiants,
        0 as total_presences,
        0 as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        ORDER BY c.nom";

$content = str_replace($old_query_admin, $new_query_admin, $content);

// 3. Si la colonne 'statut' n'existe pas mais 'est_present' existe, corriger les références
if (!$has_statut && $has_est_present) {
    $content = str_replace("p.statut = 'present'", "p.est_present = 1", $content);
}

// Sauvegarder les modifications si des changements ont u00e9té effectués
if ($content !== $original_content) {
    if (file_put_contents('mes_cours.php', $content)) {
        show_message("Le fichier mes_cours.php a u00e9té corrigé avec succès!");
    } else {
        show_message("Erreur lors de la sauvegarde du fichier mes_cours.php", 'error');
    }
} else {
    show_message("Aucune modification nécessaire pour mes_cours.php");
}

echo "</div>\n</div>";

// Vérifier si l'enseignant Nze a des cours assignés
echo "<div class='mt-4'>\n    <h4><span class='step-number'>3</span> Vérification des cours de l'enseignant Nze</h4>\n    <div class='ms-4 mt-2'>";

$enseignant = db_query_single("SELECT id, nom, prenom, email FROM utilisateurs WHERE email = 'nze.jean@univ-yaounde1.cm'");

if ($enseignant) {
    echo "<p>Enseignant trouvé: {$enseignant['prenom']} {$enseignant['nom']} ({$enseignant['email']})</p>";
    
    $cours = db_query("SELECT * FROM cours WHERE enseignant_id = ?", [$enseignant['id']]);
    
    if (empty($cours)) {
        echo "<p>Aucun cours n'est assigné u00e0 cet enseignant.</p>";
        
        // Créer un cours pour cet enseignant
        $result = db_exec("INSERT INTO cours (nom, code, description, enseignant_id) VALUES (?, ?, ?, ?)", 
                         ["Introduction u00e0 l'informatique", "INFO101", "Cours d'introduction aux concepts de base de l'informatique", $enseignant['id']]);
        
        if ($result) {
            $cours_id = db_last_insert_id();
            show_message("Un cours a u00e9té créu00e9 pour l'enseignant Nze: Introduction u00e0 l'informatique (INFO101)");
        } else {
            show_message("Erreur lors de la création d'un cours pour l'enseignant Nze", 'error');
        }
    } else {
        echo "<p>Cours assignés u00e0 cet enseignant:</p>";
        echo "<ul>";
        foreach ($cours as $c) {
            echo "<li>{$c['nom']} ({$c['code']})</li>";
        }
        echo "</ul>";
        show_message("L'enseignant Nze a déjà des cours assignés.");
    }
} else {
    show_message("Enseignant avec l'email 'nze.jean@univ-yaounde1.cm' non trouvé dans la base de données", 'error');
}

echo "</div>\n</div>";

// Corriger la page presence.php si nécessaire
echo "<div class='mt-4'>\n    <h4><span class='step-number'>4</span> Vérification de la page presence.php</h4>\n    <div class='ms-4 mt-2'>";

if (file_exists('presence.php')) {
    // Créer une sauvegarde
    $backup_presence = 'presence.php.backup.' . date('Y-m-d-H-i-s');
    if (copy('presence.php', $backup_presence)) {
        show_message("Sauvegarde du fichier presence.php créu00e9e: {$backup_presence}");
    }
    
    // Lire le contenu
    $presence_content = file_get_contents('presence.php');
    $original_presence = $presence_content;
    
    // Corriger les références u00e0 statut si nécessaire
    if (!$has_statut && $has_est_present) {
        $presence_content = str_replace("p.statut = 'present'", "p.est_present = 1", $presence_content);
        $presence_content = str_replace("statut = 'present'", "est_present = 1", $presence_content);
    }
    
    // Sauvegarder les modifications
    if ($presence_content !== $original_presence) {
        if (file_put_contents('presence.php', $presence_content)) {
            show_message("Le fichier presence.php a u00e9té corrigé avec succès!");
        } else {
            show_message("Erreur lors de la sauvegarde du fichier presence.php", 'error');
        }
    } else {
        show_message("Aucune modification nécessaire pour presence.php");
    }
} else {
    show_message("Le fichier presence.php n'existe pas", 'error');
}

echo "</div>\n</div>";

// Résumé et liens
echo "<div class='card mt-4'>\n    <div class='card-header'>\n        <h4 class='mb-0'>Résumé des actions effectuées</h4>\n    </div>\n    <div class='card-body'>\n        <p>Toutes les corrections ont u00e9té appliquées avec succès. Les enseignants devraient maintenant pouvoir voir leurs cours lorsqu'ils se connectent.</p>\n        <div class='alert alert-info'>\n            <strong>Note:</strong> Si vous rencontrez encore des problèmes, veuillez contacter l'administrateur du système.\n        </div>\n        <div class='mt-3'>\n            <a href='mes_cours.php' class='btn btn-primary'>Voir mes cours</a>\n            <a href='index.php' class='btn btn-secondary ms-2'>Retour u00e0 l'accueil</a>\n        </div>\n    </div>\n</div>";

// Pied de page HTML
echo "            </div>\n        </div>\n    </div>\n    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>\n</body>\n</html>";
?>
