<?php
// Tableau de bord
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Récupérer les statistiques
$nbEtudiants = obtenirNombreEtudiants($pdo);
$nbCours = obtenirNombreCours($pdo);
$nbPresencesAujourdhui = obtenirPresencesAujourdhui($pdo);
$nbAbsencesAujourdhui = obtenirAbsencesAujourdhui($pdo);

// Récupérer les statistiques globales pour le graphique
$statsGlobales = obtenirStatistiquesGlobales($pdo);
$totalPresent = $statsGlobales['total_present'] ?? 0;
$totalAbsent = $statsGlobales['total_absent'] ?? 0;

// Récupérer les statistiques par mois pour le graphique
$anneeActuelle = date('Y');
$statsMensuelles = obtenirStatistiquesParMois($pdo, $anneeActuelle);
$statsMensuellesJson = json_encode($statsMensuelles);

// Récupérer les 5 dernières entrées de présence
$stmt = $pdo->query("
    SELECT p.date_presence, p.statut, e.nom as etudiant_nom, e.prenom as etudiant_prenom, c.nom as cours_nom
    FROM presences p
    JOIN etudiants e ON p.etudiant_id = e.id
    JOIN cours c ON p.cours_id = c.id
    ORDER BY p.date_enregistrement DESC
    LIMIT 5
");
$dernieresPresences = $stmt->fetchAll();

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de Bord</h1>
        <div class="d-none d-sm-inline-block">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                <?php echo formaterDate(date('Y-m-d')); ?>
            </span>
        </div>
    </div>

    <!-- Bannière d'images décorative -->
    <div class="image-banner mb-4">
        <img src="assets/images/students.svg" alt="Étudiants" width="120">
        <img src="assets/images/courses.svg" alt="Cours" width="120">
        <img src="assets/images/attendance.svg" alt="Présence" width="120">
        <img src="assets/images/reports.svg" alt="Rapports" width="120">
    </div>

    <!-- Cartes statistiques avec images -->
    <div class="row">
        <!-- Présences aujourd'hui -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <img src="assets/images/attendance.svg" alt="Présences" width="80">
                <h4 class="text-vert">Présences aujourd'hui</h4>
                <h2 class="display-4 fw-bold"><?php echo $nbPresencesAujourdhui; ?></h2>
                <p class="text-muted">Étudiants présents en cours</p>
            </div>
        </div>

        <!-- Absences aujourd'hui -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <i class="fas fa-times-circle text-rouge fa-5x mb-3"></i>
                <h4 class="text-rouge">Absences aujourd'hui</h4>
                <h2 class="display-4 fw-bold"><?php echo $nbAbsencesAujourdhui; ?></h2>
                <p class="text-muted">Étudiants absents en cours</p>
            </div>
        </div>

        <!-- Nombre total d'étudiants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <img src="assets/images/students.svg" alt="Étudiants" width="80">
                <h4 class="text-bleu">Nombre d'étudiants</h4>
                <h2 class="display-4 fw-bold"><?php echo $nbEtudiants; ?></h2>
                <p class="text-muted">Étudiants inscrits</p>
            </div>
        </div>

        <!-- Nombre total de cours -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <img src="assets/images/courses.svg" alt="Cours" width="80">
                <h4 class="text-orange">Nombre de cours</h4>
                <h2 class="display-4 fw-bold"><?php echo $nbCours; ?></h2>
                <p class="text-muted">Cours disponibles</p>
            </div>
        </div>
    </div>

    <!-- Graphiques simplifiés -->
    <div class="row">
        <!-- Graphique Présence vs Absence (Global) -->
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="text-center text-bleu mb-3">Présence vs Absence (Global)</h4>
                
                <!-- Image SVG du graphique remplaçant le canvas -->
                <img src="assets/images/reports.svg" alt="Graphique de présence" class="img-fluid">
                
                <!-- Statistiques en texte simple -->
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <div class="p-3 rounded bg-vert text-white">
                            <h5>Présences</h5>
                            <h3><?php echo $totalPresent; ?></h3>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded bg-rouge text-white">
                            <h5>Absences</h5>
                            <h3><?php echo $totalAbsent; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique mensuel de présence -->
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="text-center text-bleu mb-3">Présences par mois (<?php echo $anneeActuelle; ?>)</h4>
                
                <!-- Graphique simplifié en HTML/CSS pur -->
                <div class="d-flex align-items-end justify-content-around" style="height: 200px;">
                    <?php 
                    // Mois en français
                    $moisFR = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
                    
                    // Récupération des données du JSON
                    $statsMensuelles = json_decode($statsMensuellesJson, true);
                    
                    // Pour chaque mois, afficher une barre
                    for ($i = 0; $i < 12; $i++) {
                        $valeur = isset($statsMensuelles[$i]) ? $statsMensuelles[$i]['present'] : 0;
                        $hauteur = $valeur > 0 ? ($valeur / 10 * 20) + 20 : 20; // Calcul de la hauteur en %
                        
                        echo '<div class="text-center" style="width: 30px;">';
                        echo '<div class="bg-bleu" style="height: ' . $hauteur . 'px; width: 20px; margin: auto;"></div>';
                        echo '<small>' . $moisFR[$i] . '</small>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <p class="text-center text-muted mt-3">
                    <small>Graphique simplifié des présences mensuelles</small>
                </p>
            </div>
        </div>
    </div>

    <!-- Tableau des dernières présences enregistrées (version simplifiée) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-bleu">Dernières présences enregistrées</h4>
                    <img src="assets/images/attendance.svg" alt="Présences" width="80">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead class="bg-bleu text-white">
                            <tr>
                                <th>Date</th>
                                <th>Étudiant</th>
                                <th>Cours</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($dernieresPresences) > 0): ?>
                                <?php foreach ($dernieresPresences as $presence): ?>
                                    <tr>
                                        <td><?php echo formaterDate($presence['date_presence']); ?></td>
                                        <td><?php echo echapper($presence['etudiant_prenom']) . ' ' . echapper($presence['etudiant_nom']); ?></td>
                                        <td><?php echo echapper($presence['cours_nom']); ?></td>
                                        <td>
                                            <?php if ($presence['statut'] === 'present'): ?>
                                                <span class="badge-present">Présent</span>
                                            <?php else: ?>
                                                <span class="badge-absent">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p>Aucune présence enregistrée récemment.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-4">
                    <a href="presence.php" class="btn btn-lg bg-bleu text-white me-2">
                        <i class="fas fa-clipboard-check me-2"></i> Marquer les présences
                    </a>
                    <a href="rapports.php" class="btn btn-lg btn-outline-primary">
                        <i class="fas fa-chart-bar me-2"></i> Voir tous les rapports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
