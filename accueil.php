<?php
/**
 * Page d'accueil petit
 */

// Inclure les fichiers nécessaires attention au extension mrd
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Statistiques globales
$stats = stats_presence_globales();

// Statistiques supplémentaires mais bon c'est optionnel 
$stats_query = db_query("
    SELECT 
        COUNT(*) as total_presences,
        COALESCE(SUM(CASE WHEN est_present = TRUE THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 0) as taux_presence_global,
        COUNT(*) * 1.0 / COUNT(DISTINCT date) as moyenne_presences_jour
    FROM presences
");

    if (!empty($stats_query)) {
    $stats['total_presences'] = $stats_query[0]['total_presences']; 
    $stats['taux_presence_global'] = round($stats_query[0]['taux_presence_global']);
    $stats['moyenne_presences_jour'] = $stats_query[0]['moyenne_presences_jour'];
} else {
    $stats['total_presences'] = 0;
    $stats['taux_presence_global'] = 0;
    $stats['moyenne_presences_jour'] = 0;
} 

// Inclure le header
include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/accueil-modern.css?v=<?= time() ?>">

<!-- Bandeau décoratif université -->
<div class="accueil-hero">
    <img src="assets/images/3.jpg" alt="Cameroun" class="hero-logo">
    <img src="assets/images/decoratives/le-chef-ingenieur-role-et-competences.png" alt="Étudiant studieux" class="hero-student-img">
    <img src="assets/images/livres-stack-realistic_1284-4735.png" alt="Pile de livres" class="hero-books-img">
</div>

<div class="dashboard-header modern-header">
    <h1><img src="assets/images/decoratives/etudiant.jpg" alt="Accueil Université" class="accueil-title-img"> <i class="fas fa-home"></i> Accueil</h1>
    <div class="actions">
        <a href="presence.php" class="btn btn-primary modern-btn"><i class="fas fa-clipboard-check"></i> Marquer Présence</a>
        <a href="rapports.php" class="btn modern-btn"><i class="fas fa-chart-bar"></i> Consulter les Rapports</a>
    </div>
</div>

<div class="banner banner-img-bg">
    <img src="assets/images/login-background.svg" alt="Fond Université" class="banner-bg-img">
    <h2><i class="fas fa-graduation-cap"></i> Système de Gestion de Présence</h2>
    <p>Bienvenue dans votre outil de gestion des présences des étudiants</p>
    <div class="banner-links">
        <a href="etudiants.php"><i class="fas fa-user-graduate"></i> Gérer les Étudiants</a>
        <a href="cours.php"><i class="fas fa-book"></i> Gérer les Cours</a>
    </div>
</div>

<div class="stats-row"> 
    <div class="stat-card">
        <h3><i class="fas fa-check-circle"></i> Présences Aujourd'hui</h3>
        <div class="stat-value"><?php echo $stats['present_auj']; ?></div>
        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
    </div>

    <div class="stat-card">
        <h3><i class="fas fa-times-circle"></i> Absences Aujourd'hui</h3>
        <div class="stat-value"><?php echo $stats['absent_auj']; ?></div>
        <div class="stat-icon"><i class="fas fa-user-times"></i></div>
    </div>

    <div class="stat-card">
        <h3><i class="fas fa-user-graduate"></i> Nombre d'Étudiants</h3>
        <div class="stat-value"><?php echo $stats['total_etudiants']; ?></div>
        <div class="stat-icon"><i class="fas fa-users"></i></div>
    </div>

    <div class="stat-card">
        <h3><i class="fas fa-book"></i> Nombre de Cours</h3>
        <div class="stat-value"><?php echo $stats['total_cours']; ?></div>
        <div class="stat-icon"><i class="fas fa-chalkboard"></i></div>
    </div>
</div>

<div class="row">
    <div class="col" style="width: 100%;">
        <div class="card">
            <div class="card-header"><i class="fas fa-chart-pie"></i> Statistiques Globales de Présence</div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                    <img src="assets/images/dashboard.svg" alt="Statistiques de présence" class="decorative-image" style="max-width: 45%;">
                    <img src="assets/images/3.jpg" alt="Carte du Cameroun" class="decorative-image" style="max-width: 45%;">
                </div>
                
                <div class="stats-summary">
                    <div class="stat-box">
                        <i class="fas fa-clipboard-check stat-box-icon"></i>
                        <div class="stat-number"><?= $stats['total_presences'] ?></div>
                        <div class="stat-label">Total Présences</div>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-user-graduate stat-box-icon"></i>
                        <div class="stat-number"><?= $stats['total_etudiants'] ?></div>
                        <div class="stat-label">Étudiants Actifs</div>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-calendar-check stat-box-icon"></i>
                        <div class="stat-number"><?= number_format($stats['moyenne_presences_jour'], 1) ?></div>
                        <div class="stat-label">Moyenne/Jour</div>
                    </div>
                     <!--<div class="stat-box">
                        <i class="fas fa-percentage stat-box-icon"></i>
                        <div class="stat-number"><?= $stats['taux_presence_global'] ?>%</div>
                        <div class="stat-label">Taux de Présence</div>
                    </div> -->
                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col" style="width: 50%;">
        <div class="card">
            <div class="card-header"><i class="fas fa-book"></i> Quelques Cours</div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Cours</th>
                            <th>Enseignant</th>
                            <th>Étudiants</th>
                            <th>Sessions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cours_populaires = db_query("
                            SELECT c.id, c.nom, e.nom as enseignant_nom, 
                            COUNT(DISTINCT i.etudiant_id) as nb_etudiants,
                            COUNT(DISTINCT p.date) as nb_sessions
                            FROM cours c 
                            LEFT JOIN enseignants e ON c.enseignant_id = e.id
                            LEFT JOIN inscriptions i ON c.id = i.cours_id
                            LEFT JOIN presences p ON c.id = p.cours_id
                            GROUP BY c.id, c.nom, e.nom
                            ORDER BY nb_etudiants DESC, nb_sessions DESC
                            LIMIT 5
                        ");

                        if (empty($cours_populaires)) {
                            echo '<tr><td colspan="4" class="text-center">Aucun cours disponible.</td></tr>';
                        } else {
                            foreach ($cours_populaires as $cours) {
                                echo '<tr>
                                    <td>' . htmlspecialchars($cours['nom']) . '</td>
                                    <td>' . htmlspecialchars($cours['enseignant_nom']) . '</td>
                                    <td>' . $cours['nb_etudiants'] . '</td>
                                    <td>' . $cours['nb_sessions'] . '</td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col" style="width: 50%;">
        <div class="card">
            <div class="card-header"><i class="fas fa-medal"></i> Étudiants les Plus Assidus</div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Matricule</th>
                            <th>Nombre Cours</th>
                            <th>Présences</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $etudiants_assidus = db_query("
                            SELECT e.id, e.nom, e.prenom, e.matricule, 
                            COUNT(DISTINCT i.cours_id) as nb_cours,
                            SUM(CASE WHEN p.est_present = TRUE THEN 1 ELSE 0 END) as nb_presences
                            FROM etudiants e
                            LEFT JOIN inscriptions i ON e.id = i.etudiant_id
                            LEFT JOIN presences p ON e.id = p.etudiant_id
                            GROUP BY e.id, e.nom, e.prenom, e.matricule
                            ORDER BY nb_presences DESC, nb_cours DESC
                            LIMIT 5
                        ");

                        if (empty($etudiants_assidus)) {
                            echo '<tr><td colspan="4" class="text-center">Aucun étudiant disponible.</td></tr>';
                        } else {
                            foreach ($etudiants_assidus as $etudiant) {
                                echo '<tr>
                                    <td>' . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) . '</td>
                                    <td>' . htmlspecialchars($etudiant['matricule']) . '</td>
                                    <td>' . $etudiant['nb_cours'] . '</td>
                                    <td>' . ($etudiant['nb_presences'] ? $etudiant['nb_presences'] : '0') . '</td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
