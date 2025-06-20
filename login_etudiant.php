<?php
/**
 * Page de connexion pour les étudiants
 */

session_start();
require_once 'config/database.php';

// Si l'étudiant est déjà connecté, rediriger vers tableau de bord
if (isset($_SESSION['etudiant_id'])) {
    header('Location: dashboard_etudiant.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    
    if (!empty($email) && !empty($mot_de_passe)) {
        // Vérifier les identifiants de l'étudiant
        $sql = "SELECT id, nom, prenom, email, mot_de_passe FROM etudiants WHERE email = ?";
        $etudiant = db_query_single($sql, [$email]);
        
        if ($etudiant && password_verify($mot_de_passe, $etudiant['mot_de_passe'])) {
            $_SESSION['etudiant_id'] = $etudiant['id'];
            $_SESSION['etudiant_nom'] = $etudiant['prenom'] . ' ' . $etudiant['nom'];
            $_SESSION['etudiant_email'] = $etudiant['email'];
            
            header('Location: dashboard_etudiant.php');
            exit();
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
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
                            Utilisez votre matricule comme mot de passe par défaut
                        </small>
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