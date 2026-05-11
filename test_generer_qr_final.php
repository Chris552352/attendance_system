<?php
/**
 * Test final de génération QR Code après corrections
 */

session_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config_reseau.php';

// Import des classes
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

echo "<h1>🎯 Test Final - Génération QR Code</h1>";

// Simuler une session d'enseignant
$_SESSION['user_id'] = 1;

echo "<h2>1. Test de création de séance</h2>";

try {
    $nom_cours = "Test Final QR - " . date('H:i:s');
    $duree_minutes = 10;
    $token = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', time() + ($duree_minutes * 60));
    
    echo "<p>✅ Paramètres préparés</p>";
    echo "<ul>";
    echo "<li>Nom: $nom_cours</li>";
    echo "<li>Durée: $duree_minutes minutes</li>";
    echo "<li>Token: $token</li>";
    echo "<li>Expiration: $expiration</li>";
    echo "</ul>";
    
    // Enregistrer la séance
    $sql = "INSERT INTO seances (nom_cours, enseignant_id, token, expiration) VALUES (?, ?, ?, ?)";
    if (db_exec($sql, [$nom_cours, $_SESSION['user_id'], $token, $expiration])) {
        $seance_id = db_last_insert_id();
        echo "<p>✅ Séance créée avec ID: $seance_id</p>";
        
        // Vérifier que la séance existe
        $sql_check = "SELECT * FROM seances WHERE id = ?";
        $seance = db_query_single($sql_check, [$seance_id]);
        if ($seance) {
            echo "<p>✅ Séance trouvée en base de données</p>";
            
            // Générer l'URL QR
            $qr_url = URL_BASE_QR . "/presence_qr.php?seance=" . $seance_id . "&token=" . $token;
            echo "<p>URL QR: <a href='$qr_url' target='_blank'>$qr_url</a></p>";
            
            // Générer le QR Code
            echo "<h2>2. Génération du QR Code</h2>";
            try {
                // Nouvelle API pour Endroid QR Code 6.x
                $builder = new Builder(
                    writer: new PngWriter(),
                    data: $qr_url,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 300,
                    margin: 10
                );
                
                $result = $builder->build();
                $qr_code_data_uri = $result->getDataUri();
                
                echo "<p>✅ QR Code généré avec succès !</p>";
                echo "<div style='background: white; padding: 20px; border-radius: 10px; display: inline-block; margin: 10px;'>";
                echo "<img src='" . $qr_code_data_uri . "' alt='QR Code Test'>";
                echo "</div>";
                
                echo "<h2>3. Test de l'URL QR</h2>";
                echo "<p><a href='$qr_url' class='btn btn-primary' target='_blank'>🔗 Tester l'URL du QR Code</a></p>";
                
                echo "<h2>4. Test de la page presence_qr.php</h2>";
                // Simuler l'accès à presence_qr.php
                $test_seance = db_query_single("SELECT * FROM seances WHERE id = ? AND token = ? AND expiration > NOW() AND active = TRUE", [$seance_id, $token]);
                if ($test_seance) {
                    echo "<p>✅ Séance valide pour presence_qr.php</p>";
                } else {
                    echo "<p>❌ Séance invalide pour presence_qr.php</p>";
                }
                
            } catch (Exception $e) {
                echo "<p>❌ Erreur de génération QR: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
        } else {
            echo "<p>❌ Séance non trouvée en base de données</p>";
        }
        
        // Nettoyer la séance de test
        echo "<h2>5. Nettoyage</h2>";
        db_exec("DELETE FROM seances WHERE id = ?", [$seance_id]);
        echo "<p>✅ Séance de test supprimée</p>";
        
    } else {
        echo "<p>❌ Erreur lors de la création de la séance</p>";
        global $pdo;
        if (isset($pdo) && $pdo instanceof PDO) {
            $err = $pdo->errorInfo();
            echo "<p>Erreur SQL: " . implode(' - ', $err) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur générale: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>🎉 Résultat du test</h2>";
echo "<p>Si vous voyez un QR code ci-dessus, le système fonctionne correctement !</p>";

echo "<h2>🔗 Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='generer_qr.php'>📱 Page originale generer_qr.php</a></li>";
echo "<li><a href='diagnostic_qr.php'>🔍 Diagnostic complet</a></li>";
echo "<li><a href='test_qr.php'>🧪 Test QR simple</a></li>";
echo "<li><a href='test_generer_qr_simple.php'>⚡ Test simple</a></li>";
echo "</ul>";

echo "<h2>📋 Prochaines étapes</h2>";
echo "<ol>";
echo "<li>Testez la page <a href='generer_qr.php'>generer_qr.php</a> directement</li>";
echo "<li>Vérifiez que vous êtes connecté en tant qu'enseignant</li>";
echo "<li>Scannez le QR code généré avec votre téléphone</li>";
echo "<li>Vérifiez que la présence est bien enregistrée</li>";
echo "</ol>";
?> 