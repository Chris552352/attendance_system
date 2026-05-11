<?php
/**
 * Promotions universitaires : niveau (L1, M1…), filière, groupes (admin).
 */

require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/ensure_classes_etablissement_schema.php';

require_auth();
require_admin();

ensure_classes_etablissement_schema();

$message = '';
$type_message = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $code = trim((string) ($_POST['code'] ?? ''));
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $niveau = trim((string) ($_POST['niveau'] ?? ''));
        $filiere = trim((string) ($_POST['filiere'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($code === '' || $nom === '' || $niveau === '' || $filiere === '') {
            $message = 'Le code, le nom du groupe, le niveau (LMD) et la filière sont obligatoires.';
            $type_message = 'danger';
        } else {
            $exists = db_query_single('SELECT id FROM classes_etablissement WHERE code = ?', [$code]);
            if ($exists) {
                $message = 'Ce code existe déjà.';
                $type_message = 'warning';
            } else {
                db_exec(
                    'INSERT INTO classes_etablissement (code, nom, niveau, filiere, description, actif) VALUES (?, ?, ?, ?, ?, 1)',
                    [
                        $code,
                        $nom,
                        $niveau,
                        $filiere,
                        $description !== '' ? $description : null,
                    ]
                );
                $message = 'Promotion / groupe créé.';
                $type_message = 'success';
            }
        }
    } elseif ($action === 'toggle' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $row = db_query_single('SELECT actif FROM classes_etablissement WHERE id = ?', [$id]);
        if ($row) {
            $new = (int) $row['actif'] === 1 ? 0 : 1;
            db_exec('UPDATE classes_etablissement SET actif = ? WHERE id = ?', [$new, $id]);
            $message = $new === 1 ? 'Classe réactivée.' : 'Classe désactivée (les étudiants restent liés, mais la classe ne sera plus proposée).';
            $type_message = 'success';
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $row = db_query_single(
            'SELECT id, code, nom, (SELECT COUNT(*) FROM etudiants e WHERE e.classe_id = classes_etablissement.id) AS nb
             FROM classes_etablissement
             WHERE id = ?',
            [$id]
        );
        if (!$row) {
            $message = 'Promotion introuvable.';
            $type_message = 'danger';
        } else {
            $nb = (int) ($row['nb'] ?? 0);
            if ($nb > 0) {
                $message = 'Impossible de supprimer : des étudiants sont encore rattachés à cette promotion. Réaffectez-les d’abord, ou désactivez la promotion.';
                $type_message = 'warning';
            } else {
                db_exec('DELETE FROM classes_etablissement WHERE id = ?', [$id]);
                $message = 'Promotion supprimée.';
                $type_message = 'success';
            }
        }
    }
}

$classes = db_query(
    'SELECT c.*, (SELECT COUNT(*) FROM etudiants e WHERE e.classe_id = c.id) AS nb_etudiants
     FROM classes_etablissement c
     ORDER BY c.actif DESC, c.niveau, c.filiere, c.code'
);

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-university"></i> Promotions &amp; groupes (université)</h1>
        <a href="etudiants.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Étudiants</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($type_message) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>Nouvelle promotion / groupe</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Ex. code <code>L2-INFO-A</code> : niveau <strong>Licence 2</strong>, filière <strong>Informatique</strong>, groupe A.
                    </p>
                    <form method="post">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required maxlength="40" placeholder="Ex. L2-INFO-A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Intitulé du groupe <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" required maxlength="180" placeholder="Ex. Groupe A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Niveau (LMD) <span class="text-danger">*</span></label>
                            <input type="text" name="niveau" class="form-control" maxlength="80" required placeholder="Ex. Licence 1, Master 2…">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Filière / parcours <span class="text-danger">*</span></label>
                            <input type="text" name="filiere" class="form-control" maxlength="120" required placeholder="Ex. Informatique, Gestion, Droit…">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (optionnel)</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Créer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Liste des promotions / groupes</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Groupe</th>
                                    <th>Niveau</th>
                                    <th>Filière</th>
                                    <th class="text-center">Étudiants</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($classes)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune promotion.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($classes as $c): ?>
                                        <tr class="<?= (int) $c['actif'] !== 1 ? 'table-secondary' : '' ?>">
                                            <td><code><?= htmlspecialchars($c['code']) ?></code></td>
                                            <td><?= htmlspecialchars($c['nom']) ?></td>
                                            <td><?= htmlspecialchars($c['niveau'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($c['filiere'] ?? '') ?></td>
                                            <td class="text-center"><?= (int) $c['nb_etudiants'] ?></td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Confirmer ?');">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                                        <?php if ((int) $c['actif'] === 1): ?>
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">Désactiver</button>
                                                        <?php else: ?>
                                                            <button type="submit" class="btn btn-sm btn-outline-success">Réactiver</button>
                                                        <?php endif; ?>
                                                    </form>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Supprimer définitivement cette promotion ?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" <?= (int) $c['nb_etudiants'] > 0 ? 'disabled' : '' ?>>
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p class="small text-muted mt-2">
                L'affectation des étudiants se fait dans <a href="ajouter_etudiant.php">Ajouter / modifier un étudiant</a> (promotion / groupe).
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
