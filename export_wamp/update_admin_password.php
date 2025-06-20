<?php
require_once 'config/database.php';

// Générer le hash pour le mot de passe 552352
$nouveau_mot_de_passe = '552352';
$hash = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);

echo "Nouveau hash généré: " . $hash . "\n\n";

// Mettre à jour le mot de passe de l'administrateur
$result = db_exec(
    "UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?",
    [$hash, 'chris552352@gmail.com']
);

if ($result) {
    echo "Mot de passe administrateur mis à jour avec succès!\n";
    echo "Email: chris552352@gmail.com\n";
    echo "Nouveau mot de passe: 552352\n";
} else {
    echo "Erreur lors de la mise à jour du mot de passe.\n";
}

// Vérifier la mise à jour
$admin = db_query_single(
    "SELECT nom, prenom, email FROM utilisateurs WHERE email = ?",
    ['chris552352@gmail.com']
);

if ($admin) {
    echo "\nCompte administrateur trouvé:\n";
    echo "Nom: " . $admin['nom'] . " " . $admin['prenom'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
} else {
    echo "\nErreur: Compte administrateur non trouvé.\n";
}
?>