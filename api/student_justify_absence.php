<?php
/**
 * Soumission d'une justification d'absence (app mobile, même règles que etudiant_justifier_absence.php).
 */

require_once __DIR__ . '/api_bootstrap.php';
require_once dirname(__DIR__) . '/includes/justification_schema.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

// Support JSON (ancien) et multipart/form-data (avec pièce jointe)
$isMultipart = isset($_FILES) && !empty($_FILES);
if ($isMultipart) {
    $auth_token = trim((string) ($_POST['auth_token'] ?? ''));
    $presence_id = (int) ($_POST['presence_id'] ?? 0);
    $texte = trim((string) ($_POST['contenu'] ?? $_POST['justification'] ?? ''));
} else {
    $body = api_read_json_body();
    $auth_token = trim((string) ($body['auth_token'] ?? ''));
    $presence_id = (int) ($body['presence_id'] ?? 0);
    $texte = trim((string) ($body['contenu'] ?? $body['justification'] ?? ''));
}

if ($auth_token === '') {
    api_json_response(['success' => false, 'message' => 'auth_token requis.'], 400);
}
if ($presence_id <= 0) {
    api_json_response(['success' => false, 'message' => 'presence_id invalide.'], 400);
}
if ($texte === '') {
    api_json_response(['success' => false, 'message' => 'Veuillez saisir une justification.'], 400);
}

$etudiant_id = api_verify_auth_token($auth_token);
if ($etudiant_id === null) {
    api_json_response(['success' => false, 'message' => 'Session expirée. Reconnectez-vous.'], 401);
}

try {
    $presence = db_query_single(
        'SELECT p.* FROM presences p WHERE p.id = ? AND p.etudiant_id = ?',
        [$presence_id, $etudiant_id]
    );
} catch (Throwable $e) {
    api_json_response(['success' => false, 'message' => 'Erreur serveur.'], 500);
}

if (!$presence || ($presence['statut'] ?? '') !== 'absent') {
    api_json_response(['success' => false, 'message' => 'Absence introuvable ou statut incorrect.'], 400);
}

if (!empty($presence['justifie'])) {
    api_json_response(['success' => false, 'message' => 'Cette absence est déjà marquée comme justifiée.'], 400);
}

// Assurer que la table existe pour éviter "table manquante".
ensure_justifications_schema();
ensure_justification_messages_schema();

try {
    $existant = db_query_single(
        "SELECT id FROM justifications WHERE presence_id = ? AND statut = 'en_attente'",
        [$presence_id]
    );
} catch (Throwable $e) {
    api_json_response([
        'success' => false,
        'message' => 'Service de justification indisponible (table manquante). Contactez l’administrateur.',
    ], 503);
}

if ($existant) {
    api_json_response([
        'success' => false,
        'message' => 'Une justification est déjà en attente de traitement pour cette absence.',
    ], 409);
}

// Pièce jointe optionnelle (multipart uniquement)
$pieceRelPath = null;
$pieceMime = null;
$pieceSize = null;
$pieceOriginal = null;
if ($isMultipart && isset($_FILES['piece']) && is_array($_FILES['piece'])) {
    $f = $_FILES['piece'];
    if (!empty($f['error']) && (int) $f['error'] !== UPLOAD_ERR_OK) {
        api_json_response(['success' => false, 'message' => 'Erreur lors de l’envoi du fichier.'], 400);
    }
    $size = (int) ($f['size'] ?? 0);
    if ($size <= 0) {
        api_json_response(['success' => false, 'message' => 'Fichier vide.'], 400);
    }
    if ($size > 5 * 1024 * 1024) {
        api_json_response(['success' => false, 'message' => 'Pièce jointe trop volumineuse (max 5 Mo).'], 400);
    }
    $tmp = (string) ($f['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        api_json_response(['success' => false, 'message' => 'Fichier invalide.'], 400);
    }

    $original = (string) ($f['name'] ?? 'piece');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowedExt, true)) {
        api_json_response(['success' => false, 'message' => 'Format non autorisé. (PDF, JPG, PNG)'], 400);
    }
    $mime = null;
    try {
        $fi = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fi->file($tmp) ?: null;
    } catch (Throwable $e) {}
    $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];
    if ($mime === null || !in_array($mime, $allowedMime, true)) {
        api_json_response(['success' => false, 'message' => 'Type de fichier non autorisé.'], 400);
    }

    $baseDir = dirname(__DIR__) . '/uploads/justifications';
    $subDir = date('Ym');
    $targetDir = $baseDir . '/' . $subDir;
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0775, true);
    }
    // Empêcher le listing / exécution accidentelle
    if (!is_file($baseDir . '/index.html')) {
        @file_put_contents($baseDir . '/index.html', '<!doctype html><meta charset="utf-8">');
    }
    if (!is_file($targetDir . '/index.html')) {
        @file_put_contents($targetDir . '/index.html', '<!doctype html><meta charset="utf-8">');
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $targetDir . '/' . $safeName;
    if (!@move_uploaded_file($tmp, $dest)) {
        api_json_response(['success' => false, 'message' => 'Impossible d’enregistrer la pièce jointe.'], 500);
    }

    $pieceRelPath = 'uploads/justifications/' . $subDir . '/' . $safeName;
    $pieceMime = $mime;
    $pieceSize = $size;
    $pieceOriginal = $original;
}

try {
    $ok = db_exec(
        "INSERT INTO justifications (presence_id, contenu, statut, piece_path, piece_nom_original, piece_mime, piece_taille)
         VALUES (?, ?, 'en_attente', ?, ?, ?, ?)",
        [$presence_id, $texte, $pieceRelPath, $pieceOriginal, $pieceMime, $pieceSize]
    );
} catch (Throwable $e) {
    // Compat: certaines bases utilisent la colonne `raison` au lieu de `contenu`.
    try {
        $ok = db_exec(
            "INSERT INTO justifications (presence_id, raison, statut, piece_path, piece_nom_original, piece_mime, piece_taille)
             VALUES (?, ?, 'en_attente', ?, ?, ?, ?)",
            [$presence_id, $texte, $pieceRelPath, $pieceOriginal, $pieceMime, $pieceSize]
        );
    } catch (Throwable $e2) {
        api_json_response([
            'success' => false,
            'message' => 'Service de justification indisponible (table manquante ou schéma incompatible). Contactez l’administrateur.',
        ], 503);
    }
}

if (!$ok) {
    api_json_response(['success' => false, 'message' => 'Erreur lors de l’enregistrement.'], 500);
}

// Créer le premier message dans la conversation (copie du texte envoyé)
try {
    $jid = (int) db_last_insert_id();
    if ($jid > 0) {
        db_exec(
            'INSERT INTO justification_messages (justification_id, sender_type, sender_id, message) VALUES (?, ?, ?, ?)',
            [$jid, 'etudiant', $etudiant_id, $texte]
        );
    }
} catch (Throwable $e) {
    // non bloquant
}

api_json_response([
    'success' => true,
    'message' => 'Votre justification a été envoyée. Elle sera examinée par l’enseignant ou l’administration.',
]);
