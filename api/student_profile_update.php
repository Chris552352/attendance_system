<?php
/**
 * Profil étudiant (app mobile) : mise à jour (nom/prénom/email).
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

$nom = trim((string) ($body['nom'] ?? ''));
$prenom = trim((string) ($body['prenom'] ?? ''));
$email = trim((string) ($body['email'] ?? ''));

if ($nom === '' || $prenom === '' || $email === '') {
    api_json_response(['success' => false, 'message' => 'Nom, prénom et email requis.'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_json_response(['success' => false, 'message' => 'Email invalide.'], 400);
}

// Mise à jour : etudiants.email + comptes_etudiants.email (si existe) doivent rester cohérents.
global $pdo;
try {
    $pdo->beginTransaction();

    $ok1 = db_exec(
        'UPDATE etudiants SET nom = ?, prenom = ?, email = ? WHERE id = ?',
        [$nom, $prenom, $email, $etudiant_id]
    );

    // Si un compte étudiant existe, refléter l'email de connexion.
    try {
        db_exec('UPDATE comptes_etudiants SET email = ? WHERE etudiant_id = ?', [$email, $etudiant_id]);
    } catch (Throwable $e) {
        // ignorer si table/colonne absente (rare)
    }

    if (!$ok1) {
        $pdo->rollBack();
        api_json_response(['success' => false, 'message' => 'Impossible de mettre à jour le profil.'], 500);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // 23000 = violation contrainte (email unique)
    if (($e->getCode() ?? '') === '23000') {
        api_json_response(['success' => false, 'message' => 'Cet email est déjà utilisé.'], 409);
    }
    api_json_response(['success' => false, 'message' => 'Erreur serveur.'], 500);
}

api_json_response(['success' => true, 'message' => 'Profil mis à jour.']);

