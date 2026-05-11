<?php
/**
 * Page minimale (pas de session, pas d’include) — pour test téléphone.
 * URL : http://VOTRE_IP/attendance_system/lan_ok.php
 */
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo "LAN OK\n";
echo 'Heure serveur : ' . gmdate('Y-m-d H:i:s') . " UTC\n";
echo 'Votre IP (client) : ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . "\n";
