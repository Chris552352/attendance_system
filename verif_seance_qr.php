<?php
require_once 'config/database.php';

$seance_id = isset($_GET['seance_id']) ? intval($_GET['seance_id']) : null;
$token = isset($_GET['token']) ? $_GET['token'] : '';

function get_mysql_now() {
    try {
        $row = db_query_single('SELECT NOW() as mysql_now');
        return $row ? $row['mysql_now'] : null;
    } catch (Exception $e) {
        return 'Erreur: ' . $e->getMessage();
    }
}

function get_active_column_type() {
    try {
        $row = db_query_single("SHOW COLUMNS FROM seances WHERE Field = 'active'");
        return $row ? $row['Type'] : 'Colonne active non trouvée';
    } catch (Exception $e) {
        return 'Erreur: ' . $e->getMessage();
    }
}

$now_php = date('Y-m-d H:i:s');
$now_mysql = get_mysql_now();
$active_type = get_active_column_type();

if (!$seance_id || !$token) {
    echo '<form method="get">';
    echo '<label>ID de la séance : <input type="number" name="seance_id" required></label><br>';
    echo '<label>Token : <input type="text" name="token" required></label><br>';
    echo '<button type="submit">Vérifier</button>';
    echo '</form>';
    exit;
}

$seance = db_query_single("SELECT * FROM seances WHERE id = ?", [$seance_id]);

if (!$seance) {
    echo "<p style='color:red;'>Aucune séance trouvée avec l'ID $seance_id.</p>";
    exit;
}

$token_ok = ($seance['token'] === $token);
$not_expired = (strtotime($seance['expiration']) > strtotime($now_php));
$is_active = (isset($seance['active']) ? $seance['active'] == 1 : true);
$qr_seance = db_query_single("SELECT * FROM seances WHERE id = ? AND token = ? AND expiration > NOW() AND active = 1", [$seance_id, $token]);

?>
<h2>Diagnostic QR pour la séance ID <?php echo htmlspecialchars($seance_id); ?></h2>
<ul>
    <li><b>Type colonne active :</b> <?php echo htmlspecialchars($active_type); ?></li>
    <li><b>Heure PHP :</b> <?php echo htmlspecialchars($now_php); ?></li>
    <li><b>Heure MySQL (NOW) :</b> <?php echo htmlspecialchars($now_mysql); ?></li>
    <li><b>Token attendu (brut) :</b> <code><?php echo htmlspecialchars($seance['token']); ?></code> (longueur : <?php echo strlen($seance['token']); ?>)</li>
    <li><b>Token fourni :</b> <code><?php echo htmlspecialchars($token); ?></code> (longueur : <?php echo strlen($token); ?>)</li>
    <li><b>Expiration (brut) :</b> <?php echo htmlspecialchars($seance['expiration']); ?></li>
    <li><b>Active :</b> <?php echo htmlspecialchars($seance['active']); ?></li>
    <li><b>Token correct ?</b> <?php echo $token_ok ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
    <li><b>Non expiré ? (PHP)</b> <?php echo $not_expired ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
    <li><b>Active ?</b> <?php echo $is_active ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
</ul>
<h3>Résultat de la requête presence_qr.php :</h3>
<?php
if ($qr_seance) {
    echo '<p style="color:green;font-weight:bold;">La requête retourne la séance : QR VALIDE</p>';
} else {
    echo '<p style="color:red;font-weight:bold;">La requête ne retourne rien : QR Code expiré ou invalide pour le système</p>';
}
?>