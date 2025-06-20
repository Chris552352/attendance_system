<?php
/**
 * Fichier de fonctions utilitaires
 * Ce fichier contient des fonctions utilisées dans l'ensemble de l'application
 */

// S'assurer que la session est démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool True si connecté, sinon false
 */
function est_connecte() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirige l'utilisateur vers une page donnée
 * 
 * @param string $page La page de destination
 */
function rediriger($page) {
    header("Location: $page");
    exit;
}

/**
 * Sécurise les données entrées par l'utilisateur
 * 
 * @param string $data Les données à sécuriser
 * @return string Les données sécurisées
 */
function securiser($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function alerte($message, $type = 'info') {
    $_SESSION['alerte'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Affiche les messages d'alerte et les supprime ensuite
 */
function afficher_alertes() {
    if (isset($_SESSION['alerte'])) {
        echo '<div class="alert alert-' . $_SESSION['alerte']['type'] . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['alerte']['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>';
        echo '</div>';
        unset($_SESSION['alerte']);
    }
}

/**
 * Formate la date en français
 * 
 * @param string $date La date à formater (format de base: Y-m-d)
 * @return string La date formatée en français
 */
function formater_date($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    
    $jour_semaine = $jours[date('w', $timestamp)];
    $jour = date('j', $timestamp);
    $mois_chiffre = date('n', $timestamp);
    $mois_nom = $mois[$mois_chiffre - 1];
    $annee = date('Y', $timestamp);
    
    return "$jour_semaine $jour $mois_nom $annee";
}

/**
 * Génère un identifiant unique pour un étudiant
 * 
 * @return string Identifiant étudiant au format 'ETU-YYYYNNNNN'
 */
function generer_id_etudiant() {
    $annee = date('Y');
    $nombre = mt_rand(10000, 99999);
    return "ETU-{$annee}{$nombre}";
}

/**
 * Vérifie si un email existe déjà dans la base de données
 * 
 * @param string $email L'email à vérifier
 * @param string $table La table dans laquelle chercher
 * @param int $id Exclure cet ID de la recherche (pour mise à jour)
 * @return bool True si l'email existe, sinon false
 */
function email_existe($email, $table, $id = null) {
    try {
        if ($id) {
            $result = db_query_single("SELECT id FROM $table WHERE email = ? AND id != ?", [$email, $id]);
        } else {
            $result = db_query_single("SELECT id FROM $table WHERE email = ?", [$email]);
        }
        // Si un résultat est retourné, l'email existe déjà
        return $result !== false;
    } catch (Exception $e) {
        // En cas d'erreur, considérer que l'email n'existe pas
        error_log("Erreur lors de la vérification de l'email: " . $e->getMessage());
        return false;
    }
}

/**
 * Calcule les statistiques de présence pour un étudiant
 * 
 * @param int $etudiant_id L'ID de l'étudiant
 * @return array Les statistiques
 */
function stats_presence_etudiant($etudiant_id) {
    $presences = db_query("SELECT est_present FROM presences WHERE etudiant_id = ?", [$etudiant_id]);
    $total = count($presences);
    $present = 0;
    
    foreach ($presences as $p) {
        if ($p['est_present'] == true || $p['est_present'] == 't' || $p['est_present'] == '1' || $p['est_present'] === true) {
            $present++;
        }
    }
    
    $absent = $total - $present;
    $taux = $total > 0 ? round(($present / $total) * 100) : 0;
    
    return [
        'total' => $total,
        'present' => $present,
        'absent' => $absent,
        'taux' => $taux
    ];
}

/**
 * Calcule les statistiques globales de présence
 * 
 * @return array Les statistiques
 */
function stats_presence_globales() {
    $aujourd_hui = date('Y-m-d');
    
    // Présences aujourd'hui
    $presences_auj = db_query("SELECT COUNT(*) as total FROM presences WHERE date = ? AND est_present = TRUE", [$aujourd_hui]);
    $present_auj = $presences_auj[0]['total'] ?? 0;
    
    // Absences aujourd'hui  
    $absences_auj = db_query("SELECT COUNT(*) as total FROM presences WHERE date = ? AND est_present = FALSE", [$aujourd_hui]);
    $absent_auj = $absences_auj[0]['total'] ?? 0;
    
    // Total étudiants
    $etudiants = db_query("SELECT COUNT(*) as total FROM etudiants");
    $total_etudiants = $etudiants[0]['total'] ?? 0;
    
    // Total cours
    $cours = db_query("SELECT COUNT(*) as total FROM cours");
    $total_cours = $cours[0]['total'] ?? 0;
    
    return [
        'present_auj' => $present_auj,
        'absent_auj' => $absent_auj,
        'total_etudiants' => $total_etudiants,
        'total_cours' => $total_cours
    ];
}

/**
 * Obtient les données pour le graphique de présence
 * 
 * @param int $cours_id L'ID du cours (optionnel)
 * @param string $date_debut Date de début (optionnel)
 * @param string $date_fin Date de fin (optionnel)
 * @return array Les données pour le graphique
 */
function donnees_graphique_presence($cours_id = null, $date_debut = null, $date_fin = null) {
    $params = [];
    $sql = "SELECT date, 
            SUM(CASE WHEN est_present = TRUE THEN 1 ELSE 0 END) as presents, 
            SUM(CASE WHEN est_present = FALSE THEN 1 ELSE 0 END) as absents
            FROM presences";
    
    $conditions = [];
    
    if ($cours_id) {
        $conditions[] = "cours_id = ?";
        $params[] = $cours_id;
    }
    
    if ($date_debut) {
        $conditions[] = "date >= ?";
        $params[] = $date_debut;
    }
    
    if ($date_fin) {
        $conditions[] = "date <= ?";
        $params[] = $date_fin;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " GROUP BY date ORDER BY date";
    
    return db_query($sql, $params);
}
?>
