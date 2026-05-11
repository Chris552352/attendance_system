<?php
/**
 * Diagnostic complet pour la génération de QR Code
 */

// Import des classes au début
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

echo "<h1>🔍 Diagnostic QR Code - Attendance System</h1>";

// 1. Vérification des extensions PHP
echo "<h2>1. Extensions PHP requises</h2>";
$extensions_requises = ['gd', 'fileinfo', 'json', 'mbstring'];
foreach ($extensions_requises as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>$status Extension $ext: " . (extension_loaded($ext) ? 'Installée' : 'Manquante') . "</p>";
}

// 2. Vérification de l'autoloader
echo "<h2>2. Autoloader Composer</h2>";
$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo "<p>✅ Autoloader trouvé: $autoload_path</p>";
    require_once $autoload_path;
} else {
    echo "<p>❌ Autoloader manquant: $autoload_path</p>";
    exit;
}

// 3. Test des classes Endroid QR Code
echo "<h2>3. Classes Endroid QR Code</h2>";
try {
    echo "<p>✅ Classes importées avec succès</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur d'import: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// 4. Test de génération simple
echo "<h2>4. Test de génération QR Code</h2>";
try {
    $test_url = "http://localhost/attendance_system/presence_qr.php?seance=1&token=test123";
    
    // Nouvelle API pour Endroid QR Code 6.x
    $builder = new Builder(
        writer: new PngWriter(),
        data: $test_url,
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10
    );
    
    $result = $builder->build();
    $qr_code_data_uri = $result->getDataUri();
    
    echo "<p>✅ QR Code généré avec succès !</p>";
    echo "<div style='background: white; padding: 20px; border-radius: 10px; display: inline-block; margin: 10px;'>";
    echo "<img src='" . $qr_code_data_uri . "' alt='QR Code Test' style='max-width: 200px;'>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ Erreur de génération: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// 5. Test de la configuration réseau
echo "<h2>5. Configuration réseau</h2>";
if (file_exists(__DIR__ . '/config_reseau.php')) {
    require_once __DIR__ . '/config_reseau.php';
    echo "<p>✅ Fichier config_reseau.php trouvé</p>";
    echo "<p>IP configurée: " . (defined('IP_POINT_ACCES') ? IP_POINT_ACCES : 'Non définie') . "</p>";
    echo "<p>URL de base: " . (defined('URL_BASE_QR') ? URL_BASE_QR : 'Non définie') . "</p>";
} else {
    echo "<p>❌ Fichier config_reseau.php manquant</p>";
}

// 6. Test de la base de données
echo "<h2>6. Connexion base de données</h2>";
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
    try {
        $test_query = db_query_single("SELECT 1 as test");
        echo "<p>✅ Connexion base de données OK</p>";
    } catch (Exception $e) {
        echo "<p>❌ Erreur base de données: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>❌ Fichier database.php manquant</p>";
}

// 7. Test de la table seances
echo "<h2>7. Structure de la table seances</h2>";
try {
    $sql = "DESCRIBE seances";
    $columns = db_query($sql);
    if ($columns) {
        echo "<p>✅ Table seances accessible</p>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>" . htmlspecialchars($col['Field']) . " - " . htmlspecialchars($col['Type']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ Table seances vide ou inaccessible</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur table seances: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 8. Test de création d'une séance
echo "<h2>8. Test de création de séance</h2>";
try {
    $token = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', time() + 600); // 10 minutes
    
    $sql = "INSERT INTO seances (nom_cours, enseignant_id, token, expiration) VALUES (?, ?, ?, ?)";
    if (db_exec($sql, ['Test QR Code', 1, $token, $expiration])) {
        $seance_id = db_last_insert_id();
        echo "<p>✅ Séance créée avec ID: $seance_id</p>";
        
        // Nettoyer la séance de test
        db_exec("DELETE FROM seances WHERE id = ?", [$seance_id]);
        echo "<p>✅ Séance de test supprimée</p>";
    } else {
        echo "<p>❌ Erreur lors de la création de la séance</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur création séance: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 9. Test de l'URL générée
echo "<h2>9. Test de l'URL QR Code</h2>";
if (defined('URL_BASE_QR')) {
    $test_url = URL_BASE_QR . "/presence_qr.php?seance=1&token=test123";
    echo "<p>URL générée: <a href='$test_url' target='_blank'>$test_url</a></p>";
    
    // Test si le fichier presence_qr.php existe
    if (file_exists(__DIR__ . '/presence_qr.php')) {
        echo "<p>✅ Fichier presence_qr.php trouvé</p>";
    } else {
        echo "<p>❌ Fichier presence_qr.php manquant</p>";
    }
} else {
    echo "<p>❌ URL_BASE_QR non définie</p>";
}

echo "<h2>🎯 Recommandations</h2>";
echo "<ul>";
echo "<li>Si toutes les vérifications sont ✅, le problème peut venir de la session ou de l'authentification</li>";
echo "<li>Vérifiez les logs d'erreur PHP dans le dossier logs de WAMP</li>";
echo "<li>Testez l'accès direct à <a href='generer_qr.php'>generer_qr.php</a></li>";
echo "<li>Vérifiez que vous êtes bien connecté en tant qu'enseignant</li>";
echo "</ul>";

echo "<p><strong>Test terminé !</strong></p>";
?> 