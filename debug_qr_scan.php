<?php
/**
 * Page de debug pour le scan QR code
 * Affiche toutes les informations utiles pour diagnostiquer le problème
 */

session_start();
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Debug QR Scan</title></head><body>";
echo "<h1>=== DEBUG SCAN QR CODE ===</h1>";

echo "<h2>Informations de base</h2>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>IP Serveur:</strong> " . $_SERVER['SERVER_ADDR'] . "</p>";
echo "<p><strong>IP Client:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>";
echo "<p><strong>URL complète:</strong> " . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";

echo "<h2>Paramètres reçus</h2>";
echo "<table border='1'>";
echo "<tr><th>Paramètre</th><th>Valeur</th></tr>";
foreach ($_GET as $key => $value) {
    echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
}
echo "</table>";

echo "<h2>Test de connexion BDD</h2>";
try {
    require_once 'config/database.php';
    $result = db_query("SELECT COUNT(*) as count FROM seances");
    echo "<p style='color:green'>BDD: OK - " . $result[0]['count'] . " séances trouvées</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>BDD: ERREUR - " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Session</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session active:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'OUI' : 'NON') . "</p>";
if (isset($_SESSION['user_id'])) {
    echo "<p><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
}
if (isset($_SESSION['etudiant_id'])) {
    echo "<p><strong>Étudiant ID:</strong> " . $_SESSION['etudiant_id'] . "</p>";
}

echo "<h2>Test de validation QR</h2>";
if (isset($_GET['seance']) && isset($_GET['token'])) {
    $seance_id = intval($_GET['seance']);
    $token = $_GET['token'];
    
    try {
        $seance = db_query_single("SELECT * FROM seances WHERE id = ? AND token = ?", [$seance_id, $token]);
        if ($seance) {
            echo "<p style='color:green'>Séance trouvée: " . htmlspecialchars($seance['nom_cours']) . "</p>";
            echo "<p><strong>Expiration:</strong> " . $seance['expiration'] . "</p>";
            echo "<p><strong>Active:</strong> " . ($seance['active'] ? 'OUI' : 'NON') . "</p>";
            
            if (strtotime($seance['expiration']) > time()) {
                echo "<p style='color:green'>QR Code VALIDE</p>";
            } else {
                echo "<p style='color:red'>QR Code EXPIRÉ</p>";
            }
        } else {
            echo "<p style='color:red'>Séance non trouvée ou token invalide</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Erreur recherche séance: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:orange'>Paramètres seance ou token manquants</p>";
}

echo "<h2>Liens de test</h2>";
echo "<p><a href='/attendance_system/'>Page d'accueil</a></p>";
echo "<p><a href='/attendance_system/login_etudiant.php'>Connexion étudiant</a></p>";
echo "<p><a href='/attendance_system/presence_qr.php?seance=1&token=test123'>Test QR direct</a></p>";

echo "</body></html>";
?>
