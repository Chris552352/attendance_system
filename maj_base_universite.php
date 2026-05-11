<?php
/**
 * Mise à jour one-shot de la base existante : schéma « présence avancée » + promotions (niveau / filière) + index.
 *
 * Usage (WAMP) : ouvrir une fois dans le navigateur :
 *   http://localhost/attendance_system/maj_base_universite.php
 *
 * À désactiver ou supprimer en production (protéger par mot de passe / IP si besoin).
 */

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/ensure_presence_avancee_schema.php';

try {
    ensure_presence_avancee_schema();

    $nbClasses = db_query_single('SELECT COUNT(*) AS n FROM classes_etablissement');
    $n = (int) ($nbClasses['n'] ?? 0);

    $colsE = [];
    foreach (db_query('SHOW COLUMNS FROM etudiants') as $r) {
        if (!empty($r['Field'])) {
            $colsE[] = (string) $r['Field'];
        }
    }
    $hasClasse = in_array('classe_id', $colsE, true);

    echo "OK — Migration terminée.\n";
    echo "- Schéma présence avancée (types de séances, salles, colonnes étendues, etc.)\n";
    echo "- Table classes_etablissement + colonne filiere + liaison etudiants.classe_id\n";
    echo "- Promotions LMD insérées pour chaque code manquant (L1-INFO-A … M2-INFO-A)\n\n";
    echo "Promotions en base : {$n}\n";
    echo "Colonne etudiants.classe_id : " . ($hasClasse ? 'oui' : 'non') . "\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erreur : ' . $e->getMessage() . "\n";
}
