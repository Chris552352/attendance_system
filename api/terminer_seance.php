<?php
/**
 * API pour terminer une séance
 */

require_once __DIR__ . '/api_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$body = api_read_json_body();
$seance_id = (int) ($body['seance_id'] ?? 0);

if ($seance_id <= 0) {
    api_json_response(['success' => false, 'message' => 'ID de séance invalide.'], 400);
}

// Vérifier que la séance appartient à l'enseignant connecté
$seance = db_query_single("
    SELECT * FROM seances 
    WHERE id = ? AND enseignant_id = ? AND statut_seance = 'en_cours'
", [$seance_id, $_SESSION['user_id'] ?? 0]);

if (!$seance) {
    api_json_response(['success' => false, 'message' => 'Séance introuvable ou déjà terminée.'], 404);
}

// Mettre à jour le statut de la séance
if (db_exec("UPDATE seances SET statut_seance = 'terminee' WHERE id = ?", [$seance_id])) {
    // Calculer les statistiques mensuelles
    $cours = db_query_single("SELECT id FROM cours WHERE nom = ? LIMIT 1", [$seance['nom_cours']]);
    if ($cours) {
        // Récupérer tous les étudiants présents
        $presences = db_query("
            SELECT etudiant_id, statut 
            FROM presences 
            WHERE seance_id = ? AND date_presence = CURDATE()
        ", [$seance_id]);
        
        foreach ($presences as $presence) {
            // Mettre à jour le compteur mensuel
            $mois = date('n');
            $annee = date('Y');
            
            $existante = db_query_single("
                SELECT id FROM presence_mensuelle 
                WHERE etudiant_id = ? AND cours_id = ? AND mois = ? AND annee = ?
            ", [$presence['etudiant_id'], $cours['id'], $mois, $annee]);
            
            if ($existante) {
                // Mettre à jour le compteur existant
                if ($presence['statut'] === 'present') {
                    db_exec("UPDATE presence_mensuelle SET total_present = total_present + 1 WHERE id = ?", [$existante['id']]);
                } elseif ($presence['statut'] === 'retard') {
                    db_exec("UPDATE presence_mensuelle SET total_retard = total_retard + 1 WHERE id = ?", [$existante['id']]);
                }
            } else {
                // Créer une nouvelle entrée
                $present = $presence['statut'] === 'present' ? 1 : 0;
                $retard = $presence['statut'] === 'retard' ? 1 : 0;
                db_exec("
                    INSERT INTO presence_mensuelle (etudiant_id, cours_id, mois, annee, total_present, total_retard)
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [$presence['etudiant_id'], $cours['id'], $mois, $annee, $present, $retard]);
            }
        }
    }
    
    api_json_response([
        'success' => true,
        'message' => 'Séance terminée avec succès.',
        'data' => [
            'seance_id' => $seance_id,
            'statut' => 'terminee'
        ]
    ]);
} else {
    api_json_response(['success' => false, 'message' => 'Erreur lors de la terminaison de la séance.'], 500);
}
