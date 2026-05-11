<?php
/**
 * Import d'étudiants depuis un fichier CSV (UTF-8), réservé à l'admin.
 * Colonnes obligatoires : nom, prenom, email, code_promotion (code d'une promotion existante).
 * Optionnel : matricule (sinon généré), cours (codes séparés par ; ou , ex. CS103;FR835).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config_app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/ensure_classes_etablissement_schema.php';

require_auth();
require_admin();

ensure_classes_etablissement_schema();

$rapport = null;

/**
 * @return array{0: string, 1: array<int, array<int, string>>, 2: array<int, string>}
 */
function import_etudiants_csv_parse(string $raw): array
{
    if (strncmp($raw, "\xEF\xBB\xBF", 3) === 0) {
        $raw = substr($raw, 3);
    }
    $lines = preg_split("/\r\n|\r|\n/", $raw);
    $lines = array_values(array_filter($lines, static function ($l) {
        return trim((string) $l) !== '';
    }));
    if ($lines === []) {
        return [';', [], []];
    }
    $first = $lines[0];
    $sc = substr_count($first, ';');
    $cc = substr_count($first, ',');
    $delim = $sc >= $cc ? ';' : ',';
    $header = str_getcsv($lines[0], $delim);
    $rows = [];
    for ($i = 1, $n = count($lines); $i < $n; $i++) {
        $rows[] = str_getcsv($lines[$i], $delim);
    }

    return [$delim, $rows, $header];
}

function import_etudiants_normalize_key(string $h): string
{
    $h = trim(function_exists('mb_strtolower') ? mb_strtolower($h) : strtolower($h));
    $h = str_replace([' ', '-'], '_', $h);

    return $h;
}

/**
 * @param array<int, string> $header
 * @return array<string, int>
 */
function import_etudiants_map_columns(array $header): array
{
    $map = [];
    foreach ($header as $i => $h) {
        $k = import_etudiants_normalize_key($h);
        if (
            $k === 'code_promotion'
            || $k === 'code'
            || $k === 'code_promo'
            || $k === 'code_classe'
            || $k === 'promotion'
            || $k === 'promotion_code'
        ) {
            $map['code_promotion'] = $i;
        } else {
            $map[$k] = $i;
        }
    }

    return $map;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_csv'])) {
    $f = $_FILES['fichier_csv'];
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $rapport = ['ok' => false, 'erreur' => 'Envoi du fichier impossible.'];
    } elseif (($f['size'] ?? 0) > 5 * 1024 * 1024) {
        $rapport = ['ok' => false, 'erreur' => 'Fichier trop volumineux (max 5 Mo).'];
    } else {
        $raw = file_get_contents((string) $f['tmp_name']);
        if ($raw === false || $raw === '') {
            $rapport = ['ok' => false, 'erreur' => 'Fichier vide ou illisible.'];
        } else {
            /** @var array $parsed */
            $parsed = import_etudiants_csv_parse($raw);
            $header = $parsed[2] ?? [];
            $rows = $parsed[1] ?? [];
            $map = import_etudiants_map_columns($header);
            $req = ['nom', 'prenom', 'email', 'code_promotion'];
            $manque = [];
            foreach ($req as $r) {
                if (!isset($map[$r])) {
                    $manque[] = $r;
                }
            }
            if ($manque !== []) {
                $rapport = [
                    'ok' => false,
                    'erreur' => 'Colonnes manquantes : ' . implode(', ', $manque) . '. Utilisez la première ligne avec : nom;prenom;email;code_promotion',
                ];
            } else {
                $hash = password_hash(MOT_DE_PASSE_UNIVERSEL_ETUDIANT, PASSWORD_DEFAULT);
                $ok = 0;
                $lignes_err = [];
                $nrow = 1;
                foreach ($rows as $cells) {
                    $nrow++;
                    $nom = trim((string) ($cells[$map['nom']] ?? ''));
                    $prenom = trim((string) ($cells[$map['prenom']] ?? ''));
                    $email = trim((string) ($cells[$map['email']] ?? ''));
                    $codePromo = trim((string) ($cells[$map['code_promotion']] ?? ''));
                    $matricule = isset($map['matricule']) ? trim((string) ($cells[$map['matricule']] ?? '')) : '';
                    $coursRaw = isset($map['cours']) ? trim((string) ($cells[$map['cours']] ?? '')) : '';

                    if ($nom === '' || $prenom === '' || $email === '' || $codePromo === '') {
                        $lignes_err[] = "Ligne {$nrow} : champs obligatoires vides.";
                        continue;
                    }
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $lignes_err[] = "Ligne {$nrow} : email invalide ({$email}).";
                        continue;
                    }
                    $cl = db_query_single(
                        "SELECT id FROM classes_etablissement
                         WHERE code = ? AND actif = 1
                           AND TRIM(niveau) <> '' AND TRIM(filiere) <> ''",
                        [$codePromo]
                    );
                    if (!$cl) {
                        $lignes_err[] = "Ligne {$nrow} : code promotion « {$codePromo} » introuvable ou incomplet (niveau/filière).";
                        continue;
                    }
                    $classeId = (int) $cl['id'];
                    if (email_existe($email, 'etudiants', null)) {
                        $lignes_err[] = "Ligne {$nrow} : email déjà utilisé ({$email}).";
                        continue;
                    }
                    if ($matricule !== '') {
                        $exm = db_query_single('SELECT id FROM etudiants WHERE matricule = ?', [$matricule]);
                        if ($exm) {
                            $lignes_err[] = "Ligne {$nrow} : matricule déjà utilisé ({$matricule}).";
                            continue;
                        }
                    } else {
                        do {
                            $matricule = generer_id_etudiant();
                        } while (db_query_single('SELECT id FROM etudiants WHERE matricule = ?', [$matricule]));
                    }

                    try {
                        db_exec(
                            'INSERT INTO etudiants (nom, prenom, matricule, email, classe_id) VALUES (?, ?, ?, ?, ?)',
                            [$nom, $prenom, $matricule, $email, $classeId]
                        );
                        $eid = (int) db_last_insert_id();
                        db_exec(
                            'INSERT INTO comptes_etudiants (etudiant_id, email, mot_de_passe, actif) VALUES (?, ?, ?, 1)',
                            [$eid, $email, $hash]
                        );
                        if ($coursRaw !== '') {
                            $parts = preg_split('/[;,]+/', $coursRaw, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($parts as $pc) {
                                $codeC = trim($pc);
                                if ($codeC === '') {
                                    continue;
                                }
                                $c = db_query_single('SELECT id FROM cours WHERE code = ?', [$codeC]);
                                if ($c) {
                                    db_exec(
                                        'INSERT IGNORE INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)',
                                        [$eid, (int) $c['id']]
                                    );
                                }
                            }
                        }
                        $ok++;
                    } catch (Throwable $e) {
                        $lignes_err[] = 'Ligne ' . $nrow . ' : ' . $e->getMessage();
                    }
                }
                $rapport = [
                    'ok' => true,
                    'importes' => $ok,
                    'erreurs_lignes' => $lignes_err,
                ];
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-csv"></i> Import étudiants (CSV)</h1>
        <div>
            <a href="assets/modeles/import_etudiants_exemple.csv" class="btn btn-outline-primary btn-sm" download>
                <i class="fas fa-download"></i> Modèle CSV
            </a>
            <a href="imports_csv.php" class="btn btn-outline-secondary btn-sm">Autres imports</a>
            <a href="etudiants.php" class="btn btn-outline-secondary btn-sm">Étudiants</a>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>Première ligne :</strong> en-têtes <code>nom</code>, <code>prenom</code>, <code>email</code>,
        <code>code_promotion</code> (code exact d’une promotion dans
        <a href="classes_etablissement.php">Promotions (LMD)</a>).
        Séparateur <code>;</code> ou <code>,</code>. Encodage <strong>UTF-8</strong>.<br>
        <strong>Optionnel :</strong> <code>matricule</code> ; <code>cours</code> = codes matières séparés par <code>;</code> ou <code>,</code> (ex. <code>CS103;FR835</code>).<br>
        Mot de passe initial : celui défini dans la config (<code>config_app.php</code>), en général <code>12345</code>.
    </div>

    <?php if (is_array($rapport)): ?>
        <?php if (!empty($rapport['ok'])): ?>
            <div class="alert alert-success">
                Import terminé : <strong><?= (int) ($rapport['importes'] ?? 0) ?></strong> étudiant(s) créé(s).
            </div>
            <?php if (!empty($rapport['erreurs_lignes'])): ?>
                <div class="alert alert-warning">
                    <strong>Avertissements / lignes ignorées :</strong>
                    <ul class="mb-0 small">
                        <?php foreach ($rapport['erreurs_lignes'] as $msg): ?>
                            <li><?= htmlspecialchars((string) $msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) ($rapport['erreur'] ?? 'Erreur.')) ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow-sm" style="max-width: 540px;">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Fichier .csv</label>
                    <input type="file" name="fichier_csv" class="form-control" accept=".csv,text/csv" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Importer</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
