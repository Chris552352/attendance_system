<?php
/**
 * Gestion des salles (admin) — université.
 *
 * Table: salles (créée par ensure_presence_avancee_schema).
 * Actions : créer, modifier (simple), activer/désactiver, supprimer (si possible).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/ensure_presence_avancee_schema.php';

require_auth();
require_admin();

ensure_presence_avancee_schema();

$message = '';
$type_message = 'info';

/**
 * @return array{nom:string, batiment:string, capacite:int, equipements:string}
 */
function salle_form_defaults(): array
{
    return [
        'nom' => '',
        'batiment' => '',
        'capacite' => 30,
        'equipements' => '',
    ];
}

$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editing = $edit_id > 0;
$form = salle_form_defaults();
if ($editing) {
    $row = db_query_single('SELECT * FROM salles WHERE id = ?', [$edit_id]);
    if ($row) {
        $form['nom'] = (string) ($row['nom'] ?? '');
        $form['batiment'] = (string) ($row['batiment'] ?? '');
        $form['capacite'] = (int) ($row['capacite'] ?? 30);
        $form['equipements'] = (string) ($row['equipements'] ?? '');
    } else {
        $editing = false;
        $edit_id = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create' || $action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $batiment = trim((string) ($_POST['batiment'] ?? ''));
        $cap = (int) ($_POST['capacite'] ?? 30);
        $equip = trim((string) ($_POST['equipements'] ?? ''));

        if ($nom === '') {
            $message = 'Le nom de la salle est obligatoire.';
            $type_message = 'danger';
        } elseif ($cap <= 0) {
            $message = 'La capacité doit être supérieure à 0.';
            $type_message = 'danger';
        } else {
            if ($action === 'create') {
                $exists = db_query_single('SELECT id FROM salles WHERE nom = ?', [$nom]);
                if ($exists) {
                    $message = 'Une salle avec ce nom existe déjà.';
                    $type_message = 'warning';
                } else {
                    db_exec(
                        'INSERT INTO salles (nom, batiment, capacite, equipements, actif) VALUES (?, ?, ?, ?, 1)',
                        [$nom, $batiment, $cap, $equip !== '' ? $equip : null]
                    );
                    $message = 'Salle ajoutée.';
                    $type_message = 'success';
                }
            } else {
                if ($id <= 0) {
                    $message = 'Salle invalide.';
                    $type_message = 'danger';
                } else {
                    $dup = db_query_single('SELECT id FROM salles WHERE nom = ? AND id <> ?', [$nom, $id]);
                    if ($dup) {
                        $message = 'Une autre salle utilise déjà ce nom.';
                        $type_message = 'warning';
                    } else {
                        db_exec(
                            'UPDATE salles SET nom = ?, batiment = ?, capacite = ?, equipements = ? WHERE id = ?',
                            [$nom, $batiment, $cap, $equip !== '' ? $equip : null, $id]
                        );
                        $message = 'Salle mise à jour.';
                        $type_message = 'success';
                    }
                }
            }
        }
    } elseif ($action === 'toggle' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $row = db_query_single('SELECT actif FROM salles WHERE id = ?', [$id]);
        if ($row) {
            $new = (int) $row['actif'] === 1 ? 0 : 1;
            db_exec('UPDATE salles SET actif = ? WHERE id = ?', [$new, $id]);
            $message = $new === 1 ? 'Salle réactivée.' : 'Salle désactivée.';
            $type_message = 'success';
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $row = db_query_single('SELECT id, nom FROM salles WHERE id = ?', [$id]);
        if (!$row) {
            $message = 'Salle introuvable.';
            $type_message = 'danger';
        } else {
            // Séances peuvent référencer salle_id : on les met à NULL pour permettre la suppression.
            try {
                db_exec('UPDATE seances SET salle_id = NULL WHERE salle_id = ?', [$id]);
            } catch (Throwable $e) {
            }
            db_exec('DELETE FROM salles WHERE id = ?', [$id]);
            $message = 'Salle supprimée.';
            $type_message = 'success';
        }
    }
}

$salles = db_query(
    'SELECT s.*,
            (SELECT COUNT(*) FROM seances se WHERE se.salle_id = s.id) AS nb_seances
     FROM salles s
     ORDER BY s.actif DESC, s.batiment, s.nom'
);

include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-door-open"></i> Salles (université)</h1>
        <div class="d-flex gap-2">
            <a href="salles.php" class="btn btn-outline-secondary btn-sm">Rafraîchir</a>
            <a href="presence_etablissement_superieur.php" class="btn btn-outline-secondary btn-sm">Feuille d’émargement</a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($type_message) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong><?= $editing ? 'Modifier une salle' : 'Ajouter une salle' ?></strong>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
                        <input type="hidden" name="id" value="<?= (int) $edit_id ?>">

                        <div class="mb-3">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input name="nom" class="form-control" required maxlength="50" value="<?= htmlspecialchars($form['nom']) ?>" placeholder="Ex. Amphi A101">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bâtiment / bloc</label>
                            <input name="batiment" class="form-control" maxlength="50" value="<?= htmlspecialchars($form['batiment']) ?>" placeholder="Ex. Bâtiment A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacité <span class="text-danger">*</span></label>
                            <input name="capacite" type="number" min="1" class="form-control" required value="<?= (int) $form['capacite'] ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Équipements</label>
                            <textarea name="equipements" class="form-control" rows="2" placeholder="Ex. Projecteur, Wi‑Fi, prises…"><?= htmlspecialchars($form['equipements']) ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $editing ? 'Enregistrer' : 'Ajouter' ?>
                            </button>
                            <?php if ($editing): ?>
                                <a href="salles.php" class="btn btn-outline-secondary">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="alert alert-info mt-3 small">
                Si tu construis de nouveaux bâtiments/salles, ajoute-les ici : elles seront disponibles pour associer les séances (champ <code>salle_id</code>).
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Liste des salles</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Bâtiment</th>
                                    <th class="text-center">Cap.</th>
                                    <th class="text-center">Séances</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($salles)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune salle.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($salles as $s): ?>
                                        <tr class="<?= (int) $s['actif'] !== 1 ? 'table-secondary' : '' ?>">
                                            <td><?= htmlspecialchars((string) ($s['nom'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($s['batiment'] ?? '')) ?></td>
                                            <td class="text-center"><?= (int) ($s['capacite'] ?? 0) ?></td>
                                            <td class="text-center"><?= (int) ($s['nb_seances'] ?? 0) ?></td>
                                            <td class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <a class="btn btn-sm btn-outline-primary" href="salles.php?edit=<?= (int) $s['id'] ?>">Modifier</a>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Confirmer ?');">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                                        <?php if ((int) $s['actif'] === 1): ?>
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">Désactiver</button>
                                                        <?php else: ?>
                                                            <button type="submit" class="btn btn-sm btn-outline-success">Réactiver</button>
                                                        <?php endif; ?>
                                                    </form>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Supprimer définitivement cette salle ? (les séances seront détachées)');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
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
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

