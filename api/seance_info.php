<?php
/**
 * API pour récupérer les informations d'une séance (app mobile / contrôle QR).
 */

require_once __DIR__ . '/api_bootstrap.php';
require_once dirname(__DIR__) . '/includes/seance_mode.php';

$seance_id = (int) ($_GET['seance_id'] ?? 0);

if ($seance_id <= 0) {
    api_json_response(['success' => false, 'message' => 'ID de séance invalide.'], 400);
}

$seance = db_query_single(
    'SELECT s.*,
            ts.nom AS type_seance,
            ts.mode_marquage AS ts_mode_marquage,
            ts.code AS type_code,
            ts.couleur,
            sa.nom AS salle_nom,
            sa.capacite AS salle_capacite
     FROM seances s
     LEFT JOIN types_seances ts ON s.type_seance_id = ts.id
     LEFT JOIN salles sa ON s.salle_id = sa.id
     WHERE s.id = ?
       AND s.active = 1
       AND (s.statut_seance IS NULL OR s.statut_seance IN (\'planifiee\', \'en_cours\'))',
    [$seance_id]
);

if (!$seance) {
    api_json_response(['success' => false, 'message' => 'Séance introuvable ou inactive.'], 404);
}

$mode_eff = seance_effective_mode_marquage($seance);
$student_qr = seance_etudiant_marquage_qr_autorise($seance);

if ($student_qr && !empty($seance['token'])) {
    if (($seance['expiration'] ?? '') < date('Y-m-d H:i:s')) {
        api_json_response(['success' => false, 'message' => 'QR code expiré.'], 400);
    }
}

api_json_response([
    'success' => true,
    'data' => [
        'id' => (int) $seance['id'],
        'nom_cours' => $seance['nom_cours'],
        'type_seance' => $seance['type_seance'] ?? '',
        'type_code' => $seance['type_code'] ?? '',
        'salle_nom' => $seance['salle_nom'] ?? '',
        'mode_marquage' => $mode_eff,
        'student_qr_allowed' => $student_qr,
        'token' => $seance['token'] ?? '',
        'heure_debut' => $seance['heure_debut'] ?? null,
        'heure_fin' => $seance['heure_fin'] ?? null,
        'statut_seance' => $seance['statut_seance'] ?? 'en_cours',
        'couleur' => $seance['couleur'] ?? '#1976d2',
        'salle_capacite' => (int) ($seance['salle_capacite'] ?? 0),
    ],
]);
