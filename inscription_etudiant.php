<?php
/**
 * Inscription étudiant : active le compte avec le mot de passe par défaut (12345).
 */

session_start();
require_once 'config/database.php';
require_once 'config/config_app.php';

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = trim($_POST['matricule'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($matricule) || empty($email)) {
        $message = 'Le matricule et l’email sont obligatoires.';
        $type_message = 'error';
    } else {
        try {
            $etudiant = db_query_single(
                'SELECT * FROM etudiants WHERE matricule = ? AND email = ?',
                [$matricule, $email]
            );

            if (!$etudiant) {
                $message = 'Matricule ou email non trouvé. Contactez votre enseignant.';
                $type_message = 'error';
            } else {
                $compte_existant = db_query_single(
                    'SELECT id FROM comptes_etudiants WHERE etudiant_id = ?',
                    [$etudiant['id']]
                );

                if ($compte_existant) {
                    $message = 'Un compte existe déjà pour ce matricule.';
                    $type_message = 'error';
                } else {
                    $mot_de_passe_hash = password_hash(MOT_DE_PASSE_UNIVERSEL_ETUDIANT, PASSWORD_DEFAULT);

                    $result = db_exec(
                        'INSERT INTO comptes_etudiants (etudiant_id, email, mot_de_passe) VALUES (?, ?, ?)',
                        [$etudiant['id'], $email, $mot_de_passe_hash]
                    );

                    if ($result) {
                        $message = 'Compte créé avec succès. Mot de passe par défaut : <strong>' . htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT) . '</strong> (connexion et scan QR). Vous pourrez le modifier dans votre espace.';
                        $type_message = 'success';
                        header('refresh:4;url=login_etudiant.php');
                    } else {
                        $message = 'Erreur lors de la création du compte. Réessayez.';
                        $type_message = 'error';
                    }
                }
            }
        } catch (Exception $e) {
            $message = 'Erreur système. Contactez l’administrateur.';
            $type_message = 'error';
        }
    }
}

include 'includes/header_public.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> Inscription étudiant</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $type_message === 'success' ? 'success' : 'danger'; ?>" role="alert">
                            <?php echo $type_message === 'success' ? $message : htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info small mb-4">
                        <strong>Mot de passe :</strong> tous les comptes utilisent le mot de passe par défaut
                        <code><?php echo htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT); ?></code>
                        pour la connexion et le scan des QR codes de présence. Vous pourrez le changer après connexion.
                    </div>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="matricule" class="form-label">Matricule</label>
                            <input type="text" class="form-control" id="matricule" name="matricule"
                                   placeholder="Ex: CMD-UDS-23IUT1045"
                                   value="<?php echo htmlspecialchars($_POST['matricule'] ?? ''); ?>" required>
                            <div class="form-text">Votre matricule étudiant officiel</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="votre.email@example.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            <div class="form-text">L’email associé à votre matricule dans le système</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Activer mon compte
                            </button>
                        </div>
                    </form>

                    <hr>
                    <div class="text-center">
                        <p class="mb-2">Vous avez déjà un compte ?</p>
                        <a href="login_etudiant.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <a href="index.php" class="text-muted">
                            <i class="fas fa-arrow-left"></i> Retour à l’accueil
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle"></i> Instructions</h6>
                    <ul class="small text-muted">
                        <li>Utilisez votre matricule et l’email enregistrés par l’établissement</li>
                        <li>Mot de passe initial : <strong><?php echo htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT); ?></strong></li>
                        <li>En cas de problème, contactez votre enseignant ou l’administration</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
