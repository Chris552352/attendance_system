<?php
/**
 * Test complet du système de présence avec correction de la base de données
 */

require_once 'config/database.php';
require_once 'config/config_app.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🧪 Test Complet - Système de Présence (Version 2)</h1>";
    
    // ÉTAPE 1: Correction de la base de données
    echo "<h2>1. 🔧 Correction de la base de données</h2>";
    
    try {
        // Vérifier si la contrainte existe
        $stmt = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'presences' 
                            AND CONSTRAINT_NAME = 'presences_ibfk_2'");
        $constraint_exists = $stmt->fetch();
        
        if ($constraint_exists) {
            echo "<p>✅ Suppression de la contrainte existante...</p>";
            $pdo->exec("ALTER TABLE presences DROP FOREIGN KEY presences_ibfk_2");
        }
        
        // Rendre cours_id nullable
        echo "<p>✅ Modification de la colonne cours_id...</p>";
        $pdo->exec("ALTER TABLE presences MODIFY cours_id int(11) NULL");
        
        // Ajouter la nouvelle contrainte
        echo "<p>✅ Ajout de la nouvelle contrainte...</p>";
        $pdo->exec("ALTER TABLE presences ADD CONSTRAINT presences_ibfk_2 FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE SET NULL");
        
        echo "<p>✅ Base de données corrigée avec succès !</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Note: " . $e->getMessage() . "</p>";
    }
    
    // ÉTAPE 2: Création d'une séance de test
    echo "<h2>2. 📝 Création d'une séance de test</h2>";
    
    $nom_cours = "Test Système QR - " . date('H:i:s');
    $enseignant_id = 1; // Admin
    $token = md5(uniqid() . time());
    $expiration = date('Y-m-d H:i:s', strtotime('+2 hours'));
    
    $sql_seance = "INSERT INTO seances (nom_cours, enseignant_id, token, expiration, active) VALUES (?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($sql_seance);
    $stmt->execute([$nom_cours, $enseignant_id, $token, $expiration]);
    $seance_id = $pdo->lastInsertId();
    
    echo "<p>✅ Séance créée avec ID: <strong>$seance_id</strong></p>";
    echo "<p>🔗 URL QR: <a href='http://192.168.167.70/attendance_system/presence_qr.php?seance=$seance_id&token=$token' target='_blank'>http://192.168.167.70/attendance_system/presence_qr.php?seance=$seance_id&token=$token</a></p>";
    
    // ÉTAPE 3: Récupération des étudiants de test
    echo "<h2>3. 👥 Récupération des étudiants de test</h2>";
    
    $sql_etudiants = "SELECT id, nom, prenom, email FROM etudiants LIMIT 3";
    $stmt = $pdo->query($sql_etudiants);
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($etudiants)) {
        echo "<p>❌ Aucun étudiant trouvé dans la base de données.</p>";
        exit;
    }
    
    echo "<p>✅ Étudiants trouvés pour les tests :</p>";
    echo "<ul>";
    foreach ($etudiants as $etudiant) {
        echo "<li><strong>{$etudiant['nom']} {$etudiant['prenom']}</strong> ({$etudiant['email']})</li>";
    }
    echo "</ul>";
    
    // ÉTAPE 4: Test d'enregistrement de présence
    echo "<h2>4. ✅ Test d'enregistrement de présence</h2>";
    
    $presences_enregistrees = 0;
    
    foreach ($etudiants as $etudiant) {
        echo "<h4>Test avec l'étudiant : {$etudiant['nom']} {$etudiant['prenom']}</h4>";
        
        try {
            // Vérifier si présence déjà existante
            $sql_check = "SELECT id FROM presences WHERE etudiant_id = ? AND seance_id = ? AND date_presence = CURRENT_DATE";
            $stmt = $pdo->prepare($sql_check);
            $stmt->execute([$etudiant['id'], $seance_id]);
            $presence_existante = $stmt->fetch();
            
            if ($presence_existante) {
                echo "<p>⚠️ Présence déjà enregistrée pour cet étudiant.</p>";
                continue;
            }
            
            // Enregistrer la présence
            $sql_insert = "INSERT INTO presences (etudiant_id, seance_id, date_presence, statut, enregistre_par, date_enregistrement) 
                           VALUES (?, ?, CURRENT_DATE, 'present', ?, NOW())";
            $stmt = $pdo->prepare($sql_insert);
            $stmt->execute([$etudiant['id'], $seance_id, $enseignant_id]);
            
            echo "<p>✅ Présence enregistrée avec succès !</p>";
            $presences_enregistrees++;
            
        } catch (Exception $e) {
            echo "<p>❌ Erreur lors de l'enregistrement pour {$etudiant['nom']} : " . $e->getMessage() . "</p>";
        }
    }
    
    // ÉTAPE 5: Vérification des présences enregistrées
    echo "<h2>5. 📊 Vérification des présences enregistrées</h2>";
    
    $sql_count = "SELECT COUNT(*) as total FROM presences WHERE seance_id = ? AND date_presence = CURRENT_DATE";
    $stmt = $pdo->prepare($sql_count);
    $stmt->execute([$seance_id]);
    $result = $stmt->fetch();
    $total_presences = $result['total'];
    
    echo "<p>📊 Total des présences pour cette séance : <strong>$total_presences</strong></p>";
    
    if ($total_presences > 0) {
        echo "<p>✅ Présences trouvées !</p>";
        
        // Afficher les détails
        $sql_details = "SELECT p.*, e.nom, e.prenom, e.email 
                       FROM presences p 
                       JOIN etudiants e ON p.etudiant_id = e.id 
                       WHERE p.seance_id = ? AND p.date_presence = CURRENT_DATE";
        $stmt = $pdo->prepare($sql_details);
        $stmt->execute([$seance_id]);
        $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
        echo "<tr><th>Étudiant</th><th>Email</th><th>Date</th><th>Statut</th></tr>";
        foreach ($presences as $presence) {
            echo "<tr>";
            echo "<td>{$presence['nom']} {$presence['prenom']}</td>";
            echo "<td>{$presence['email']}</td>";
            echo "<td>{$presence['date_presence']}</td>";
            echo "<td>{$presence['statut']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Aucune présence trouvée</p>";
    }
    
    // ÉTAPE 6: Test de la page de présence
    echo "<h2>6. 🔗 Test de la page de présence</h2>";
    echo "<p>🔗 <a href='http://192.168.167.70/attendance_system/presence_qr.php?seance=$seance_id&token=$token' target='_blank'>Tester la page de présence</a></p>";
    
    // ÉTAPE 7: Instructions pour les étudiants
    echo "<h2>7. 📋 Instructions pour les étudiants</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Comment marquer sa présence :</h4>";
    echo "<ol>";
    echo "<li>Scannez le QR code ou cliquez sur le lien ci-dessus</li>";
    echo "<li>Connectez-vous avec :<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;• Email : votre email étudiant<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;• Mot de passe : " . MOT_DE_PASSE_UNIVERSEL_ETUDIANT . "</li>";
    echo "<li>Cliquez sur 'Confirmer ma présence'</li>";
    echo "<li>Votre présence sera enregistrée automatiquement</li>";
    echo "</ol>";
    echo "</div>";
    
    // ÉTAPE 8: Nettoyage des données de test
    echo "<h2>8. 🧹 Nettoyage des données de test</h2>";
    
    try {
        // Supprimer les présences de test
        $sql_delete_presences = "DELETE FROM presences WHERE seance_id = ?";
        $stmt = $pdo->prepare($sql_delete_presences);
        $stmt->execute([$seance_id]);
        
        // Supprimer la séance de test
        $sql_delete_seance = "DELETE FROM seances WHERE id = ?";
        $stmt = $pdo->prepare($sql_delete_seance);
        $stmt->execute([$seance_id]);
        
        echo "<p>✅ Données de test supprimées</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Erreur lors du nettoyage : " . $e->getMessage() . "</p>";
    }
    
    // RÉSULTAT FINAL
    echo "<h2>🎯 Résultat du test</h2>";
    if ($presences_enregistrees > 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<h3>✅ SUCCÈS !</h3>";
        echo "<p>Le système de présence fonctionne correctement !</p>";
        echo "<p><strong>$presences_enregistrees</strong> présences ont été enregistrées avec succès.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "<h3>❌ ÉCHEC</h3>";
        echo "<p>Le système de présence ne fonctionne pas correctement.</p>";
        echo "<p>Aucune présence n'a pu être enregistrée.</p>";
        echo "</div>";
    }
    
    // LIENS UTILES
    echo "<h2>🔗 Liens utiles</h2>";
    echo "<ul>";
    echo "<li><a href='http://192.168.167.70/attendance_system/test_connectivite_reseau.php' target='_blank'>Test de connectivité réseau</a></li>";
    echo "<li><a href='http://192.168.167.70/attendance_system/diagnostic_qr.php' target='_blank'>Test de génération QR</a></li>";
    echo "<li><a href='http://192.168.167.70/attendance_system/suivi_presences_temps_reel.php' target='_blank'>Suivi temps réel</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erreur de base de données</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 