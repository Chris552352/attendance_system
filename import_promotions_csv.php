<?php
/**
 * Import de promotions (niveau + filière + groupe) depuis un CSV — admin uniquement.
 * Colonnes : code, nom, niveau, filière ; optionnel : description
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/ensure_classes_etablissement_schema.php';

require_auth();
require_admin();

ensure_classes_etablissement_schema();

$rapport = null;

function import_promotions_csv_parse(string $raw): array
{
    if (strncmp($raw, "\xEF\xBB\xBF", 3) === 0) {
        $raw = substr($raw, 3);
    }
    $lines = preg_split("/\r\n|\r|\n/", $raw);
    $lines = array_values(array_filter($lines, static function ($l) {
        return trim((string) $l) !== '';
    }));
    if ($lines === []) {
        return [[], []];
    }
    $first = $lines[0];
    $delim = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
    $header = str_getcsv($lines[0], $delim);
    $rows = [];
    for ($i = 1, $n = count($lines); $i < $n; $i++) {
        $rows[] = str_getcsv($lines[$i], $delim);
    }

    return [$header, $rows];
}

function import_promotions_norm(string $h): string
{
    $h = trim(function_exists('mb_strtolower') ? mb_strtolower($h) : strtolower($h));
    $h = str_replace([' ', '-'], '_', $h);

    return $h;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_csv'])) {
    $f = $_FILES['fichier_csv'];
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $rapport = ['ok' => false, 'erreur' => 'Envoi du fichier impossible.'];
    } elseif (($f['size'] ?? 0) > 2 * 1024 * 1024) {
        $rapport = ['ok' => false, 'erreur' => 'Fichier trop volumineux (max 2 Mo).'];
    } else {
        $raw = file_get_contents((string) $f['tmp_name']);
        if ($raw === false || $raw === '') {
            $rapport = ['ok' => false, 'erreur' => 'Fichier vide ou illisible.'];
        } else {
            [$header, $rows] = import_promotions_csv_parse($raw);
            $map = [];
            foreach ($header as $i => $h) {
                $map[import_promotions_norm($h)] = $i;
            }
            $req = ['code', 'nom', 'niveau', 'filiere'];
            $manque = [];
            foreach ($req as $r) {
                if (!isset($map[$r])) {
                    $manque[] = $r;
                }
            }
            if ($manque !== []) {
                $rapport = [
                    'ok' => false,
                    'erreur' => 'Colonnes obligatoires manquantes : ' . implode(', ', $manque),
                ];
            } else {
                $ok = 0;
                $err = [];
                $nrow = 1;
                foreach ($rows as $cells) {
                    $nrow++;
                    $code = trim((string) ($cells[$map['code']] ?? ''));
                    $nom = trim((string) ($cells[$map['nom']] ?? ''));
                    $niveau = trim((string) ($cells[$map['niveau']] ?? ''));
                    $filiere = trim((string) ($cells[$map['filiere']] ?? ''));
                    $desc = isset($map['description']) ? trim((string) ($cells[$map['description']] ?? '')) : '';

                    if ($code === '' || $nom === '' || $niveau === '' || $filiere === '') {
                        $err[] = "Ligne {$nrow} : code, nom, niveau et filière sont obligatoires.";
                        continue;
                    }
                    $ex = db_query_single('SELECT id FROM classes_etablissement WHERE code = ?', [$code]);
                    if ($ex) {
                        $err[] = "Ligne {$nrow} : code « {$code} » existe déjà — ignoré.";
                        continue;
                    }
                    try {
                        db_exec(
                            'INSERT INTO classes_etablissement (code, nom, niveau, filiere, description, actif) VALUES (?, ?, ?, ?, ?, 1)',
                            [
                                $code,
                                $nom,
                                $niveau,
                                $filiere,
                                $desc !== '' ? $desc : null,
                            ]
                        );
                        $ok++;
                    } catch (Throwable $e) {
                        $err[] = 'Ligne ' . $nrow . ' : ' . $e->getMessage();
                    }
                }
                $rapport = ['ok' => true, 'importes' => $ok, 'erreurs_lignes' => $err];
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-csv"></i> Import promotions (CSV)</h1>
        <div>
            <a href="assets/modeles/import_promotions_exemple.csv" class="btn btn-outline-primary btn-sm" download>
                <i class="fas fa-download"></i> Modèle CSV
            </a>
            <a href="imports_csv.php" class="btn btn-outline-secondary btn-sm">Autres imports</a>
            <a href="classes_etablissement.php" class="btn btn-outline-secondary btn-sm">Liste</a>
        </div>
    </div>

    <div class="alert alert-info">
        Colonnes obligatoires : <code>code</code>, <code>nom</code>, <code>niveau</code>, <code>filiere</code> ;
        optionnel : <code>description</code>. Séparateur <code>;</code> ou <code>,</code>. UTF-8.
    </div>

    <?php if (is_array($rapport)): ?>
        <?php if (!empty($rapport['ok'])): ?>
            <div class="alert alert-success">
                <strong><?= (int) ($rapport['importes'] ?? 0) ?></strong> promotion(s) créée(s).
            </div>
            <?php if (!empty($rapport['erreurs_lignes'])): ?>
                <div class="alert alert-warning">
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
