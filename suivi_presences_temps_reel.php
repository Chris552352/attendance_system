<?php
/**
 * Suivi en temps réel des présences
 */

session_start();
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'config_reseau.php';

// Vérifier l'authentification
if (!est_connecte()) {
    rediriger('login.php');
}

$seance_id = intval($_GET['seance'] ?? 0);
if (!$seance_id) {
    rediriger('generer_qr.php');
}

// Récupérer les informations de la séance
$sql = "SELECT * FROM seances WHERE id = ? AND enseignant_id = ?";
$seance = db_query_single($sql, [$seance_id, $_SESSION['user_id']]);

$erreur_matiere = '';
if (!$seance) {
    $erreur_matiere = "Erreur : la matière ou la séance demandée n'existe pas.";
}

// Vérifier si la séance est encore active
$est_active = strtotime($seance['expiration']) > time();

// Récupérer les présences en temps réel
$sql_presences = "SELECT p.*, e.nom, e.prenom, e.email, e.matricule
                  FROM presences p 
                  JOIN etudiants e ON p.etudiant_id = e.id 
                  WHERE p.seance_id = ? AND p.date_presence = CURRENT_DATE 
                  ORDER BY p.date_enregistrement DESC";
$presences = db_query($sql_presences, [$seance_id]);

// Compter les présences
$total_presences = count($presences);

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
                        <h4><i class="fas fa-users"></i> Suivi des Présences - <?php echo htmlspecialchars($seance['nom_cours']); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if ($est_active): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                <strong>Séance Active</strong> - Expire le <?php echo date('d/m/Y à H:i', strtotime($seance['expiration'])); ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle"></i> 
                                <strong>Séance Expirée</strong> - A expiré le <?php echo date('d/m/Y à H:i', strtotime($seance['expiration'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo $total_presences; ?></h3>
                                        <p>Présences enregistrées</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo date('H:i'); ?></h3>
                                        <p>Heure actuelle</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo $est_active ? 'Actif' : 'Expiré'; ?></h3>
                                        <p>Statut de la séance</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($presences): ?>
                            <h5><i class="fas fa-list"></i> Liste des présences</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Étudiant</th>
                                            <th>Matricule</th>
                                            <th>Email</th>
                                            <th>Heure d'arrivée</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($presences as $index => $presence): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($presence['nom'] . ' ' . $presence['prenom']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($presence['matricule']); ?></td>
                                                <td><?php echo htmlspecialchars($presence['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?php echo date('H:i:s', strtotime($presence['date_enregistrement'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Présent</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucune présence enregistrée pour le moment.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <button onclick="location.reload()" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                            <a href="generer_qr.php" class="btn btn-secondary">
                                <i class="fas fa-plus"></i> Nouvelle Séance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Informations de la Séance</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nom :</strong><br>
                           <?php echo htmlspecialchars($seance['nom_cours']); ?></p>
                        
                        <p><strong>Créée le :</strong><br>
                           <?php echo date('d/m/Y à H:i', strtotime($seance['date_creation'])); ?></p>
                        
                        <p><strong>Expire le :</strong><br>
                           <?php echo date('d/m/Y à H:i', strtotime($seance['expiration'])); ?></p>
                        
                        <p><strong>Token :</strong><br>
                           <code><?php echo htmlspecialchars($seance['token']); ?></code></p>
                        
                        <hr>
                        
                        <h6>QR Code URL :</h6>
                        <p><small><?php echo URL_BASE_QR . "/presence_qr.php?seance=" . $seance_id . "&token=" . $seance['token']; ?></small></p>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-cog"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="afficher_qr.php?seance=<?php echo $seance_id; ?>" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-qrcode"></i> Afficher QR Code
                        </a>
                        <a href="rapports.php" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-chart-line"></i> Voir Rapports
                        </a>
                        <button onclick="exportPresences()" class="btn btn-outline-success w-100">
                            <i class="fas fa-download"></i> Exporter Présences
                        </button>
                    </div>
                </div>

                <?php if (!$est_active): ?>
                    <form method="POST" action="cloturer_seance.php" onsubmit="return confirm('Clôturer la séance et marquer tous les absents ?');">
                        <input type="hidden" name="seance_id" value="<?php echo $seance_id; ?>">
                        <button type="submit" class="btn btn-danger w-100 mt-2">
                            <i class="fas fa-user-slash"></i> Clôturer la séance et marquer les absents
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Actualisation automatique toutes les 30 secondes
setInterval(function() {
    location.reload();
}, 30000);

function exportPresences() {
    // Fonction pour exporter les présences (à implémenter)
    alert('Fonction d\'export à implémenter');
}
</script>

<?php include 'includes/footer.php'; ?> 