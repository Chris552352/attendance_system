<?php
/**
 * Page de validation de présence via QR Code
 */

session_start();
require_once 'config/database.php';

$seance_id = intval($_GET['seance']);
$token = $_GET['token'] ?? '';

$error = '';
$success = '';

// Vérifier si les paramètres sont valides
if (!$seance_id || !$token) {
    $error = 'QR Code invalide.';
} else {
    // Vérifier la validité de la séance et du token
    $sql = "SELECT * FROM seances WHERE id = ? AND token = ? AND expiration > NOW() AND actif = TRUE";
    $seance = db_query_single($sql, [$seance_id, $token]);
    
    if (!$seance) {
        $error = 'QR Code expiré ou invalide.';
    }
}

// Traitement de la validation de présence
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    if (!isset($_SESSION['etudiant_id'])) {
        $error = 'Vous devez être connecté pour marquer votre présence.';
    } else {
        $etudiant_id = $_SESSION['etudiant_id'];
        
        // Vérifier si l'étudiant a déjà marqué sa présence pour cette séance
        $sql_check = "SELECT id FROM presences WHERE etudiant_id = ? AND cours_id = ? AND date_presence = CURRENT_DATE";
        $presence_existante = db_query_single($sql_check, [$etudiant_id, $seance_id]);
        
        if ($presence_existante) {
            $error = 'Vous avez déjà marqué votre présence pour cette séance.';
        } else {
            // Enregistrer la présence
            $sql_insert = "INSERT INTO presences (etudiant_id, cours_id, date_presence, statut, enregistre_par, date_enregistrement) 
                           VALUES (?, ?, CURRENT_DATE, 'present', ?, NOW())";
            
            if (db_exec($sql_insert, [$etudiant_id, $seance_id, $etudiant_id])) {
                $success = 'Votre présence a été enregistrée avec succès !';
            } else {
                $error = 'Erreur lors de l\'enregistrement de votre présence.';
            }
        }
    }
}

include 'includes/header_public.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-qrcode"></i> Validation de Présence</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                        
                        <?php if (!isset($_SESSION['etudiant_id'])): ?>
                            <div class="text-center">
                                <a href="login_etudiant.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Se connecter
                                </a>
                            </div>
                        <?php endif; ?>
                        
                    <?php elseif ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                        
                        <div class="text-center">
                            <a href="dashboard_etudiant.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Retour au tableau de bord
                            </a>
                        </div>
                        
                    <?php elseif (isset($_SESSION['etudiant_id'])): ?>
                        <!-- Étudiant connecté, afficher les détails et le formulaire de confirmation -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Confirmation de Présence</h5>
                            <p><strong>Cours/Séance :</strong> <?php echo htmlspecialchars($seance['nom_cours']); ?></p>
                            <p><strong>Date :</strong> <?php echo date('d/m/Y à H:i'); ?></p>
                        </div>
                        
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5><i class="fas fa-user"></i> Vous êtes connecté en tant que :</h5>
                                <p class="mb-0">
                                    <strong><?php echo htmlspecialchars($_SESSION['etudiant_nom']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($_SESSION['etudiant_email']); ?></small>
                                </p>
                            </div>
                        </div>
                        
                        <form method="POST" class="mt-3">
                            <div class="text-center">
                                <p>Confirmez-vous votre présence pour cette séance ?</p>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check"></i> Confirmer ma présence
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Pas vous ? <a href="logout_etudiant.php">Se déconnecter</a>
                            </small>
                        </div>
                        
                    <?php else: ?>
                        <!-- Étudiant non connecté -->
                        <div class="alert alert-warning">
                            <i class="fas fa-sign-in-alt"></i> 
                            <strong>Connexion requise</strong><br>
                            Vous devez vous connecter pour marquer votre présence.
                        </div>
                        
                        <?php if (isset($seance)): ?>
                            <div class="alert alert-info">
                                <p><strong>Cours/Séance :</strong> <?php echo htmlspecialchars($seance['nom_cours']); ?></p>
                                <p><strong>Date :</strong> <?php echo date('d/m/Y à H:i'); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <a href="login_etudiant.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>