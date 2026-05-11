-- Correction du schéma pour la table presences
-- Le champ 'statut' existe déjà, il faut le modifier et non l'ajouter

-- Étape 1: Ajouter les nouvelles colonnes (sauf statut qui existe déjà)
ALTER TABLE `presences` 
  ADD COLUMN `heure_arrivee` time DEFAULT NULL AFTER `statut`,
  ADD COLUMN `heure_depart` time DEFAULT NULL AFTER `heure_arrivee`,
  ADD COLUMN `geolocalisation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `heure_depart`,
  ADD COLUMN `photo_profil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `geolocalisation`,
  ADD COLUMN `marque_par_enseignant` tinyint(1) DEFAULT 0 AFTER `photo_profil`;

-- Étape 2: Modifier la colonne statut existante pour ajouter les nouvelles valeurs
ALTER TABLE `presences` 
  MODIFY COLUMN `statut` enum('present','retard','absent','excuse') COLLATE utf8mb4_unicode_ci DEFAULT 'present';
