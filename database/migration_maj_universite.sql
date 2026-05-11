-- =============================================================================
-- Mise à jour base EXISTANTE → modèle universitaire (promotions : niveau + filière)
-- À exécuter dans phpMyAdmin sur la base `attendance_system` (ou adapter le nom).
-- Idempotent : rejouer le script est sans danger (colonnes / contraintes déjà là).
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 1) Table promotions (création si absente)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `classes_etablissement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveau` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filiere` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_unique` (`code`),
  KEY `idx_actif` (`actif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2) Colonne filiere (si table créée avant sans cette colonne)
-- ---------------------------------------------------------------------------
SET @dbn = DATABASE();
SET @sql = (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @dbn AND TABLE_NAME = 'classes_etablissement' AND COLUMN_NAME = 'filiere'
    ),
    'SELECT "colonne filiere déjà présente"',
    'ALTER TABLE classes_etablissement ADD COLUMN filiere VARCHAR(120) DEFAULT NULL'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index sur filiere (ignorer si message « Duplicate » : supprimer cette ligne puis relancer)
SET @sql2 = (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @dbn AND TABLE_NAME = 'classes_etablissement' AND INDEX_NAME = 'idx_filiere_col'
    ),
    'SELECT "index idx_filiere_col déjà présent"',
    'ALTER TABLE classes_etablissement ADD KEY idx_filiere_col (filiere)'
  )
);
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- ---------------------------------------------------------------------------
-- 3) Liaison étudiants → promotion
-- ---------------------------------------------------------------------------
SET @sql3 = (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @dbn AND TABLE_NAME = 'etudiants' AND COLUMN_NAME = 'classe_id'
    ),
    'SELECT "colonne classe_id déjà présente"',
    'ALTER TABLE etudiants ADD COLUMN classe_id INT NULL DEFAULT NULL'
  )
);
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SET @sql4 = (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @dbn AND TABLE_NAME = 'etudiants' AND INDEX_NAME = 'idx_etudiants_classe'
    ),
    'SELECT "index idx_etudiants_classe déjà présent"',
    'ALTER TABLE etudiants ADD KEY idx_etudiants_classe (classe_id)'
  )
);
PREPARE stmt4 FROM @sql4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

-- Contrainte FK (échoue si déjà là ou données invalides — ignorer l’erreur dans ce cas)
SET @fk = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = @dbn AND TABLE_NAME = 'etudiants' AND CONSTRAINT_NAME = 'etudiants_classe_fk'
);
SET @sqlfk = IF(
  @fk = 0,
  'ALTER TABLE etudiants ADD CONSTRAINT etudiants_classe_fk FOREIGN KEY (classe_id) REFERENCES classes_etablissement(id) ON DELETE SET NULL',
  'SELECT "FK etudiants_classe_fk déjà présente"'
);
PREPARE stmtfk FROM @sqlfk;
EXECUTE stmtfk;
DEALLOCATE PREPARE stmtfk;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- 4) Insérer les promotions de référence (uniquement si le code est libre)
-- ---------------------------------------------------------------------------
INSERT INTO classes_etablissement (code, nom, niveau, filiere, actif)
SELECT 'L1-INFO-A', 'Groupe A', 'Licence 1', 'Informatique', 1
FROM (SELECT 1 AS _) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes_etablissement WHERE code = 'L1-INFO-A');

INSERT INTO classes_etablissement (code, nom, niveau, filiere, actif)
SELECT 'L1-INFO-B', 'Groupe B', 'Licence 1', 'Informatique', 1
FROM (SELECT 1 AS _) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes_etablissement WHERE code = 'L1-INFO-B');

INSERT INTO classes_etablissement (code, nom, niveau, filiere, actif)
SELECT 'L2-INFO-A', 'Groupe A', 'Licence 2', 'Informatique', 1
FROM (SELECT 1 AS _) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes_etablissement WHERE code = 'L2-INFO-A');

INSERT INTO classes_etablissement (code, nom, niveau, filiere, actif)
SELECT 'L3-INFO-A', 'Groupe A', 'Licence 3', 'Informatique', 1
FROM (SELECT 1 AS _) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes_etablissement WHERE code = 'L3-INFO-A');

INSERT INTO classes_etablissement (code, nom, niveau, filiere, actif)
SELECT 'M1-GEST-A', 'Groupe A', 'Master 1', 'Gestion', 1
FROM (SELECT 1 AS _) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes_etablissement WHERE code = 'M1-GEST-A');

INSERT INTO classes_etablissement (code, nom, niveau, filiere, actif)
SELECT 'M2-INFO-A', 'Groupe A', 'Master 2', 'Informatique', 1
FROM (SELECT 1 AS _) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes_etablissement WHERE code = 'M2-INFO-A');

-- =============================================================================
-- Pour le reste (types_seances, colonnes seances/presences, etc.), préférer :
--   http://localhost/attendance_system/maj_base_universite.php
-- ou appeler une API une fois (api_bootstrap charge les mêmes migrations PHP).
-- =============================================================================
