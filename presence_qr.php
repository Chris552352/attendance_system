<?php
/**
 * Page de validation de présence via QR Code
 */

session_start();
require_once 'config/database.php';
require_once 'config/config_app.php';
require_once __DIR__ . '/includes/seance_mode.php';

$seance_id = intval($_GET['seance']);
$token = $_GET['token'] ?? '';

$error = '';
$success = '';

/**
 * Navigateur téléphone / tablette : message si scan sans appli (évite l’impression « page vide »).
 */
function qr_est_client_mobile(): bool
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile/i', $ua);
}

// Vérifier si les paramètres sont valides
if (!$seance_id || !$token) {
    $error = 'QR Code invalide.';
} else {
    // Vérifier la validité de la séance et du token
    $sql = 'SELECT s.*, ts.mode_marquage AS ts_mode_marquage
            FROM seances s
            LEFT JOIN types_seances ts ON s.type_seance_id = ts.id
            WHERE s.id = ? AND s.token = ? AND s.expiration > ? AND s.active = 1';
    $seance = db_query_single($sql, [$seance_id, $token, date('Y-m-d H:i:s')]);

    if (!$seance) {
        $error = 'QR Code expiré ou invalide.';
    } elseif (!seance_etudiant_marquage_qr_autorise($seance)) {
        $error = 'Cette séance (examen ou contrôle) ne permet pas le pointage en ligne : l’enseignant enregistre les présences sur la feuille d’émargement.';
    }
}

// Gestion de l'authentification et du pointage
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) && $_POST['action'] === 'login' &&
    !$error && !isset($_SESSION['etudiant_id'])
) {
    // Tentative de connexion étudiant (flux QR)
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez saisir votre email et le mot de passe.";
    } else {
        // Vérifier l'existence de l'email dans la table etudiants
        $etudiant = db_query_single("SELECT * FROM etudiants WHERE email = ?", [$email]);
        if (!$etudiant) {
            $error = "Email étudiant inconnu.";
        } elseif ($password !== MOT_DE_PASSE_UNIVERSEL_ETUDIANT) {
            $error = "Mot de passe incorrect.";
        } else {
            $_SESSION['etudiant_id'] = $etudiant['id'];
            $_SESSION['etudiant_prenom'] = $etudiant['prenom'];
            $_SESSION['etudiant_nom'] = $etudiant['nom'];
            $_SESSION['etudiant_email'] = $etudiant['email'];
            $_SESSION['etudiant_display'] = trim($etudiant['prenom'] . ' ' . $etudiant['nom']);
            // Rafraîchir pour afficher la confirmation
            header("Location: presence_qr.php?seance=$seance_id&token=$token");
            exit();
        }
    }
}

// Traitement de la validation de présence
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) && $_POST['action'] === 'confirmer' &&
    !$error && isset($_SESSION['etudiant_id'])
) {
    $etudiant_id = $_SESSION['etudiant_id'];
    
    // Vérifier si l'étudiant a déjà marqué sa présence pour cette séance
    $sql_check = "SELECT id FROM presences WHERE etudiant_id = ? AND seance_id = ? AND date_presence = CURRENT_DATE";
    $presence_existante = db_query_single($sql_check, [$etudiant_id, $seance_id]);
    
    if ($presence_existante) {
        $error = 'Vous avez déjà marqué votre présence pour cette séance.';
    } else {
        // cours_id est obligatoire (FK) : retrouver le cours à partir du libellé de la séance
        $cours_row = db_query_single(
            'SELECT id FROM cours WHERE nom = ? AND enseignant_id = ? LIMIT 1',
            [$seance['nom_cours'], $seance['enseignant_id']]
        );
        if (!$cours_row) {
            $cours_row = db_query_single('SELECT id FROM cours WHERE nom = ? LIMIT 1', [$seance['nom_cours']]);
        }
        if (!$cours_row) {
            $error = 'Impossible d\'enregistrer la présence : le cours associé à cette séance est introuvable. Contactez votre enseignant.';
        } else {
        $sql_insert = 'INSERT INTO presences (etudiant_id, cours_id, seance_id, date_presence, statut, enregistre_par, date_enregistrement) 
                       VALUES (?, ?, ?, CURRENT_DATE, \'present\', ?, NOW())';
        if (db_exec($sql_insert, [$etudiant_id, (int) $cours_row['id'], $seance_id, $seance['enseignant_id']])) {
            $success = 'Votre présence a été enregistrée avec succès !';
            
            // Log de la présence pour debug
            error_log("Présence enregistrée - Étudiant: $etudiant_id, Séance: $seance_id, Date: " . date('Y-m-d H:i:s'));
            
            // Déconnexion automatique après pointage
            session_unset();
            session_destroy();
        } else {
            $error = 'Erreur lors de l\'enregistrement de votre présence.';
            error_log("Erreur enregistrement présence - Étudiant: $etudiant_id, Séance: $seance_id");
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
                <strong><?php echo htmlspecialchars($_SESSION['etudiant_display'] ?? trim(($_SESSION['etudiant_prenom'] ?? '') . ' ' . ($_SESSION['etudiant_nom'] ?? ''))); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($_SESSION['etudiant_email'] ?? ''); ?></small>
            </p>
        </div>
    </div>
    <form method="POST" class="mt-3">
        <input type="hidden" name="action" value="confirmer">
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
    <!-- Étudiant non connecté : formulaire de connexion QR -->
    <?php if (qr_est_client_mobile() && !$error && isset($seance)): ?>
    <div class="alert alert-secondary border">
        <p class="mb-1"><strong><i class="fas fa-mobile-alt"></i> Scan sans application</strong></p>
        <p class="small mb-0">Vous pouvez confirmer votre présence sur cette page avec votre <strong>email étudiant</strong> ci-dessous. Avec l’application mobile « Présence étudiant », le scan du même QR est plus rapide une fois connecté.</p>
    </div>
    <?php endif; ?>
    <div class="alert alert-warning">
        <i class="fas fa-sign-in-alt"></i> 
        <strong>Connexion requise</strong><br>
        Connectez-vous avec votre email étudiant et le mot de passe par défaut (<?php echo htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT); ?>), le même qu’à l’inscription.
    </div>
    <?php if (isset($seance)): ?>
        <div class="alert alert-info">
            <p><strong>Cours/Séance :</strong> <?php echo htmlspecialchars($seance['nom_cours']); ?></p>
            <p><strong>Date :</strong> <?php echo date('d/m/Y à H:i'); ?></p>
        </div>
    <?php endif; ?>
    <form method="POST" class="mt-4">
        <input type="hidden" name="action" value="login">
        <div class="mb-3">
            <label for="email" class="form-label">Email étudiant</label>
            <input type="email" class="form-control" name="email" id="email" required placeholder="prenom.nom@...">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" name="password" id="password" required placeholder="<?php echo htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT); ?>">
            <small class="form-text text-muted">Mot de passe par défaut : <?php echo htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT); ?> (identique à la connexion / inscription)</small>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt"></i> Se connecter et confirmer la présence
            </button>
        </div>
    </form>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>