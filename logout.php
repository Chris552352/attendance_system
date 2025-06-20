<?php
/**
 * Page de déconnexion
 */

// Démarrer la session
session_start();

// Inclure les fichiers nécessaires
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Déconnecter l'utilisateur
deconnecter();

// Afficher un message de succès
alerte('Vous avez été déconnecté avec succès.', 'success');

// Rediriger vers la page de connexion
rediriger('login.php');
?>
