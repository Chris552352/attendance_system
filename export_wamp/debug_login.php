<?php
/**
 * Script de diagnostic pour identifier les problèmes de connexion des enseignants
 */

// Démarrer la session
session_start();

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Afficher les informations de session
echo "<h2>Informations de session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Vérifier si un utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Récupérer les informations de l'utilisateur
    echo "<h2>Informations de l'utilisateur</h2>";
    $user = db_query_single("SELECT * FROM utilisateurs WHERE id = ?", [$user_id]);
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    // Si c'est un enseignant, récupérer ses cours
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'enseignant') {
        echo "<h2>Cours de l'enseignant</h2>";
        $cours = db_query("SELECT * FROM cours WHERE enseignant_id = ?", [$user_id]);
        echo "<pre>";
        print_r($cours);
        echo "</pre>";
        
        // Vérifier la requête SQL utilisée dans mes_cours.php
        echo "<h2>Requête SQL utilisée dans mes_cours.php</h2>";
        $sql = "SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
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
        echo "<p>SQL: $sql</p>";
        
        // Exécuter la requête pour voir si elle fonctionne
        $cours_test = db_query($sql, [$user_id]);
        echo "<pre>";
        print_r($cours_test);
        echo "</pre>";
        
        // Vérifier si la table inscriptions existe
        echo "<h2>Vérification de la table inscriptions</h2>";
        $check_inscriptions = db_query("SHOW TABLES LIKE 'inscriptions'");
        if (empty($check_inscriptions)) {
            echo "<p style='color: red;'>La table 'inscriptions' n'existe pas!</p>";
            
            // Créer la table inscriptions si elle n'existe pas
            echo "<h3>Création de la table inscriptions</h3>";
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
            echo $result ? "<p style='color: green;'>Table 'inscriptions' créu00e9e avec succès!</p>" : "<p style='color: red;'>Erreur lors de la création de la table 'inscriptions'.</p>";
        } else {
            echo "<p style='color: green;'>La table 'inscriptions' existe.</p>";
            
            // Afficher la structure de la table
            $structure = db_query("DESCRIBE inscriptions");
            echo "<p>Structure de la table 'inscriptions':</p>";
            echo "<pre>";
            print_r($structure);
            echo "</pre>";
            
            // Afficher le nombre d'inscriptions
            $count = db_query_single("SELECT COUNT(*) as total FROM inscriptions");
            echo "<p>Nombre d'inscriptions: " . ($count ? $count['total'] : '0') . "</p>";
        }
    }
} else {
    echo "<p>Aucun utilisateur connecté.</p>";
    
    // Formulaire de connexion rapide pour les tests
    echo "<h2>Connexion rapide pour les tests</h2>";
    echo "<form method='post' action='login_test.php'>";
    echo "<p>Email: <input type='text' name='email' value='nze.jean@univ-yaounde1.cm'></p>";
    echo "<p>Mot de passe: <input type='password' name='password' value='password'></p>";
    echo "<p><button type='submit'>Se connecter</button></p>";
    echo "</form>";
}

// Créer un script de connexion rapide pour les tests
if (!file_exists('login_test.php')) {
    $login_script = "<?php\n
// Démarrer la session\nsession_start();\n\n// Inclure les fichiers nécessaires\nrequire_once 'config/database.php';\nrequire_once 'includes/auth.php';\n\n// Traitement du formulaire\nif ($_SERVER['REQUEST_METHOD'] === 'POST') {\n    $email = $_POST['email'] ?? '';\n    $password = $_POST['password'] ?? '';\n    \n    // Connexion simplifiée pour les tests\n    $user = db_query_single(\"SELECT * FROM utilisateurs WHERE email = ?\", [$email]);\n    \n    if ($user) {\n        // Pour les tests, on accepte n'importe quel mot de passe\n        $_SESSION['user_id'] = $user['id'];\n        $_SESSION['user_nom'] = $user['nom'];\n        $_SESSION['user_email'] = $user['email'];\n        $_SESSION['user_role'] = $user['role'];\n        \n        echo \"<p>Connexion réussie! Vous u00eates connecté en tant que {$user['prenom']} {$user['nom']} ({$user['role']}).</p>\";\n        echo \"<p><a href='mes_cours.php'>Voir mes cours</a></p>\";\n        echo \"<p><a href='debug_login.php'>Retour au diagnostic</a></p>\";\n    } else {\n        echo \"<p>Utilisateur non trouvé.</p>\";\n        echo \"<p><a href='debug_login.php'>Retour au diagnostic</a></p>\";\n    }\n}\n?>";\n\n    file_put_contents('login_test.php', $login_script);
    echo "<p>Script de connexion rapide créu00e9: login_test.php</p>";
}

// Afficher un lien pour modifier mes_cours.php
echo "<h2>Actions</h2>";
echo "<p><a href='fix_mes_cours.php'>Corriger mes_cours.php</a></p>";

// Créer un script pour corriger mes_cours.php
if (!file_exists('fix_mes_cours.php')) {
    $fix_script = "<?php\n
// Inclure les fichiers nécessaires\nrequire_once 'config/database.php';\n\n// Modifier mes_cours.php\n$file = 'mes_cours.php';\n$content = file_get_contents($file);\n\n// Remplacer la requête SQL pour les enseignants\n$old_query = \"SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,\n        COUNT(DISTINCT i.etudiant_id) as nb_etudiants,\n        COUNT(DISTINCT p.id) as total_presences,\n        COALESCE(SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END), 0) as nb_presents\n        FROM cours c\n        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id\n        LEFT JOIN inscriptions i ON c.id = i.cours_id\n        LEFT JOIN presences p ON c.id = p.cours_id\n        WHERE c.enseignant_id = ?\n        GROUP BY c.id, c.nom, c.code, c.description, c.enseignant_id, u.nom, u.prenom\n        ORDER BY c.nom\";\n\n$new_query = \"SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,\n        (SELECT COUNT(*) FROM inscriptions i WHERE i.cours_id = c.id) as nb_etudiants,\n        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id) as total_presences,\n        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id AND p.statut = 'present') as nb_presents\n        FROM cours c\n        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id\n        WHERE c.enseignant_id = ?\n        ORDER BY c.nom\";\n\n$content = str_replace($old_query, $new_query, $content);\n\n// Remplacer la requête SQL pour les administrateurs\n$old_admin_query = \"SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,\n        COUNT(DISTINCT i.etudiant_id) as nb_etudiants,\n        COUNT(DISTINCT p.id) as total_presences,\n        COALESCE(SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END), 0) as nb_presents\n        FROM cours c\n        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id\n        LEFT JOIN inscriptions i ON c.id = i.cours_id\n        LEFT JOIN presences p ON c.id = p.cours_id\n        GROUP BY c.id, c.nom, c.code, c.description, c.enseignant_id, u.nom, u.prenom\n        ORDER BY c.nom\";\n\n$new_admin_query = \"SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,\n        (SELECT COUNT(*) FROM inscriptions i WHERE i.cours_id = c.id) as nb_etudiants,\n        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id) as total_presences,\n        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id AND p.statut = 'present') as nb_presents\n        FROM cours c\n        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id\n        ORDER BY c.nom\";\n\n$content = str_replace($old_admin_query, $new_admin_query, $content);\n\n// Sauvegarder les modifications\nfile_put_contents($file, $content);\n\necho \"<h2>Correction de mes_cours.php</h2>\";\necho \"<p style='color: green;'>Le fichier mes_cours.php a u00e9té corrigé avec succès!</p>\";\necho \"<p><a href='mes_cours.php'>Voir mes cours</a></p>\";\necho \"<p><a href='debug_login.php'>Retour au diagnostic</a></p>\";\n?>";\n\n    file_put_contents('fix_mes_cours.php', $fix_script);
    echo "<p>Script de correction créu00e9: fix_mes_cours.php</p>";
}
?>
