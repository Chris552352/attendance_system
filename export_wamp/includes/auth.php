<?php
/**
 * Fonctions d'authentification
 */

require_once 'functions.php';
require_once dirname(__DIR__) . '/config/database.php';

// S'assurer que la session est démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Tente de connecter un utilisateur
 * 
 * @param string $email L'email de l'utilisateur
 * @param string $password Le mot de passe de l'utilisateur
 * @return bool True si connecté avec succès, sinon false
 */
function connecter($email, $password) {
    $user = db_query_single("SELECT * FROM utilisateurs WHERE email = ?", [$email]);
    
    if (!$user) {
        return false;
    }
    
    // Vérification du mot de passe avec password_verify
    if (password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    
    return false;
}

/**
 * Déconnecte l'utilisateur actuel
 */
function deconnecter() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Protège une page nécessitant une authentification
 * Redirige vers la page de connexion si non connecté
 */
function require_auth() {
    if (!est_connecte()) {
        alerte("Veuillez vous connecter pour accéder à cette page.", "warning");
        rediriger("login.php");
    }
    
    // Vérifier si l'utilisateur est un enseignant ajouté par l'administrateur
    if ($_SESSION['user_role'] === 'enseignant') {
        // Vérifier si l'enseignant a été ajouté par l'administrateur
        $enseignant = db_query_single("SELECT * FROM utilisateurs WHERE id = ? AND role = 'enseignant'", [$_SESSION['user_id']]);
        
        if (!$enseignant) {
            alerte("Vous n'avez pas les droits nécessaires pour accéder à cette page.", "danger");
            deconnecter();
            rediriger("login.php");
        }
    }
}

/**
 * Vérifie si l'utilisateur est administrateur
 * 
 * @return bool True si administrateur, sinon false
 */
function est_admin() {
    return est_connecte() && ($_SESSION['user_role'] === 'admin');
}

/**
 * Vérifie si l'utilisateur est enseignant
 * 
 * @return bool True si enseignant, sinon false
 */
function est_enseignant() {
    return est_connecte() && ($_SESSION['user_role'] === 'enseignant');
}

/**
 * Vérifie si l'enseignant est associé à un cours spécifique
 * 
 * @param int $cours_id L'ID du cours à vérifier
 * @return bool True si l'enseignant est associé au cours, sinon false
 */
function est_enseignant_du_cours($cours_id) {
    if (!est_enseignant()) {
        return false;
    }
    
    $cours = db_query_single("SELECT * FROM cours WHERE id = ? AND enseignant_id = ?", [$cours_id, $_SESSION['user_id']]);
    return $cours !== false;
}

/**
 * Protège une page réservée aux administrateurs
 */
function require_admin() {
    require_auth();
    
    if (!est_admin()) {
        alerte("Vous n'avez pas les droits nécessaires pour accéder à cette page.", "danger");
        rediriger("accueil.php");
    }
}

/**
 * Protège une page réservée aux enseignants pour leurs propres cours
 * 
 * @param int $cours_id L'ID du cours à vérifier
 * @param bool $redirect Si true, redirige l'utilisateur, sinon retourne false
 * @return bool True si l'enseignant est autorisé, sinon false (ou redirection)
 */
function require_enseignant_cours($cours_id, $redirect = true) {
    require_auth();
    
    // Les administrateurs ont accès à tous les cours
    if (est_admin()) {
        return true;
    }
    
    // Vérifier si l'enseignant est associé au cours
    if (!est_enseignant_du_cours($cours_id)) {
        if ($redirect) {
            alerte("Vous n'avez pas les droits nécessaires pour accéder à ce cours.", "danger");
            rediriger("mes_cours.php");
        }
        return false;
    }
    
    return true;
}

/**
 * Change le mot de passe d'un utilisateur
 * 
 * @param int $user_id L'ID de l'utilisateur
 * @param string $ancien_mot_de_passe L'ancien mot de passe
 * @param string $nouveau_mot_de_passe Le nouveau mot de passe
 * @return bool True si le changement est réussi, sinon false
 */
function changer_mot_de_passe($user_id, $ancien_mot_de_passe, $nouveau_mot_de_passe) {
    $user = db_query_single("SELECT * FROM utilisateurs WHERE id = ?", [$user_id]);
    
    if (!$user) {
        return false;
    }
    
    // Vérification du mot de passe
    
    if (!password_verify($ancien_mot_de_passe, $user['mot_de_passe'])) {
        return false;
    }
    
    $hash = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
    return db_exec("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?", [$hash, $user_id]);
}
?>
