<?php
/**
 * Schéma « présence avancée » (local) : types de séances, salles, retards, colonnes étendues.
 * Appels idempotents (CREATE IF NOT EXISTS + ALTER si colonne absente).
 */

function presence_avancee_table_columns(string $table): array
{
    $cols = [];
    try {
        $rows = db_query('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`');
        foreach ($rows as $r) {
            if (!empty($r['Field'])) {
                $cols[] = (string) $r['Field'];
            }
        }
    } catch (Throwable $e) {
    }

    return $cols;
}

function presence_avancee_exec_ignore(string $sql): void
{
    try {
        db_exec($sql);
    } catch (Throwable $e) {
    }
}

/**
 * Crée / met à jour tables et colonnes nécessaires au module établissement supérieur.
 */
function ensure_presence_avancee_schema(): void
{
    // --- types_seances
    presence_avancee_exec_ignore(
        "CREATE TABLE IF NOT EXISTS types_seances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(50) NOT NULL,
            code VARCHAR(10) NOT NULL,
            couleur VARCHAR(7) DEFAULT '#007bff',
            duree_minutes INT DEFAULT 60,
            presence_obligatoire TINYINT(1) DEFAULT 1,
            mode_marquage ENUM('qr','manuel','enseignant_seul') DEFAULT 'qr',
            description TEXT,
            UNIQUE KEY code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // --- salles
    presence_avancee_exec_ignore(
        "CREATE TABLE IF NOT EXISTS salles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(50) NOT NULL,
            batiment VARCHAR(50) DEFAULT '',
            capacite INT DEFAULT 30,
            equipements TEXT,
            actif TINYINT(1) DEFAULT 1,
            UNIQUE KEY nom (nom)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // --- retards (sans FK si échec, on retente version simple)
    presence_avancee_exec_ignore(
        "CREATE TABLE IF NOT EXISTS retards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            etudiant_id INT NOT NULL,
            seance_id INT NOT NULL,
            minutes_retard INT NOT NULL,
            motif VARCHAR(255) DEFAULT NULL,
            justifie TINYINT(1) DEFAULT 0,
            date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY etudiant_id (etudiant_id),
            KEY seance_id (seance_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    presence_avancee_exec_ignore(
        "CREATE TABLE IF NOT EXISTS seuils_alerte (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cours_id INT NOT NULL,
            type_alerte ENUM('absence','retard') NOT NULL,
            seuil INT NOT NULL,
            email_destinataire VARCHAR(150) DEFAULT NULL,
            actif TINYINT(1) DEFAULT 1,
            KEY cours_id (cours_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    presence_avancee_exec_ignore(
        "CREATE TABLE IF NOT EXISTS presence_mensuelle (
            id INT AUTO_INCREMENT PRIMARY KEY,
            etudiant_id INT NOT NULL,
            cours_id INT NOT NULL,
            mois INT NOT NULL,
            annee INT NOT NULL,
            total_present INT DEFAULT 0,
            total_absent INT DEFAULT 0,
            total_retard INT DEFAULT 0,
            taux_presence DECIMAL(5,2) DEFAULT 0.00,
            date_calcul TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY etudiant_cours_mois_annee (etudiant_id, cours_id, mois, annee),
            KEY cours_id (cours_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // --- colonnes seances
    $sc = presence_avancee_table_columns('seances');
    $add = [
        'salle_id' => 'ALTER TABLE seances ADD COLUMN salle_id INT NULL',
        'type_seance_id' => 'ALTER TABLE seances ADD COLUMN type_seance_id INT NULL',
        'heure_debut' => 'ALTER TABLE seances ADD COLUMN heure_debut TIME NULL',
        'heure_fin' => 'ALTER TABLE seances ADD COLUMN heure_fin TIME NULL',
        'mode_marquage' => "ALTER TABLE seances ADD COLUMN mode_marquage ENUM('qr','manuel','enseignant_seul') DEFAULT 'qr'",
        'statut_seance' => "ALTER TABLE seances ADD COLUMN statut_seance ENUM('planifiee','en_cours','terminee','annulee') NULL DEFAULT NULL",
        'date_seance' => 'ALTER TABLE seances ADD COLUMN date_seance DATE NULL',
    ];
    foreach ($add as $col => $sql) {
        if (!in_array($col, $sc, true)) {
            presence_avancee_exec_ignore($sql);
        }
    }

    // --- colonnes presences
    $pc = presence_avancee_table_columns('presences');
    $addp = [
        'heure_arrivee' => 'ALTER TABLE presences ADD COLUMN heure_arrivee TIME NULL',
        'heure_depart' => 'ALTER TABLE presences ADD COLUMN heure_depart TIME NULL',
        'geolocalisation' => 'ALTER TABLE presences ADD COLUMN geolocalisation VARCHAR(100) NULL',
        'photo_profil' => 'ALTER TABLE presences ADD COLUMN photo_profil VARCHAR(255) NULL',
        'marque_par_enseignant' => 'ALTER TABLE presences ADD COLUMN marque_par_enseignant TINYINT(1) DEFAULT 0',
    ];
    foreach ($addp as $col => $sql) {
        if (!in_array($col, $pc, true)) {
            presence_avancee_exec_ignore($sql);
        }
    }

    // Élargir ENUM statut si possible
    presence_avancee_exec_ignore(
        "ALTER TABLE presences MODIFY COLUMN statut ENUM('present','absent','retard','excuse') COLLATE utf8mb4_unicode_ci DEFAULT 'present'"
    );

    // Données initiales types
    try {
        $n = db_query_single('SELECT COUNT(*) AS c FROM types_seances');
        if ($n && (int) $n['c'] === 0) {
            db_exec(
                "INSERT INTO types_seances (nom, code, couleur, duree_minutes, presence_obligatoire, mode_marquage, description) VALUES
                ('Cours magistral', 'CM', '#28a745', 90, 1, 'qr', 'Cours théorique'),
                ('Travaux dirigés', 'TD', '#007bff', 120, 1, 'qr', 'TD'),
                ('Travaux pratiques', 'TP', '#fd7e14', 180, 1, 'qr', 'TP'),
                ('Examen', 'EX', '#dc3545', 180, 1, 'enseignant_seul', 'Examen — pointage par l’enseignant uniquement'),
                ('Contrôle', 'CTRL', '#c0392b', 60, 1, 'enseignant_seul', 'Contrôle — pointage par l’enseignant uniquement'),
                ('Conférence', 'CF', '#6f42c1', 60, 0, 'manuel', 'Conférence — présence optionnelle / manuelle')"
            );
        } else {
            $hasCtrl = db_query_single("SELECT id FROM types_seances WHERE code = 'CTRL' LIMIT 1");
            if (!$hasCtrl) {
                db_exec(
                    "INSERT INTO types_seances (nom, code, couleur, duree_minutes, presence_obligatoire, mode_marquage, description)
                     VALUES ('Contrôle', 'CTRL', '#c0392b', 60, 1, 'enseignant_seul', 'Contrôle — pointage par l’enseignant uniquement')"
                );
            }
        }
    } catch (Throwable $e) {
    }

    try {
        $ns = db_query_single('SELECT COUNT(*) AS c FROM salles');
        if ($ns && (int) $ns['c'] === 0) {
            db_exec(
                "INSERT INTO salles (nom, batiment, capacite, equipements, actif) VALUES
                ('Amphi A101', 'Bâtiment A', 150, 'Projecteur', 1),
                ('Salle B205', 'Bâtiment B', 30, 'Vidéoprojecteur', 1),
                ('Lab TP C301', 'Bâtiment C', 25, 'PC', 1)"
            );
        }
    } catch (Throwable $e) {
    }

    require_once __DIR__ . '/ensure_classes_etablissement_schema.php';
    ensure_classes_etablissement_schema();
}
