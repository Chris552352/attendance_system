<?php
/**
 * Génération de QR Code pour les séances
 */

session_start();
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
if (!est_connecte()) {
    rediriger('login.php');
}

// Pas besoin de récupérer l'utilisateur, on utilise $_SESSION['user_id']

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_cours = trim($_POST['nom_cours']);
    $duree_minutes = intval($_POST['duree_minutes']);
    
    if (!empty($nom_cours) && $duree_minutes > 0) {
        // Générer un token unique
        $token = bin2hex(random_bytes(16));
        
        // Calculer l'expiration
        $expiration = date('Y-m-d H:i:s', time() + ($duree_minutes * 60));
        
        // Enregistrer la séance
        $sql = "INSERT INTO seances (nom_cours, enseignant_id, token, expiration) VALUES (?, ?, ?, ?)";
        if (db_exec($sql, [$nom_cours, $_SESSION['user_id'], $token, $expiration])) {
            $seance_id = db_last_insert_id();
            
            // URL pour le QR code - Configuration réseau local
            require_once 'config_reseau.php';
            $qr_url = URL_BASE_QR . "/presence_qr.php?seance=" . $seance_id . "&token=" . $token;
            
            // Rediriger vers la page d'affichage du QR
            header("Location: afficher_qr.php?seance=" . $seance_id);
            exit();
        } else {
            $error = "Erreur lors de la création de la séance.";
        }
    } else {
        $error = "Veuillez remplir tous les champs correctement.";
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Générer un QR Code</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-qrcode"></i> Nouvelle Séance avec QR Code</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nom_cours" class="form-label">Nom du Cours/Séance</label>
                                <input type="text" class="form-control" id="nom_cours" name="nom_cours" 
                                       placeholder="Ex: Mathématiques - TD1" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="duree_minutes" class="form-label">Durée de validité (minutes)</label>
                                <select class="form-control" id="duree_minutes" name="duree_minutes" required>
                                    <option value="5">5 minutes</option>
                                    <option value="10" selected>10 minutes</option>
                                    <option value="15">15 minutes</option>
                                    <option value="30">30 minutes</option>
                                    <option value="60">1 heure</option>
                                </select>
                                <small class="form-text text-muted">
                                    Le QR code sera valide pendant cette durée après génération
                                </small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-qrcode"></i> Générer le QR Code
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Comment ça marche :</strong></p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Créez une séance</li>
                            <li><i class="fas fa-check text-success"></i> Affichez le QR code aux étudiants</li>
                            <li><i class="fas fa-check text-success"></i> Les étudiants scannent avec leur téléphone</li>
                            <li><i class="fas fa-check text-success"></i> Leur présence est automatiquement enregistrée</li>
                        </ul>
                        
                        <div class="alert alert-warning mt-3">
                            <small><i class="fas fa-shield-alt"></i> Le QR code expire automatiquement pour éviter les fraudes.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>