<?php
/**
 * Téléchargement sécurisé d'une pièce jointe de justification (enseignant / admin).
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

require_auth();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Requête invalide.';
    exit;
}

require_once __DIR__ . '/includes/justification_schema.php';
ensure_justification_messaging_schema();

$row = db_query_single(
    'SELECT j.piece_path, j.piece_nom_original, j.piece_mime, p.cours_id
     FROM justifications j
     INNER JOIN presences p ON j.presence_id = p.id
     WHERE j.id = ?',
    [$id]
);

if (!$row || empty($row['piece_path'])) {
    http_response_code(404);
    echo 'Pièce jointe introuvable.';
    exit;
}

$cours_id = (int) $row['cours_id'];
$allowed = est_admin() || est_enseignant_du_cours($cours_id);
if (!$allowed) {
    http_response_code(403);
    echo 'Accès refusé.';
    exit;
}

$rel = (string) $row['piece_path'];
$rel = str_replace(['\\', "\0"], '', $rel);
if (strpos($rel, 'uploads/justifications/') !== 0) {
    http_response_code(403);
    echo 'Chemin invalide.';
    exit;
}

$baseDir = realpath(__DIR__ . '/uploads/justifications');
$full = realpath(__DIR__ . '/' . $rel);
if ($baseDir === false || $full === false || strpos($full, $baseDir) !== 0 || !is_file($full)) {
    http_response_code(404);
    echo 'Fichier introuvable.';
    exit;
}

$downloadName = $row['piece_nom_original'] ?: basename($full);
$mime = !empty($row['piece_mime']) ? (string) $row['piece_mime'] : 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($full));
header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
header('X-Content-Type-Options: nosniff');
readfile($full);
exit;
