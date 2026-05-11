<?php
/**
 * Page de gestion des présences pour établissement supérieur
 * Support multi-salles, types de séances, mode examen (enseignant seul)
 */

require_once 'includes/auth.php';
require_once 'config/database.php';
require_once __DIR__ . '/includes/seance_mode.php';

// Vérifier l'authentification
require_auth();

// Récupérer les paramètres
$cours_id = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : 0;
$type_seance_id = isset($_GET['type_seance_id']) ? (int)$_GET['type_seance_id'] : 1;
$salle_id = isset($_GET['salle_id']) ? (int)$_GET['salle_id'] : 1;

// Récupérer les informations du cours et du type de séance
$cours = db_query_single("SELECT * FROM cours WHERE id = ?", [$cours_id]);
$type_seance = db_query_single("SELECT * FROM types_seances WHERE id = ?", [$type_seance_id]);
$salle = db_query_single("SELECT * FROM salles WHERE id = ?", [$salle_id]);

// Vérifier les conflits d'horaire et de salle
$conflits = [];
if ($cours && $type_seance && $salle) {
    $conflits = db_query("
        SELECT s.*, ts.nom as type_nom, c.nom as cours_nom
        FROM seances s
        JOIN types_seances ts ON s.type_seance_id = ts.id
        JOIN cours c ON s.enseignant_id = c.enseignant_id
        WHERE s.salle_id = ? 
        AND s.statut_seance IN ('planifiee', 'en_cours')
        AND DATE(s.date_creation) = CURDATE()
        AND (
            (s.heure_debut <= ? AND s.heure_fin > ?)
        )
    ", [$salle_id, date('H:i:s'), date('H:i:s')]);
}

// Créer une nouvelle séance si demandé
if (isset($_POST['creer_seance'])) {
    $nom_cours = $_POST['nom_cours'] ?? ($cours['nom'] ?? '');
    $duree_minutes = (int)($_POST['duree_minutes'] ?? $type_seance['duree_minutes'] ?? 60);
    $mode_marquage = $_POST['mode_marquage'] ?? $type_seance['mode_marquage'] ?? 'qr';
    
    // Calculer l'heure de fin
    $heure_debut = date('H:i:s');
    $heure_fin = date('H:i:s', strtotime("+$duree_minutes minutes"));
    
    // Token toujours présent (contrainte BDD) ; expiration longue si pas de QR étudiant
    $token = bin2hex(random_bytes(16));
    if ($mode_marquage === 'qr') {
        $expiration = date('Y-m-d H:i:s', strtotime("+{$duree_minutes} minutes"));
    } else {
        $expiration = date('Y-m-d H:i:s', strtotime('+48 hours'));
    }

    $ok = db_exec(
        'INSERT INTO seances (nom_cours, enseignant_id, token, expiration, salle_id, type_seance_id,
            heure_debut, heure_fin, mode_marquage, statut_seance)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'planifiee\')',
        [
            $nom_cours,
            $_SESSION['user_id'],
            $token,
            $expiration,
            $salle_id,
            $type_seance_id,
            $heure_debut,
            $heure_fin,
            $mode_marquage,
        ]
    );

    if ($ok) {
        $new_seance_id = (int) db_last_insert_id();
        db_exec('UPDATE seances SET statut_seance = \'en_cours\' WHERE id = ?', [$new_seance_id]);
        alerte('Séance créée avec succès !', 'success');
        rediriger('presence_etablissement_superieur.php?seance_id=' . $new_seance_id);
    } else {
        alerte('Erreur lors de la création de la séance.', 'danger');
    }
}

// Récupérer la séance active
$seance_id = isset($_GET['seance_id']) ? (int)$_GET['seance_id'] : 0;
$seance = null;
if ($seance_id > 0) {
    $seance = db_query_single("
        SELECT s.*, ts.nom as type_nom, ts.mode_marquage, ts.couleur,
               sa.nom as salle_nom, sa.capacite as salle_capacite,
               c.nom as cours_nom
        FROM seances s
        LEFT JOIN types_seances ts ON s.type_seance_id = ts.id
        LEFT JOIN salles sa ON s.salle_id = sa.id
        LEFT JOIN cours c ON s.nom_cours = c.nom
        WHERE s.id = ? AND s.enseignant_id = ?
    ", [$seance_id, $_SESSION['user_id']]);
}

// Marquer la présence (mode enseignant seul pour examens)
if (isset($_POST['marquer_presence']) && $seance && $seance['mode_marquage'] === 'enseignant_seul') {
    $etudiant_id = (int)$_POST['etudiant_id'];
    $statut = $_POST['statut'] ?? 'present';
    $heure_arrivee = $_POST['heure_arrivee'] ?? date('H:i:s');
    
    // Vérifier si la présence n'est pas déjà marquée
    $existante = db_query_single("
        SELECT id FROM presences 
        WHERE etudiant_id = ? AND seance_id = ? AND date_presence = CURDATE()
    ", [$etudiant_id, $seance_id]);
    
    if (!$existante) {
        // Récupérer le cours_id
        $cours_id = db_query_single("SELECT id FROM cours WHERE nom = ? LIMIT 1", [$seance['nom_cours']]);
        
        // Insérer la présence
        db_exec("
            INSERT INTO presences (etudiant_id, cours_id, seance_id, date_presence, statut, 
                                 heure_arrivee, enregistre_par, marque_par_enseignant)
            VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 1)
        ", [
            $etudiant_id,
            $cours_id['id'],
            $seance_id,
            $statut,
            $heure_arrivee,
            $_SESSION['user_id']
        ]);
        
        // Gérer les retards
        if ($statut === 'retard') {
            $seance_heure_debut = new DateTime($seance['heure_debut']);
            $arrivee = new DateTime($heure_arrivee);
            $retard_minutes = $arrivee > $seance_heure_debut ? $arrivee->diff($seance_heure_debut)->i : 0;
            
            if ($retard_minutes > 0) {
                db_exec("
                    INSERT INTO retards (etudiant_id, seance_id, minutes_retard)
                    VALUES (?, ?, ?)
                ", [$etudiant_id, $seance_id, $retard_minutes]);
            }
        }
        
        alerte("Présence marquée avec succès!", "success");
    } else {
        alerte("Présence déjà enregistrée pour cet étudiant.", "warning");
    }
}

// Récupérer les étudiants inscrits au cours
$etudiants = [];
if ($seance) {
    $cours_info = db_query_single("SELECT id FROM cours WHERE nom = ? LIMIT 1", [$seance['nom_cours']]);
    if ($cours_info) {
        $etudiants = db_query("
            SELECT e.*, 
                   p.id as presence_id,
                   p.statut as presence_statut,
                   p.heure_arrivee,
                   r.minutes_retard
            FROM etudiants e
            LEFT JOIN inscriptions i ON e.id = i.etudiant_id AND i.cours_id = ?
            LEFT JOIN presences p ON e.id = p.etudiant_id AND p.seance_id = ? AND p.date_presence = CURDATE()
            LEFT JOIN retards r ON e.id = r.etudiant_id AND r.seance_id = ?
            WHERE i.cours_id = ?
            ORDER BY e.nom, e.prenom
        ", [$cours_info['id'], $seance_id, $seance_id, $cours_info['id']]);
    }
}

// Statistiques de la séance
$stats = [];
if ($seance && $etudiants) {
    $stats['total'] = count($etudiants);
    $stats['presents'] = count(array_filter($etudiants, fn($e) => $e['presence_statut'] === 'present'));
    $stats['retards'] = count(array_filter($etudiants, fn($e) => $e['presence_statut'] === 'retard'));
    $stats['absents'] = count(array_filter($etudiants, fn($e) => $e['presence_statut'] === 'absent'));
    $stats['taux'] = $stats['total'] > 0 ? round(($stats['presents'] + $stats['retards']) / $stats['total'] * 100, 1) : 0;
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/presence-etablissement.css?v=<?= time() ?>">

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-check"></i> Gestion Présence - Établissement Supérieur</h1>
                <div>
                    <?php if (!$seance): ?>
                        <a href="cours.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour aux cours
                        </a>
                    <?php else: ?>
                        <button onclick="terminerSeance(<?= $seance_id ?>)" class="btn btn-danger">
                            <i class="fas fa-stop"></i> Terminer la séance
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$seance): ?>
        <!-- Création de séance -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-plus-circle"></i> Nouvelle Séance</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cours</label>
                                        <select name="cours_id" class="form-control" required>
                                            <?php
                                            $cours_list = db_query("SELECT c.*, u.nom as enseignant_nom FROM cours c LEFT JOIN utilisateurs u ON c.enseignant_id = u.id WHERE c.enseignant_id = ? OR ? = 'admin'", [$_SESSION['user_id'], $_SESSION['user_role']]);
                                            foreach ($cours_list as $c): ?>
                                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $cours_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c['nom']) ?> - <?= htmlspecialchars($c['enseignant_nom']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Type de Séance</label>
                                        <select name="type_seance_id" class="form-control" required onchange="updateModeMarquage(this.value)">
                                            <?php
                                            $types = db_query("SELECT * FROM types_seances ORDER BY nom");
                                            foreach ($types as $t): ?>
                                                <option value="<?= $t['id'] ?>" data-mode="<?= $t['mode_marquage'] ?>" <?= $t['id'] == $type_seance_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($t['nom']) ?> (<?= $t['mode_marquage'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Salle</label>
                                        <select name="salle_id" class="form-control" required>
                                            <?php
                                            $salles = db_query("SELECT * FROM salles WHERE actif = 1 ORDER BY batiment, nom");
                                            foreach ($salles as $s): ?>
                                                <option value="<?= $s['id'] ?>" <?= $s['id'] == $salle_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($s['nom']) ?> - <?= htmlspecialchars($s['batiment']) ?> (Capacité: <?= $s['capacite'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Durée (minutes)</label>
                                        <input type="number" name="duree_minutes" class="form-control" value="60" min="15" max="240" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nom de la séance</label>
                                <input type="text" name="nom_cours" class="form-control" value="<?= htmlspecialchars($cours['nom'] ?? '') ?>" required>
                            </div>
                            
                            <?php if (!empty($conflits)): ?>
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Conflits détectés:</h6>
                                    <ul>
                                        <?php foreach ($conflits as $conflit): ?>
                                            <li><?= htmlspecialchars($conflit['cours_nom']) ?> - <?= htmlspecialchars($conflit['type_nom']) ?> dans <?= htmlspecialchars($conflit['salle_nom']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" name="creer_seance" class="btn btn-primary">
                                <i class="fas fa-play"></i> Démarrer la séance
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-info-circle"></i> Modes de Marquage</h6>
                    </div>
                    <div class="card-body">
                        <div class="mode-info">
                            <div class="badge badge-success mb-2">QR Code</div>
                            <p>Les étudiants scannent le QR code pour marquer leur présence.</p>
                        </div>
                        <div class="mode-info">
                            <div class="badge badge-primary mb-2">Manuel</div>
                            <p>L'enseignant coche manuellement la présence des étudiants.</p>
                        </div>
                        <div class="mode-info">
                            <div class="badge badge-danger mb-2">Enseignant Seul</div>
                            <p><strong>Mode Examen:</strong> Seul l'enseignant peut marquer la présence.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Séance active -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="fas fa-play-circle"></i> 
                                    Séance Active: <?= htmlspecialchars($seance['nom_cours']) ?>
                                </h5>
                                <small>
                                    <?= htmlspecialchars($seance['type_nom']) ?> - 
                                    <?= htmlspecialchars($seance['salle_nom']) ?> -
                                    Mode: <strong><?= $seance['mode_marquage'] ?></strong>
                                </small>
                            </div>
                            <div class="text-right">
                                <div class="badge badge-<?= $seance['mode_marquage'] === 'enseignant_seul' ? 'danger' : 'success' ?> badge-lg">
                                    <?= $seance['mode_marquage'] === 'enseignant_seul' ? 'EXAMEN' : 'NORMAL' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <div class="stat-number"><?= $stats['total'] ?></div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box text-success">
                                    <div class="stat-number"><?= $stats['presents'] ?></div>
                                    <div class="stat-label">Présents</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box text-warning">
                                    <div class="stat-number"><?= $stats['retards'] ?></div>
                                    <div class="stat-label">Retards</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box text-danger">
                                    <div class="stat-number"><?= $stats['absents'] ?></div>
                                    <div class="stat-label">Absents</div>
                                </div>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: <?= $stats['taux'] ?>%">
                                Taux: <?= $stats['taux'] ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code pour mode normal -->
        <?php if ($seance['mode_marquage'] === 'qr' && $seance['token']): ?>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-header bg-success text-white">
                            <h6><i class="fas fa-qrcode"></i> QR Code de Présence</h6>
                        </div>
                        <div class="card-body">
                            <div id="qrcode"></div>
                            <p class="mt-2">
                                <small>Valide jusqu'à: <?= date('H:i', strtotime($seance['expiration'])) ?></small>
                            </p>
                            <button onclick="refreshQR()" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync"></i> Regénérer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Liste des étudiants -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-users"></i> Liste des Étudiants</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Statut</th>
                                        <?php if ($seance['mode_marquage'] === 'enseignant_seul'): ?>
                                            <th>Heure Arrivée</th>
                                            <th>Actions</th>
                                        <?php else: ?>
                                            <th>Heure Arrivée</th>
                                            <th>Retard</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($etudiants as $etudiant): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                                            <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                            <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                            <td>
                                                <?php if ($etudiant['presence_statut']): ?>
                                                    <span class="badge badge-<?= $etudiant['presence_statut'] === 'present' ? 'success' : ($etudiant['presence_statut'] === 'retard' ? 'warning' : 'danger') ?>">
                                                        <?= ucfirst($etudiant['presence_statut']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Non marqué</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $etudiant['heure_arrivee'] ? date('H:i', strtotime($etudiant['heure_arrivee'])) : '-' ?></td>
                                            <?php if ($seance['mode_marquage'] === 'enseignant_seul'): ?>
                                                <td>
                                                    <?php if (!$etudiant['presence_statut']): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="etudiant_id" value="<?= $etudiant['id'] ?>">
                                                            <input type="hidden" name="heure_arrivee" value="<?= date('H:i') ?>">
                                                            <select name="statut" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                                                <option value="present">Présent</option>
                                                                <option value="retard">Retard</option>
                                                                <option value="absent">Absent</option>
                                                            </select>
                                                            <button type="submit" name="marquer_presence" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-check"></i> Marquer
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle"></i> Déjà marqué
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php else: ?>
                                                <td><?= $etudiant['minutes_retard'] ? $etudiant['minutes_retard'] . ' min' : '-' ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
function updateModeMarquage(typeId) {
    const select = document.querySelector('select[name="type_seance_id"]');
    const selectedOption = select.options[select.selectedIndex];
    const mode = selectedOption.getAttribute('data-mode');
    
    // Mettre à jour l'affichage du mode
    const modeInfo = document.querySelector('.mode-info');
    if (modeInfo) {
        modeInfo.style.display = mode === 'enseignant_seul' ? 'block' : 'none';
    }
}

function terminerSeance(seanceId) {
    if (confirm('Êtes-vous sûr de vouloir terminer cette séance?')) {
        fetch('api/terminer_seance.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({seance_id: seanceId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Séance terminée avec succès!');
                window.location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function refreshQR() {
    if (confirm('Regénérer le QR code? L\'ancien sera invalide.')) {
        window.location.reload();
    }
}

// Générer le QR code si disponible
<?php if ($seance && $seance['mode_marquage'] === 'qr' && $seance['token']): ?>
document.addEventListener('DOMContentLoaded', function() {
    const qrContainer = document.getElementById('qrcode');
    if (qrContainer) {
        new QRCode(qrContainer, {
            text: '<?= "http://192.168.167.70:8000/api/mark_presence_mobile.php?seance_id=" . $seance_id . "&token=" . $seance['token'] ?>',
            width: 200,
            height: 200
        });
    }
});
<?php endif; ?>

// Auto-rafraîchissement des statistiques
<?php if ($seance): ?>
setInterval(function() {
    fetch('api/stats_seance.php?seance_id=<?= $seance_id ?>')
        .then(response => response.json())
        .then(data => {
            // Mettre à jour les statistiques
            document.querySelector('.stat-number').textContent = data.total;
            // Mettre à jour autres stats...
        });
}, 30000); // Toutes les 30 secondes
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
