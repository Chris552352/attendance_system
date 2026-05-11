<?php
// Script pour vérifier et réinitialiser le mot de passe admin du projet
// Place ce fichier dans attendance_system puis visite-le via http://localhost/attendance_system/reset_admin_password.php

require_once __DIR__ . '/config/database.php';

$email = 'chris552352@gmail.com';
$new_password = '552352';
$hash = password_hash($new_password, PASSWORD_BCRYPT);

try {
    // Utilise la connexion $pdo déjà créée dans config/database.php
    global $pdo;
    if (!$pdo) {
        throw new Exception('Connexion à la base de données non initialisée.');
    }
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $stmt = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?');
        $stmt->execute([$hash, $email]);
        echo "Mot de passe admin réinitialisé avec succès pour $email.<br>";
    } else {
        // Crée l'utilisateur admin si absent
        $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Administrateur', $email, $hash, 'admin']);
        echo "Compte admin créé et mot de passe défini pour $email.<br>";
    }
    echo "Mot de passe = 552352<br>";
} catch (Exception $e) {
    echo "Erreur: ", $e->getMessage();
}
