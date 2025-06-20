<?php
/**
 * Script pour créer automatiquement les comptes de connexion des étudiants
 * Génère un mot de passe unique pour chaque étudiant basé sur son matricule
 */

require_once 'config/database.php';

function genererMotDePasse($matricule, $nom) {
    // Génère un mot de passe basé sur les 3 derniers chiffres du matricule + 3 premières lettres du nom
    $derniers_chiffres = substr(preg_replace('/[^0-9]/', '', $matricule), -3);
    $premieres_lettres = strtolower(substr($nom, 0, 3));
    return $premieres_lettres . $derniers_chiffres;
}

try {
    echo "<h2>Création des comptes étudiants</h2>";
    
    // Récupérer tous les étudiants
    $etudiants = db_query("SELECT * FROM etudiants ORDER BY nom, prenom");
    
    if (!$etudiants) {
        throw new Exception("Impossible de récupérer la liste des étudiants");
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Nom Prénom</th><th>Email</th><th>Matricule</th><th>Mot de passe</th><th>Statut</th>";
    echo "</tr>";
    
    foreach ($etudiants as $etudiant) {
        $mot_de_passe = genererMotDePasse($etudiant['matricule'], $etudiant['nom']);
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        // Vérifier si le compte existe déjà
        $compte_existant = db_query_single(
            "SELECT id FROM comptes_etudiants WHERE etudiant_id = ?", 
            [$etudiant['id']]
        );
        
        if ($compte_existant) {
            // Mettre à jour le mot de passe
            $result = db_exec(
                "UPDATE comptes_etudiants SET mot_de_passe = ?, email = ? WHERE etudiant_id = ?",
                [$mot_de_passe_hash, $etudiant['email'], $etudiant['id']]
            );
            $statut = $result ? "Mis à jour" : "Erreur mise à jour";
        } else {
            // Créer le compte
            $result = db_exec(
                "INSERT INTO comptes_etudiants (etudiant_id, email, mot_de_passe) VALUES (?, ?, ?)",
                [$etudiant['id'], $etudiant['email'], $mot_de_passe_hash]
            );
            $statut = $result ? "Créé" : "Erreur création";
        }
        
        echo "<tr>";
        echo "<td>{$etudiant['nom']} {$etudiant['prenom']}</td>";
        echo "<td>{$etudiant['email']}</td>";
        echo "<td>{$etudiant['matricule']}</td>";
        echo "<td><strong>$mot_de_passe</strong></td>";
        echo "<td style='color: " . ($result ? "green" : "red") . ";'>$statut</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #d4edda; border-radius: 5px;'>";
    echo "<h3>Instructions pour les étudiants:</h3>";
    echo "<p>1. Chaque étudiant peut se connecter avec son email et le mot de passe généré</p>";
    echo "<p>2. Le mot de passe est composé des 3 premières lettres du nom + les 3 derniers chiffres du matricule</p>";
    echo "<p>3. Exemple: Simo (matricule CMD-UDS-23IUT1045) → mot de passe: <strong>sim045</strong></p>";
    echo "<p>4. Les étudiants peuvent changer leur mot de passe après la première connexion</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; background-color: #f8d7da; border-radius: 5px;'>";
    echo "Erreur: " . $e->getMessage();
    echo "</div>";
}
?>