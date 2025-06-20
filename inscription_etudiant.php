<?php
/**
 * Page d'inscription pour les étudiants
 * Permet aux étudiants de créer leur compte en utilisant leur matricule
 */

session_start();
require_once 'config/database.php';

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = trim($_POST['matricule'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
    
    // Validation
    if (empty($matricule) || empty($email) || empty($mot_de_passe)) {
        $message = "Tous les champs sont obligatoires.";
        $type_message = 'error';
    } elseif ($mot_de_passe !== $confirmer_mot_de_passe) {
        $message = "Les mots de passe ne correspondent pas.";
        $type_message = 'error';
    } elseif (strlen($mot_de_passe) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
        $type_message = 'error';
    } else {
        try {
            // Vérifier si l'étudiant existe dans la base
            $etudiant = db_query_single(
                "SELECT * FROM etudiants WHERE matricule = ? AND email = ?",
                [$matricule, $email]
            );
            
            if (!$etudiant) {
                $message = "Matricule ou email non trouvé. Contactez votre enseignant.";
                $type_message = 'error';
            } else {
                // Vérifier si le compte existe déjà
                $compte_existant = db_query_single(
                    "SELECT id FROM comptes_etudiants WHERE etudiant_id = ?",
                    [$etudiant['id']]
                );
                
                if ($compte_existant) {
                    $message = "Un compte existe déjà pour ce matricule.";
                    $type_message = 'error';
                } else {
                    // Créer le compte
                    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                    
                    $result = db_exec(
                        "INSERT INTO comptes_etudiants (etudiant_id, email, mot_de_passe) VALUES (?, ?, ?)",
                        [$etudiant['id'], $email, $mot_de_passe_hash]
                    );
                    
                    if ($result) {
                        $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                        $type_message = 'success';
                        
                        // Redirection après 3 secondes
                        header("refresh:3;url=login_etudiant.php");
                    } else {
                        $message = "Erreur lors de la création du compte. Réessayez.";
                        $type_message = 'error';
                    }
                }
            }
        } catch (Exception $e) {
            $message = "Erreur système. Contactez l'administrateur.";
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
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> Inscription Étudiant</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $type_message === 'success' ? 'success' : 'danger'; ?>" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
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
                            <div class="form-text">L'email associé à votre matricule</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                                   placeholder="Minimum 6 caractères" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmer_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirmer_mot_de_passe" 
                                   name="confirmer_mot_de_passe" placeholder="Répétez le mot de passe" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Créer mon compte
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
                            <i class="fas fa-arrow-left"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle"></i> Instructions</h6>
                    <ul class="small text-muted">
                        <li>Utilisez votre matricule étudiant officiel</li>
                        <li>Utilisez l'email fourni par votre établissement</li>
                        <li>Choisissez un mot de passe sécurisé</li>
                        <li>Contactez votre enseignant en cas de problème</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>