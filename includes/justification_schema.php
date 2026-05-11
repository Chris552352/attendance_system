<?php
/**
 * Schéma partagé : justifications + messages (création / colonnes manquantes).
 */

/**
 * Crée/alimente la table justifications si manquante/incomplète.
 */
function ensure_justifications_schema(): void
{
    try {
        db_exec(
            "CREATE TABLE IF NOT EXISTS justifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                presence_id INT NOT NULL,
                contenu TEXT NOT NULL,
                date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
                statut ENUM('en_attente', 'validee', 'rejetee') DEFAULT 'en_attente',
                validee_par INT NULL,
                date_validation DATETIME NULL,
                commentaire TEXT NULL,
                piece_path VARCHAR(255) NULL,
                piece_nom_original VARCHAR(255) NULL,
                piece_mime VARCHAR(100) NULL,
                piece_taille INT NULL,
                FOREIGN KEY (presence_id) REFERENCES presences(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } catch (Throwable $e) {
        return;
    }

    $cols = [];
    try {
        $rows = db_query('SHOW COLUMNS FROM justifications');
        foreach ($rows as $r) {
            if (!empty($r['Field'])) {
                $cols[] = (string) $r['Field'];
            }
        }
    } catch (Throwable $e) {
        return;
    }

    $need = [
        'contenu' => "ALTER TABLE justifications ADD COLUMN contenu TEXT NULL",
        'date_soumission' => "ALTER TABLE justifications ADD COLUMN date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP",
        'statut' => "ALTER TABLE justifications ADD COLUMN statut ENUM('en_attente','validee','rejetee') DEFAULT 'en_attente'",
        'validee_par' => "ALTER TABLE justifications ADD COLUMN validee_par INT NULL",
        'date_validation' => "ALTER TABLE justifications ADD COLUMN date_validation DATETIME NULL",
        'commentaire' => "ALTER TABLE justifications ADD COLUMN commentaire TEXT NULL",
        'piece_path' => "ALTER TABLE justifications ADD COLUMN piece_path VARCHAR(255) NULL",
        'piece_nom_original' => "ALTER TABLE justifications ADD COLUMN piece_nom_original VARCHAR(255) NULL",
        'piece_mime' => "ALTER TABLE justifications ADD COLUMN piece_mime VARCHAR(100) NULL",
        'piece_taille' => "ALTER TABLE justifications ADD COLUMN piece_taille INT NULL",
    ];
    foreach ($need as $col => $sql) {
        if (!in_array($col, $cols, true)) {
            try {
                db_exec($sql);
            } catch (Throwable $e) {
            }
        }
    }
}

function ensure_justification_messages_schema(): void
{
    try {
        db_exec(
            "CREATE TABLE IF NOT EXISTS justification_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                justification_id INT NOT NULL,
                sender_type ENUM('etudiant','staff') NOT NULL,
                sender_id INT NULL,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (justification_id) REFERENCES justifications(id) ON DELETE CASCADE,
                KEY justification_id (justification_id),
                KEY created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } catch (Throwable $e) {
    }
}

function ensure_justification_messaging_schema(): void
{
    ensure_justifications_schema();
    ensure_justification_messages_schema();
}
