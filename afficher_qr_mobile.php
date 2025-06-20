<?php
/**
 * Affichage QR Code optimisé pour scan mobile
 */

require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'config_reseau.php';

require_auth();

// Récupérer les informations du QR généré
if (!isset($_SESSION['qr_generated'])) {
    header('Location: generer_qr_mobile.php');
    exit;
}

$qr_info = $_SESSION['qr_generated'];

include 'includes/header.php';
?>

<style>
.qr-container {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
    max-width: 500px;
    margin: 0 auto;
}

.qr-code {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: inline-block;
    margin: 20px 0;
}

.countdown {
    font-size: 1.2em;
    font-weight: bold;
    color: #dc3545;
}

.info-box {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
}

@media print {
    .no-print { display: none !important; }
    .qr-container { box-shadow: none; }
}
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="qr-container">
                <h2 class="text-primary mb-4">
                    <i class="fas fa-qrcode"></i> QR Code de Présence
                </h2>
                
                <div class="info-box">
                    <h5><i class="fas fa-book"></i> <?= htmlspecialchars($qr_info['cours']) ?></h5>
                    <p class="mb-1"><strong>IP Serveur:</strong> <?= $qr_info['ip_detectee'] ?></p>
                    <p class="mb-1"><strong>Validité:</strong> <?= $qr_info['duree'] ?> minutes</p>
                    <p class="mb-0"><strong>Expire le:</strong> <?= date('d/m/Y à H:i', strtotime($qr_info['expiration'])) ?></p>
                </div>

                <!-- QR Code généré via API Google (simple et rapide) -->
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?= urlencode($qr_info['url']) ?>" 
                         alt="QR Code" 
                         style="max-width: 100%; height: auto;">
                </div>

                <!-- Countdown Timer -->
                <div class="countdown" id="countdown">
                    Calcul du temps restant...
                </div>

                <!-- URL en texte (pour debug) -->
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>URL:</strong> <?= htmlspecialchars($qr_info['url']) ?>
                    </small>
                </div>

                <!-- Actions -->
                <div class="mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                    <a href="generer_qr_mobile.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau QR Code
                    </a>
                    <a href="accueil.php" class="btn btn-outline-primary">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </div>
            </div>

            <!-- Instructions pour les étudiants -->
            <div class="card mt-4 no-print">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-mobile-alt"></i> Instructions pour les Étudiants
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>1. Connexion Wi-Fi</h6>
                            <ul class="small">
                                <li>Se connecter au réseau Wi-Fi du point d'accès</li>
                                <li>Vérifier que l'IP <?= $qr_info['ip_detectee'] ?> est accessible</li>
                            </ul>

                            <h6>2. Scan du QR Code</h6>
                            <ul class="small">
                                <li>Ouvrir l'appareil photo du téléphone</li>
                                <li>Pointer vers le QR code affiché</li>
                                <li>Cliquer sur le lien qui s'affiche</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>3. Validation de Présence</h6>
                            <ul class="small">
                                <li>Se connecter avec son compte étudiant</li>
                                <li>Vérifier son identité affichée</li>
                                <li>Confirmer sa présence</li>
                            </ul>

                            <h6>4. Création de Compte</h6>
                            <ul class="small">
                                <li>Accéder à: <?= $qr_info['ip_detectee'] ?>/attendance_system/inscription_etudiant.php</li>
                                <li>Utiliser matricule et email officiels</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test de connectivité -->
            <div class="card mt-4 no-print">
                <div class="card-header">
                    <i class="fas fa-network-wired"></i> Test de Connectivité
                </div>
                <div class="card-body">
                    <p><strong>Test depuis un téléphone connecté au Wi-Fi:</strong></p>
                    <div class="list-group">
                        <a href="<?= $qr_info['url'] ?>" class="list-group-item list-group-item-action" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Tester l'URL du QR Code
                        </a>
                        <a href="http://<?= $qr_info['ip_detectee'] ?>/attendance_system/" class="list-group-item list-group-item-action" target="_blank">
                            <i class="fas fa-home"></i> Accéder à l'accueil du système
                        </a>
                        <a href="http://<?= $qr_info['ip_detectee'] ?>/attendance_system/inscription_etudiant.php" class="list-group-item list-group-item-action" target="_blank">
                            <i class="fas fa-user-plus"></i> Page d'inscription étudiant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Countdown timer
function updateCountdown() {
    const expiration = new Date('<?= $qr_info['expiration'] ?>').getTime();
    const now = new Date().getTime();
    const timeLeft = expiration - now;
    
    if (timeLeft > 0) {
        const hours = Math.floor(timeLeft / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
        
        document.getElementById('countdown').innerHTML = 
            `Temps restant: ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        // Changer la couleur selon le temps restant
        const countdownElement = document.getElementById('countdown');
        if (timeLeft < 300000) { // Moins de 5 minutes
            countdownElement.style.color = '#dc3545';
        } else if (timeLeft < 600000) { // Moins de 10 minutes
            countdownElement.style.color = '#fd7e14';
        } else {
            countdownElement.style.color = '#28a745';
        }
    } else {
        document.getElementById('countdown').innerHTML = 'QR Code EXPIRÉ';
        document.getElementById('countdown').style.color = '#dc3545';
    }
}

// Mise à jour chaque seconde
updateCountdown();
setInterval(updateCountdown, 1000);

// Auto-refresh toutes les 30 secondes pour les nouvelles sessions
setTimeout(() => {
    location.reload();
}, 30000);
</script>

<?php include 'includes/footer.php'; ?>