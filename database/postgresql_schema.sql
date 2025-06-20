-- PostgreSQL Schema for Attendance System
-- Converted from MySQL schema for Replit compatibility

-- Create tables with PostgreSQL syntax

-- Table: utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'enseignant' CHECK (role IN ('admin', 'enseignant')),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP DEFAULT NULL
);

-- Table: cours
CREATE TABLE IF NOT EXISTS cours (
    id SERIAL PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(150) NOT NULL,
    enseignant_id INTEGER REFERENCES utilisateurs(id),
    description TEXT
);

-- Table: etudiants
CREATE TABLE IF NOT EXISTS etudiants (
    id SERIAL PRIMARY KEY,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: inscriptions
CREATE TABLE IF NOT EXISTS inscriptions (
    id SERIAL PRIMARY KEY,
    etudiant_id INTEGER NOT NULL REFERENCES etudiants(id),
    cours_id INTEGER NOT NULL REFERENCES cours(id),
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(etudiant_id, cours_id)
);

-- Table: presences
CREATE TABLE IF NOT EXISTS presences (
    id SERIAL PRIMARY KEY,
    etudiant_id INTEGER NOT NULL REFERENCES etudiants(id),
    cours_id INTEGER NOT NULL REFERENCES cours(id),
    date_presence DATE NOT NULL,
    statut VARCHAR(10) DEFAULT 'present' CHECK (statut IN ('present', 'absent')),
    justification TEXT,
    justifie BOOLEAN DEFAULT FALSE,
    enregistre_par INTEGER NOT NULL REFERENCES utilisateurs(id),
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(etudiant_id, cours_id, date_presence)
);

-- Insert sample data
INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe, role) VALUES
(372, 'Dupont', 'Marie', 'marie.dupont@example.com', '$2y$10$example_hash_1', 'enseignant'),
(732, 'Martin', 'Jean', 'jean.martin@example.com', '$2y$10$example_hash_2', 'enseignant')
ON CONFLICT (id) DO NOTHING;

INSERT INTO cours (id, code, nom, enseignant_id, description) VALUES
(3, 'CS103', 'Réseaux Informatiques', 372, 'Principes de communication, protocoles réseaux et configuration des équipements'),
(4, 'FR835', 'Français', 732, 'La lettre formelles et la rédaction d''un rapport')
ON CONFLICT (id) DO NOTHING;

INSERT INTO etudiants (id, matricule, nom, prenom, email) VALUES
(6, 'CMD-UDS-23IUT1045', 'Simo', 'Ange', 'angesimo@gmail.com'),
(7, 'CMD-UDS-23IUT1022', 'Ateba', 'jean', 'Ateba@gmail.com'),
(8, 'CMD-UDS-23IUT1087', 'Tchouche', 'Cindy', 'Cindy90@gmail.com'),
(9, 'CMD-UDS-23IUT1098', 'Manga', 'Patrick', 'Manga45@gmail.com'),
(10, 'CMD-UDS-IUTBJN01', 'Nguekam', 'Karl', 'nguekam.karl@gmail.com'),
(11, 'CMD-UDS-IUTDLA02', 'Mbouombouo', 'Diane', 'mbouombouo.diane@icloud.com'),
(12, 'CMD-UDS-IUTYDE03', 'Fomekong', 'Richard', 'fomekong.richard@gmail.com'),
(13, 'CMD-UDS-IUTBJN04', 'Kamgang', 'Inès', 'kamgang.ines@icloud.com'),
(14, 'CMD-UDS-IUTBDA05', 'Fokou', 'Donald', 'fokou.donald@gmail.com'),
(15, 'CMD-UDS-IUTDLA06', 'Tchameni', 'Sandrine', 'tchameni.sandrine@icloud.com')
ON CONFLICT (id) DO NOTHING;

-- Reset sequences to handle manual ID insertions
SELECT setval('utilisateurs_id_seq', (SELECT MAX(id) FROM utilisateurs));
SELECT setval('cours_id_seq', (SELECT MAX(id) FROM cours));
SELECT setval('etudiants_id_seq', (SELECT MAX(id) FROM etudiants));