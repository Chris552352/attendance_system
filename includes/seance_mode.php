<?php
/**
 * Résolution du mode de marquage (qr / manuel / enseignant_seul) pour une séance.
 */
require_once __DIR__ . '/ensure_presence_avancee_schema.php';
ensure_presence_avancee_schema();

/**
 * Mode effectif : colonne seances.mode_marquage, sinon type de séance, défaut qr.
 *
 * @param array<string,mixed> $seance
 */
function seance_effective_mode_marquage(array $seance): string
{
    if (!empty($seance['mode_marquage'])) {
        return (string) $seance['mode_marquage'];
    }
    if (!empty($seance['ts_mode_marquage'])) {
        return (string) $seance['ts_mode_marquage'];
    }
    $tid = isset($seance['type_seance_id']) ? (int) $seance['type_seance_id'] : 0;
    if ($tid > 0) {
        try {
            $row = db_query_single('SELECT mode_marquage FROM types_seances WHERE id = ?', [$tid]);
            if ($row && !empty($row['mode_marquage'])) {
                return (string) $row['mode_marquage'];
            }
        } catch (Throwable $e) {
        }
    }

    return 'qr';
}

/**
 * L'étudiant peut marquer sa présence via QR (web ou app) uniquement en mode qr.
 *
 * @param array<string,mixed> $seance
 */
function seance_etudiant_marquage_qr_autorise(array $seance): bool
{
    return seance_effective_mode_marquage($seance) === 'qr';
}

/**
 * Charge une séance avec infos type pour résolution du mode.
 *
 * @return array<string,mixed>|false
 */
function seance_load_pour_mode(int $seance_id)
{
    return db_query_single(
        'SELECT s.*, ts.mode_marquage AS ts_mode_marquage, ts.code AS type_code, ts.nom AS type_nom
         FROM seances s
         LEFT JOIN types_seances ts ON s.type_seance_id = ts.id
         WHERE s.id = ?',
        [$seance_id]
    );
}
