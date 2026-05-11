<?php
// Script pour ajouter des enseignants, des cours et faire les affectations
require_once 'config/database.php';

// --- ÉTAPE 1: DÉFINIR ET AJOUTER LES ENSEIGNANTS ---
$enseignants = [
    ['nom' => 'Fouda', 'prenom' => 'Paul', 'email' => 'paul.fouda@example.com'],
    ['nom' => 'Ngono', 'prenom' => 'Marie', 'email' => 'marie.ngono@example.com'],
];

echo "<strong>--- Ajout des enseignants ---</strong><br>\n";
foreach ($enseignants as $ens) {
    $existant = db_query_single("SELECT id FROM utilisateurs WHERE email = ?", [$ens['email']]);
    if (!$existant) {
        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'enseignant')";
        $hash = password_hash('12345', PASSWORD_BCRYPT); // Mot de passe par défaut
        db_exec($sql, [$ens['nom'], $ens['prenom'], $ens['email'], $hash]);
        echo "Enseignant ajouté : {$ens['prenom']} {$ens['nom']}<br>\n";
    } else {
        echo "Enseignant déjà présent : {$ens['prenom']} {$ens['nom']}<br>\n";
    }
}

// --- ÉTAPE 2: DÉFINIR ET AJOUTER LES COURS ---
$cours_a_creer = [
    ['code' => 'MATH101', 'nom' => 'Mathématiques', 'enseignant_email' => 'paul.fouda@example.com'],
    ['code' => 'PHY102', 'nom' => 'Physique', 'enseignant_email' => 'marie.ngono@example.com'],
    ['code' => 'INFO103', 'nom' => 'Informatique', 'enseignant_email' => 'paul.fouda@example.com'],
];

echo "<br><strong>--- Ajout des cours ---</strong><br>\n";
foreach ($cours_a_creer as $c) {
    $enseignant = db_query_single("SELECT id FROM utilisateurs WHERE email = ?", [$c['enseignant_email']]);
    if (!$enseignant) {
        echo "Erreur : Enseignant non trouvé pour le cours {$c['nom']}<br>\n";
        continue;
    }
    $existant = db_query_single("SELECT id FROM cours WHERE code = ?", [$c['code']]);
    if (!$existant) {
        $sql = "INSERT INTO cours (code, nom, enseignant_id) VALUES (?, ?, ?)";
        db_exec($sql, [$c['code'], $c['nom'], $enseignant['id']]);
        echo "Cours ajouté : {$c['nom']}<br>\n";
    } else {
        echo "Cours déjà présent : {$c['nom']}<br>\n";
    }
}

// --- ÉTAPE 3: INSCRIRE TOUS LES ÉTUDIANTS EXISTANTS À TOUS LES COURS ---
echo "<br><strong>--- Inscription des étudiants aux cours ---</strong><br>\n";

// Récupérer tous les étudiants de la base de données
$tous_les_etudiants = db_query("SELECT id, email FROM etudiants");
if (empty($tous_les_etudiants)) {
    die("Erreur : Aucun étudiant trouvé dans la base de données. Le script de réinitialisation a-t-il été exécuté ?");
}

// Récupérer tous les cours
$tous_les_cours = db_query("SELECT id FROM cours");
if (empty($tous_les_cours)) {
    die("Erreur : Aucun cours trouvé. Veuillez d'abord ajouter des cours.");
}

// Boucler pour faire les inscriptions
foreach ($tous_les_etudiants as $etudiant) {
    foreach ($tous_les_cours as $cours) {
        // Vérifier si l'inscription existe déjà
        $deja_inscrit = db_query_single(
            "SELECT id FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?",
            [$etudiant['id'], $cours['id']]
        );

        if (!$deja_inscrit) {
            db_exec(
                "INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)",
                [$etudiant['id'], $cours['id']]
            );
            echo "Inscription effectuée : {$etudiant['email']} au cours ID {$cours['id']}<br>\n";
        } else {
            echo "Déjà inscrit : {$etudiant['email']} au cours ID {$cours['id']}<br>\n";
        }
    }
}

echo '<hr>Script de peuplement terminé.';
