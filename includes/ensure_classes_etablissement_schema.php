<?php
/**
 * Classes de l'établissement (groupes / promotions) : une table + lien optionnel sur etudiants.
 * Idempotent (CREATE IF NOT EXISTS + ALTER si colonne absente).
 */

function classes_etablissement_schema_exec_ignore(string $sql): void
{
    try {
        db_exec($sql);
    } catch (Throwable $e) {
    }
}

/**
 * Crée la table classes_etablissement et la colonne etudiants.classe_id si besoin.
 */
function ensure_classes_etablissement_schema(): void
{
    classes_etablissement_schema_exec_ignore(
        "CREATE TABLE IF NOT EXISTS classes_etablissement (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(40) NOT NULL,
            nom VARCHAR(180) NOT NULL,
            niveau VARCHAR(80) DEFAULT NULL,
            filiere VARCHAR(120) DEFAULT NULL,
            description TEXT,
            actif TINYINT(1) DEFAULT 1,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY code_unique (code),
            KEY idx_actif (actif),
            KEY idx_niveau (niveau),
            KEY idx_filiere (filiere)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $cc = [];
    try {
        $rowsC = db_query('SHOW COLUMNS FROM classes_etablissement');
        foreach ($rowsC as $r) {
            if (!empty($r['Field'])) {
                $cc[] = (string) $r['Field'];
            }
        }
    } catch (Throwable $e) {
        $cc = [];
    }
    if (!in_array('filiere', $cc, true)) {
        classes_etablissement_schema_exec_ignore(
            'ALTER TABLE classes_etablissement ADD COLUMN filiere VARCHAR(120) DEFAULT NULL'
        );
        classes_etablissement_schema_exec_ignore(
            'ALTER TABLE classes_etablissement ADD KEY idx_filiere_col (filiere)'
        );
    }

    $cols = [];
    try {
        $rows = db_query('SHOW COLUMNS FROM etudiants');
        foreach ($rows as $r) {
            if (!empty($r['Field'])) {
                $cols[] = (string) $r['Field'];
            }
        }
    } catch (Throwable $e) {
        return;
    }

    if (!in_array('classe_id', $cols, true)) {
        classes_etablissement_schema_exec_ignore(
            'ALTER TABLE etudiants ADD COLUMN classe_id INT NULL DEFAULT NULL'
        );
        classes_etablissement_schema_exec_ignore(
            'ALTER TABLE etudiants ADD KEY idx_etudiants_classe (classe_id)'
        );
        classes_etablissement_schema_exec_ignore(
            'ALTER TABLE etudiants ADD CONSTRAINT etudiants_classe_fk FOREIGN KEY (classe_id) REFERENCES classes_etablissement(id) ON DELETE SET NULL'
        );
    }

    /** Promotions LMD de référence : insérées seulement si le code n’existe pas encore. */
    $promotions = [
        ['L1-INFO-A', 'Groupe A', 'Licence 1', 'Informatique'],
        ['L1-INFO-B', 'Groupe B', 'Licence 1', 'Informatique'],
        ['L2-INFO-A', 'Groupe A', 'Licence 2', 'Informatique'],
        ['L3-INFO-A', 'Groupe A', 'Licence 3', 'Informatique'],
        ['M1-GEST-A', 'Groupe A', 'Master 1', 'Gestion'],
        ['M2-INFO-A', 'Groupe A', 'Master 2', 'Informatique'],
    ];
    foreach ($promotions as $row) {
        try {
            [$code, $nom, $niveau, $filiere] = $row;
            $exists = db_query_single('SELECT id FROM classes_etablissement WHERE code = ?', [$code]);
            if (!$exists) {
                db_exec(
                    'INSERT INTO classes_etablissement (code, nom, niveau, filiere, actif) VALUES (?, ?, ?, ?, 1)',
                    [$code, $nom, $niveau, $filiere]
                );
            }
        } catch (Throwable $e) {
        }
    }

    classes_etablissement_enforce_obligatoires();
}

/**
 * Niveau + filière obligatoires sur chaque promotion ; chaque étudiant doit avoir une promotion.
 */
function classes_etablissement_enforce_obligatoires(): void
{
    try {
        db_exec(
            "UPDATE classes_etablissement SET niveau = 'À préciser'
             WHERE niveau IS NULL OR TRIM(niveau) = ''"
        );
        db_exec(
            "UPDATE classes_etablissement SET filiere = 'À préciser'
             WHERE filiere IS NULL OR TRIM(filiere) = ''"
        );
    } catch (Throwable $e) {
    }

    try {
        $first = db_query_single(
            'SELECT id FROM classes_etablissement WHERE actif = 1 ORDER BY id ASC LIMIT 1'
        );
        if ($first) {
            $fid = (int) $first['id'];
            db_exec('UPDATE etudiants SET classe_id = ? WHERE classe_id IS NULL', [$fid]);
        }
    } catch (Throwable $e) {
    }

    classes_etablissement_schema_exec_ignore(
        'ALTER TABLE etudiants DROP FOREIGN KEY etudiants_classe_fk'
    );

    classes_etablissement_schema_exec_ignore(
        'ALTER TABLE classes_etablissement MODIFY niveau VARCHAR(80) NOT NULL'
    );
    classes_etablissement_schema_exec_ignore(
        'ALTER TABLE classes_etablissement MODIFY filiere VARCHAR(120) NOT NULL'
    );

    try {
        $any = db_query_single('SELECT COUNT(*) AS c FROM classes_etablissement WHERE actif = 1');
        if ($any && (int) $any['c'] > 0) {
            classes_etablissement_schema_exec_ignore(
                'ALTER TABLE etudiants MODIFY classe_id INT NOT NULL'
            );
        }
    } catch (Throwable $e) {
    }

    classes_etablissement_schema_exec_ignore(
        'ALTER TABLE etudiants ADD CONSTRAINT etudiants_classe_fk FOREIGN KEY (classe_id) REFERENCES classes_etablissement(id) ON DELETE RESTRICT ON UPDATE CASCADE'
    );
}
