<?php
/**
 * Liste des adresses à utiliser (PC + téléphone). Ouvrez sur le PC après avoir lancé WAMP.
 */
require_once __DIR__ . '/config_reseau.php';

header('Content-Type: text/html; charset=utf-8');
$ip = defined('IP_POINT_ACCES') ? IP_POINT_ACCES : '';
$base = $ip !== '' ? 'http://' . $ip . '/attendance_system' : '';
$local = 'http://localhost/attendance_system';

function u($href, $label) {
    return '<p><a href="' . htmlspecialchars($href) . '">' . htmlspecialchars($label) . '</a><br><code style="user-select:all;word-break:break-all">' . htmlspecialchars($href) . '</code></p>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Adresses réseau — présence</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 720px; margin: 20px auto; padding: 16px; line-height: 1.5; }
    h1 { font-size: 1.2rem; }
    h2 { font-size: 1.05rem; margin-top: 1.5rem; border-bottom: 1px solid #ccc; padding-bottom: 6px; }
    .ip { background: #e8f4fc; padding: 12px 14px; border-radius: 8px; margin: 12px 0; }
    code { background: #f0f0f0; padding: 2px 6px; }
    .note { color: #444; font-size: 0.95rem; }
  </style>
</head>
<body>
  <h1>Adresses pour le système de présence</h1>

  <div class="ip">
    <strong>IPv4 du PC (carte Wi‑Fi)</strong> utilisée dans les QR :<br>
    <code style="font-size:1.1rem"><?= htmlspecialchars($ip ?: '(non définie)') ?></code>
    <p class="note">Pour vérifier sur le PC : ouvrez <code>cmd</code> → tapez <code>ipconfig</code> → ligne « Adresse IPv4 » sous <strong>Carte réseau sans fil Wi‑Fi</strong>. Si ce n’est plus la même, modifiez <code>config_reseau.php</code> → <code>$ip_serveur_qr_manuelle</code>.</p>
  </div>

  <h2>Sur le PC (navigateur)</h2>
  <?= u($local . '/', 'Accueil du projet') ?>
  <?= u($local . '/generer_qr.php', 'Générer un QR (enseignant connecté)') ?>

  <h2>Sur le téléphone (même Wi‑Fi que le PC)</h2>
  <p class="note">Collez l’URL <strong>en entier</strong>, avec <code>http://</code> au début (pas <code>https://</code>).</p>
  <?php if ($base !== ''): ?>
    <?= u($base . '/lan_ok.html', 'Test le plus simple (page statique)') ?>
    <?= u($base . '/lan_ok.php', 'Test PHP (texte brut)') ?>
    <?= u($base . '/depannage_lan_wamp.php', 'Page d’aide réseau') ?>
    <?= u($base . '/login_etudiant.php', 'Connexion étudiant') ?>
  <?php else: ?>
    <p>Configurez d’abord <code>config_reseau.php</code>.</p>
  <?php endif; ?>

  <h2>Si le téléphone ne charge rien (timeout)</h2>
  <ul class="note">
    <li>Le téléphone doit être sur le <strong>même réseau Wi‑Fi</strong> que le PC. Dans les infos du Wi‑Fi sur le téléphone, l’adresse doit souvent commencer comme celle du PC (ex. PC <code>10.24.123.70</code> → téléphone souvent <code>10.24.123.…</code>).</li>
    <li>Certains Wi‑Fi (invité, école) <strong>empêchent</strong> un téléphone d’accéder à un autre appareil : dans ce cas il faut un autre réseau (ex. partage de connexion depuis le PC).</li>
    <li>WAMP <strong>vert</strong>, Apache démarré. Sur le PC : exécutez <strong><code>autoriser_acces_reseau_wamp.bat</code></strong> (clic droit → <strong>Exécuter en tant qu’administrateur</strong>) — met le Wi‑Fi en « réseau privé » et ouvre le pare-feu pour Apache.</li>
  </ul>
</body>
</html>
