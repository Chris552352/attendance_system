<?php
/**
 * Colonnes appareil sur comptes_etudiants (liaison jeton ↔ téléphone).
 */
function ensure_compte_device_schema(): void
{
    $cols = [];
    try {
        $rows = db_query('SHOW COLUMNS FROM comptes_etudiants');
        foreach ($rows as $r) {
            if (!empty($r['Field'])) {
                $cols[] = (string) $r['Field'];
            }
        }
    } catch (Throwable $e) {
        return;
    }

    $add = [
        'device_id' => 'ALTER TABLE comptes_etudiants ADD COLUMN device_id VARCHAR(80) NULL DEFAULT NULL',
        'device_model' => 'ALTER TABLE comptes_etudiants ADD COLUMN device_model VARCHAR(200) NULL DEFAULT NULL',
        'device_bound_at' => 'ALTER TABLE comptes_etudiants ADD COLUMN device_bound_at DATETIME NULL DEFAULT NULL',
    ];
    foreach ($add as $col => $sql) {
        if (!in_array($col, $cols, true)) {
            try {
                db_exec($sql);
            } catch (Throwable $e) {
            }
        }
    }
}

/**
 * Enregistre l’appareil associé au compte (connexion mobile).
 */
function api_bind_etudiant_device(int $etudiant_id, string $device_id, string $device_model): void
{
    $device_id = trim($device_id);
    if ($device_id === '' || strlen($device_id) > 80) {
        return;
    }
    ensure_compte_device_schema();
    $model = trim($device_model);
    if (strlen($model) > 200) {
        $model = substr($model, 0, 200);
    }
    try {
        db_exec(
            'UPDATE comptes_etudiants SET device_id = ?, device_model = ?, device_bound_at = NOW() WHERE etudiant_id = ?',
            [$device_id, $model !== '' ? $model : null, $etudiant_id]
        );
    } catch (Throwable $e) {
    }
}

/**
 * Vérifie que le pointage vient du même appareil qu’à la connexion.
 *
 * @return array{code:string,message:string}|null null si OK
 */
function api_verify_etudiant_device_for_presence(int $etudiant_id, string $request_device_id): ?array
{
    $request_device_id = trim($request_device_id);
    if ($request_device_id === '' || strlen($request_device_id) > 80) {
        return [
            'code' => 'device_required',
            'message' => 'Identifiant appareil manquant. Mettez à jour l’application et reconnectez-vous.',
        ];
    }

    ensure_compte_device_schema();
    $row = db_query_single('SELECT device_id FROM comptes_etudiants WHERE etudiant_id = ?', [$etudiant_id]);
    if (!$row) {
        return [
            'code' => 'device_error',
            'message' => 'Compte étudiant introuvable.',
        ];
    }

    $stored = isset($row['device_id']) ? trim((string) $row['device_id']) : '';

    if ($stored === '') {
        return [
            'code' => 'device_login_required',
            'message' => 'Appareil non enregistré. Déconnectez-vous puis reconnectez-vous depuis cette application pour lier ce téléphone.',
        ];
    }

    if (!hash_equals($stored, $request_device_id)) {
        return [
            'code' => 'device_mismatch',
            'message' => 'Ce compte est associé à un autre appareil. Impossible de marquer la présence depuis ce téléphone.',
        ];
    }

    return null;
}
