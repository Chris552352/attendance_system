<?php
// S'assurer que la session est démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/includes/functions.php';

// Déterminer le thème de couleur selon la page actuelle
$page_courante = basename($_SERVER['PHP_SELF']);
$theme_class = '';

switch ($page_courante) {
    case 'etudiants.php':
    case 'ajouter_etudiant.php':
        $theme_class = 'theme-etudiants';
        break;
    case 'enseignants.php':
    case 'ajouter_enseignant.php':
        $theme_class = 'theme-enseignants';
        break;
    case 'cours.php':
    case 'ajouter_cours.php':
        $theme_class = 'theme-cours';
        break;
    case 'presence.php':
        $theme_class = 'theme-presence';
        break;
    case 'rapports.php':
        $theme_class = 'theme-rapports';
        break;
    case 'accueil.php':
        $theme_class = 'theme-dashboard';
        break;
    default:
        $theme_class = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Système de Gestion de Présence - Université Camerounaise</title>
    <!-- FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Nos fichiers CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/teacher-student.css">
    <!-- Sidebar améliorée -->
    <link rel="stylesheet" href="assets/css/sidebar-enhanced.css?v=<?= time() ?>">
</head>
<body<?php if (!empty($theme_class)) echo ' class="' . $theme_class . '"'; ?>>

<?php if (est_connecte()): ?>
    <!-- Barre de navigation moderne -->
    <div class="navbar-modern student-fade-in">
        <div class="navbar-modern-left">
            <a href="accueil.php" class="navbar-modern-logo"><i class="fas fa-university"></i> <span class="d-none d-md-inline">Université</span></a>
        </div>
        <div class="navbar-modern-center">
            <span class="navbar-modern-title">Système de Gestion de Présence</span>
        </div>
        <div class="navbar-modern-right">
            <span class="navbar-user-avatar"><i class="fas fa-user-circle"></i></span>
            <span class="navbar-user-name"> <?php echo htmlspecialchars($_SESSION['user_nom']); ?> </span>
            <a href="profil.php" class="navbar-modern-link" title="Profil"><i class="fas fa-id-badge"></i><span class="d-none d-md-inline"> Profil</span></a>
            <a href="change_password.php" class="navbar-modern-link" title="Changer mot de passe"><i class="fas fa-key"></i></a>
            <a href="logout.php" class="navbar-modern-link logout" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
    <style>
    .navbar-modern {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255,255,255,0.35);
        box-shadow: 0 8px 32px 0 rgba(171,71,188,0.25);
        border-radius: 1.5rem;
        margin: 18px 0 32px 0;
        padding: 0.75rem 2.2rem;
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(171,71,188,0.12);
        animation: fadeInDown 0.9s cubic-bezier(.42,0,.58,1);
    }
    .navbar-modern-logo {
        color: #6a1b9a;
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.18s;
    }
    .navbar-modern-logo:hover { color: #ab47bc; }
    .navbar-modern-center {
        flex: 1;
        text-align: center;
    }
    .navbar-modern-title {
        font-size: 1.18rem;
        font-weight: 500;
        color: #6a1b9a;
        letter-spacing: 1px;
        text-shadow: 0 2px 8px #ab47bc22;
    }
    .navbar-modern-right {
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .navbar-user-avatar {
        font-size: 1.8rem;
        color: #ab47bc;
        margin-right: 0.3rem;
        filter: drop-shadow(0 2px 8px #ab47bc44);
        transition: transform 0.2s;
    }
    .navbar-user-avatar:hover { transform: scale(1.07) rotate(-4deg); }
    .navbar-user-name {
        font-weight: 600;
        color: #333;
        margin-right: 0.7rem;
        font-size: 1.05rem;
        letter-spacing: 0.5px;
    }
    .navbar-modern-link {
        color: #6a1b9a;
        font-size: 1.13rem;
        margin-left: 2px;
        padding: 6px 9px;
        border-radius: 8px;
        text-decoration: none;
        background: none;
        transition: background 0.15s, color 0.15s, box-shadow 0.18s;
        position: relative;
    }
    .navbar-modern-link:hover, .navbar-modern-link.logout:hover {
        background: #f3e5f5;
        color: #ab47bc;
        box-shadow: 0 2px 12px #ab47bc22;
    }
    .navbar-modern-link.logout { color: #b71c1c; }
    .navbar-modern-link.logout:hover { background: #ffebee; color: #e53935; }
    @media (max-width: 768px) {
        .navbar-modern { flex-direction: column; align-items: stretch; padding: 0.7rem 0.7rem 0.5rem 0.7rem; }
        .navbar-modern-center { text-align: left; margin: 0.4rem 0; }
        .navbar-modern-title { font-size: 1.01rem; }
        .navbar-user-name { display: none; }
        .navbar-modern-logo span { display: none; }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-32px); }
        to { opacity: 1; transform: translateY(0); }
    }
    </style>
<?php endif; ?>

<?php if (est_connecte()): ?>
    
    <div class="container">
        <!-- Menu latéral -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Contenu principal  -->
        <main class="content">
            <?php afficher_alertes(); ?>
            
<?php else: ?>
    <!-- Contenu sans menu latéral (page de login) -->
    <div class="container">
        <main class="content-full">
            <?php afficher_alertes(); ?>
            
<?php endif; ?>
<?php
// Le contenu spécifique de la page sera inséré ici
?>

