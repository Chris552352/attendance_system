<?php
/**
 * Script pour ajouter des données de test spécifiques au Cameroun
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

// 1. Ajouter les enseignants
echo "<h2>Ajout des enseignants</h2>";

// Enseignant 1: Jean Nze
$enseignant1 = [
    'nom' => 'Nze',
    'prenom' => 'Jean',
    'email' => 'nze.jean@univ-yaounde1.cm'
];

// Enseignant 2: Marie Moukoko
$enseignant2 = [
    'nom' => 'Moukoko',
    'prenom' => 'Marie',
    'email' => 'moukoko.marie@univ-yaounde1.cm'
];

// Ajouter les enseignants
$enseignants = [$enseignant1, $enseignant2];
foreach ($enseignants as $enseignant) {
    // Vérifier si l'enseignant existe déjà
    $existing = db_query_single("SELECT id FROM utilisateurs WHERE email = ?", [$enseignant['email']]);
    
    if ($existing) {
        show_message("Enseignant " . htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) . " existe déjà.", 'warning');
        continue;
    }

    // Générer un mot de passe temporaire
    $password = 'enseignant123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insérer l'utilisateur
    $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'enseignant')";
    if (db_exec($sql, [$enseignant['nom'], $enseignant['prenom'], $enseignant['email'], $hashed_password])) {
        $enseignant_id = db_last_insert_id();
        show_message("Enseignant " . htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) . " ajouté avec succès.", 'success');
        
        // Ajouter les cours pour Jean Nze
        if ($enseignant['nom'] === 'Nze') {
            $cours = [
                [
                    'nom' => "Introduction à l'Informatique",
                    'code' => 'INFO101',
                    'description' => "Cours d'introduction aux concepts fondamentaux de l'informatique"
                ],
                [
                    'nom' => "Programmation en Python",
                    'code' => 'INFO201',
                    'description' => "Initiation à la programmation avec le langage Python"
                ]
            ];
        }
        // Ajouter les cours pour Marie Moukoko
        else if ($enseignant['nom'] === 'Moukoko') {
            $cours = [
                [
                    'nom' => 'Mathématiques Discrètes',
                    'code' => 'MATH301',
                    'description' => 'Théorie des ensembles et logique mathématique'
                ],
                [
                    'nom' => 'Algèbre Linéaire',
                    'code' => 'MATH302',
                    'description' => 'Espaces vectoriels et applications linéaires'
                ]
            ];
        }

        // Ajouter les cours
        if (isset($cours)) {
            foreach ($cours as $c) {
                $sql = "INSERT INTO cours (nom, code, description, enseignant_id) VALUES (?, ?, ?, ?)";
                if (db_exec($sql, [$c['nom'], $c['code'], $c['description'], $enseignant_id])) {
                    show_message("Cours " . htmlspecialchars($c['nom']) . " ajouté avec succès.", 'success');
                } else {
                    show_message("Erreur lors de l'ajout du cours " . htmlspecialchars($c['nom']) . ": " . db_error(), 'danger');
                }
            }
        }
    } else {
        show_message("Erreur lors de l'ajout de l'enseignant " . htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) . ": " . db_error(), 'danger');
    }
}

// 2. Ajouter les étudiants
echo "<h2>Ajout des étudiants</h2>";
$etudiants = [
    [
        'nom' => 'Nkeng',
        'prenom' => 'Pierre',
        'email' => 'nkeng.pierre@etu.univ-yaounde1.cm',
        'telephone' => '+237699123456'
    ],
    [
        'nom' => 'Moumi',
        'prenom' => 'Claire',
        'email' => 'moumi.claire@etu.univ-yaounde1.cm',
        'telephone' => '+237699654321'
    ],
    [
        'nom' => 'Nkoumou',
        'prenom' => 'Jean',
        'email' => 'nkoumou.jean@etu.univ-yaounde1.cm',
        'telephone' => '+237699789456'
    ],
    [
        'nom' => 'Moussambi',
        'prenom' => 'Sarah',
        'email' => 'moussambi.sarah@etu.univ-yaounde1.cm',
        'telephone' => '+237699321654'
    ]
];

foreach ($etudiants as $etudiant) {
    // Vérifier si l'étudiant existe déjà
    $existing = db_query_single("SELECT id FROM utilisateurs WHERE email = ?", [$etudiant['email']]);
    
    if ($existing) {
        show_message("Étudiant " . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) . " existe déjà.", 'warning');
        continue;
    }

    // Générer un mot de passe temporaire
    $password = 'etudiant123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insérer l'étudiant
    $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'etudiant')";
    if (db_exec($sql, [$etudiant['nom'], $etudiant['prenom'], $etudiant['email'], $hashed_password])) {
        $etudiant_id = db_last_insert_id();
        show_message("Étudiant " . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) . " ajouté avec succès.", 'success');
        
        // Inscrire l'étudiant dans les cours
        $cours = db_query("SELECT id FROM cours");
        foreach ($cours as $c) {
            // Inscrire avec 70% de chance
            if (rand(1, 100) <= 70) {
                $sql = "INSERT IGNORE INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)";
                if (db_exec($sql, [$etudiant_id, $c['id']])) {
                    show_message("Étudiant inscrit au cours avec succès.", 'success');
                }
            }
        }
    } else {
        show_message("Erreur lors de l'ajout de l'étudiant " . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) . ": " . db_error(), 'danger');
    }
}

// 3. Afficher un résumé final
show_message("<strong>Résumé des données ajoutées :</strong>", 'info');

// Compter les enseignants ajoutés
$enseignants_count = db_query("SELECT COUNT(*) as count FROM utilisateurs WHERE role = 'enseignant'");
show_message("Nombre total d'enseignants : " . $enseignants_count[0]['count'], 'info');

// Compter les cours ajoutés
$cours_count = db_query("SELECT COUNT(*) as count FROM cours");
show_message("Nombre total de cours : " . $cours_count[0]['count'], 'info');

// Compter les étudiants ajoutés
$etudiants_count = db_query("SELECT COUNT(*) as count FROM utilisateurs WHERE role = 'etudiant'");
show_message("Nombre total d'étudiants : " . $etudiants_count[0]['count'], 'info');

// Compter les inscriptions
$inscriptions_count = db_query("SELECT COUNT(*) as count FROM inscriptions");
show_message("Nombre total d'inscriptions : " . $inscriptions_count[0]['count'], 'info');

// Ajouter un bouton pour retourner à la page d'accueil
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
?>
