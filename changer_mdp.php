<?php
// Page de changement de mot de passe
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $ancien_mdp = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';
    
    // Validation basique
    $erreurs = [];
    
    if (empty($ancien_mdp)) {
        $erreurs[] = "L'ancien mot de passe est obligatoire.";
    }
    
    if (empty($nouveau_mdp)) {
        $erreurs[] = "Le nouveau mot de passe est obligatoire.";
    } elseif (strlen($nouveau_mdp) < 8) {
        $erreurs[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    }
    
    if ($nouveau_mdp !== $confirmer_mdp) {
        $erreurs[] = "La confirmation du mot de passe ne correspond pas.";
    }
    
    // Vérifier l'ancien mot de passe
    if (empty($erreurs)) {
        try {
            $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['utilisateur_id']]);
            $utilisateur = $stmt->fetch();
            
            if (!$utilisateur || !password_verify($ancien_mdp, $utilisateur['mot_de_passe'])) {
                $erreurs[] = "L'ancien mot de passe est incorrect.";
            }
        } catch (PDOException $e) {
            $erreurs[] = "Erreur lors de la vérification du mot de passe: " . $e->getMessage();
        }
    }
    
    // Si pas d'erreurs, mettre à jour le mot de passe
    if (empty($erreurs)) {
        try {
            // Hachage du nouveau mot de passe
            $hash_mdp = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                UPDATE utilisateurs 
                SET mot_de_passe = :mot_de_passe
                WHERE id = :id
            ");
            
            $stmt->execute([
                'mot_de_passe' => $hash_mdp,
                'id' => $_SESSION['utilisateur_id']
            ]);
            
            $_SESSION['message_succes'] = "Votre mot de passe a été mis à jour avec succès.";
            
            // Redirection pour éviter la réexécution du code en cas de rafraîchissement
            header('Location: profil.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['message_erreur'] = "Erreur lors de la mise à jour du mot de passe: " . $e->getMessage();
        }
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Changer mon mot de passe</h1>
    <a href="profil.php" class="d-none d-sm-inline-block btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 me-2"></i> Retour au profil
    </a>
</div>

<!-- Formulaire de changement de mot de passe -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Modifier mon mot de passe</h6>
            </div>
            <div class="card-body">
                <?php if (isset($erreurs) && !empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?php echo $erreur; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="ancien_mdp" class="form-label">Ancien mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="ancien_mdp" name="ancien_mdp" required>
                            <div class="invalid-feedback">
                                Veuillez saisir votre ancien mot de passe.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nouveau_mdp" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="nouveau_mdp" name="nouveau_mdp" required minlength="8">
                            <div class="invalid-feedback">
                                Le nouveau mot de passe doit contenir au moins 8 caractères.
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Le mot de passe doit contenir au moins 8 caractères.
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmer_mdp" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                            <input type="password" class="form-control" id="confirmer_mdp" name="confirmer_mdp" required>
                            <div class="invalid-feedback">
                                Veuillez confirmer votre nouveau mot de passe.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Mettre à jour le mot de passe
                        </button>
                        <a href="profil.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-2"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i> Conseils de sécurité</h5>
            <ul class="mb-0">
                <li>Utilisez un mot de passe unique pour chaque site ou application.</li>
                <li>Mélangez lettres, chiffres et caractères spéciaux.</li>
                <li>Évitez d'utiliser des informations personnelles facilement devinables.</li>
                <li>Changez régulièrement votre mot de passe pour plus de sécurité.</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
