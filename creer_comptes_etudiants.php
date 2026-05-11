<?php
/**
 * Création / mise à jour des comptes étudiants avec le mot de passe par défaut (12345).
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config_app.php';

try {
    echo '<h2>Création des comptes étudiants</h2>';
    echo '<p>Mot de passe attribué à tous : <strong>' . htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT) . '</strong></p>';

    $etudiants = db_query('SELECT * FROM etudiants ORDER BY nom, prenom');

    if (!$etudiants) {
        throw new Exception('Impossible de récupérer la liste des étudiants');
    }

    $mot_de_passe_hash = password_hash(MOT_DE_PASSE_UNIVERSEL_ETUDIANT, PASSWORD_DEFAULT);

    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo '<th>Nom Prénom</th><th>Email</th><th>Matricule</th><th>Mot de passe</th><th>Statut</th>';
    echo '</tr>';

    foreach ($etudiants as $etudiant) {
        $compte_existant = db_query_single(
            'SELECT id FROM comptes_etudiants WHERE etudiant_id = ?',
            [$etudiant['id']]
        );

        if ($compte_existant) {
            $result = db_exec(
                'UPDATE comptes_etudiants SET mot_de_passe = ?, email = ? WHERE etudiant_id = ?',
                [$mot_de_passe_hash, $etudiant['email'], $etudiant['id']]
            );
            $statut = $result ? 'Mis à jour (MDP réinitialisé)' : 'Erreur mise à jour';
        } else {
            $result = db_exec(
                'INSERT INTO comptes_etudiants (etudiant_id, email, mot_de_passe) VALUES (?, ?, ?)',
                [$etudiant['id'], $etudiant['email'], $mot_de_passe_hash]
            );
            $statut = $result ? 'Créé' : 'Erreur création';
        }

        echo '<tr>';
        echo '<td>' . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) . '</td>';
        echo '<td>' . htmlspecialchars($etudiant['email']) . '</td>';
        echo '<td>' . htmlspecialchars($etudiant['matricule']) . '</td>';
        echo '<td><strong>' . htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT) . '</strong></td>';
        echo '<td style="color: ' . ($result ? 'green' : 'red') . ';">' . htmlspecialchars($statut) . '</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '<div style="margin-top: 20px; padding: 15px; background-color: #d4edda; border-radius: 5px;">';
    echo '<h3>Instructions pour les étudiants</h3>';
    echo '<p>1. Connexion : email du compte + mot de passe <strong>' . htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT) . '</strong></p>';
    echo '<p>2. Scan QR de présence : même mot de passe <strong>' . htmlspecialchars(MOT_DE_PASSE_UNIVERSEL_ETUDIANT) . '</strong></p>';
    echo '<p>3. Ils peuvent modifier leur mot de passe dans l’espace étudiant après connexion</p>';
    echo '</div>';
} catch (Exception $e) {
    echo '<div style="color: red; padding: 10px; background-color: #f8d7da; border-radius: 5px;">';
    echo 'Erreur: ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
