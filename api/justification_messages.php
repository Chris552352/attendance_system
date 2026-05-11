<?php
/**
 * Messagerie interne liée aux justifications (app mobile).
 *
 * - POST JSON { auth_token, presence_id } => liste messages du dernier dossier de justification
 * - POST JSON { auth_token, presence_id, message } => ajoute message étudiant
 */
require_once __DIR__ . '/api_bootstrap.php';
require_once dirname(__DIR__) . '/includes/justification_schema.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$body = api_read_json_body();
$auth_token = trim((string) ($body['auth_token'] ?? ''));
$presence_id = (int) ($body['presence_id'] ?? 0);
$message = trim((string) ($body['message'] ?? ''));

if ($auth_token === '') {
    api_json_response(['success' => false, 'message' => 'auth_token requis.'], 400);
}
if ($presence_id <= 0) {
    api_json_response(['success' => false, 'message' => 'presence_id invalide.'], 400);
}

$etudiant_id = api_verify_auth_token($auth_token);
if ($etudiant_id === null) {
    api_json_response(['success' => false, 'message' => 'Session expirée. Reconnectez-vous.'], 401);
}

ensure_justification_messaging_schema();

// Vérifier que la présence appartient à l'étudiant
$presence = db_query_single(
    'SELECT id, etudiant_id FROM presences WHERE id = ? AND etudiant_id = ?',
    [$presence_id, $etudiant_id]
);
if (!$presence) {
    api_json_response(['success' => false, 'message' => 'Présence introuvable.'], 404);
}

// Prendre la justification la plus récente liée à cette présence (tous statuts)
$justif = null;
try {
    $justif = db_query_single(
        'SELECT * FROM justifications WHERE presence_id = ? ORDER BY date_soumission DESC, id DESC LIMIT 1',
        [$presence_id]
    );
} catch (Throwable $e) {
    api_json_response(['success' => false, 'message' => 'Messagerie indisponible.'], 503);
}

if (!$justif) {
    api_json_response(['success' => true, 'data' => ['justification' => null, 'messages' => []]]);
}

$justification_id = (int) $justif['id'];

// Envoi message
if ($message !== '') {
    if (mb_strlen($message) > 2000) {
        api_json_response(['success' => false, 'message' => 'Message trop long (max 2000 caractères).'], 400);
    }
    try {
        db_exec(
            'INSERT INTO justification_messages (justification_id, sender_type, sender_id, message) VALUES (?, ?, ?, ?)',
            [$justification_id, 'etudiant', $etudiant_id, $message]
        );
    } catch (Throwable $e) {
        api_json_response(['success' => false, 'message' => 'Impossible d’envoyer le message.'], 500);
    }
}

// Charger fil
$messages = [];
try {
    $rows = db_query(
        'SELECT id, sender_type, sender_id, message, created_at FROM justification_messages WHERE justification_id = ? ORDER BY created_at ASC, id ASC',
        [$justification_id]
    );
    foreach ($rows as $r) {
        $messages[] = [
            'id' => (int) $r['id'],
            'sender_type' => (string) $r['sender_type'],
            'sender_id' => $r['sender_id'] !== null ? (int) $r['sender_id'] : null,
            'message' => (string) $r['message'],
            'created_at' => (string) $r['created_at'],
        ];
    }
} catch (Throwable $e) {
    api_json_response(['success' => false, 'message' => 'Impossible de charger la conversation.'], 500);
}

api_json_response([
    'success' => true,
    'data' => [
        'justification' => [
            'id' => $justification_id,
            'statut' => (string) ($justif['statut'] ?? ''),
            'date_soumission' => (string) ($justif['date_soumission'] ?? ''),
            'piece_path' => $justif['piece_path'] ?? null,
            'piece_nom_original' => $justif['piece_nom_original'] ?? null,
            'piece_mime' => $justif['piece_mime'] ?? null,
            'piece_taille' => $justif['piece_taille'] ?? null,
        ],
        'messages' => $messages,
    ],
]);

