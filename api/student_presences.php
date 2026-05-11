<?php
/**
 * Liste des présences / absences de l'étudiant connecté (app mobile).
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

// Certaines bases utilisent `justifications.contenu`, d'autres `justifications.raison`.
// Certaines ont `commentaire`, d'autres non.
$sql_candidates = [
    // contenu + commentaire
    "SELECT p.id, p.date_presence, p.statut, p.justifie, p.justification, p.seance_id,
            c.nom AS cours_nom, c.code AS cours_code,
            (SELECT j.statut FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_statut,
            (SELECT j.contenu FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_contenu,
            (SELECT j.date_soumission FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_date,
            (SELECT j.commentaire FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_commentaire
     FROM presences p
     LEFT JOIN cours c ON p.cours_id = c.id
     WHERE p.etudiant_id = ?
     ORDER BY p.date_presence DESC, p.id DESC
     LIMIT 200",
    // raison + commentaire
    "SELECT p.id, p.date_presence, p.statut, p.justifie, p.justification, p.seance_id,
            c.nom AS cours_nom, c.code AS cours_code,
            (SELECT j.statut FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_statut,
            (SELECT j.raison FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_contenu,
            (SELECT j.date_soumission FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_date,
            (SELECT j.commentaire FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_commentaire
     FROM presences p
     LEFT JOIN cours c ON p.cours_id = c.id
     WHERE p.etudiant_id = ?
     ORDER BY p.date_presence DESC, p.id DESC
     LIMIT 200",
    // contenu sans commentaire
    "SELECT p.id, p.date_presence, p.statut, p.justifie, p.justification, p.seance_id,
            c.nom AS cours_nom, c.code AS cours_code,
            (SELECT j.statut FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_statut,
            (SELECT j.contenu FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_contenu,
            (SELECT j.date_soumission FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_date
     FROM presences p
     LEFT JOIN cours c ON p.cours_id = c.id
     WHERE p.etudiant_id = ?
     ORDER BY p.date_presence DESC, p.id DESC
     LIMIT 200",
    // raison sans commentaire
    "SELECT p.id, p.date_presence, p.statut, p.justifie, p.justification, p.seance_id,
            c.nom AS cours_nom, c.code AS cours_code,
            (SELECT j.statut FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_statut,
            (SELECT j.raison FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_contenu,
            (SELECT j.date_soumission FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_date
     FROM presences p
     LEFT JOIN cours c ON p.cours_id = c.id
     WHERE p.etudiant_id = ?
     ORDER BY p.date_presence DESC, p.id DESC
     LIMIT 200",
];

$sql_without_justif = "SELECT p.id, p.date_presence, p.statut, p.justifie, p.justification, p.seance_id,
               c.nom AS cours_nom, c.code AS cours_code
        FROM presences p
        LEFT JOIN cours c ON p.cours_id = c.id
        WHERE p.etudiant_id = ?
        ORDER BY p.date_presence DESC, p.id DESC
        LIMIT 200";

$rows = null;
foreach ($sql_candidates as $sql) {
    try {
        $rows = db_query($sql, [$etudiant_id]);
        break;
    } catch (PDOException $e) {
        $code = $e->getCode() ?? '';
        $msg = $e->getMessage();
        // Table manquante
        if ($code === '42S02' || str_contains($msg, 'justifications')) {
            $rows = null;
            break;
        }
        // Colonne manquante : on essaie le prochain SQL.
        if ($code === '42S22') {
            continue;
        }
        api_json_response(['success' => false, 'message' => 'Impossible de charger les présences.'], 500);
    } catch (Throwable $e) {
        api_json_response(['success' => false, 'message' => 'Impossible de charger les présences.'], 500);
    }
}

if ($rows === null) {
    try {
        $rows = db_query($sql_without_justif, [$etudiant_id]);
    } catch (Throwable $e) {
        api_json_response(['success' => false, 'message' => 'Impossible de charger les présences.'], 500);
    }
}

$out = [];
foreach ($rows as $r) {
    $justifStatut = null;
    if (array_key_exists('justif_statut', $r) && $r['justif_statut'] !== null) {
        $justifStatut = (string) $r['justif_statut'];
        // Normaliser les anciens statuts éventuels: approuve/rejete -> validee/rejetee
        if ($justifStatut === 'approuve') $justifStatut = 'validee';
        if ($justifStatut === 'rejete') $justifStatut = 'rejetee';
    }
    $out[] = [
        'presence_id' => (int) $r['id'],
        'date_presence' => (string) $r['date_presence'],
        'statut' => (string) $r['statut'],
        'justifie' => !empty($r['justifie']),
        'justification_legacy' => $r['justification'] !== null ? (string) $r['justification'] : null,
        'seance_id' => $r['seance_id'] !== null ? (int) $r['seance_id'] : null,
        'cours_nom' => $r['cours_nom'] !== null ? (string) $r['cours_nom'] : '',
        'cours_code' => $r['cours_code'] !== null ? (string) $r['cours_code'] : '',
        'justif_statut' => $justifStatut,
        'justif_contenu' => array_key_exists('justif_contenu', $r) && $r['justif_contenu'] !== null ? (string) $r['justif_contenu'] : null,
        'justif_date' => array_key_exists('justif_date', $r) && $r['justif_date'] !== null ? (string) $r['justif_date'] : null,
        'justif_commentaire' => array_key_exists('justif_commentaire', $r) && $r['justif_commentaire'] !== null ? (string) $r['justif_commentaire'] : null,
    ];
}

api_json_response([
    'success' => true,
    'data' => ['presences' => $out],
]);
