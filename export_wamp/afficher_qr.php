<?php
/**
 * Affichage du QR Code généré
 */

session_start();
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
if (!est_connecte()) {
    rediriger('login.php');
}

$seance_id = intval($_GET['seance']);
if (!$seance_id) {
    rediriger('generer_qr.php');
}

// Récupérer les informations de la séance
$sql = "SELECT * FROM seances WHERE id = ? AND enseignant_id = ?";
$seance = db_query_single($sql, [$seance_id, $_SESSION['user_id']]);

if (!$seance) {
    rediriger('generer_qr.php');
}

// Vérifier si la séance est encore active
$est_active = strtotime($seance['expiration']) > time();

// URL pour le QR code - Configuration réseau local
require_once 'config_reseau.php';
$qr_url = URL_BASE_QR . "/presence_qr.php?seance=" . $seance_id . "&token=" . $seance['token'];

// Récupérer les présences pour cette séance
$sql_presences = "SELECT COUNT(*) as total FROM presences WHERE cours_id = ? AND date_presence = CURRENT_DATE";
$presences_count = db_query_single($sql_presences, [$seance_id])['total'] ?? 0;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-qrcode"></i> QR Code - <?php echo htmlspecialchars($seance['nom_cours']); ?></h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($est_active): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                <strong>QR Code Actif</strong> - Expire le <?php echo date('d/m/Y à H:i', strtotime($seance['expiration'])); ?>
                            </div>
                            
                            <!-- QR Code généré avec une librairie simple -->
                            <div class="qr-code-container p-4">
                                <div style="background: white; padding: 20px; border-radius: 10px; display: inline-block;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode($qr_url); ?>" 
                                         alt="QR Code" class="img-fluid">
                                </div>
                            </div>
                            
                            <p class="mt-3">
                                <strong>Instructions pour les étudiants :</strong><br>
                                Scannez ce QR code avec votre téléphone pour marquer votre présence
                            </p>
                            
                            <div class="mt-3">
                                <a href="<?php echo htmlspecialchars($qr_url); ?>" class="btn btn-info" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> Tester le lien
                                </a>
                                <button onclick="window.print()" class="btn btn-secondary">
                                    <i class="fas fa-print"></i> Imprimer
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle"></i> 
                                <strong>QR Code Expiré</strong> - A expiré le <?php echo date('d/m/Y à H:i', strtotime($seance['expiration'])); ?>
                            </div>
                            
                            <a href="generer_qr.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Générer un nouveau QR Code
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> Statistiques de la Séance</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <h3 class="text-primary"><?php echo $presences_count; ?></h3>
                            <p>Présences enregistrées</p>
                        </div>
                        
                        <hr>
                        
                        <p><strong>Créé le :</strong><br>
                           <?php echo date('d/m/Y à H:i', strtotime($seance['date_heure'])); ?></p>
                        
                        <p><strong>Expire le :</strong><br>
                           <?php echo date('d/m/Y à H:i', strtotime($seance['expiration'])); ?></p>
                        
                        <p><strong>Statut :</strong><br>
                           <?php if ($est_active): ?>
                               <span class="badge bg-success">Actif</span>
                           <?php else: ?>
                               <span class="badge bg-danger">Expiré</span>
                           <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-cog"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="generer_qr.php" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-plus"></i> Nouvelle Séance
                        </a>
                        <a href="rapports.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-chart-line"></i> Voir Rapports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .main-content { margin: 0 !important; }
    .card-header, .btn, .alert { display: none !important; }
    .qr-code-container { page-break-inside: avoid; }
}
</style>

<?php include 'includes/footer.php'; ?>