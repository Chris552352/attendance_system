<?php
/**
 * Initialisation commune des endpoints API mobiles (JSON).
 */

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204);
    exit;
}

header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/config_app.php';
require_once dirname(__DIR__) . '/includes/ensure_classes_etablissement_schema.php';
ensure_classes_etablissement_schema();

/**
 * Données « classe / groupe » pour l’app (null si non affecté).
 *
 * @return array<string, mixed>|null
 */
function api_classe_etudiant_payload(?int $classe_id): ?array
{
    if ($classe_id === null || $classe_id <= 0) {
        return null;
    }
    $row = db_query_single(
        'SELECT id, code, nom, niveau, filiere FROM classes_etablissement WHERE id = ? AND actif = 1',
        [$classe_id]
    );
    if (!$row) {
        return null;
    }

    $nv = isset($row['niveau']) && $row['niveau'] !== null && (string) $row['niveau'] !== ''
        ? (string) $row['niveau']
        : null;
    $fi = isset($row['filiere']) && $row['filiere'] !== null && (string) $row['filiere'] !== ''
        ? (string) $row['filiere']
        : null;

    return [
        'id' => (int) $row['id'],
        'code' => (string) $row['code'],
        'nom' => (string) $row['nom'],
        'niveau' => $nv,
        'filiere' => $fi,
    ];
}

if (!defined('API_MOBILE_SECRET')) {
    define('API_MOBILE_SECRET', 'changez-cette-cle-secrete-pour-la-demo-locale');
}

/**
 * @param array<string, mixed> $data
 */
function api_json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * @return array<string, mixed>
 */
function api_read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function api_create_auth_token(int $etudiant_id): string
{
    $exp = time() + 86400;
    $payload = $etudiant_id . '|' . $exp;
    $sig = hash_hmac('sha256', $payload, API_MOBILE_SECRET);
    return base64_encode($payload . '|' . $sig);
}

function api_verify_auth_token(string $token): ?int
{
    $raw = base64_decode($token, true);
    if ($raw === false) {
        return null;
    }
    $parts = explode('|', $raw);
    if (count($parts) !== 3) {
        return null;
    }
    [$etudiant_id, $exp, $sig] = $parts;
    $payload = $etudiant_id . '|' . $exp;
    $expected = hash_hmac('sha256', $payload, API_MOBILE_SECRET);
    if (!hash_equals($expected, $sig)) {
        return null;
    }
    if (time() > (int) $exp) {
        return null;
    }
    return (int) $etudiant_id;
}
