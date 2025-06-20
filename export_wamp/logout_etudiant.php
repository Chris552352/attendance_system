<?php
/**
 * Déconnexion étudiant
 */

session_start();

// Détruire toutes les variables de session étudiantes
unset($_SESSION['etudiant_id']);
unset($_SESSION['etudiant_nom']);
unset($_SESSION['etudiant_email']);

// Rediriger vers la page d'accueil
header('Location: index.php');
exit();
?>