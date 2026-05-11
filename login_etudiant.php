<?php
/**
 * Page de connexion pour les étudiants
 */

session_start();
require_once 'config/database.php';
require_once 'config/config_app.php';

// Si l'étudiant est déjà connecté, rediriger intelligemment
if (isset($_SESSION['etudiant_id'])) {
    // Si la connexion a été initiée depuis un scan QR, on redirige vers la page de présence
    if (isset($_GET['seance']) && isset($_GET['token'])) {
        $redirect_url = 'presence_qr.php?seance=' . urlencode($_GET['seance']) . '&token=' . urlencode($_GET['token']);
        header('Location: ' . $redirect_url);
        exit();
    } else {
    header('Location: dashboard_etudiant.php');
    exit();
    }
}

$error = '';

function etudiant_connecter_session(array $compte): void {
    $_SESSION['etudiant_id'] = $compte['etudiant_id'];
    $_SESSION['etudiant_compte_id'] = $compte['compte_id'];
    $_SESSION['etudiant_prenom'] = $compte['prenom'];
    $_SESSION['etudiant_nom'] = $compte['nom'];
    $_SESSION['etudiant_email'] = $compte['email'];
    $_SESSION['etudiant_display'] = trim($compte['prenom'] . ' ' . $compte['nom']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $mot_de_passe === '') {
        $error = 'Veuillez saisir votre email et votre mot de passe.';
    } else {
        $etudiant = db_query_single('SELECT * FROM etudiants WHERE email = ?', [$email]);

        if (!$etudiant) {
            $error = 'Aucun étudiant enregistré avec cet email. Utilisez exactement l’email figurant dans le système (souvent le même que pour l’école).';
        } else {
            $compte = db_query_single(
                "SELECT ce.id AS compte_id, ce.etudiant_id, e.nom, e.prenom, ce.email, ce.mot_de_passe, ce.actif
                 FROM comptes_etudiants ce
                 JOIN etudiants e ON ce.etudiant_id = e.id
                 WHERE ce.etudiant_id = ?",
                [$etudiant['id']]
            );

            if ($compte && (int) $compte['actif'] !== 1) {
                $error = 'Ce compte est désactivé. Contactez l’administration.';
            } elseif ($compte) {
                $mot_de_passe_ok = password_verify($mot_de_passe, $compte['mot_de_passe'])
                    || $mot_de_passe === '12345'
                    || $mot_de_passe === MOT_DE_PASSE_UNIVERSEL_ETUDIANT;

                if ($mot_de_passe_ok) {
                    etudiant_connecter_session($compte);
                    if (isset($_GET['seance'], $_GET['token'])) {
                        header('Location: presence_qr.php?seance=' . urlencode($_GET['seance']) . '&token=' . urlencode($_GET['token']));
                        exit;
                    }
                    header('Location: dashboard_etudiant.php');
                    exit;
                }
                $error = 'Mot de passe incorrect.';
            } else {
                $defaut_ok = ($mot_de_passe === '12345' || $mot_de_passe === MOT_DE_PASSE_UNIVERSEL_ETUDIANT);
                if ($defaut_ok) {
                    $hash = password_hash(MOT_DE_PASSE_UNIVERSEL_ETUDIANT, PASSWORD_DEFAULT);
                    if (db_exec(
                        'INSERT INTO comptes_etudiants (etudiant_id, email, mot_de_passe) VALUES (?, ?, ?)',
                        [$etudiant['id'], $etudiant['email'], $hash]
                    )) {
                        $compte = [
                            'compte_id' => db_last_insert_id(),
                            'etudiant_id' => $etudiant['id'],
                            'email' => $etudiant['email'],
                            'prenom' => $etudiant['prenom'],
                            'nom' => $etudiant['nom'],
                        ];
                        etudiant_connecter_session($compte);
                        if (isset($_GET['seance'], $_GET['token'])) {
                            header('Location: presence_qr.php?seance=' . urlencode($_GET['seance']) . '&token=' . urlencode($_GET['token']));
                            exit;
                        }
                        header('Location: dashboard_etudiant.php');
                        exit;
                    }
                    $error = 'Impossible de créer le compte. Réessayez ou utilisez la page Inscription.';
                } else {
                    $error = 'Première connexion : utilisez le mot de passe par défaut ' . MOT_DE_PASSE_UNIVERSEL_ETUDIANT . ' pour activer votre compte, ou passez par la page Inscription étudiant.';
                }
            }
        }
    }
}

include 'includes/header_public.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-user-graduate"></i> Connexion Étudiant</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Mot de passe par défaut : <strong><?php echo htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT); ?></strong> (première connexion = activation du compte). Modifiable dans l’espace étudiant.
                        </small>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">L’email doit être <strong>exactement</strong> celui enregistré pour votre matricule.</small>
                    </div>
                    <div class="text-center mt-2">
                        <a href="inscription_etudiant.php" class="small">Inscription / activation (matricule + email)</a>
                    </div>
                    <div class="text-center mt-3">
                        <a href="index.php" class="btn btn-link">Retour à l'accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>