<?php
/**
 * Tableau de bord étudiant
 */

session_start();
require_once 'config/database.php';

// Vérifier si l'étudiant est connecté
if (!isset($_SESSION['etudiant_id'])) {
    header('Location: login_etudiant.php');
    exit();
}

$etudiant_id = $_SESSION['etudiant_id'];
$etudiant_nom = $_SESSION['etudiant_nom'];
$etudiant_email = $_SESSION['etudiant_email'];

// Récupérer les présences récentes de l'étudiant
$sql = "SELECT p.*, c.nom as cours_nom, s.nom_cours as seance_nom, s.date_heure 
        FROM presences p 
        LEFT JOIN cours c ON p.cours_id = c.id 
        LEFT JOIN seances s ON p.cours_id = s.id 
        WHERE p.etudiant_id = ? 
        ORDER BY p.date_enregistrement DESC 
        LIMIT 10";
$presences = db_query($sql, [$etudiant_id]);

include 'includes/header_public.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fas fa-user-graduate"></i> Tableau de Bord Étudiant</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Bienvenue, <?php echo htmlspecialchars($etudiant_nom); ?>!</h4>
                            <p class="text-muted">Email: <?php echo htmlspecialchars($etudiant_email); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="logout_etudiant.php" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-qrcode"></i> 
                        <strong>Scanner un QR Code:</strong> 
                        Utilisez votre téléphone pour scanner le QR code affiché par votre enseignant pour marquer votre présence.
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Mes Présences Récentes</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($presences)): ?>
                                <p class="text-muted">Aucune présence enregistrée pour le moment.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Cours/Séance</th>
                                                <th>Statut</th>
                                                <th>Enregistré le</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($presences as $presence): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($presence['date_presence'])); ?></td>
                                                    <td><?php echo htmlspecialchars($presence['cours_nom'] ?: $presence['seance_nom']); ?></td>
                                                    <td>
                                                        <?php if ($presence['statut'] === 'present'): ?>
                                                            <span class="badge bg-success">Présent</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Absent</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($presence['date_enregistrement'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>