<?php
/**
 * Déconnexion étudiant
 */

session_start();

foreach (['etudiant_id', 'etudiant_compte_id', 'etudiant_prenom', 'etudiant_nom', 'etudiant_email', 'etudiant_display'] as $k) {
    unset($_SESSION[$k]);
}

// Détruire complètement la session
session_unset();
session_destroy();

// Rediriger vers la page d'accueil
header('Location: index.php');
exit();
?>