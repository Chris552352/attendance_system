<?php
/**
 * Script de vérification de la table presences
 */

// Inclure les fichiers nécessaires
require_once 'config/database.php';

// Vérifier la structure de la table presences
echo "<h2>Structure de la table presences</h2>";
$structure = db_query("DESCRIBE presences");
echo "<table border='1' cellpadding='5'>";
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

// Compter le nombre d'enregistrements dans la table presences
$count = db_query_single("SELECT COUNT(*) as total FROM presences");
echo "<h2>Nombre total d'enregistrements : {$count['total']}</h2>";

// Afficher les 10 derniers enregistrements
echo "<h2>10 derniers enregistrements</h2>";
$presences = db_query("SELECT p.*, e.nom, e.prenom, c.nom as cours_nom FROM presences p JOIN etudiants e ON p.etudiant_id = e.id JOIN cours c ON p.cours_id = c.id ORDER BY p.id DESC LIMIT 10");

if (empty($presences)) {
    echo "<p>Aucun enregistrement trouvé dans la table presences.</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Étudiant</th><th>Cours</th><th>Date</th><th>Statut</th><th>Justifié</th></tr>";
    foreach ($presences as $p) {
        echo "<tr>";
        echo "<td>{$p['id']}</td>";
        echo "<td>{$p['nom']} {$p['prenom']}</td>";
        echo "<td>{$p['cours_nom']}</td>";
        echo "<td>{$p['date_presence']}</td>";
        echo "<td>{$p['statut']}</td>";
        echo "<td>" . ($p['justifie'] ? 'Oui' : 'Non') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Vérifier la requête utilisée dans rapports.php
echo "<h2>Test de la requête des rapports</h2>";
$date_debut = '2025-01-01';
$date_fin = '2025-05-31';

$sql = "
    SELECT p.date_presence as date, e.id as etudiant_id, e.nom as etudiant_nom, e.prenom as etudiant_prenom, 
    e.matricule, c.id as cours_id, c.nom as cours_nom, c.code as cours_code, 
    p.statut
    FROM presences p
    JOIN etudiants e ON p.etudiant_id = e.id
    JOIN cours c ON p.cours_id = c.id
    WHERE p.date_presence BETWEEN ? AND ?
    ORDER BY p.date_presence DESC, e.nom, e.prenom, c.nom
";

$test_results = db_query($sql, [$date_debut, $date_fin]);

if (empty($test_results)) {
    echo "<p>Aucun résultat trouvé pour la période du {$date_debut} au {$date_fin}.</p>";
    
    // Vérifier s'il y a des données sans filtrage de date
    $all_results = db_query("SELECT COUNT(*) as total FROM presences");
    echo "<p>Nombre total d'enregistrements sans filtrage de date : {$all_results[0]['total']}</p>";
    
    // Vérifier le format des dates dans la base de données
    $dates = db_query("SELECT DISTINCT date_presence FROM presences ORDER BY date_presence DESC LIMIT 10");
    if (!empty($dates)) {
        echo "<p>Exemples de dates dans la base de données :</p>";
        echo "<ul>";
        foreach ($dates as $d) {
            echo "<li>{$d['date_presence']}</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>Nombre de résultats trouvés : " . count($test_results) . "</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Date</th><th>Étudiant</th><th>Matricule</th><th>Cours</th><th>Statut</th></tr>";
    foreach ($test_results as $r) {
        echo "<tr>";
        echo "<td>{$r['date']}</td>";
        echo "<td>{$r['etudiant_nom']} {$r['etudiant_prenom']}</td>";
        echo "<td>{$r['matricule']}</td>";
        echo "<td>{$r['cours_nom']} ({$r['cours_code']})</td>";
        echo "<td>{$r['statut']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
