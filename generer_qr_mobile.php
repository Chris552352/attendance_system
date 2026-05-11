<?php
/**
 * Génération de QR codes optimisés pour scan mobile
 * Détection automatique de l'IP locale et génération d'URLs accessibles
 */

require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'config_reseau.php';

// Vérifier l'authentification
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cours_id = (int)$_POST['cours_id'];
    $duree = (int)($_POST['duree'] ?? 30); // Durée en minutes
    
    if ($cours_id > 0) {
        // Récupérer les informations du cours
        $cours = db_query_single("SELECT * FROM cours WHERE id = ?", [$cours_id]);
        
        if ($cours) {
            // Générer un token unique
            $token = bin2hex(random_bytes(16));
            $expiration = date('Y-m-d H:i:s', time() + ($duree * 60));
            
            // Insérer la séance dans la base
            $result = db_exec(
                "INSERT INTO seances (nom_cours, enseignant_id, token, expiration) VALUES (?, ?, ?, ?)",
                [$cours['nom'], $_SESSION['user_id'], $token, $expiration]
            );
            
            if ($result) {
                $seance_id = db_last_insert_id();
                
                // Générer l'URL avec l'IP détectée automatiquement
                $url_presence = URL_BASE_QR . "/attendance_system/presence.php?seance=$seance_id&token=$token";
                
                // Informations pour affichage
                $qr_info = [
                    'url' => $url_presence,
                    'cours' => $cours['nom'],
                    'token' => $token,
                    'expiration' => $expiration,
                    'ip_detectee' => IP_POINT_ACCES,
                    'duree' => $duree
                ];
                
                // Stocker en session pour affichage
                $_SESSION['qr_generated'] = $qr_info;
                
                header('Location: afficher_qr.php');
                exit;
            } else {
                $erreur = "Erreur lors de la création de la séance.";
            }
        } else {
            $erreur = "Erreur : la matière ou le cours demandé n'existe pas.";
        }
    } else {
        $erreur = "Veuillez sélectionner un cours.";
    }
}

// Récupérer les cours de l'enseignant
if (!isset($_SESSION['role'])) { $_SESSION['role'] = ''; }
if ($_SESSION['role'] === 'admin') {
    $cours_enseignant = db_query("SELECT * FROM cours ORDER BY nom");
} else {
$cours_enseignant = db_query(
    "SELECT * FROM cours WHERE enseignant_id = ? ORDER BY nom",
    [$_SESSION['user_id']]
);
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-qrcode"></i> Générer QR Code pour Présence Mobile
                    </h4>
                </div>
                <div class="card-body">
                    
                    <!-- Informations réseau -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-wifi"></i> Configuration Réseau Détectée</h6>
                        <p class="mb-1"><strong>IP du serveur:</strong> <?= IP_POINT_ACCES ?></p>
                        <p class="mb-1"><strong>URL de base:</strong> <?= URL_BASE_QR ?></p>
                        <p class="mb-0">
                            <small>Les étudiants doivent être connectés au même réseau Wi-Fi pour scanner le QR code.</small>
                        </p>
                    </div>

                    <?php if (isset($erreur) && !empty($erreur)): ?>
                        <div class="alert alert-danger mt-4">
                            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erreur) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="cours_id" class="form-label">
                                <i class="fas fa-book"></i> Sélectionner le cours
                            </label>
                            <select class="form-select" id="cours_id" name="cours_id" required>
                                <option value="">-- Choisir un cours --</option>
                                <?php foreach ($cours_enseignant as $cours): ?>
                                    <option value="<?= $cours['id'] ?>">
                                        <?= htmlspecialchars($cours['code'] . ' - ' . $cours['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="duree" class="form-label">
                                <i class="fas fa-clock"></i> Durée de validité du QR code
                            </label>
                            <select class="form-select" id="duree" name="duree">
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">1 heure</option>
                                <option value="90">1h30</option>
                                <option value="120">2 heures</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-qrcode"></i> Générer le QR Code
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- Instructions pour configuration point d'accès -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-info-circle"></i> Instructions Point d'Accès Wi-Fi
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Windows 10/11:</h6>
                                    <ol class="small">
                                        <li>Paramètres → Réseau et Internet</li>
                                        <li>Point d'accès mobile → Activer</li>
                                        <li>IP généralement: 192.168.137.1</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h6>Réseau existant:</h6>
                                    <ol class="small">
                                        <li>Connecter PC au routeur Wi-Fi</li>
                                        <li>Connecter téléphones au même réseau</li>
                                        <li>IP automatiquement détectée</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des QR codes récents -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-history"></i> QR Codes Récents
                </div>
                <div class="card-body">
                    <?php
                    $qr_recents = db_query("
                        SELECT s.*, c.nom as cours_nom,
                        CASE 
                            WHEN s.expiration > NOW() THEN 'Actif' 
                            ELSE 'Expiré' 
                        END as statut
                        FROM seances s
                        LEFT JOIN cours c ON s.nom_cours = c.nom
                        WHERE s.enseignant_id = ?
                        ORDER BY s.date_creation DESC
                        LIMIT 5
                    ", [$_SESSION['user_id']]);
                    ?>
                    
                    <?php if (empty($qr_recents)): ?>
                        <p class="text-muted">Aucun QR code généré récemment.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cours</th>
                                        <th>Créé le</th>
                                        <th>Expire le</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($qr_recents as $qr): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($qr['nom_cours']) ?></td>
                                            <td><?= date('d/m H:i', strtotime($qr['date_creation'])) ?></td>
                                            <td><?= date('d/m H:i', strtotime($qr['expiration'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $qr['statut'] === 'Actif' ? 'success' : 'secondary' ?>">
                                                    <?= $qr['statut'] ?>
                                                </span>
                                            </td>
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

<?php include 'includes/footer.php'; ?>