<?php
/**
 * Page d'accueil principale
 */

// Démarrer la session
session_start();

// Inclure les fichiers nécessaires
require_once 'includes/functions.php';
require_once 'config/database.php';

// Si l'utilisateur est connecté, rediriger vers accueil.php
if (est_connecte()) {
    rediriger('accueil.php');
}

// Inclure le header public
include 'includes/header_public.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-clipboard-check"></i> Système de Gestion de Présence</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="assets/images/attendance.svg" alt="Gestion de présence" class="img-fluid" style="max-height: 200px;">
                    </div>
                    
                    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="display-4 text-center mb-4">Bienvenue dans le Système de Gestion de Présence</h1>
                        <p class="lead text-center mb-4">Un outil moderne pour suivre et gérer les présences des étudiants.</p>
                        <div class="text-center mt-4">
                            <img src="assets/images/decoratives/La-reussite-scolaire.jpg" alt="La réussite scolaire" class="img-fluid rounded" style="max-width: 600px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-graduate fa-3x mb-3 text-primary"></i>
                                    <h4>Espace Étudiant</h4>
                                    <p>Justifiez vos absences en ligne</p>
                                    <a href="login_etudiant.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Se connecter
                                    </a>
                                    <a href="justifier_absence.php" class="btn btn-outline-primary mt-2">
                                        <i class="fas fa-clipboard-check"></i> Justifier une absence
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-tie fa-3x mb-3 text-primary"></i>
                                    <h4>Espace Enseignant</h4>
                                    <p>Gérez les présences de vos cours</p>
                                    <a href="login.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Connexion
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
