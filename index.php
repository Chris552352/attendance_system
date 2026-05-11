<?php
/**
 * Page d'accueil - Version WAMP
 */

session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

// Si l'utilisateur est connecté, rediriger vers accueil.php
if (est_connecte()) {
    rediriger('accueil.php');
}

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
                        <h1 class="display-4 text-center mb-4">Bienvenue dans le Système de Gestion de Présence</h1>
                        <p class="lead text-center mb-4">Un outil moderne pour suivre et gérer les présences des étudiants avec QR codes mobile.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-graduate fa-3x mb-3 text-primary"></i>
                                    <h4>Espace Étudiant</h4>
                                    <p>Connectez-vous et validez votre présence</p>
                                    <a href="login_etudiant.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Se connecter
                                    </a>
                                    <a href="inscription_etudiant.php" class="btn btn-success mt-2">
                                        <i class="fas fa-user-plus"></i> Créer un compte
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-tie fa-3x mb-3 text-primary"></i>
                                    <h4>Espace Enseignant</h4>
                                    <p>Gérez les présences avec QR codes</p>
                                    <a href="login.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Connexion
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h5><i class="fas fa-info-circle"></i> Fonctionnalités</h5>
                        <ul class="mb-0">
                            <li>Génération automatique de QR codes temporisés</li>
                            <li>Scan mobile pour validation de présence</li>
                            <li>Authentification sécurisée par étudiant</li>
                            <li>Rapports de présence en temps réel</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Informations techniques -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-cog"></i> Informations Système
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Configuration détectée:</h6>
                            <p class="small mb-1"><strong>IP Serveur:</strong> 
                                <?php 
                                require_once 'config_reseau.php';
                                echo IP_POINT_ACCES; 
                                ?>
                            </p>
                            <p class="small mb-1"><strong>URL Mobile:</strong> <?= URL_BASE_QR ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Outils de diagnostic:</h6>
                            <a href="test_wamp.php" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-tools"></i> Test Configuration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>