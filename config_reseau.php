<?php
/**
 * Configuration réseau pour point d'accès local
 * Modifier cette configuration selon votre environnement
 */

// Configuration IP pour réseau local
define('IP_POINT_ACCES', '192.168.1.10'); // IP de votre ordinateur/point d'accès
define('PORT_SERVEUR', '5000'); // Port du serveur PHP

// URL de base pour les QR codes
define('URL_BASE_QR', 'http://' . IP_POINT_ACCES . ':' . PORT_SERVEUR);

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