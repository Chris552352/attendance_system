<?php

/**
 * Affichage du QR Code généré
 */

session_start();
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once __DIR__ . '/includes/seance_mode.php';
require_once __DIR__ . '/vendor/autoload.php';

// --- QR Code Library ---
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

// Vérifier l'authentification
if (!est_connecte()) {
    rediriger('login.php');
}

$seance_id = intval($_GET['seance'] ?? 0);
if (!$seance_id) {
    rediriger('generer_qr.php');
}

// Récupérer les informations de la séance
$sql = 'SELECT s.*, ts.mode_marquage AS ts_mode_marquage
        FROM seances s
        LEFT JOIN types_seances ts ON s.type_seance_id = ts.id
        WHERE s.id = ? AND s.enseignant_id = ?';
$seance = db_query_single($sql, [$seance_id, $_SESSION['user_id']]);

$erreur_matiere = '';
if (!$seance) {
    $erreur_matiere = "Erreur : la matière ou la séance demandée n'existe pas.";
}

$qr_etudiant_ok = $seance ? seance_etudiant_marquage_qr_autorise($seance) : false;

// Vérifier si la séance est encore active (fenêtre QR étudiant)
$est_active = $seance && $qr_etudiant_ok && strtotime($seance['expiration']) > time();

// URL pour le QR code - Configuration réseau local
require_once 'config_reseau.php';
$qr_url = $seance ? URL_BASE_QR . '/presence_qr.php?seance=' . $seance_id . '&token=' . $seance['token'] : '';

// --- Générer le QR code localement avec le Builder ---
$qr_code_data_uri = '';
if ($est_active) {
    try {
        // Nouvelle API pour Endroid QR Code 6.x
        $builder = new Builder(
            writer: new PngWriter(),
            data: $qr_url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10
        );
        
        $result = $builder->build();
        $qr_code_data_uri = $result->getDataUri();
    } catch (Exception $e) {
        error_log('Erreur de génération QR Code: ' . $e->getMessage());
        $qr_code_data_uri = ''; // Laisser l'image vide en cas d'erreur
    }
}

// Présences pour cette séance (aujourd'hui)
$presences_row = $seance ? db_query_single(
    'SELECT COUNT(*) AS total FROM presences WHERE seance_id = ? AND date_presence = CURDATE()',
    [$seance_id]
) : null;
$presences_count = (int) ($presences_row['total'] ?? 0);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <?php if (!empty($erreur_matiere)): ?>
            <div class="alert alert-danger mt-4">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erreur_matiere); ?>
            </div>
        <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-qrcode"></i> QR Code - <?php echo htmlspecialchars($seance['nom_cours']); ?></h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($seance && !$qr_etudiant_ok): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-user-lock"></i>
                                <strong>Mode examen / contrôle ou séance manuelle</strong> — les étudiants ne peuvent pas pointer via ce QR.
                            </div>
                            <p class="text-start">Utilisez la feuille d’émargement pour enregistrer les présences :</p>
                            <a href="presence_etablissement_superieur.php?seance_id=<?php echo (int) $seance_id; ?>" class="btn btn-primary mb-3">
                                <i class="fas fa-clipboard-list"></i> Feuille d’émargement enseignant
                            </a>
                        <?php elseif ($est_active && !empty($qr_code_data_uri)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                <strong>QR Code Actif</strong> - Expire le <?php echo date('d/m/Y à H:i', strtotime($seance['expiration'])); ?>
                            </div>
                            
                            <!-- QR Code généré localement -->
                            <div class="qr-code-container p-4">
                                <div style="background: white; padding: 20px; border-radius: 10px; display: inline-block;">
                                    <img src="<?php echo $qr_code_data_uri; ?>" 
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
                                <a href="suivi_presences_temps_reel.php?seance=<?php echo $seance_id; ?>" class="btn btn-success">
                                    <i class="fas fa-users"></i> Suivi en temps réel
                                </a>
                                <button onclick="window.print()" class="btn btn-secondary">
                                    <i class="fas fa-print"></i> Imprimer
                                </button>
                            </div>
                        <?php elseif ($est_active && empty($qr_code_data_uri)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle"></i> 
                                <strong>Erreur de génération du QR Code.</strong><br>
                                Veuillez vérifier la configuration du serveur et les logs d'erreur.
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
                        <form method="POST" action="cloturer_seance.php" onsubmit="return confirm('Clôturer la séance et marquer tous les absents ?');">
                            <input type="hidden" name="seance_id" value="<?php echo $seance_id; ?>">
                            <button type="submit" class="btn btn-danger w-100 mt-2">
                                <i class="fas fa-user-slash"></i> Clôturer la séance et marquer les absents
                            </button>
                        </form>
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
                           <?php echo date('d/m/Y à H:i', strtotime($seance['date_creation'])); ?></p>
                        
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
        <?php endif; ?>
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