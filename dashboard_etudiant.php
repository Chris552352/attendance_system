<?php
/**
 * Tableau de bord étudiant : profil, cours / enseignants, présences, liens utiles
 */

session_start();
require_once 'config/database.php';

if (!isset($_SESSION['etudiant_id'])) {
    header('Location: login_etudiant.php');
    exit();
}

$etudiant_id = (int) $_SESSION['etudiant_id'];

$etudiant = db_query_single(
    'SELECT e.*, ce.email AS compte_email
     FROM etudiants e
     LEFT JOIN comptes_etudiants ce ON ce.etudiant_id = e.id AND ce.actif = 1
     WHERE e.id = ?',
    [$etudiant_id]
);

if (!$etudiant) {
    session_destroy();
    header('Location: login_etudiant.php');
    exit();
}

$email_affiche = $etudiant['compte_email'] ?: $etudiant['email'];
$nom_complet = trim($etudiant['prenom'] . ' ' . $etudiant['nom']);

$mes_cours = db_query(
    'SELECT c.id, c.code, c.nom, c.description,
            u.prenom AS ens_prenom, u.nom AS ens_nom, u.email AS ens_email
     FROM inscriptions i
     JOIN cours c ON i.cours_id = c.id
     JOIN utilisateurs u ON c.enseignant_id = u.id
     WHERE i.etudiant_id = ?
     ORDER BY c.nom',
    [$etudiant_id]
);
if (!is_array($mes_cours)) {
    $mes_cours = [];
}

$sql_presences = 'SELECT p.*, c.nom AS cours_nom, c.code AS cours_code,
        s.nom_cours AS seance_nom,
        u.prenom AS ens_prenom, u.nom AS ens_nom
     FROM presences p
     LEFT JOIN cours c ON p.cours_id = c.id
     LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
     LEFT JOIN seances s ON p.seance_id = s.id
     WHERE p.etudiant_id = ?
     ORDER BY p.date_presence DESC, p.date_enregistrement DESC
     LIMIT 50';
$presences = db_query($sql_presences, [$etudiant_id]);
if (!is_array($presences)) {
    $presences = [];
}

$pending_justif = [];
if ($presences) {
    try {
        $ids = array_column($presences, 'id');
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $pj = db_query(
            "SELECT presence_id FROM justifications WHERE statut = 'en_attente' AND presence_id IN ($ph)",
            $ids
        );
        if (is_array($pj)) {
            foreach ($pj as $row) {
                $pending_justif[(int) $row['presence_id']] = true;
            }
        }
    } catch (Throwable $e) {
        // table justifications absente : ignorer
    }
}

$compte_existe = db_query_single(
    'SELECT id FROM comptes_etudiants WHERE etudiant_id = ? AND actif = 1',
    [$etudiant_id]
);

include 'includes/header_public.php';
?>

<div class="container mt-4 mb-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h3 class="mb-0"><i class="fas fa-user-graduate"></i> Tableau de bord étudiant</h3>
            <a href="logout_etudiant.php" class="btn btn-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-primary text-white py-2">
                            <i class="fas fa-id-card"></i> Mes informations
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Bienvenue,</strong> <?php echo htmlspecialchars($nom_complet); ?> !</p>
                            <p class="mb-1 text-muted small">Matricule : <?php echo htmlspecialchars($etudiant['matricule']); ?></p>
                            <p class="mb-1"><strong>Email :</strong> <?php echo htmlspecialchars($email_affiche); ?></p>
                            <?php if ($compte_existe): ?>
                                <a href="etudiant_mot_de_passe.php" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fas fa-key"></i> Changer mon mot de passe
                                </a>
                            <?php else: ?>
                                <p class="small text-muted mt-2 mb-0">Compte de connexion non activé : utilisez l’inscription ou contactez l’administration.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-info">
                        <div class="card-header bg-info text-white py-2">
                            <i class="fas fa-qrcode"></i> Pointage
                        </div>
                        <div class="card-body">
                            <p class="small mb-0">Scannez le QR code affiché en cours pour enregistrer votre présence. Connectez-vous avec l’email et le mot de passe fournis par l’établissement.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Mes cours et enseignants</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($mes_cours)): ?>
                        <p class="text-muted mb-0">Aucune inscription à un cours pour le moment.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Cours</th>
                                        <th>Enseignant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mes_cours as $c): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($c['code']); ?></td>
                                            <td><?php echo htmlspecialchars($c['nom']); ?></td>
                                            <td>
                                                <?php
                                                $ens = trim(($c['ens_prenom'] ?? '') . ' ' . ($c['ens_nom'] ?? ''));
                                                echo htmlspecialchars($ens ?: '—');
                                                ?>
                                                <?php if (!empty($c['ens_email'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($c['ens_email']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Mes présences</h5>
                    <a href="justifier_absence_new.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-file-alt"></i> Justifier (par matricule)
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($presences)): ?>
                        <p class="text-muted p-3 mb-0">Aucune présence enregistrée.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Cours / séance</th>
                                        <th>Enseignant</th>
                                        <th>Statut</th>
                                        <th>Détail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($presences as $p): ?>
                                        <?php
                                        $libelle = $p['seance_nom'] ?: $p['cours_nom'];
                                        if (!$libelle) {
                                            $libelle = '—';
                                        }
                                        $ens = trim(($p['ens_prenom'] ?? '') . ' ' . ($p['ens_nom'] ?? ''));
                                        ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($p['date_presence'])); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($libelle); ?>
                                                <?php if (!empty($p['cours_code'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($p['cours_code']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><small><?php echo htmlspecialchars($ens ?: '—'); ?></small></td>
                                            <td>
                                                <?php if ($p['statut'] === 'present'): ?>
                                                    <span class="badge bg-success">Présent</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Absent</span>
                                                    <?php if (!empty($p['justifie'])): ?>
                                                        <span class="badge bg-secondary">Justifié</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">Enreg. <?php echo date('d/m/Y H:i', strtotime($p['date_enregistrement'])); ?></small>
                                                <?php if ($p['statut'] === 'absent' && empty($p['justifie'])): ?>
                                                    <?php if (!empty($pending_justif[(int) $p['id']])): ?>
                                                        <span class="badge bg-warning text-dark">Justif. en attente</span>
                                                    <?php else: ?>
                                                        <a href="etudiant_justifier_absence.php?presence_id=<?php echo (int) $p['id']; ?>" class="btn btn-sm btn-outline-primary mt-1">Justifier</a>
                                                    <?php endif; ?>
                                                <?php elseif ($p['statut'] === 'absent' && !empty($p['justification'])): ?>
                                                    <small class="d-block text-muted"><?php
                                                    $j = (string) $p['justification'];
                                                    echo htmlspecialchars(strlen($j) > 80 ? substr($j, 0, 77) . '...' : $j);
                                                    ?></small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
