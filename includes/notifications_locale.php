<?php
/**
 * Notifications locales (WAMP) : journalisation dans logs/ au lieu d’envoi SMTP/SMS réel.
 * Brancher ici PHPMailer ou un provider SMS en production.
 */

function notifications_log(string $canal, string $sujet, string $corps): void
{
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $line = date('c') . "\t" . $canal . "\t" . $sujet . "\t" . str_replace(["\r", "\n"], ' ', $corps) . "\n";
    @file_put_contents($dir . '/notifications.log', $line, FILE_APPEND | LOCK_EX);
}
