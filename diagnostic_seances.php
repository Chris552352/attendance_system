<?php
require_once 'config/database.php';

$seances = db_query("SELECT * FROM seances ORDER BY id DESC LIMIT 10");

echo "<h2>Diagnostic des 10 dernières séances</h2>";
echo "<table border='1' cellpadding='6' style='border-collapse:collapse;'>";
echo "<tr style='background:#1976d2;color:white;'><th>ID</th><th>Nom du cours</th><th>Token</th><th>Expiration</th><th>Active</th><th>Statut</th></tr>";
foreach ($seances as $s) {
    $is_active = ($s['active'] == 1);
    $not_expired = (strtotime($s['expiration']) > time());
    $statut = ($is_active && $not_expired) ? "<span style='color:green;font-weight:bold;'>Valide</span>" : "<span style='color:red;font-weight:bold;'>Expiré ou désactivé</span>";
    echo "<tr>";
    echo "<td>" . htmlspecialchars($s['id']) . "</td>";
    echo "<td>" . htmlspecialchars($s['nom_cours']) . "</td>";
    echo "<td style='font-size:11px;'>" . htmlspecialchars($s['token']) . "</td>";
    echo "<td>" . htmlspecialchars($s['expiration']) . "</td>";
    echo "<td>" . ($is_active ? "1" : "0") . "</td>";
    echo "<td>" . $statut . "</td>";
    echo "</tr>";
}
echo "</table>";

// Diagnostic détaillé pour la séance 39 et le token donné
$seance_id = 39;
$token = 'ec4add55c9805185fd3f4ffc2afdcf2c';
$seance = db_query_single("SELECT * FROM seances WHERE id = ?", [$seance_id]);

if ($seance) {
    echo "<h2 style='margin-top:30px;'>Diagnostic détaillé pour la séance ID 39</h2>";
    echo "<ul>";
    echo "<li><b>ID :</b> " . htmlspecialchars($seance['id']) . "</li>";
    echo "<li><b>Nom du cours :</b> " . htmlspecialchars($seance['nom_cours']) . "</li>";
    echo "<li><b>Token attendu :</b> <code>" . htmlspecialchars($seance['token']) . "</code></li>";
    echo "<li><b>Token du QR :</b> <code>" . htmlspecialchars($token) . "</code></li>";
    echo "<li><b>Expiration :</b> " . htmlspecialchars($seance['expiration']) . "</li>";
    echo "<li><b>Active :</b> " . ($seance['active'] ? '1' : '0') . "</li>";
    $is_active = ($seance['active'] == 1);
    $not_expired = (strtotime($seance['expiration']) > time());
    $token_ok = ($seance['token'] === $token);
    echo "<li><b>Statut :</b> ";
    if (!$token_ok) {
        echo "<span style='color:red;font-weight:bold;'>Token du QR ne correspond pas à la base</span>";
    } elseif (!$is_active) {
        echo "<span style='color:red;font-weight:bold;'>Séance désactivée</span>";
    } elseif (!$not_expired) {
        echo "<span style='color:red;font-weight:bold;'>Séance expirée</span>";
    } else {
        echo "<span style='color:green;font-weight:bold;'>Valide</span>";
    }
    echo "</li>";
    echo "</ul>";
} else {
    echo "<h2 style='color:red;'>Aucune séance trouvée avec l'ID 39</h2>";
}

// Diagnostic détaillé pour la séance ID 40
$seance_id = 40;
$token_qr = '7865c7f8dd702d5bfc43e69704e03f23';
$s = db_query_single("SELECT * FROM seances WHERE id = ?", [$seance_id]);

if ($s) {
    echo "<h2>Diagnostic détaillé pour la séance ID 40</h2>";
    echo "<ul>";
    echo "<li><b>ID :</b> " . htmlspecialchars($s['id']) . "</li>";
    echo "<li><b>Nom du cours :</b> " . htmlspecialchars($s['nom_cours']) . "</li>";
    echo "<li><b>Token attendu :</b> " . htmlspecialchars($s['token']) . "</li>";
    echo "<li><b>Token du QR :</b> " . htmlspecialchars($token_qr) . "</li>";
    echo "<li><b>Expiration :</b> " . htmlspecialchars($s['expiration']) . "</li>";
    echo "<li><b>Active :</b> " . htmlspecialchars($s['active']) . "</li>";
    $is_active = ($s['active'] == 1);
    $not_expired = (strtotime($s['expiration']) > time());
    $token_ok = ($s['token'] === $token_qr);
    $statut = ($is_active && $not_expired && $token_ok) ? "<span style='color:green;font-weight:bold;'>Valide</span>" : "<span style='color:red;font-weight:bold;'>Expiré, désactivé ou token incorrect</span>";
    echo "<li><b>Statut :</b> $statut</li>";
    if (!$is_active) echo "<li style='color:red;'>La séance n'est pas active.</li>";
    if (!$not_expired) echo "<li style='color:red;'>La séance est expirée.</li>";
    if (!$token_ok) echo "<li style='color:red;'>Le token ne correspond pas.</li>";
    echo "</ul>";
} else {
    echo "<h2 style='color:red;'>Aucune séance trouvée avec l'ID 40</h2>";
}

// Diagnostic détaillé pour la séance ID 42
$seance_id = 42;
$token_qr = 'ae3c047cac90e92071a752e0e9c62ce4';
$s = db_query_single("SELECT * FROM seances WHERE id = ?", [$seance_id]);

if ($s) {
    echo "<h2>Diagnostic détaillé pour la séance ID 42</h2>";
    echo "<ul>";
    echo "<li><b>ID :</b> " . htmlspecialchars($s['id']) . "</li>";
    echo "<li><b>Nom du cours :</b> " . htmlspecialchars($s['nom_cours']) . "</li>";
    echo "<li><b>Token attendu :</b> " . htmlspecialchars($s['token']) . "</li>";
    echo "<li><b>Token du QR :</b> " . htmlspecialchars($token_qr) . "</li>";
    echo "<li><b>Expiration :</b> " . htmlspecialchars($s['expiration']) . "</li>";
    echo "<li><b>Active :</b> " . htmlspecialchars($s['active']) . "</li>";
    $is_active = ($s['active'] == 1);
    $not_expired = (strtotime($s['expiration']) > time());
    $token_ok = ($s['token'] === $token_qr);
    $statut = ($is_active && $not_expired && $token_ok) ? "<span style='color:green;font-weight:bold;'>Valide</span>" : "<span style='color:red;font-weight:bold;'>Expiré, désactivé ou token incorrect</span>";
    echo "<li><b>Statut :</b> $statut</li>";
    if (!$is_active) echo "<li style='color:red;'>La séance n'est pas active.</li>";
    if (!$not_expired) echo "<li style='color:red;'>La séance est expirée.</li>";
    if (!$token_ok) echo "<li style='color:red;'>Le token ne correspond pas.</li>";
    echo "</ul>";
} else {
    echo "<h2 style='color:red;'>Aucune séance trouvée avec l'ID 42</h2>";
}

echo "<p style='margin-top:20px;'><a href='generer_qr.php' class='btn btn-primary'>Générer un nouveau QR</a></p>"; 