<?php
/**
 * One-shot : université uniquement.
 * - Désactive les anciennes promotions "Secondaire" (si présentes)
 * - Crée des cours universitaires de base (si manquants)
 * - Répartit les étudiants dans des promotions LMD actives
 * - Inscrit les étudiants à des cours selon filière (sans supprimer les inscriptions existantes)
 *
 * Usage : ouvrir une fois
 *   http://localhost/attendance_system/maj_affectations_universite.php
 *
 * ⚠️ À supprimer / protéger après exécution.
 */

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/ensure_presence_avancee_schema.php';
require_once __DIR__ . '/includes/functions.php';

require_auth();
require_admin();

if (!function_exists('str_starts_with')) {
    /**
     * @internal Compat PHP < 8
     */
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, 0, strlen($needle)) === $needle;
    }
}

/**
 * @param array<int, array{code: string, nom: string, description?: string|null}> $courses
 * @return array<string, int> code => id
 */
function ensure_courses(array $courses): array
{
    $map = [];
    foreach ($courses as $c) {
        $code = $c['code'];
        $nom = $c['nom'];
        $desc = $c['description'] ?? null;

        $row = db_query_single('SELECT id FROM cours WHERE code = ?', [$code]);
        if (!$row) {
            db_exec(
                'INSERT INTO cours (code, nom, description, enseignant_id) VALUES (?, ?, ?, NULL)',
                [$code, $nom, $desc]
            );
            $id = (int) db_last_insert_id();
        } else {
            $id = (int) $row['id'];
        }
        $map[$code] = $id;
    }
    return $map;
}

/**
 * @return array<int, array{id:int, code:string, niveau:string, filiere:string}>
 */
function active_promotions(): array
{
    $rows = db_query(
        "SELECT id, code, niveau, filiere
         FROM classes_etablissement
         WHERE actif = 1
           AND TRIM(niveau) <> ''
           AND TRIM(filiere) <> ''
           AND niveau <> 'Secondaire'
           AND filiere <> 'Secondaire'
           AND code NOT LIKE 'SEC-%'
         ORDER BY niveau, filiere, code"
    );
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'id' => (int) $r['id'],
            'code' => (string) $r['code'],
            'niveau' => (string) $r['niveau'],
            'filiere' => (string) $r['filiere'],
        ];
    }
    return $out;
}

try {
    // Assure schéma + promotions LMD + contraintes.
    ensure_presence_avancee_schema();

    // 1) Nettoyer tout ce qui ressemble à du secondaire : on réaffecte d'abord, puis on supprime.
    // (L'application ne traite pas le secondaire.)

    // 2) Promotions LMD actives.
    $promos = active_promotions();
    if ($promos === []) {
        throw new RuntimeException('Aucune promotion LMD active trouvée. Crée des promotions dans Promotions (LMD) puis relance.');
    }

    // 3) Cours universitaires de base (si manquants).
    // Tu peux renommer/adapter plus tard : c’est juste pour que tous aient des cours.
    $courseMap = ensure_courses([
        ['code' => 'UNI-ETH', 'nom' => 'Méthodologie / Éthique', 'description' => 'Cours transversal'],
        ['code' => 'UNI-TIC', 'nom' => 'TIC & Outils numériques', 'description' => 'Cours transversal'],

        ['code' => 'INFO-ALG', 'nom' => 'Algorithmique', 'description' => 'Informatique'],
        ['code' => 'INFO-POO', 'nom' => 'Programmation orientée objet', 'description' => 'Informatique'],
        ['code' => 'INFO-BDD', 'nom' => 'Bases de données', 'description' => 'Informatique'],
        ['code' => 'INFO-RES', 'nom' => 'Réseaux', 'description' => 'Informatique'],

        ['code' => 'GEST-CPT', 'nom' => 'Comptabilité', 'description' => 'Gestion'],
        ['code' => 'GEST-MKT', 'nom' => 'Marketing', 'description' => 'Gestion'],
        ['code' => 'GEST-GRH', 'nom' => 'Gestion des ressources humaines', 'description' => 'Gestion'],
        ['code' => 'GEST-ECO', 'nom' => 'Économie', 'description' => 'Gestion'],
    ]);

    // 4) Affectation étudiants → promos.
    $students = db_query('SELECT id, classe_id FROM etudiants ORDER BY id ASC');
    $updatedPromo = 0;
    $keptPromo = 0;
    $i = 0;
    foreach ($students as $s) {
        $sid = (int) $s['id'];
        $cid = isset($s['classe_id']) ? (int) $s['classe_id'] : 0;

        $ok = false;
        if ($cid > 0) {
            $cl = db_query_single('SELECT id, code, niveau, filiere, actif FROM classes_etablissement WHERE id = ?', [$cid]);
            if ($cl && (int) $cl['actif'] === 1) {
                $code = (string) $cl['code'];
                $niv = (string) ($cl['niveau'] ?? '');
                $fil = (string) ($cl['filiere'] ?? '');
                $ok = !(str_starts_with($code, 'SEC-')) && $niv !== 'Secondaire' && $fil !== 'Secondaire';
            }
        }

        if (!$ok) {
            $target = $promos[$i % count($promos)];
            $i++;
            db_exec('UPDATE etudiants SET classe_id = ? WHERE id = ?', [$target['id'], $sid]);
            $updatedPromo++;
        } else {
            $keptPromo++;
        }
    }

    // 4-bis) Suppression définitive des promotions "Secondaire".
    // À ce stade, aucun étudiant ne doit pointer dessus (réaffectation ci-dessus).
    $secBefore = db_query_single(
        "SELECT COUNT(*) AS c
         FROM classes_etablissement
         WHERE code LIKE 'SEC-%'
            OR niveau = 'Secondaire'
            OR filiere = 'Secondaire'
            OR nom LIKE '%Secondaire%'"
    );
    $secCount = (int) ($secBefore['c'] ?? 0);
    if ($secCount > 0) {
        db_exec(
            "DELETE FROM classes_etablissement
             WHERE code LIKE 'SEC-%'
                OR niveau = 'Secondaire'
                OR filiere = 'Secondaire'
                OR nom LIKE '%Secondaire%'"
        );
    }

    // 5) Inscriptions aux cours.
    // Règle :
    // - si l’étudiant a déjà des inscriptions -> on complète juste les cours "transversaux" si manquants
    // - sinon -> transversaux + 2-4 cours selon filière (INFO ou GEST)
    $transversal = [$courseMap['UNI-ETH'], $courseMap['UNI-TIC']];
    $infoPack = [$courseMap['INFO-ALG'], $courseMap['INFO-POO'], $courseMap['INFO-BDD'], $courseMap['INFO-RES']];
    $gestPack = [$courseMap['GEST-CPT'], $courseMap['GEST-MKT'], $courseMap['GEST-GRH'], $courseMap['GEST-ECO']];

    $students2 = db_query(
        "SELECT e.id, cl.filiere
         FROM etudiants e
         JOIN classes_etablissement cl ON e.classe_id = cl.id
         ORDER BY e.id ASC"
    );

    $addedInscr = 0;
    foreach ($students2 as $s) {
        $sid = (int) $s['id'];
        $filiere = trim((string) ($s['filiere'] ?? ''));

        $nb = db_query_single('SELECT COUNT(*) AS c FROM inscriptions WHERE etudiant_id = ?', [$sid]);
        $has = (int) ($nb['c'] ?? 0) > 0;

        $toAdd = $transversal;
        if (!$has) {
            if (stripos($filiere, 'info') !== false) {
                $toAdd = array_merge($toAdd, $infoPack);
            } elseif (stripos($filiere, 'gest') !== false) {
                $toAdd = array_merge($toAdd, $gestPack);
            } else {
                // Filière inconnue : on met juste un pack “informatique” minimal
                $toAdd = array_merge($toAdd, [$courseMap['INFO-ALG'], $courseMap['INFO-BDD']]);
            }
        }

        foreach ($toAdd as $cid) {
            $ok = db_exec('INSERT IGNORE INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)', [$sid, $cid]);
            if ($ok) {
                $addedInscr++;
            }
        }
    }

    $nbPromos = db_query_single('SELECT COUNT(*) AS c FROM classes_etablissement WHERE actif = 1');
    $nbSec = db_query_single("SELECT COUNT(*) AS c FROM classes_etablissement WHERE (code LIKE 'SEC-%' OR niveau='Secondaire' OR filiere='Secondaire')");

    echo "OK — Université uniquement : affectations terminées.\n";
    echo "- Promotions actives (total) : " . (int) ($nbPromos['c'] ?? 0) . "\n";
    echo "- Promotions \"Secondaire\" supprimées : {$secCount}\n";
    echo "- Promotions \"Secondaire\" restantes : " . (int) ($nbSec['c'] ?? 0) . "\n";
    echo "- Étudiants gardant une promo valide : {$keptPromo}\n";
    echo "- Étudiants réaffectés vers une promo LMD : {$updatedPromo}\n";
    echo "- Inscriptions ajoutées (INSERT IGNORE) : {$addedInscr}\n\n";
    echo "Tu peux maintenant utiliser l'import CSV et/ou l'app Flutter.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erreur : ' . $e->getMessage() . "\n";
}

