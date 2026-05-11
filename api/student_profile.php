<?php
/**
 * Profil étudiant (app mobile) : lecture.
 */

require_once __DIR__ . '/api_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$body = api_read_json_body();
$auth_token = trim((string) ($body['auth_token'] ?? ''));
if ($auth_token === '') {
    api_json_response(['success' => false, 'message' => 'auth_token requis.'], 400);
}

$etudiant_id = api_verify_auth_token($auth_token);
if ($etudiant_id === null) {
    api_json_response(['success' => false, 'message' => 'Session expirée. Reconnectez-vous.'], 401);
}

$etudiant = db_query_single(
    'SELECT id, matricule, nom, prenom, email, date_inscription, classe_id FROM etudiants WHERE id = ?',
    [$etudiant_id]
);

if (!$etudiant) {
    api_json_response(['success' => false, 'message' => 'Étudiant introuvable.'], 404);
}

$cid = isset($etudiant['classe_id']) ? (int) $etudiant['classe_id'] : 0;

api_json_response([
    'success' => true,
    'data' => [
        'etudiant' => [
            'id' => (int) $etudiant['id'],
            'matricule' => (string) $etudiant['matricule'],
            'nom' => (string) $etudiant['nom'],
            'prenom' => (string) $etudiant['prenom'],
            'email' => (string) $etudiant['email'],
            'date_inscription' => $etudiant['date_inscription'] !== null ? (string) $etudiant['date_inscription'] : null,
            'classe' => api_classe_etudiant_payload($cid > 0 ? $cid : null),
        ],
    ],
]);

