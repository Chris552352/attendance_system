<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (!est_connecte()) {
    rediriger('login.php');
}

$seance_id = intval($_POST['seance_id'] ?? 0);
if (!$seance_id) {
    alerte("Séance invalide.", "danger");
    rediriger('generer_qr.php');
}

// Récupérer la séance
$seance = db_query_single("SELECT * FROM seances WHERE id = ?", [$seance_id]);
if (!$seance) {
    alerte("Séance introuvable.", "danger");
    rediriger('generer_qr.php');
}

// Vérifier droits : admin ou enseignant de la séance
if (!est_admin() && $seance['enseignant_id'] != $_SESSION['user_id']) {
    alerte("Vous n'avez pas les droits pour clôturer cette séance.", "danger");
    rediriger('afficher_qr.php?seance=' . $seance_id);
}

// Vérifier que la séance est expirée
if (strtotime($seance['expiration']) > time()) {
    alerte("La séance n'est pas encore expirée.", "warning");
    rediriger('afficher_qr.php?seance=' . $seance_id);
}

// Trouver l'ID du cours à partir du nom_cours de la séance
$cours = db_query_single("SELECT id FROM cours WHERE nom = ?", [$seance['nom_cours']]);
if (!$cours) {
    alerte("Cours introuvable pour cette séance.", "danger");
    rediriger('afficher_qr.php?seance=' . $seance_id);
}
$cours_id = $cours['id'];

// Récupérer tous les étudiants inscrits à ce cours
$etudiants = db_query("SELECT e.id FROM etudiants e JOIN inscriptions i ON e.id = i.etudiant_id WHERE i.cours_id = ?", [$cours_id]);

// Utiliser la date de création de la séance comme date de présence
$date_presence = isset($seance['date_creation']) ? date('Y-m-d', strtotime($seance['date_creation'])) : date('Y-m-d');

// Pour chaque étudiant, vérifier s'il a une présence pour cette séance
$absents = 0;
foreach ($etudiants as $etudiant) {
    $presence = db_query_single("SELECT * FROM presences WHERE seance_id = ? AND etudiant_id = ?", [$seance_id, $etudiant['id']]);
    if (!$presence) {
        // Marquer comme absent
        db_exec("INSERT INTO presences (seance_id, etudiant_id, cours_id, date_presence, statut, justifie, enregistre_par) VALUES (?, ?, ?, ?, 'absent', 0, ?)", [
            $seance_id,
            $etudiant['id'],
            $cours_id,
            $date_presence,
            $_SESSION['user_id']
        ]);
        $absents++;
    }
}

alerte("Séance clôturée. $absents étudiant(s) marqué(s) absent(s).", "success");
rediriger('afficher_qr.php?seance=' . $seance_id); 