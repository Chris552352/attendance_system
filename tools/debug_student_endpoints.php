<?php
/**
 * Debug rapide des endpoints mobiles côté serveur.
 * Usage:
 *   php tools/debug_student_endpoints.php 7
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/api_bootstrap.php';

$id = (int) ($argv[1] ?? 0);
if ($id <= 0) {
    echo "Usage: php tools/debug_student_endpoints.php <etudiant_id>\n";
    exit(1);
}

echo "Etudiant ID: $id\n";
$token = api_create_auth_token($id);
echo "Token: " . $token . "\n\n";

// 1) Presences SQL
echo "== student_presences SQL ==\n";
$sql = "SELECT p.id, p.date_presence, p.statut, p.justifie, p.justification, p.seance_id,
               c.nom AS cours_nom, c.code AS cours_code,
               (SELECT j.statut FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_statut,
               (SELECT j.contenu FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_contenu,
               (SELECT j.date_soumission FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_date,
               (SELECT j.commentaire FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) AS justif_commentaire
        FROM presences p
        LEFT JOIN cours c ON p.cours_id = c.id
        WHERE p.etudiant_id = ?
        ORDER BY p.date_presence DESC, p.id DESC
        LIMIT 200";

try {
    $rows = db_query($sql, [$id]);
    echo "OK: " . count($rows) . " ligne(s)\n";
} catch (Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

echo "\n== student_courses SQL ==\n";
$sql2 = "SELECT c.id, c.code, c.nom, c.description,
               u.nom AS enseignant_nom, u.prenom AS enseignant_prenom, u.email AS enseignant_email,
               i.date_inscription
        FROM inscriptions i
        INNER JOIN cours c ON i.cours_id = c.id
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        WHERE i.etudiant_id = ?
        ORDER BY c.nom ASC";
try {
    $rows = db_query($sql2, [$id]);
    echo "OK: " . count($rows) . " cours\n";
} catch (Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

echo "\n== HTTP call /api/student_presences.php ==\n";
$payload = json_encode(['auth_token' => $token], JSON_UNESCAPED_UNICODE);
$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $payload,
    ],
]);
$res = file_get_contents('http://localhost/attendance_system/api/student_presences.php', false, $ctx);
echo ($res === false ? "ERREUR HTTP\n" : $res . "\n");

echo "\n== HTTP call /api/student_courses.php ==\n";
$res = file_get_contents('http://localhost/attendance_system/api/student_courses.php', false, $ctx);
echo ($res === false ? "ERREUR HTTP\n" : $res . "\n");

