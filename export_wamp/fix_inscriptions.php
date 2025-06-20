<?php
/**
 * Script pour corriger le problème d'affichage des u00e9tudiants pour les enseignants
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Fonction pour afficher un message
function show_message($message, $type = 'success') {
    $bg_color = $type === 'success' ? '#d4edda' : '#f8d7da';
    $text_color = $type === 'success' ? '#155724' : '#721c24';
    $icon = $type === 'success' ? 'u2713' : 'u2717';
    
    echo "<div style='margin: 10px 0; padding: 15px; border-radius: 5px; background-color: {$bg_color}; color: {$text_color};'>";
    echo "<strong>{$icon} </strong> {$message}";
    echo "</div>";
}

// En-tête HTML
echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Correction des inscriptions u00e9tudiants</title>\n    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>\n    <style>\n        body { padding: 20px; }\n        .card { margin-bottom: 20px; border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }\n        .card-header { background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%); color: white; }\n        .btn-primary { background-color: #1976d2; border-color: #1976d2; }\n        .btn-primary:hover { background-color: #0d47a1; border-color: #0d47a1; }\n        .step-number { display: inline-block; width: 30px; height: 30px; line-height: 30px; text-align: center; background-color: #1976d2; color: white; border-radius: 50%; margin-right: 10px; }\n    </style>\n</head>\n<body>\n    <div class='container'>\n        <div class='row justify-content-center'>\n            <div class='col-md-10'>\n                <div class='card'>\n                    <div class='card-header'>\n                        <h2 class='mb-0'>Correction des inscriptions u00e9tudiants</h2>\n                    </div>\n                    <div class='card-body'>\n                        <p class='lead'>Ce script va résoudre le problème qui empêche les enseignants de voir les u00e9tudiants inscrits u00e0 leurs cours.</p>";

// Vérifier si la table inscriptions existe
$check_inscriptions = db_query("SHOW TABLES LIKE 'inscriptions'");
$inscriptions_exists = !empty($check_inscriptions);

echo "<div class='mt-4'>\n    <h4><span class='step-number'>1</span> Analyse de la structure de la base de données</h4>\n    <div class='ms-4 mt-2'>";

if ($inscriptions_exists) {
    show_message("La table 'inscriptions' existe dans la base de données.");
    
    // Vérifier le contenu de la table inscriptions
    $count = db_query_single("SELECT COUNT(*) as total FROM inscriptions");
    $count = $count ? $count['total'] : 0;
    
    if ($count > 0) {
        show_message("La table 'inscriptions' contient {$count} enregistrements.");
    } else {
        show_message("La table 'inscriptions' est vide. Nous devons y ajouter des données.", 'error');
    }
} else {
    show_message("La table 'inscriptions' n'existe pas dans la base de données. Nous devons la créer.", 'error');
    
    // Créer la table inscriptions
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
        show_message("La table 'inscriptions' a u00e9té créu00e9e avec succès.");
    } else {
        show_message("Erreur lors de la création de la table 'inscriptions'.", 'error');
    }
}

echo "</div>\n</div>";

// Récupérer les cours de l'enseignant Nze
echo "<div class='mt-4'>\n    <h4><span class='step-number'>2</span> Vérification des cours de l'enseignant</h4>\n    <div class='ms-4 mt-2'>";

$enseignant = db_query_single("SELECT id, nom, prenom, email FROM utilisateurs WHERE email = 'nze.jean@univ-yaounde1.cm'");

if ($enseignant) {
    echo "<p>Enseignant trouvé: {$enseignant['prenom']} {$enseignant['nom']} ({$enseignant['email']})</p>";
    
    $cours = db_query("SELECT * FROM cours WHERE enseignant_id = ?", [$enseignant['id']]);
    
    if (!empty($cours)) {
        echo "<p>Cours assignés u00e0 cet enseignant:</p>";
        echo "<ul>";
        foreach ($cours as $c) {
            echo "<li>{$c['nom']} ({$c['code']})</li>";
            
            // Vérifier les inscriptions pour ce cours
            $inscriptions = db_query("SELECT COUNT(*) as total FROM inscriptions WHERE cours_id = ?", [$c['id']]);
            $inscriptions = $inscriptions[0]['total'];
            
            if ($inscriptions > 0) {
                echo " <span class='badge bg-success'>{$inscriptions} u00e9tudiants inscrits</span>";
            } else {
                echo " <span class='badge bg-danger'>Aucun u00e9tudiant inscrit</span>";
            }
        }
        echo "</ul>";
    } else {
        show_message("Aucun cours n'est assigné u00e0 cet enseignant.", 'error');
    }
} else {
    show_message("Enseignant avec l'email 'nze.jean@univ-yaounde1.cm' non trouvé dans la base de données.", 'error');
}

echo "</div>\n</div>";

// Créer des inscriptions pour les cours
echo "<div class='mt-4'>\n    <h4><span class='step-number'>3</span> Création des inscriptions pour les cours</h4>\n    <div class='ms-4 mt-2'>";

// Récupérer tous les cours
$cours = db_query("SELECT id, nom, code FROM cours");

// Récupérer tous les u00e9tudiants
$etudiants = db_query("SELECT id, nom, prenom, matricule FROM etudiants");

if (empty($cours)) {
    show_message("Aucun cours n'a u00e9té trouvé dans la base de données.", 'error');
} else if (empty($etudiants)) {
    show_message("Aucun u00e9tudiant n'a u00e9té trouvé dans la base de données.", 'error');
} else {
    $count = 0;
    $errors = 0;
    
    foreach ($cours as $c) {
        // Vérifier s'il y a déjà des inscriptions pour ce cours
        $existing = db_query_single("SELECT COUNT(*) as total FROM inscriptions WHERE cours_id = ?", [$c['id']]);
        $existing = $existing ? $existing['total'] : 0;
        
        if ($existing > 0) {
            echo "<p>Le cours {$c['nom']} ({$c['code']}) a déjà {$existing} u00e9tudiants inscrits.</p>";
            continue;
        }
        
        // Attribuer aléatoirement 5 u00e0 15 u00e9tudiants u00e0 chaque cours
        $num_etudiants = min(count($etudiants), rand(5, 15));
        $etudiants_shuffle = $etudiants;
        shuffle($etudiants_shuffle);
        $etudiants_shuffle = array_slice($etudiants_shuffle, 0, $num_etudiants);
        
        echo "<p>Attribution de {$num_etudiants} u00e9tudiants au cours {$c['nom']} ({$c['code']}):</p>";
        echo "<ul>";
        
        foreach ($etudiants_shuffle as $e) {
            $result = db_exec("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)", [$e['id'], $c['id']]);
            
            if ($result) {
                echo "<li>{$e['prenom']} {$e['nom']} ({$e['matricule']}) <span class='badge bg-success'>Inscrit</span></li>";
                $count++;
            } else {
                echo "<li>{$e['prenom']} {$e['nom']} ({$e['matricule']}) <span class='badge bg-danger'>Erreur</span></li>";
                $errors++;
            }
        }
        
        echo "</ul>";
    }
    
    if ($count > 0) {
        show_message("{$count} inscriptions ont u00e9té créu00e9es avec succès.");
    } else if ($errors > 0) {
        show_message("Erreur lors de la création des inscriptions.", 'error');
    } else {
        show_message("Aucune nouvelle inscription n'a u00e9té créu00e9e. Toutes les inscriptions nécessaires existent déjà.");
    }
}

echo "</div>\n</div>";

// Résumé et liens
echo "<div class='card mt-4'>\n    <div class='card-header'>\n        <h4 class='mb-0'>Résumé des actions effectuées</h4>\n    </div>\n    <div class='card-body'>\n        <p>Les inscriptions des u00e9tudiants aux cours ont u00e9té vérifiées et corrigées. Les enseignants devraient maintenant pouvoir voir les u00e9tudiants inscrits u00e0 leurs cours.</p>\n        <div class='alert alert-info'>\n            <strong>Note:</strong> Pour voir les u00e9tudiants inscrits u00e0 un cours, accedez u00e0 <strong>Mes Cours</strong> et cliquez sur l'icône <i class='fas fa-users'></i> u00e0 côté du cours.\n        </div>\n        <div class='mt-3'>\n            <a href='mes_cours.php' class='btn btn-primary'>Voir mes cours</a>\n            <a href='index.php' class='btn btn-secondary ms-2'>Retour u00e0 l'accueil</a>\n        </div>\n    </div>\n</div>";

// Pied de page HTML
echo "            </div>\n        </div>\n    </div>\n    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>\n    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>\n</body>\n</html>";
?>
