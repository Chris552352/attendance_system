-- Script de mise u00e0 jour de la base de données attendance_system

USE attendance_system;

-- Ajouter les colonnes pour la justification d'absence si elles n'existent pas déjà
ALTER TABLE presences ADD COLUMN IF NOT EXISTS justification TEXT NULL AFTER statut;
ALTER TABLE presences ADD COLUMN IF NOT EXISTS justifie BOOLEAN DEFAULT FALSE AFTER justification;

-- Vérifier si l'administrateur existe déjà et le mettre u00e0 jour ou le créer
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Nounamo', 'Chris', 'chris552352@gmail.com', '$2y$10$.Gltio/0jwGtIIpIUXBOp./5QwJQe7FQ1ZIXCGCAFSOV8f3WXTLeu', 'admin')
ON DUPLICATE KEY UPDATE role = 'admin', mot_de_passe = '$2y$10$.Gltio/0jwGtIIpIUXBOp./5QwJQe7FQ1ZIXCGCAFSOV8f3WXTLeu';
