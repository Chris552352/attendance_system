<?php
/**
 * Test de connectivité réseau pour point d'accès téléphone
 */

require_once __DIR__ . '/config_reseau.php';

echo "<h1>📱 Test de Connectivité Réseau</h1>";

echo "<h2>🔍 Configuration actuelle</h2>";
echo "<ul>";
echo "<li><strong>IP de votre PC :</strong> " . IP_POINT_ACCES . "</li>";
echo "<li><strong>Port serveur :</strong> " . PORT_SERVEUR . "</li>";
echo "<li><strong>URL de base :</strong> " . URL_BASE_QR . "</li>";
echo "</ul>";

echo "<h2>📋 Instructions pour les étudiants</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Comment se connecter :</h3>";
echo "<ol>";
echo "<li><strong>Connectez-vous au point d'accès de votre téléphone</strong></li>";
echo "<li><strong>Ouvrez votre navigateur</strong></li>";
echo "<li><strong>Testez cette URL :</strong> <a href='" . URL_BASE_QR . "/test_page.php' target='_blank'>" . URL_BASE_QR . "/test_page.php</a></li>";
echo "<li><strong>Si ça marche, vous pourrez scanner les QR codes</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h2>🧪 Test de génération QR Code</h2>";

// Test de génération d'un QR code avec l'IP actuelle
require_once __DIR__ . '/vendor/autoload.php';
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

try {
    $test_url = URL_BASE_QR . "/presence_qr.php?seance=1&token=test123";
    
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
    
    echo "<p>✅ QR Code généré avec l'URL : <code>$test_url</code></p>";
    echo "<div style='background: white; padding: 20px; border-radius: 10px; display: inline-block; margin: 10px;'>";
    echo "<img src='" . $qr_code_data_uri . "' alt='QR Code Test'>";
    echo "</div>";
    
    echo "<h3>🔗 Test de l'URL</h3>";
    echo "<p><a href='$test_url' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Tester l'URL du QR Code</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erreur de génération : " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>📱 Configuration pour téléphone</h2>";
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Étapes pour configurer le point d'accès :</h3>";
echo "<ol>";
echo "<li><strong>Activez le point d'accès sur votre téléphone</strong></li>";
echo "<li><strong>Connectez votre PC au point d'accès</strong></li>";
echo "<li><strong>Notez l'IP de votre PC :</strong> <code>" . IP_POINT_ACCES . "</code></li>";
echo "<li><strong>Les étudiants se connectent au même point d'accès</strong></li>";
echo "<li><strong>Ils accèdent à :</strong> <code>" . URL_BASE_QR . "</code></li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔧 Dépannage</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Si les étudiants ne peuvent pas accéder :</h3>";
echo "<ul>";
echo "<li>Vérifiez que tous sont connectés au même point d'accès</li>";
echo "<li>Testez l'URL depuis votre téléphone</li>";
echo "<li>Vérifiez que le pare-feu Windows n'empêche pas l'accès</li>";
echo "<li>Essayez de désactiver temporairement l'antivirus</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🌐 Test de serveur local</h2>";
echo "<p>Pour tester localement, vous pouvez démarrer un serveur PHP :</p>";
echo "<code>php -S " . IP_POINT_ACCES . ":8000</code>";
echo "<br><br>";
echo "<p>Puis accédez à : <a href='http://" . IP_POINT_ACCES . ":8000' target='_blank'>http://" . IP_POINT_ACCES . ":8000</a></p>";

echo "<h2>📞 Support</h2>";
echo "<p>Si vous avez des problèmes :</p>";
echo "<ul>";
echo "<li>Testez d'abord l'URL depuis votre téléphone</li>";
echo "<li>Vérifiez que WAMP est bien démarré</li>";
echo "<li>Consultez les logs d'erreur</li>";
echo "</ul>";
?> 