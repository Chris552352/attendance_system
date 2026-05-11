<?php
/**
 * Configuration réseau pour les QR codes
 */

/** IPv4 du PC (ipconfig → Wi‑Fi). Vide '' = auto. */
$ip_serveur_qr_manuelle = '10.24.123.70';

/**
 * Détecte automatiquement l'IP locale de l'ordinateur
 */
function detecter_ip_locale() {
    if (!empty($_SERVER['SERVER_ADDR'])) {
        $addr = $_SERVER['SERVER_ADDR'];
        if ($addr !== '127.0.0.1' && $addr !== '::1' && strpos($addr, '169.254.') !== 0) {
            return $addr;
        }
    }

    if (PHP_OS_FAMILY === 'Windows') {
        $output = shell_exec('ipconfig');
        if ($output !== null && preg_match_all('/IPv4[^\:]*\:\s*([0-9\.]+)/', $output, $matches)) {
            $candidates = [];
            foreach ($matches[1] as $ip) {
                if ($ip === '127.0.0.1' || strpos($ip, '169.254.') === 0) {
                    continue;
                }
                if (strpos($ip, '192.168.') === 0) {
                    $candidates[] = $ip;
                }
            }
            if ($candidates !== []) {
                foreach ($candidates as $ip) {
                    if (preg_match('/^192\.168\.(0|1)\./', $ip)) {
                        return $ip;
                    }
                }
                foreach ($candidates as $ip) {
                    if ($ip !== '192.168.137.1') {
                        return $ip;
                    }
                }
                return $candidates[0];
            }
        }
    } else {
        $output = shell_exec('hostname -I 2>/dev/null || ifconfig | grep "inet " | grep -v 127.0.0.1');
        if ($output !== null && preg_match('/192\.168\.[0-9]+\.[0-9]+/', $output, $matches)) {
            return $matches[0];
        }
    }

    return '192.168.137.1';
}

$t = isset($ip_serveur_qr_manuelle) ? trim((string) $ip_serveur_qr_manuelle) : '';
$ip_locale = ($t !== '') ? $t : detecter_ip_locale();
define('IP_POINT_ACCES', $ip_locale);
define('PORT_SERVEUR', '80'); // Port 80 pour WAMP (plus simple pour téléphones)

// URL de base pour les QR codes
define('URL_BASE_QR', 'http://' . IP_POINT_ACCES . (PORT_SERVEUR != '80' ? ':' . PORT_SERVEUR : ''));

// Instructions pour configuration réseau
/*
CONFIGURATION POUR RÉSEAU LOCAL :

1. Point d'accès Wi-Fi :
   - Activez le partage de connexion sur votre ordinateur
   - Notez l'IP attribuée (généralement 192.168.137.1 sur Windows)
   - Modifiez IP_POINT_ACCES ci-dessus

2. Réseau existant :
   - Trouvez votre IP avec ipconfig (Windows) ou ifconfig (Linux/Mac)
   - Modifiez IP_POINT_ACCES avec votre IP locale
   
3. Test de connectivité :
   - Les étudiants doivent pouvoir accéder à http://VOTRE_IP:5000
   - Testez depuis un téléphone connecté au même réseau

EXEMPLES D'IP COURANTES :
- Point d'accès Windows : 192.168.137.1
- Point d'accès Android : 192.168.43.1  
- Réseau domestique : 192.168.1.X ou 192.168.0.X
- Réseau entreprise : 10.0.0.X
*/
?>