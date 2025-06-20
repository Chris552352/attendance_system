<?php
/**
 * API pour récupérer la liste des étudiants par cours
 * Utilisé par la page de présence via AJAX
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
if (!est_connecte()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Récupérer l'ID du cours
$cours_id = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if ($cours_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de cours invalide']);
    exit;
}

// Récupérer la liste des étudiants pour ce cours
$etudiants = db_query("
    SELECT e.id, e.nom, e.prenom, e.matricule, 
    COALESCE(p.est_present, NULL) as est_present
    FROM etudiants e
    JOIN inscriptions i ON e.id = i.etudiant_id
    LEFT JOIN presences p ON e.id = p.etudiant_id AND p.cours_id = ? AND p.date = ?
    WHERE i.cours_id = ?
    ORDER BY e.nom, e.prenom
", [$cours_id, $date, $cours_id]);

// Retourner les résultats au format JSON
header('Content-Type: application/json');
echo json_encode($etudiants);
?>
