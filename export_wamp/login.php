<?php
session_start();

// Inclure les fichiers nécessaires
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Si l'utilisateur est déjà connecté, rediriger vers accueil.php
if (est_connecte()) {
    rediriger('accueil.php');
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = securiser($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation des champs
    $erreurs = [];
    if (empty($email)) {
        $erreurs[] = "L'adresse email est requise.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }
    
    if (empty($password)) {
        $erreurs[] = "Le mot de passe est requis.";
    }
    
    
    if (empty($erreurs)) {
        if (connecter($email, $password)) {
            alerte("Bienvenue, " . $_SESSION['user_nom'] . "!", "success");
            rediriger('accueil.php');
        } else {
            $erreurs[] = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de Gestion de Présence</title>
    
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background-color: #f5f5f5;">
    <div class="login-container">
        <div class="logo-container" style="text-align: center; margin-bottom: 20px;">
            <img src="assets/images/livres-stack-realistic_1284-4735.png" alt="Logo Université de Yaoundé" style="max-width: 120px; height: auto;">
        </div>
        
        <div class="login-title">
            <h2><i class="fas fa-graduation-cap"></i> Système de Gestion de Présence</h2>
            <h4 style="color: #666; margin-top: 5px;"><i class="fas fa-university"></i> Université de Chris</h4>
        </div>
        
        <!--<div class="flag-decoration">
            <span class="flag-star">★</span>
        </div> -->
        
        <?php if (isset($erreurs) && !empty($erreurs)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo $erreur; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="needs-validation" novalidate>
            <div style="margin-bottom: 15px;" class="input-group">
                <label for="email"><i class="fas fa-envelope"></i> Adresse Email:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="entrer mon adresse email" required>
            </div>
            
            <div style="margin-bottom: 15px;" class="input-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de Passe:</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Se Connecter
                </button>
            </div>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <small style="color: #666;">Connectez-vous en utilisant les identifiants fournis par votre administrateur.</small>
        </div>
        
       
    </div>

    <!-- Script personnalisé -->
    <script src="assets/js/script.js"></script>
</body>
</html>
