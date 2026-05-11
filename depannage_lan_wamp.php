<?php
/**
 * Ouvrez cette page :
 * - sur le PC : http://localhost/attendance_system/depannage_lan_wamp.php
 * - sur le téléphone : http://VOTRE_IP/attendance_system/depannage_lan_wamp.php
 *
 * Si la page s’affiche sur le téléphone, l’accès LAN fonctionne (QR et app aussi).
 */
require_once __DIR__ . '/config_reseau.php';

header('Content-Type: text/html; charset=utf-8');
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$server = $_SERVER['SERVER_ADDR'] ?? '';
$ipQr = defined('IP_POINT_ACCES') ? IP_POINT_ACCES : '';
$exempleTel = $ipQr !== '' ? 'http://' . $ipQr . '/attendance_system/depannage_lan_wamp.php' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Test accès LAN — WAMP</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 640px; margin: 24px auto; padding: 0 16px; line-height: 1.45; }
    .ok { background: #d1fae5; padding: 12px 16px; border-radius: 8px; }
    .warn { background: #fef3c7; padding: 12px 16px; border-radius: 8px; margin-top: 16px; }
    .crit { background: #fee2e2; border: 2px solid #dc2626; padding: 14px 16px; border-radius: 8px; margin-top: 16px; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
    ol { padding-left: 1.2rem; }
    h1 { font-size: 1.25rem; }
    h2 { font-size: 1.05rem; margin-top: 1.25rem; }
  </style>
</head>
<body>
  <h1>Accès depuis le réseau local</h1>

  <?php if ($remote !== '' && $remote !== '127.0.0.1' && $remote !== '::1'): ?>
    <p class="ok"><strong>OK</strong> — Cette page a été chargée depuis une autre machine sur le réseau (<code><?= htmlspecialchars($remote) ?></code>). Le pare-feu et Apache acceptent les connexions LAN.</p>
  <?php else: ?>
    <p>Vous consultez cette page <strong>depuis le PC</strong> (adresse vue : <code><?= htmlspecialchars($remote ?: '?') ?></code>). Pour valider le Wi‑Fi, sur le téléphone ouvrez : <?php if ($exempleTel !== ''): ?><code><?= htmlspecialchars($exempleTel) ?></code><?php else: ?>l’URL indiquée sous le QR (config <code>config_reseau.php</code>).<?php endif; ?></p>
  <?php endif; ?>

  <p>Adresse du serveur vue par PHP : <code><?= htmlspecialchars($server ?: '?') ?></code></p>
  <?php if ($ipQr !== ''): ?>
    <p>IPv4 configurée pour les QR (<code>config_reseau.php</code>) : <strong><?= htmlspecialchars($ipQr) ?></strong></p>
  <?php endif; ?>

  <div class="crit">
    <h2>Chrome : « a mis trop de temps à répondre » — ERR_CONNECTION_TIMED_OUT</h2>
    <p>Le téléphone <strong>n’atteint pas le PC</strong> (réseau), avant même Apache.</p>
    <ol>
      <li><strong>Même Wi‑Fi, même « famille » d’IP</strong> : sur le téléphone (détails de la connexion Wi‑Fi), l’IPv4 doit ressembler à celle du PC. Exemple actuel du projet : PC <code><?= htmlspecialchars($ipQr ?: 'voir ipconfig') ?></code> → le téléphone devrait avoir une IP du type <code>10.24.123.xxx</code> (même préfixe que le PC). Si le téléphone est en <code>192.168.…</code> et le PC en <code>10.24.…</code>, ça ne pourra pas joindre le PC.</li>
      <li><strong>Wi‑Fi invité / école / entreprise</strong> : souvent <strong>aucun accès d’un appareil à un autre</strong> → timeout. Il faut un réseau qui autorise ça, ou un <strong>point d’accès créé par le PC</strong>.</li>
      <li><strong>Point d’accès Windows</strong> : Paramètres → Réseau → Point d’accès mobile → partagez le Wi‑Fi. Connectez le téléphone à ce réseau. Sur le PC, <code>ipconfig</code> → nouvelle IPv4 (souvent <code>192.168.137.1</code>). Mettez-la dans <code>config_reseau.php</code> (<code>$ip_serveur_qr_manuelle</code>), regénérez les QR. Liste des URLs : <a href="adresses_reseau.php">adresses_reseau.php</a>.</li>
      <li>Si une appli « sécurité / DNS / filtre » sur le téléphone redirige tout le trafic, désactivez-la le temps du test.</li>
    </ol>
  </div>

  <div class="warn">
    <h2>Autres erreurs (403, connexion réinitialisée)</h2>
    <ol>
      <li>WAMP icône <strong>verte</strong> ; Apache démarré.</li>
      <li>Dans <code>httpd.conf</code> et <code>httpd-vhosts.conf</code> : <code>Require all granted</code> pour le dossier <code>www</code> (déjà fait si vous avez suivi le dépannage).</li>
      <li>Pare-feu : exécutez <code>ouvrir_port80_parefeu.bat</code> <strong>en administrateur</strong> (règle sur tous les profils réseau).</li>
      <li>URL exacte : <code>http://</code> (pas <code>https://</code>) + IP + chemin, ex. <?php if ($exempleTel !== ''): ?><code><?= htmlspecialchars($exempleTel) ?></code><?php else: ?>voir <code>config_reseau.php</code>.<?php endif; ?></li>
    </ol>
  </div>
</body>
</html>
