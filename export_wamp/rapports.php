<?php
/**
 * Page des rapports de présence
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Récupérer les filtres
$etudiant_id = isset($_GET['etudiant_id']) ? (int)$_GET['etudiant_id'] : 0;
$cours_id = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : 0;
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-m-01'); // Premier jour du mois
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-t'); // Dernier jour du mois

// Vérifier si l'enseignant a le droit d'accéder à ce cours
if ($cours_id > 0 && !est_admin()) {
    $cours_autorise = db_query_single("SELECT id FROM cours WHERE id = ? AND enseignant_id = ?", [$cours_id, $_SESSION['user_id']]);
    if (!$cours_autorise) {
        alerte("Vous n'avez pas les droits nécessaires pour accéder à ce cours.", "danger");
        rediriger("mes_cours.php");
    }
}

// Récupérer les listes pour les filtres
$etudiants = db_query("SELECT id, nom, prenom, matricule FROM etudiants ORDER BY nom, prenom");

// Récupérer la liste des cours (filtrée par enseignant si nécessaire)
if (est_admin()) {
    // Les administrateurs voient tous les cours
    $cours = db_query("SELECT id, nom, code FROM cours ORDER BY nom");
} else {
    // Les enseignants ne voient que leurs cours
    $cours = db_query("SELECT id, nom, code FROM cours WHERE enseignant_id = ? ORDER BY nom", [$_SESSION['user_id']]);
}

// Vérifier si la table presences existe et a le bon format
try {
    $check_table = db_query("SHOW TABLES LIKE 'presences'");
    if (empty($check_table)) {
        $table_error = "La table 'presences' n'existe pas dans la base de données.";
    } else {
        // Vérifier les colonnes nécessaires
        $check_columns = db_query("SHOW COLUMNS FROM presences");
        $columns = [];
        foreach ($check_columns as $col) {
            $columns[] = $col['Field'];
        }
        
        if (!in_array('date_presence', $columns) || !in_array('statut', $columns)) {
            $table_error = "La structure de la table 'presences' n'est pas correcte. Colonnes manquantes.";
        }
    }
} catch (Exception $e) {
    $table_error = "Erreur lors de la vérification de la table: " . $e->getMessage();
}

// Construire la requête de base pour les rapports
if (!isset($table_error)) {
    $sql_base = "
        SELECT 
            DATE_FORMAT(p.date_presence, '%d/%m/%Y') as date,
            e.id as etudiant_id, 
            e.nom as etudiant_nom, 
            e.prenom as etudiant_prenom, 
            e.matricule,
            c.id as cours_id, 
            c.nom as cours_nom, 
            c.code as cours_code,
            p.statut,
            p.justifie,
            p.id as presence_id,
            (SELECT j.statut FROM justifications j WHERE j.presence_id = p.id ORDER BY j.date_soumission DESC LIMIT 1) as justification_statut
        FROM presences p
        JOIN etudiants e ON p.etudiant_id = e.id
        JOIN cours c ON p.cours_id = c.id
        WHERE p.date_presence BETWEEN ? AND ?
    ";
    $params = [$date_debut, $date_fin];
    
    // Ajouter des filtres supplémentaires si spécifiés
    if ($etudiant_id > 0) {
        $sql_base .= " AND p.etudiant_id = ?";
        $params[] = $etudiant_id;
    }
    
    if ($cours_id > 0) {
        $sql_base .= " AND p.cours_id = ?";
        $params[] = $cours_id;
    }
    
    $sql_base .= " ORDER BY p.date_presence DESC, e.nom, e.prenom, c.nom";
    
    // Ajouter un message de débogage pour voir la requête SQL et les paramètres
    $debug_sql = $sql_base;
    foreach ($params as $index => $param) {
        $debug_sql = preg_replace('/\?/', "'$param'", $debug_sql, 1);
    }
    
    // Exécuter la requête avec gestion d'erreur
    try {
        $presences = db_query($sql_base, $params);
        // Nombre total de présences pour information
        $total_presences = is_array($presences) ? count($presences) : 0;
        
        // Si $presences est false, initialiser comme un tableau vide pour éviter d'autres erreurs
        if ($presences === false) {
            $presences = [];
            $query_error = "La requête n'a retourné aucun résultat.";
        }
    } catch (Exception $e) {
        $presences = [];
        $query_error = "Erreur lors de l'exécution de la requête: " . $e->getMessage();
    }
    
    // Récupérer l'erreur SQL si elle existe
    $sql_error = db_error();
} else {
    $presences = [];
    $total_presences = 0;
}

// Inclure le header
include 'includes/header.php';
?>

<style>
.rapports-full-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.rapports-full-bg img {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  opacity: 0.13;
  filter: blur(2.5px) grayscale(0.08);
}
.page-hero-rapports {
  margin-bottom: 2rem;
  padding: 2rem 1rem 1.5rem 1rem;
  border-radius: 2rem;
  background: rgba(255,255,255,0.68);
  box-shadow: 0 8px 32px 0 rgba(25,118,210,0.18);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; gap: 1.5rem;
  position: relative; z-index: 2;
}
.page-hero-rapports i {
  font-size: 2.5rem;
  color: #1976d2;
  filter: drop-shadow(0 2px 8px #1976d255);
}
.page-hero-rapports h1 {
  margin: 0; font-size: 2.1rem; font-weight: 700; color: #0d47a1;
  text-shadow: 0 2px 8px #fff5;
}
.table.rapports-table thead {
  background: linear-gradient(90deg,#1976d2 40%,#2196f3 100%);
  color: #fff;
}
.table.rapports-table th, .table.rapports-table td {
  border-radius: 0.7rem;
  vertical-align: middle;
}
.table.rapports-table tr {
  transition: background 0.15s;
}
.table.rapports-table tr:hover {
  background: #e3f2fd;
}
.btn.rapports-action {
  border-radius: 2rem;
  font-weight: 600;
  transition: background 0.18s, color 0.18s, box-shadow 0.18s;
  box-shadow: 0 2px 8px #1976d244;
}
.btn.rapports-action:hover {
  background: #1976d2;
  color: #fff;
}
.badge.rapports-badge {
  border-radius: 1.2rem;
  padding: 0.4em 1em;
  font-size: 1em;
  animation: pulse-badge 2.2s infinite;
}
@keyframes pulse-badge {
  0% { box-shadow: 0 0 0 0 #1976d233; }
  70% { box-shadow: 0 0 0 8px #1976d200; }
  100% { box-shadow: 0 0 0 0 #1976d200; }
}
.alert {
  animation: fadeInAlert 1s;
}
@keyframes fadeInAlert {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: none; }
}
</style>
<div class="rapports-full-bg">
    <img src="Nouveau dossier/iut.jpg" alt="Décor rapports">
</div>
<div class="page-hero-rapports">
    <i class="fas fa-chart-bar"></i>
    <h1>Rapports de Présence</h1>
    <div style="flex:1"></div>
    <a href="etudiants_exclus.php" class="btn rapports-action"><i class="fas fa-user-slash"></i> Étudiants exclus</a>
    <button onclick="window.print()" class="btn rapports-action"><i class="fas fa-print"></i> Imprimer</button>
</div>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2" style="color: #1976d2; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);"><i class="fas fa-chart-bar"></i> Rapports de Présence</h1>
        <div>
            <a href="etudiants_exclus.php" class="btn me-2" style="background-color: #f44336; color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                <i class="fas fa-user-slash"></i> Étudiants exclus du CC
            </a>
        </div>
    </div>

    <!-- Image décorative -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <img src="assets/images/reports.svg" alt="Rapports" class="img-fluid rounded" style="max-height: 250px; box-shadow: 0 4px 8px rgba(25, 118, 210, 0.3); border: 2px solid #1976d2;">
        </div>
    </div>

    
    <!-- Filtres -->
    <div class="card mb-4" style="border: none; box-shadow: 0 4px 12px rgba(25, 118, 210, 0.2);">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);">
            <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="rapports.php" id="reportsFilterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="etudiant_id" class="form-label">Étudiant</label>
                        <select class="form-select" id="etudiant_id" name="etudiant_id">
                            <option value="0">Tous les étudiants</option>
                            <?php foreach ($etudiants as $e): ?>
                                <option value="<?php echo $e['id']; ?>" <?php echo $etudiant_id == $e['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['nom'] . ' ' . $e['prenom'] . ' (' . $e['matricule'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cours_id" class="form-label">Cours</label>
                        <select class="form-select" id="cours_id" name="cours_id">
                            <option value="0">Tous les cours</option>
                            <?php foreach ($cours as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $cours_id == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nom'] . ' (' . $c['code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_debut" class="form-label">Date début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="date_fin" class="form-label">Date fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Image décorative -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-clipboard-list"></i> Rapport de Présence</h5>
                </div>
                <div class="rapports-full-bg">
                    <img src="Nouveau dossier/iut.jpg" alt="Campus IUT - arrière-plan décoratif" style="filter: blur(5px); opacity: 0.5;">
                </div>
                <div class="card-body text-center">
                    <p class="mt-3 text-muted">Liste détaillée des présences par étudiant et par cours</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des présences -->
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Détails des Présences</h5>
        </div>
        <div class="card-body">
            <?php if (isset($table_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Erreur de structure:</strong> <?php echo htmlspecialchars($table_error); ?>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Veuillez contacter l'administrateur du système pour résoudre ce problème.
                </div>
            <?php elseif (isset($query_error)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> <strong>Erreur de requête:</strong> <?php echo htmlspecialchars($query_error); ?>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Essayez de modifier vos critères de recherche.
                </div>
            <?php elseif (empty($presences)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucune présence enregistrée pour cette période.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Étudiant</th>
                                <th>Matricule</th>
                                <th>Cours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presences as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['date']); ?></td>
                                    <td><?php echo htmlspecialchars($p['etudiant_nom'] . ' ' . $p['etudiant_prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($p['matricule']); ?></td>
                                    <td><?php echo htmlspecialchars($p['cours_nom'] . ' (' . $p['cours_code'] . ')'); ?></td>
                                    <td>
                                        <?php if ($p['statut'] === 'present'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Présent</span>
                                        <?php elseif ($p['statut'] === 'absent' && $p['justifie'] == 1): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-file-alt"></i> Justifié</span>
                                        <?php elseif ($p['statut'] === 'absent'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Absent</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Non marqué</span>
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

    <div class="row mt-4 d-print-none">

    </div>
</div>

<!-- Section Statistiques Présence/Absence -->
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-chart-pie"></i> Statistiques de Présence</h5>
    </div>
    <div class="card-body">
        <canvas id="presenceChart" style="max-width: 400px;"></canvas>
    </div>
</div>

<!-- Bouton Export CSV -->
<div class="mt-3">
    <button class="btn btn-success" onclick="exportTableToCSV('rapport_presences.csv')">
        <i class="fas fa-file-csv"></i> Exporter en CSV
    </button>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Calcul des statistiques à partir du tableau PHP
const presences = <?php echo json_encode($presences ?? []); ?>;
let present = 0, absent = 0, justifie = 0;
presences.forEach(p => {
    if (p.statut === 'present') present++;
    else if (p.statut === 'absent' && p.justifie == 1) justifie++;
    else if (p.statut === 'absent') absent++;
});

const ctx = document.getElementById('presenceChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Présent', 'Absent', 'Justifié'],
        datasets: [{
            data: [present, absent, justifie],
            backgroundColor: ['#43e97b', '#ff5858', '#fbc02d'],
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: { display: false }
        }
    }
});

// Export CSV
function exportTableToCSV(filename) {
    let csv = '';
    const BOM = '\uFEFF'; // BOM UTF-8 pour Excel
    const rows = document.querySelectorAll('table.table tbody tr');
    const headers = Array.from(document.querySelectorAll('table.table thead th')).map(th => th.innerText);
    csv += headers.join(';') + '\n';
    rows.forEach(row => {
        const cols = row.querySelectorAll('td');
        let rowData = [];
        cols.forEach(td => rowData.push(td.innerText.replace(/\n/g, ' ').trim()));
        csv += rowData.join(';') + '\n';
    });
    const blob = new Blob([BOM + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}
</script>

