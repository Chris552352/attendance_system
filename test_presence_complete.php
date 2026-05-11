<?php
/**
 * Test complet du système d'enregistrement de présence
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config_reseau.php';

echo "<h1>🧪 Test Complet - Système de Présence</h1>";

// Simuler une session d'enseignant
$_SESSION['user_id'] = 1;

echo "<h2>1. Création d'une séance de test</h2>";

try {
    $nom_cours = "Test Présence - " . date('H:i:s');
    $duree_minutes = 30; // Plus long pour les tests
    $token = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', time() + ($duree_minutes * 60));
    
    // Enregistrer la séance
    $sql = "INSERT INTO seances (nom_cours, enseignant_id, token, expiration) VALUES (?, ?, ?, ?)";
    if (db_exec($sql, [$nom_cours, $_SESSION['user_id'], $token, $expiration])) {
        $seance_id = db_last_insert_id();
        echo "<p>✅ Séance créée avec ID: $seance_id</p>";
        
        // Générer l'URL QR
        $qr_url = URL_BASE_QR . "/presence_qr.php?seance=" . $seance_id . "&token=" . $token;
        echo "<p>URL QR: <a href='$qr_url' target='_blank'>$qr_url</a></p>";
        
        echo "<h2>2. Test d'enregistrement de présence</h2>";
        
        // Récupérer quelques étudiants pour les tests
        $etudiants = db_query("SELECT id, nom, prenom, email FROM etudiants LIMIT 3");
        
        if ($etudiants) {
            echo "<p>✅ Étudiants trouvés pour les tests</p>";
            
            foreach ($etudiants as $etudiant) {
                echo "<h3>Test avec l'étudiant : " . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) . "</h3>";
                
                // Vérifier si l'étudiant a déjà une présence pour cette séance
                $sql_check = "SELECT id FROM presences WHERE etudiant_id = ? AND seance_id = ? AND date_presence = CURRENT_DATE";
                $presence_existante = db_query_single($sql_check, [$etudiant['id'], $seance_id]);
                
                if ($presence_existante) {
                    echo "<p>⚠️ L'étudiant a déjà une présence pour cette séance</p>";
                } else {
                    // Simuler l'enregistrement de présence
                    $sql_insert = "INSERT INTO presences (etudiant_id, cours_id, seance_id, date_presence, statut, enregistre_par, date_enregistrement) 
                                   VALUES (?, ?, ?, CURRENT_DATE, 'present', ?, NOW())";
                    if (db_exec($sql_insert, [$etudiant['id'], null, $seance_id, $etudiant['id']])) {
                        echo "<p>✅ Présence enregistrée pour " . htmlspecialchars($etudiant['nom']) . "</p>";
                    } else {
                        echo "<p>❌ Erreur lors de l'enregistrement pour " . htmlspecialchars($etudiant['nom']) . "</p>";
                    }
                }
            }
        } else {
            echo "<p>❌ Aucun étudiant trouvé en base de données</p>";
        }
        
        echo "<h2>3. Vérification des présences enregistrées</h2>";
        
        // Compter les présences pour cette séance
        $sql_count = "SELECT COUNT(*) as total FROM presences WHERE seance_id = ? AND date_presence = CURRENT_DATE";
        $count_result = db_query_single($sql_count, [$seance_id]);
        $total_presences = $count_result['total'] ?? 0;
        
        echo "<p>📊 Total des présences pour cette séance : <strong>$total_presences</strong></p>";
        
        // Lister les présences
        $sql_list = "SELECT p.*, e.nom, e.prenom, e.email 
                     FROM presences p 
                     JOIN etudiants e ON p.etudiant_id = e.id 
                     WHERE p.seance_id = ? AND p.date_presence = CURRENT_DATE 
                     ORDER BY p.date_enregistrement DESC";
        $presences = db_query($sql_list, [$seance_id]);
        
        if ($presences) {
            echo "<h3>📋 Liste des présences :</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Étudiant</th><th>Email</th><th>Statut</th><th>Heure d'enregistrement</th></tr>";
            
            foreach ($presences as $presence) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($presence['nom'] . ' ' . $presence['prenom']) . "</td>";
                echo "<td>" . htmlspecialchars($presence['email']) . "</td>";
                echo "<td>" . htmlspecialchars($presence['statut']) . "</td>";
                echo "<td>" . htmlspecialchars($presence['date_enregistrement']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Aucune présence trouvée</p>";
        }
        
        echo "<h2>4. Test de la page de présence</h2>";
        echo "<p><a href='$qr_url' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Tester la page de présence</a></p>";
        
        echo "<h2>5. Instructions pour les étudiants</h2>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>Comment marquer sa présence :</h3>";
        echo "<ol>";
        echo "<li>Scannez le QR code ou cliquez sur le lien ci-dessus</li>";
        echo "<li>Connectez-vous avec :</li>";
        echo "<ul>";
        echo "<li><strong>Email :</strong> votre email étudiant</li>";
        echo "<li><strong>Mot de passe :</strong> 12345</li>";
        echo "</ul>";
        echo "<li>Cliquez sur 'Confirmer ma présence'</li>";
        echo "<li>Votre présence sera enregistrée automatiquement</li>";
        echo "</ol>";
        echo "</div>";
        
        // Nettoyer les données de test
        echo "<h2>6. Nettoyage des données de test</h2>";
        db_exec("DELETE FROM presences WHERE seance_id = ?", [$seance_id]);
        db_exec("DELETE FROM seances WHERE id = ?", [$seance_id]);
        echo "<p>✅ Données de test supprimées</p>";
        
    } else {
        echo "<p>❌ Erreur lors de la création de la séance</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur générale: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>🎯 Résultat du test</h2>";
echo "<p>Si vous voyez des présences enregistrées dans le tableau ci-dessus, le système fonctionne correctement !</p>";

echo "<h2>🔗 Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='test_connectivite_reseau.php'>Test de connectivité réseau</a></li>";
echo "<li><a href='test_generer_qr_direct.php'>Test de génération QR</a></li>";
echo "<li><a href='diagnostic_qr.php'>Diagnostic complet</a></li>";
echo "</ul>";
?> 