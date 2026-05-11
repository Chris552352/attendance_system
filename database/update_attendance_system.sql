-- Script de mise u00e0 jour de la base de données attendance_system

USE attendance_system;

-- Ajouter les colonnes pour la justification d'absence si elles n'existent pas déjà
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = 'attendance_system' 
                   AND TABLE_NAME = 'presences' 
                   AND COLUMN_NAME = 'justification');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE presences ADD COLUMN justification TEXT NULL AFTER statut',
    'SELECT "Column justification already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = 'attendance_system' 
                   AND TABLE_NAME = 'presences' 
                   AND COLUMN_NAME = 'justifie');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE presences ADD COLUMN justifie BOOLEAN DEFAULT FALSE AFTER justification',
    'SELECT "Column justifie already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Créer la table justifications si elle n'existe pas
CREATE TABLE IF NOT EXISTS justifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    presence_id INT NOT NULL,
    raison TEXT NOT NULL,
    date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'approuve', 'rejete') DEFAULT 'en_attente',
    FOREIGN KEY (presence_id) REFERENCES presences(id) ON DELETE CASCADE
);

-- Vérifier si l'administrateur existe déjà et le mettre à jour ou le créer
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Nounamo', 'Chris', 'chris552352@gmail.com', '$2y$10$.Gltio/0jwGtIIpIUXBOp./5QwJQe7FQ1ZIXCGCAFSOV8f3WXTLeu', 'admin')
ON DUPLICATE KEY UPDATE role = 'admin', mot_de_passe = '$2y$10$.Gltio/0jwGtIIpIUXBOp./5QwJQe7FQ1ZIXCGCAFSOV8f3WXTLeu';
