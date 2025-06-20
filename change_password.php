<?php
/**
 * Page de changement de mot de passe
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Récupérer l'ID de l'utilisateur
$user_id = $_SESSION['user_id'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $ancien_mot_de_passe = $_POST['ancien_mot_de_passe'] ?? '';
    $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
    
    // Validation
    $erreurs = [];
    
    if (empty($ancien_mot_de_passe)) {
        $erreurs[] = "L'ancien mot de passe est obligatoire.";
    }
    
    if (empty($nouveau_mot_de_passe)) {
        $erreurs[] = "Le nouveau mot de passe est obligatoire.";
    } elseif (strlen($nouveau_mot_de_passe) < 6) {
        $erreurs[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    }
    
    if ($nouveau_mot_de_passe !== $confirmer_mot_de_passe) {
        $erreurs[] = "Les nouveaux mots de passe ne correspondent pas.";
    }
    
    // Si pas d'erreurs, changer le mot de passe
    if (empty($erreurs)) {
        if (changer_mot_de_passe($user_id, $ancien_mot_de_passe, $nouveau_mot_de_passe)) {
            alerte("Votre mot de passe a été changé avec succès.", "success");
            rediriger('profil.php');
        } else {
            alerte("Erreur lors du changement de mot de passe. Vérifiez que votre ancien mot de passe est correct.", "danger");
        }
    }
}

// Inclure le header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="fas fa-key"></i> Changer mon Mot de Passe</h1>
        <div>
            <a href="profil.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour au profil
            </a>
        </div>
    </div>

    <?php if (isset($erreurs) && !empty($erreurs)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?php echo $erreur; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-lock"></i> Formulaire de changement de mot de passe</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="ancien_mot_de_passe" class="form-label">Ancien mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="ancien_mot_de_passe" name="ancien_mot_de_passe" required>
                            <div class="invalid-feedback">Veuillez entrer votre ancien mot de passe.</div>
                        </div>

                        <div class="mb-3">
                            <label for="nouveau_mot_de_passe" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" required>
                            <div class="invalid-feedback">Veuillez entrer un nouveau mot de passe.</div>
                            <div class="form-text">Le mot de passe doit contenir au moins 6 caractères.</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmer_mot_de_passe" class="form-label">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" required>
                            <div class="invalid-feedback">Veuillez confirmer votre nouveau mot de passe.</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Changer mon mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

   <!--   <div class="row mt-4">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 text-center mb-3">
                            <img src="https://source.unsplash.com/random/600x300/?cameroon,security" alt="Sécurité" class="img-fluid rounded">
                        </div>
                    
      <div class="col-md-12">
                            <h5><i class="fas fa-info-circle"></i> Conseils de sécurité</h5>
                            <ul>
                                <li>Choisissez un mot de passe fort, contenant des lettres majuscules, minuscules, des chiffres et des caractères spéciaux.</li>
                                <li>Ne réutilisez pas un mot de passe que vous utilisez déjà sur d'autres sites.</li>
                                <li>Ne partagez jamais votre mot de passe avec quelqu'un d'autre.</li>
                                <li>Changez régulièrement votre mot de passe pour améliorer la sécurité de votre compte.</li>
                            </ul>
                        </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
