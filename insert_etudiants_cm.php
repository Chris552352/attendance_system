<?php
// Script pour insérer 10 étudiants camerounais avec email gmail.com
require_once 'config/database.php';

$etudiants = [
    ['matricule' => 'CM2023001', 'nom' => 'Nana', 'prenom' => 'Emmanuel', 'email' => 'emmanuel.nana@gmail.com'],
    ['matricule' => 'CM2023002', 'nom' => 'Mbarga', 'prenom' => 'Sandrine', 'email' => 'sandrine.mbarga@gmail.com'],
    ['matricule' => 'CM2023003', 'nom' => 'Ngono', 'prenom' => 'Jean-Pierre', 'email' => 'jeanpierre.ngono@gmail.com'],
    ['matricule' => 'CM2023004', 'nom' => 'Tchatchoua', 'prenom' => 'Brigitte', 'email' => 'brigitte.tchatchoua@gmail.com'],
    ['matricule' => 'CM2023005', 'nom' => 'Ewodo', 'prenom' => 'Serge', 'email' => 'serge.ewodo@gmail.com'],
    ['matricule' => 'CM2023006', 'nom' => 'Fouda', 'prenom' => 'Estelle', 'email' => 'estelle.fouda@gmail.com'],
    ['matricule' => 'CM2023007', 'nom' => 'Mballa', 'prenom' => 'Boris', 'email' => 'boris.mballa@gmail.com'],
    ['matricule' => 'CM2023008', 'nom' => 'Essomba', 'prenom' => 'Patricia', 'email' => 'patricia.essomba@gmail.com'],
    ['matricule' => 'CM2023009', 'nom' => 'Abega', 'prenom' => 'Franck', 'email' => 'franck.abega@gmail.com'],
    ['matricule' => 'CM2023010', 'nom' => 'Owona', 'prenom' => 'Céline', 'email' => 'celine.owona@gmail.com'],
];

$ok = 0;
foreach ($etudiants as $etudiant) {
    $sql = "SELECT id FROM etudiants WHERE email = ? OR matricule = ?";
    $existant = db_query_single($sql, [$etudiant['email'], $etudiant['matricule']]);
    if (!$existant) {
        $sql = "INSERT INTO etudiants (matricule, nom, prenom, email) VALUES (?, ?, ?, ?)";
        db_exec($sql, [$etudiant['matricule'], $etudiant['nom'], $etudiant['prenom'], $etudiant['email']]);
        echo "Ajouté : {$etudiant['prenom']} {$etudiant['nom']} ({$etudiant['matricule']})<br>\n";
        $ok++;
    } else {
        echo "Déjà présent : {$etudiant['prenom']} {$etudiant['nom']} ({$etudiant['matricule']})<br>\n";
    }
}
echo "<hr>Insertion terminée. $ok nouveaux étudiants ajoutés.";
