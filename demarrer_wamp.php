<?php
/**
 * Script pour démarrer WAMP et vérifier la configuration
 */

echo "<h1>🚀 Démarrage WAMP - Système de Présence</h1>";

// Vérifier si WAMP est installé
$wamp_paths = [
    'C:/wamp64/',
    'C:/wamp/',
    'C:/xampp/',
    'C:/laragon/'
];

$wamp_found = false;
$wamp_path = '';

foreach ($wamp_paths as $path) {
    if (is_dir($path)) {
        $wamp_found = true;
        $wamp_path = $path;
        break;
    }
}

if (!$wamp_found) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ WAMP non trouvé</h3>";
    echo "<p>Aucune installation WAMP/XAMPP/Laragon trouvée dans les emplacements standards.</p>";
    echo "<p>Veuillez installer WAMP64 depuis : <a href='https://www.wampserver.com/' target='_blank'>https://www.wampserver.com/</a></p>";
    echo "</div>";
    exit;
}

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3>✅ WAMP trouvé</h3>";
echo "<p>Installation trouvée dans : <strong>$wamp_path</strong></p>";
echo "</div>";

// Vérifier si le projet est dans le bon dossier
$project_path = $wamp_path . 'www/attendance_system/';
if (!is_dir($project_path)) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<h3>⚠️ Projet non trouvé</h3>";
    echo "<p>Le dossier <strong>attendance_system</strong> n'est pas dans <strong>$wamp_path/www/</strong></p>";
    echo "<p>Veuillez copier le projet dans ce dossier.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Projet trouvé</h3>";
    echo "<p>Le projet est bien dans : <strong>$project_path</strong></p>";
    echo "</div>";
}

// Instructions pour démarrer WAMP
echo "<h2>📋 Instructions pour démarrer WAMP</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>Étapes à suivre :</h4>";
echo "<ol>";
echo "<li><strong>Arrêtez le serveur PHP intégré</strong> (Ctrl+C dans le terminal)</li>";
echo "<li><strong>Démarrez WAMP</strong> :";
echo "<ul>";
echo "<li>Double-cliquez sur l'icône WAMP dans le menu Démarrer</li>";
echo "<li>Ou lancez <code>$wamp_path/wampmanager.exe</code></li>";
echo "<li>Attendez que l'icône devienne verte</li>";
echo "</ul></li>";
echo "<li><strong>Vérifiez que Apache est démarré</strong> :";
echo "<ul>";
echo "<li>Cliquez sur l'icône WAMP dans la barre des tâches</li>";
echo "<li>Apache → Service → Status doit être vert</li>";
echo "</ul></li>";
echo "<li><strong>Testez l'accès</strong> :";
echo "<ul>";
echo "<li>Sur PC : <a href='http://localhost/attendance_system/' target='_blank'>http://localhost/attendance_system/</a></li>";
echo "<li>Sur téléphone : <a href='http://192.168.59.74/attendance_system/' target='_blank'>http://192.168.59.74/attendance_system/</a></li>";
echo "</ul></li>";
echo "</ol>";
echo "</div>";

// Vérifier la connectivité réseau
echo "<h2>🌐 Test de connectivité réseau</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>URLs à tester :</h4>";
echo "<ul>";
echo "<li><strong>PC (localhost)</strong> : <a href='http://localhost/attendance_system/' target='_blank'>http://localhost/attendance_system/</a></li>";
echo "<li><strong>Téléphone (réseau)</strong> : <a href='http://192.168.59.74/attendance_system/' target='_blank'>http://192.168.59.74/attendance_system/</a></li>";
echo "<li><strong>Test QR Code</strong> : <a href='http://192.168.59.74/attendance_system/diagnostic_qr.php' target='_blank'>http://192.168.59.74/attendance_system/diagnostic_qr.php</a></li>";
echo "</ul>";
echo "</div>";

// Commandes pour arrêter le serveur PHP intégré
echo "<h2>🛑 Arrêter le serveur PHP intégré</h2>";
echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
echo "<p><strong>Si vous avez un serveur PHP intégré en cours :</strong></p>";
echo "<ol>";
echo "<li>Retournez dans le terminal PowerShell</li>";
echo "<li>Appuyez sur <strong>Ctrl+C</strong> pour arrêter le serveur</li>";
echo "<li>Ou fermez la fenêtre du terminal</li>";
echo "</ol>";
echo "</div>";

// Liens utiles
echo "<h2>🔗 Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='http://localhost/attendance_system/' target='_blank'>Accueil du système (localhost)</a></li>";
echo "<li><a href='http://192.168.59.74/attendance_system/' target='_blank'>Accueil du système (réseau)</a></li>";
echo "<li><a href='http://localhost/attendance_system/test_connectivite_reseau.php' target='_blank'>Test de connectivité</a></li>";
echo "<li><a href='http://localhost/attendance_system/diagnostic_qr.php' target='_blank'>Diagnostic QR Code</a></li>";
echo "</ul>";

echo "<h2>✅ Résumé</h2>";
echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<p><strong>Pour que ça marche sur le téléphone :</strong></p>";
echo "<ol>";
echo "<li>Arrêtez le serveur PHP intégré (Ctrl+C)</li>";
echo "<li>Démarrez WAMP (icône verte)</li>";
echo "<li>Utilisez les URLs avec <code>/attendance_system/</code></li>";
echo "<li>Testez sur le téléphone avec l'IP 192.168.59.74</li>";
echo "</ol>";
echo "</div>";
?> 