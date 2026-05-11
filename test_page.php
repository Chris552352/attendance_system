<?php
/**
 * Page de test simple pour vérifier l'accès depuis les téléphones
 */

require_once 'config/database.php';
require_once 'config_reseau.php';

$now = date('Y-m-d H:i:s');
$seance_id = isset($_GET['seance_id']) ? intval($_GET['seance_id']) : null;
$seance = null;
if ($seance_id) {
    $seance = db_query_single("SELECT * FROM seances WHERE id = ?", [$seance_id]);
} else {
    $seance = db_query_single("SELECT * FROM seances WHERE expiration > ? AND active = 1 ORDER BY id DESC LIMIT 1", [$now]);
}

// Formulaire de diagnostic
?>
<form method="get" style="margin-bottom:20px;">
    <label for="seance_id">ID de la séance à diagnostiquer :</label>
    <input type="number" name="seance_id" id="seance_id" value="<?php echo htmlspecialchars($seance_id ?? ''); ?>" min="1" required>
    <button type="submit">Diagnostiquer</button>
</form>
<?php
if ($seance) {
    $url = URL_BASE_QR . "/presence_qr.php?seance=" . $seance['id'] . "&token=" . $seance['token'];
    echo "<h2>Diagnostic QR code pour la séance ID " . htmlspecialchars($seance['id']) . "</h2>";
    echo "<p><b>Cours :</b> " . htmlspecialchars($seance['nom_cours']) . "</p>";
    echo "<p><b>Expire le :</b> " . htmlspecialchars($seance['expiration']) . "</p>";
    echo "<p><b>Lien de test :</b> <a href='$url' target='_blank'>$url</a></p>";
    echo "<hr><h3>Diagnostic de la séance</h3>";
    echo "<ul>";
    echo "<li><b>ID :</b> " . htmlspecialchars($seance['id']) . "</li>";
    echo "<li><b>Token :</b> <code>" . htmlspecialchars($seance['token']) . "</code></li>";
    echo "<li><b>Expiration :</b> " . htmlspecialchars($seance['expiration']) . "</li>";
    echo "<li><b>Active :</b> " . (isset($seance['active']) ? htmlspecialchars($seance['active']) : '<span style=\'color:red\'>Non défini</span>') . "</li>";
    $not_expired = (strtotime($seance['expiration']) > strtotime($now));
    $is_active = (isset($seance['active']) ? $seance['active'] == 1 : true);
    echo "<li><b>Statut pour QR :</b> ";
    if (!$is_active) {
        echo "<span style='color:red;font-weight:bold;'>Séance désactivée (active=0)</span>";
    } elseif (!$not_expired) {
        echo "<span style='color:red;font-weight:bold;'>Séance expirée</span>";
    } else {
        echo "<span style='color:green;font-weight:bold;'>Valide pour QR</span>";
    }
    echo "</li>";
    echo "</ul>";
    echo "<p>Ouvrez ce lien sur un autre appareil connecté au même réseau pour tester le scan.</p>";
} else {
    echo "<p style='color:red;'>Aucune séance trouvée pour l'ID demandé ou aucun QR code actif trouvé. Veuillez en générer un depuis l'interface enseignant.</p>";
}

echo "<!DOCTYPE html>";
echo "<html lang='fr'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test d'accès - Système de Présence</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🎯 Test d'accès réussi !</h1>";

echo "<div class='success'>";
echo "<h2>✅ Connexion établie</h2>";
echo "<p>Votre téléphone peut accéder au système de présence par QR Code.</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📱 Informations de connexion :</h3>";
echo "<ul>";
echo "<li><strong>IP du serveur :</strong> " . $_SERVER['SERVER_ADDR'] . "</li>";
echo "<li><strong>Votre IP :</strong> " . $_SERVER['REMOTE_ADDR'] . "</li>";
echo "<li><strong>Date/Heure :</strong> " . date('d/m/Y H:i:s') . "</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔗 Liens utiles :</h3>";
echo "<p><a href='presence_qr.php?seance=1&token=test123' class='btn'>Test Page Présence</a></p>";
echo "<p><a href='login_etudiant.php' class='btn'>Connexion Étudiant</a></p>";
echo "<p><a href='index.php' class='btn'>Accueil</a></p>";

echo "<h3>📋 Instructions pour les étudiants :</h3>";
echo "<ol>";
echo "<li>Scannez le QR code affiché par l'enseignant</li>";
echo "<li>Vous arriverez sur la page de validation de présence</li>";
echo "<li>Connectez-vous avec votre email et le mot de passe : <strong>12345</strong></li>";
echo "<li>Confirmez votre présence</li>";
echo "</ol>";

echo "<div class='info'>";
echo "<h3>🔧 En cas de problème :</h3>";
echo "<ul>";
echo "<li>Vérifiez que vous êtes bien connecté au point d'accès</li>";
echo "<li>Essayez de rafraîchir la page</li>";
echo "<li>Contactez l'enseignant si le problème persiste</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 