-- Script de nettoyage pour importer les données sans erreur
-- À exécuter avant d'importer attendance_system.sql

USE attendance_system;

-- Supprimer les tables dans le bon ordre (inverse des contraintes)
DROP TABLE IF EXISTS `presences`;
DROP TABLE IF EXISTS `inscriptions`;
DROP TABLE IF EXISTS `comptes_etudiants`;
DROP TABLE IF EXISTS `seances`;
DROP TABLE IF EXISTS `cours`;
DROP TABLE IF EXISTS `etudiants`;
DROP TABLE IF EXISTS `utilisateurs`;
