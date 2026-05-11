<?php
/**
 * Enregistrement présence depuis l'app mobile (séance + token QR + jeton étudiant).
 */

require_once __DIR__ . '/api_bootstrap.php';
require_once dirname(__DIR__) . '/includes/seance_mode.php';
require_once dirname(__DIR__) . '/includes/ensure_compte_device_schema.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$body = api_read_json_body();
$seance_id = (int) ($body['seance_id'] ?? $body['seance'] ?? $body['session'] ?? 0);
$qr_token = trim((string) ($body['token'] ?? ''));
$auth_token = trim((string) ($body['auth_token'] ?? ''));
$device_id = trim((string) ($body['device_id'] ?? ''));

if ($seance_id <= 0 || $qr_token === '' || $auth_token === '') {
    api_json_response([
        'success' => false,
        'message' => 'Paramètres manquants (seance_id, token QR, auth_token).',
    ], 400);
}

$etudiant_id = api_verify_auth_token($auth_token);
if ($etudiant_id === null) {
    api_json_response(['success' => false, 'message' => 'Session expirée. Reconnectez-vous.'], 401);
}

$err_device = api_verify_etudiant_device_for_presence($etudiant_id, $device_id);
if ($err_device !== null) {
    api_json_response([
        'success' => false,
        'code' => $err_device['code'],
        'message' => $err_device['message'],
    ], 403);
}

$sql = 'SELECT s.*, ts.mode_marquage AS ts_mode_marquage, ts.code AS type_code
        FROM seances s
        LEFT JOIN types_seances ts ON s.type_seance_id = ts.id
        WHERE s.id = ? AND s.token = ? AND s.expiration > ? AND s.active = 1';
$seance = db_query_single($sql, [$seance_id, $qr_token, date('Y-m-d H:i:s')]);

if (!$seance) {
    api_json_response(['success' => false, 'message' => 'QR code invalide ou séance expirée.'], 400);
}

if (!seance_etudiant_marquage_qr_autorise($seance)) {
    api_json_response([
        'success' => false,
        'code' => 'teacher_only',
        'message' => 'Cette séance (examen ou contrôle) ne permet pas le pointage par QR : l’enseignant enregistre les présences.',
    ], 403);
}

$sql_check = 'SELECT id FROM presences WHERE etudiant_id = ? AND seance_id = ? AND date_presence = CURRENT_DATE';
$presence_existante = db_query_single($sql_check, [$etudiant_id, $seance_id]);

if ($presence_existante) {
    api_json_response([
        'success' => false,
        'code' => 'already_marked',
        'message' => 'Présence déjà enregistrée pour cette séance aujourd’hui.',
    ], 409);
}

$cours_row = db_query_single(
    'SELECT id FROM cours WHERE nom = ? AND enseignant_id = ? LIMIT 1',
    [$seance['nom_cours'], $seance['enseignant_id']]
);
if (!$cours_row) {
    $cours_row = db_query_single('SELECT id FROM cours WHERE nom = ? LIMIT 1', [$seance['nom_cours']]);
}
if (!$cours_row) {
    api_json_response([
        'success' => false,
        'message' => 'Cours associé introuvable. Contactez l’enseignant.',
    ], 500);
}

$sql_insert = 'INSERT INTO presences (etudiant_id, cours_id, seance_id, date_presence, statut, enregistre_par, date_enregistrement)
               VALUES (?, ?, ?, CURRENT_DATE, \'present\', ?, NOW())';
if (!db_exec($sql_insert, [$etudiant_id, (int) $cours_row['id'], $seance_id, $seance['enseignant_id']])) {
    api_json_response(['success' => false, 'message' => 'Erreur lors de l’enregistrement.'], 500);
}

api_json_response([
    'success' => true,
    'message' => 'Présence enregistrée avec succès.',
    'data' => [
        'seance_id' => $seance_id,
        'nom_cours' => $seance['nom_cours'],
    ],
]);
