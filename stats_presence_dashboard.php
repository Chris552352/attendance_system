<?php
/**
 * Statistiques de présence (local) : taux par cours et aperçu mensuel simplifié.
 */
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once __DIR__ . '/includes/seance_mode.php';

require_auth();

$mois = isset($_GET['mois']) ? (int) $_GET['mois'] : (int) date('n');
$annee = isset($_GET['annee']) ? (int) $_GET['annee'] : (int) date('Y');
if ($mois < 1 || $mois > 12) {
    $mois = (int) date('n');
}

$sql_cours = $_SESSION['user_role'] === 'admin'
    ? 'SELECT id, code, nom FROM cours ORDER BY nom'
    : 'SELECT id, code, nom FROM cours WHERE enseignant_id = ? ORDER BY nom';
$params_cours = $_SESSION['user_role'] === 'admin' ? [] : [$_SESSION['user_id']];
$cours_list = db_query($sql_cours, $params_cours);

$stats = [];
foreach ($cours_list as $c) {
    $cid = (int) $c['id'];
    $row = db_query_single(
        "SELECT 
            SUM(CASE WHEN p.statut IN ('present','retard') THEN 1 ELSE 0 END) AS ok,
            SUM(CASE WHEN p.statut = 'absent' THEN 1 ELSE 0 END) AS absents,
            COUNT(*) AS total
         FROM presences p
         WHERE p.cours_id = ?
           AND MONTH(p.date_presence) = ?
           AND YEAR(p.date_presence) = ?",
        [$cid, $mois, $annee]
    );
    $tot = (int) ($row['total'] ?? 0);
    $okc = (int) ($row['ok'] ?? 0);
    $stats['cours'][$cid] = [
        'nom' => $c['nom'],
        'code' => $c['code'],
        'taux' => $tot > 0 ? round(100 * $okc / $tot, 1) : null,
        'total' => $tot,
    ];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-3">Statistiques de présence</h1>
        <p class="text-muted">Vue locale (Mois <?php echo $mois; ?> / <?php echo $annee; ?>). Les exports PDF signés et alertes e-mail peuvent être branchés sur les tables <code>presences</code>, <code>seuils_alerte</code>, <code>retards</code>.</p>

        <form method="get" class="row g-2 mb-4">
            <div class="col-auto">
                <select name="mois" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m === $mois ? 'selected' : ''; ?>><?php echo $m; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <input type="number" name="annee" class="form-control" value="<?php echo $annee; ?>" min="2020" max="2100">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Actualiser</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Cours</th><th>Présences enregistrées</th><th>Taux présence / retard</th></tr></thead>
                <tbody>
                <?php foreach ($stats['cours'] ?? [] as $cid => $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['code'] . ' — ' . $s['nom']); ?></td>
                        <td><?php echo (int) $s['total']; ?></td>
                        <td><?php echo $s['taux'] === null ? '—' : htmlspecialchars((string) $s['taux']) . ' %'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
