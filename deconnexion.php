<?php
// Page de déconnexion
require_once 'includes/auth.php';

// Déconnecter l'utilisateur
deconnecter();

// Rediriger vers la page de connexion avec un message
$_SESSION['message_succes'] = "Vous avez été déconnecté avec succès.";
header('Location: login.php');
exit();
?>
