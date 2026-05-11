<?php
/**
 * Création de séance : QR (CM/TD/TP) ou examen/contrôle (enseignant seul).
 */

session_start();
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once __DIR__ . '/includes/seance_mode.php';

if (!est_connecte()) {
    rediriger('login.php');
}

$types_seances = [];
$salles = [];
try {
    $types_seances = db_query('SELECT id, nom, code, mode_marquage, duree_minutes FROM types_seances ORDER BY nom');
} catch (Throwable $e) {
    $types_seances = [];
}
try {
    $salles = db_query('SELECT id, nom, batiment, capacite FROM salles WHERE actif = 1 ORDER BY nom');
} catch (Throwable $e) {
    $salles = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_cours = trim($_POST['nom_cours'] ?? '');
    $duree_minutes = max(1, (int) ($_POST['duree_minutes'] ?? 10));
    $type_seance_id = (int) ($_POST['type_seance_id'] ?? 0);
    $salle_id = (int) ($_POST['salle_id'] ?? 0);

    if ($nom_cours === '' || $type_seance_id <= 0) {
        $error = 'Veuillez indiquer le cours et le type de séance.';
    } else {
        $cours = db_query_single('SELECT * FROM cours WHERE nom = ?', [$nom_cours]);
        $type_row = db_query_single('SELECT * FROM types_seances WHERE id = ?', [$type_seance_id]);
        if (!$cours) {
            $error = "Erreur : la matière ou le cours demandé n'existe pas.";
        } elseif (!$type_row) {
            $error = 'Type de séance invalide.';
        } else {
            $mode_marquage = $type_row['mode_marquage'] ?? 'qr';
            $heure_debut = date('H:i:s');
            $heure_fin = date('H:i:s', strtotime("+{$duree_minutes} minutes"));
            $token = bin2hex(random_bytes(16));
            if ($mode_marquage === 'qr') {
                $expiration = date('Y-m-d H:i:s', time() + ($duree_minutes * 60));
            } else {
                $expiration = date('Y-m-d H:i:s', strtotime('+48 hours'));
            }

            $conflit_id = null;
            if ($salle_id > 0) {
                $conflit = db_query_single(
                    'SELECT s.id FROM seances s
                     WHERE s.salle_id = ?
                       AND s.active = 1
                       AND DATE(COALESCE(s.date_seance, s.date_creation)) = CURDATE()
                       AND (s.statut_seance IS NULL OR s.statut_seance IN (\'planifiee\',\'en_cours\'))
                       AND s.heure_debut IS NOT NULL AND s.heure_fin IS NOT NULL
                       AND NOT (s.heure_fin <= ? OR s.heure_debut >= ?)',
                    [$salle_id, $heure_debut, $heure_fin]
                );
                if ($conflit) {
                    $conflit_id = (int) $conflit['id'];
                }
            }

            if ($conflit_id !== null) {
                $error = 'Conflit : une autre séance utilise déjà cette salle sur ce créneau aujourd’hui (#' . $conflit_id . ').';
            } else {
                $sid = $salle_id > 0 ? $salle_id : null;
                $ok = db_exec(
                    'INSERT INTO seances (nom_cours, enseignant_id, token, expiration, salle_id, type_seance_id,
                        heure_debut, heure_fin, mode_marquage, statut_seance)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'en_cours\')',
                    [
                        $nom_cours,
                        $_SESSION['user_id'],
                        $token,
                        $expiration,
                        $sid,
                        $type_seance_id,
                        $heure_debut,
                        $heure_fin,
                        $mode_marquage,
                    ]
                );
                if ($ok) {
                    $seance_id = (int) db_last_insert_id();
                    if ($mode_marquage === 'enseignant_seul') {
                        header('Location: presence_etablissement_superieur.php?seance_id=' . $seance_id);
                        exit;
                    }
                    header('Location: afficher_qr.php?seance=' . $seance_id);
                    exit;
                }
                $error = 'Erreur lors de la création de la séance.';
            }
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Nouvelle séance de présence</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-qrcode"></i> Paramètres</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nom_cours" class="form-label">Nom du cours (exactement comme dans « Cours »)</label>
                                <input type="text" class="form-control" id="nom_cours" name="nom_cours"
                                       placeholder="Ex: Réseaux Informatiques" required
                                       value="<?php echo htmlspecialchars($_POST['nom_cours'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="type_seance_id" class="form-label">Type de séance</label>
                                <select class="form-select" id="type_seance_id" name="type_seance_id" required>
                                    <option value="">— Choisir —</option>
                                    <?php foreach ($types_seances as $t): ?>
                                        <option value="<?php echo (int) $t['id']; ?>"
                                            <?php echo (isset($_POST['type_seance_id']) && (int) $_POST['type_seance_id'] === (int) $t['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($t['nom'] . ' (' . $t['code'] . ') — ' . $t['mode_marquage']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Examen / contrôle : seul l’enseignant marque les présences (pas de scan étudiant).</small>
                            </div>

                            <div class="mb-3">
                                <label for="salle_id" class="form-label">Salle (planning &amp; capacité)</label>
                                <select class="form-select" id="salle_id" name="salle_id">
                                    <option value="0">— Non renseignée —</option>
                                    <?php foreach ($salles as $s): ?>
                                        <option value="<?php echo (int) $s['id']; ?>"
                                            <?php echo (isset($_POST['salle_id']) && (int) $_POST['salle_id'] === (int) $s['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['nom'] . ' — cap. ' . (int) $s['capacite']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="duree_minutes" class="form-label">Durée / validité QR (minutes)</label>
                                <select class="form-control" id="duree_minutes" name="duree_minutes">
                                    <?php foreach ([5, 10, 15, 30, 45, 60, 90, 120] as $dm): ?>
                                        <option value="<?php echo $dm; ?>" <?php echo ((int) ($_POST['duree_minutes'] ?? 10) === $dm) ? 'selected' : ''; ?>><?php echo $dm; ?> min</option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Pour les séances QR : fenêtre de scan. Pour examen/contrôle : durée indicative (créneau horaire).</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-play"></i> Créer la séance
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Règles</h5>
                    </div>
                    <div class="card-body small">
                        <p><strong>CM / TD / TP</strong> : QR affiché, les étudiants pointent avec l’app ou la page web.</p>
                        <p><strong>Examen / contrôle</strong> : redirection vers la feuille d’émargement enseignant (pas de pointage étudiant).</p>
                        <p><strong>Conférence</strong> : mode manuel (pas de scan étudiant automatique).</p>
                        <p class="mb-0"><a href="presence_etablissement_superieur.php">Ouvrir la feuille d’émargement avancée</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
