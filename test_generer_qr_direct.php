<?php
/**
 * Test direct de la page generer_qr.php sans authentification
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

// Simuler une session d'enseignant
$_SESSION['user_id'] = 1;

echo "<h1>🧪 Test Direct - Page generer_qr.php</h1>";

// Simuler le traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>📝 Traitement du formulaire POST</h2>";
    
    $nom_cours = trim($_POST['nom_cours'] ?? '');
    $duree_minutes = intval($_POST['duree_minutes'] ?? 10);
    
    echo "<p>Nom du cours: $nom_cours</p>";
    echo "<p>Durée: $duree_minutes minutes</p>";
    
    if (!empty($nom_cours) && $duree_minutes > 0) {
        // Générer un token unique
        $token = bin2hex(random_bytes(16));
        
        // Calculer l'expiration
        $expiration = date('Y-m-d H:i:s', time() + ($duree_minutes * 60));
        
        echo "<p>Token généré: $token</p>";
        echo "<p>Expiration: $expiration</p>";
        
        // Enregistrer la séance
        $sql = "INSERT INTO seances (nom_cours, enseignant_id, token, expiration) VALUES (?, ?, ?, ?)";
        if (db_exec($sql, [$nom_cours, $_SESSION['user_id'], $token, $expiration])) {
            $seance_id = db_last_insert_id();
            echo "<p>✅ Séance créée avec ID: $seance_id</p>";
            
            // Générer l'URL QR
            $qr_url = URL_BASE_QR . "/presence_qr.php?seance=" . $seance_id . "&token=" . $token;
            echo "<p>URL QR: <a href='$qr_url' target='_blank'>$qr_url</a></p>";
            
            // Générer le QR Code
            echo "<h2>🎯 Génération du QR Code</h2>";
            try {
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
                echo "<img src='" . $qr_code_data_uri . "' alt='QR Code'>";
                echo "</div>";
                
                echo "<h2>🔗 Test de l'URL</h2>";
                echo "<p><a href='$qr_url' class='btn btn-primary' target='_blank'>Tester l'URL du QR Code</a></p>";
                
                // Nettoyer la séance de test
                echo "<h2>🧹 Nettoyage</h2>";
                db_exec("DELETE FROM seances WHERE id = ?", [$seance_id]);
                echo "<p>✅ Séance de test supprimée</p>";
                
            } catch (Exception $e) {
                echo "<p>❌ Erreur de génération QR: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
        } else {
            echo "<p>❌ Erreur lors de la création de la séance</p>";
            global $pdo;
            if (isset($pdo) && $pdo instanceof PDO) {
                $err = $pdo->errorInfo();
                echo "<p>Erreur SQL: " . implode(' - ', $err) . "</p>";
            }
        }
    } else {
        echo "<p>❌ Données invalides</p>";
    }
} else {
    // Afficher le formulaire
    echo "<h2>📋 Formulaire de génération QR Code</h2>";
    ?>
    <form method="POST" style="max-width: 500px; margin: 20px 0;">
        <div style="margin-bottom: 15px;">
            <label for="nom_cours" style="display: block; margin-bottom: 5px; font-weight: bold;">Nom du Cours/Séance</label>
            <input type="text" id="nom_cours" name="nom_cours" 
                   placeholder="Ex: Mathématiques - TD1" required 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="duree_minutes" style="display: block; margin-bottom: 5px; font-weight: bold;">Durée de validité (minutes)</label>
            <select id="duree_minutes" name="duree_minutes" required 
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="5">5 minutes</option>
                <option value="10" selected>10 minutes</option>
                <option value="15">15 minutes</option>
                <option value="30">30 minutes</option>
                <option value="60">1 heure</option>
            </select>
        </div>
        
        <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
            🎯 Générer le QR Code
        </button>
    </form>
    <?php
}

echo "<h2>🔗 Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='generer_qr.php'>📱 Page originale generer_qr.php</a></li>";
echo "<li><a href='diagnostic_qr.php'>🔍 Diagnostic complet</a></li>";
echo "<li><a href='test_generer_qr_final.php'>⚡ Test final</a></li>";
echo "</ul>";

echo "<h2>📋 Instructions</h2>";
echo "<ol>";
echo "<li>Remplissez le formulaire ci-dessus</li>";
echo "<li>Cliquez sur 'Générer le QR Code'</li>";
echo "<li>Le QR code sera généré et affiché</li>";
echo "<li>Testez l'URL générée</li>";
echo "</ol>";
?> 