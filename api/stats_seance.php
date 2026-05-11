<?php
/**
 * API pour récupérer les statistiques en temps réel d'une séance
 */

require_once __DIR__ . '/api_bootstrap.php';

$seance_id = (int) ($_GET['seance_id'] ?? 0);

if ($seance_id <= 0) {
    api_json_response(['success' => false, 'message' => 'ID de séance invalide.'], 400);
}

// Vérifier que la séance appartient à l'enseignant connecté
$seance = db_query_single("
    SELECT * FROM seances 
    WHERE id = ? AND enseignant_id = ?
", [$seance_id, $_SESSION['user_id'] ?? 0]);

if (!$seance) {
    api_json_response(['success' => false, 'message' => 'Séance introuvable.'], 404);
}

// Récupérer les statistiques
$cours = db_query_single("SELECT id FROM cours WHERE nom = ? LIMIT 1", [$seance['nom_cours']]);
if (!$cours) {
    api_json_response(['success' => false, 'message' => 'Cours introuvable.'], 404);
}

$stats = db_query_single("
    SELECT 
        COUNT(DISTINCT i.etudiant_id) as total_etudiants,
        COUNT(DISTINCT CASE WHEN p.statut = 'present' THEN p.etudiant_id END) as presents,
        COUNT(DISTINCT CASE WHEN p.statut = 'retard' THEN p.etudiant_id END) as retards,
        COUNT(DISTINCT CASE WHEN p.statut = 'absent' OR p.statut IS NULL THEN p.etudiant_id END) as absents,
        ROUND(
            (COUNT(DISTINCT CASE WHEN p.statut IN ('present', 'retard') THEN p.etudiant_id END) * 100.0) / 
            COUNT(DISTINCT i.etudiant_id), 1
        ) as taux_presence
    FROM inscriptions i
    LEFT JOIN presences p ON i.etudiant_id = p.etudiant_id AND p.seance_id = ? AND p.date_presence = CURDATE()
    WHERE i.cours_id = ?
", [$seance_id, $cours['id']]);

api_json_response([
    'success' => true,
    'data' => [
        'seance_id' => $seance_id,
        'total' => (int) $stats['total_etudiants'],
        'presents' => (int) $stats['presents'],
        'retards' => (int) $stats['retards'],
        'absents' => (int) $stats['absents'],
        'taux' => (float) $stats['taux_presence'],
        'timestamp' => date('Y-m-d H:i:s')
    ]
]);
