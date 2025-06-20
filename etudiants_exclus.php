<?php
/**
 * Page affichant les u00e9tudiants exclus du CC (3 absences non justifiées ou plus)
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Récupérer les u00e9tudiants ayant 3 absences non justifiées ou plus
$etudiants_exclus = db_query("
    SELECT e.id, e.matricule, e.nom, e.prenom, e.email, 
    COUNT(p.id) as nb_absences_non_justifiees,
    GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as cours
    FROM etudiants e
    JOIN presences p ON e.id = p.etudiant_id
    JOIN cours c ON p.cours_id = c.id
    WHERE p.statut = 'absent' AND (p.justifie = FALSE OR p.justifie IS NULL)
    GROUP BY e.id
    HAVING COUNT(p.id) >= 3
    ORDER BY nb_absences_non_justifiees DESC, e.nom, e.prenom
");

// Inclure le header
include 'includes/header.php';
?>

<style>
.exclus-full-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.exclus-full-bg img {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  opacity: 0.13;
  filter: blur(2.5px) grayscale(0.08);
}
.page-hero-exclus {
  margin-bottom: 2rem;
  padding: 2rem 1rem 1.5rem 1rem;
  border-radius: 2rem;
  background: rgba(255,255,255,0.68);
  box-shadow: 0 8px 32px 0 rgba(123,31,162,0.18);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; gap: 1.5rem;
  position: relative; z-index: 2;
}
.page-hero-exclus i {
  font-size: 2.5rem;
  color: #7b1fa2;
  filter: drop-shadow(0 2px 8px #7b1fa255);
}
.page-hero-exclus h1 {
  margin: 0; font-size: 2.1rem; font-weight: 700; color: #4a148c;
  text-shadow: 0 2px 8px #fff5;
}
.table.exclus-table thead {
  background: linear-gradient(90deg,#7b1fa2 40%,#4a148c 100%);
  color: #fff;
}
.table.exclus-table th, .table.exclus-table td {
  border-radius: 0.7rem;
  vertical-align: middle;
}
.table.exclus-table tr {
  transition: background 0.15s;
}
.table.exclus-table tr:hover {
  background: #f3e5f5;
}
.btn.exclus-action {
  border-radius: 2rem;
  font-weight: 600;
  transition: background 0.18s, color 0.18s, box-shadow 0.18s;
  box-shadow: 0 2px 8px #7b1fa244;
}
.btn.exclus-action:hover {
  background: #7b1fa2;
  color: #fff;
}
.badge.exclus-badge {
  border-radius: 1.2rem;
  padding: 0.4em 1em;
  font-size: 1em;
  animation: pulse-badge 2.2s infinite;
}
@keyframes pulse-badge {
  0% { box-shadow: 0 0 0 0 #7b1fa233; }
  70% { box-shadow: 0 0 0 8px #7b1fa200; }
  100% { box-shadow: 0 0 0 0 #7b1fa200; }
}
.alert {
  animation: fadeInAlert 1s;
}
@keyframes fadeInAlert {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: none; }
}
</style>
<div class="exclus-full-bg">
    <img src="Nouveau dossier/etudiant.jpg" alt="Décor étudiants exclus">
</div>
<div class="page-hero-exclus">
    <i class="fas fa-user-slash"></i>
    <h1>Étudiants Exclus du CC</h1>
    <div style="flex:1"></div>
    <a href="rapports.php" class="btn exclus-action"><i class="fas fa-arrow-left"></i> Retour aux rapports</a>
</div>
    
    <!-- Image décorative -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <img src="assets/images/graduation.svg" alt="Étudiants Exclus" class="img-fluid rounded" style="max-height: 250px; box-shadow: 0 4px 8px rgba(123, 31, 162, 0.3); border: 2px solid #7b1fa2; filter: grayscale(50%);">
        </div>
    </div>

    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle"></i> 

    </div>

    <div class="card" style="border: none; box-shadow: 0 4px 12px rgba(123, 31, 162, 0.2);">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #7b1fa2 0%, #4a148c 100%);">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Liste des Étudiants Exclus</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover data-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Absences Non Justifiées</th>
                            <th>Cours concernés</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($etudiants_exclus)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucun u00e9tudiant n'est actuellement exclu du CC.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($etudiants_exclus as $etudiant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['email']) ?></td>
                                    <td>
                                        <span class="badge bg-danger rounded-pill"><?= $etudiant['nb_absences_non_justifiees'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($etudiant['cours']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="mailto:<?= htmlspecialchars($etudiant['email']) ?>" class="btn btn-sm btn-outline-primary" title="Envoyer un email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                            <a href="justifier_absence.php?matricule=<?= htmlspecialchars($etudiant['matricule']) ?>" class="btn btn-sm btn-outline-success" title="Justifier des absences" target="_blank">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
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
    
    <div class="row mt-4">
        <div class="col-md-12">

        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
