<?php
require_once 'config/database.php';

// Style de base pour la page
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { background: #e8f4f8; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

// Vérifier la structure de la table etudiants
echo "<h2>1. Structure de la table etudiants</h2>";
$result = db_query("DESCRIBE etudiants");
if ($result) {
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    // Vérifier les champs nécessaires
    $required_fields = ['nom', 'prenom', 'matricule', 'email', 'telephone', 'date_naissance', 'adresse'];
    $missing_fields = [];
    
    $existing_fields = [];
    foreach ($result as $field) {
        $existing_fields[] = $field['Field'];
    }
    
    foreach ($required_fields as $field) {
        if (!in_array($field, $existing_fields)) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo "<div class='error'>Champs manquants dans la table etudiants: " . implode(', ', $missing_fields) . "</div>";
        
        // Suggérer une requête SQL pour ajouter les champs manquants
        echo "<div class='info'>
            <p>Exécutez la requête SQL suivante pour ajouter les champs manquants:</p>
            <pre>";
        
        foreach ($missing_fields as $field) {
            $type = ($field == 'date_naissance') ? 'DATE' : 'VARCHAR(255)';
            echo "ALTER TABLE etudiants ADD COLUMN $field $type NULL;\n";
        }
        
        echo "</pre>
        </div>";
    } else {
        echo "<div class='success'>Tous les champs requis sont présents dans la table etudiants.</div>";
    }
} else {
    echo "<div class='error'>Impossible d'obtenir la structure de la table etudiants. Erreur: " . db_error() . "</div>";
}

// Tester l'ajout d'un étudiant avec tous les champs
echo "<h2>2. Test d'ajout d'un étudiant</h2>";

// Construire une requête d'insertion avec tous les champs existants
if ($result) {
    $fields = [];
    $placeholders = [];
    $values = [];
    
    foreach ($result as $field) {
        $field_name = $field['Field'];
        
        // Ignorer le champ id (auto-incrémenté)
        if ($field_name == 'id') continue;
        
        $fields[] = $field_name;
        $placeholders[] = '?';
        
        // Valeurs de test pour chaque champ
        switch ($field_name) {
            case 'nom':
                $values[] = 'Test';
                break;
            case 'prenom':
                $values[] = 'Etudiant';
                break;
            case 'matricule':
                $values[] = 'ETU-TEST-' . time();
                break;
            case 'email':
                $values[] = 'test' . time() . '@example.com';
                break;
            case 'telephone':
                $values[] = '123456789';
                break;
            case 'date_naissance':
                $values[] = '2000-01-01';
                break;
            case 'adresse':
                $values[] = 'Adresse de test';
                break;
            default:
                $values[] = null;
        }
    }
    
    $sql = "INSERT INTO etudiants (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    echo "<div class='info'>Exécution de la requête: <pre>$sql</pre></div>";
    
    $success = db_exec($sql, $values);
    
    if ($success) {
        $last_id = db_last_insert_id();
        echo "<div class='success'>Ajout réussi! ID de l'étudiant: $last_id</div>";
        
        // Afficher les données de l'étudiant ajouté
        $etudiant = db_query_single("SELECT * FROM etudiants WHERE id = ?", [$last_id]);
        echo "<div class='info'>
            <p>Données de l'étudiant ajouté:</p>
            <pre>";
        print_r($etudiant);
        echo "</pre>
        </div>";
        
        // Supprimer l'étudiant de test
        db_exec("DELETE FROM etudiants WHERE id = ?", [$last_id]);
        echo "<div class='info'>L'étudiant de test a été supprimé.</div>";
    } else {
        echo "<div class='error'>Échec de l'ajout! Erreur: " . db_error() . "</div>";
    }
} else {
    echo "<div class='error'>Impossible de tester l'ajout d'un étudiant car la structure de la table n'a pas pu être récupérée.</div>";
}

// Vérifier le code d'ajout d'étudiant dans ajouter_etudiant.php
echo "<h2>3. Analyse du code d'ajout d'étudiant</h2>";

$file_path = 'ajouter_etudiant.php';
if (file_exists($file_path)) {
    $file_content = file_get_contents($file_path);
    
    // Rechercher le code d'insertion
    if (preg_match('/INSERT INTO etudiants.*?VALUES.*?\)/', $file_content, $matches)) {
        echo "<div class='info'>
            <p>Code d'insertion trouvé dans ajouter_etudiant.php:</p>
            <pre>" . htmlspecialchars($matches[0]) . "</pre>
        </div>";
    } else {
        echo "<div class='error'>Impossible de trouver le code d'insertion dans ajouter_etudiant.php</div>";
    }
} else {
    echo "<div class='error'>Le fichier ajouter_etudiant.php n'existe pas.</div>";
}
?>
