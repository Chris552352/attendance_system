<?php
/**
 * Liste des cours de l'étudiant connecté (app mobile).
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

$sql = "SELECT c.id, c.code, c.nom, c.description,
               u.nom AS enseignant_nom, u.prenom AS enseignant_prenom, u.email AS enseignant_email,
               i.date_inscription
        FROM inscriptions i
        INNER JOIN cours c ON i.cours_id = c.id
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        WHERE i.etudiant_id = ?
        ORDER BY c.nom ASC";

try {
    $rows = db_query($sql, [$etudiant_id]);
} catch (Throwable $e) {
    api_json_response(['success' => false, 'message' => 'Impossible de charger les cours.'], 500);
}

$out = [];
foreach ($rows as $r) {
    $enseignant = trim(((string) ($r['enseignant_prenom'] ?? '')) . ' ' . ((string) ($r['enseignant_nom'] ?? '')));
    $out[] = [
        'cours_id' => (int) $r['id'],
        'code' => (string) ($r['code'] ?? ''),
        'nom' => (string) ($r['nom'] ?? ''),
        'description' => $r['description'] !== null ? (string) $r['description'] : null,
        'enseignant' => $enseignant,
        'enseignant_email' => $r['enseignant_email'] !== null ? (string) $r['enseignant_email'] : null,
        'date_inscription' => $r['date_inscription'] !== null ? (string) $r['date_inscription'] : null,
    ];
}

api_json_response(['success' => true, 'data' => ['cours' => $out]]);

