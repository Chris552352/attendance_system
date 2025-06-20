<?php
/**
 * Page de gestion des enseignants
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification et les droits d'administrateur
require_admin();

// Supprimer un enseignant
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Vérifier si l'enseignant existe
    $enseignant = db_query_single("SELECT * FROM utilisateurs WHERE id = ? AND role = 'enseignant'", [$id]);
    
    if ($enseignant) {
        // Vérifier si l'enseignant est associé à des cours
        $cours_associes = db_query_single("SELECT COUNT(*) as total FROM cours WHERE enseignant_id = ?", [$id]);
        
        if ($cours_associes && $cours_associes['total'] > 0) {
            alerte("Cet enseignant est associé à des cours. Veuillez d'abord réassigner ou supprimer ces cours.", "warning");
        } else {
            // Supprimer l'enseignant
            if (db_exec("DELETE FROM utilisateurs WHERE id = ? AND role = 'enseignant'", [$id])) {
                alerte("L'enseignant a été supprimé avec succès.", "success");
            } else {
                alerte("Erreur lors de la suppression de l'enseignant.", "danger");
            }
        }
    } else {
        alerte("Enseignant introuvable.", "danger");
    }
    
    rediriger('enseignants.php');
}

// Récupérer la liste des enseignants avec le nombre de cours
$enseignants = db_query("
    SELECT u.id, u.nom, u.prenom, u.email, u.date_creation, COUNT(c.id) as nb_cours
    FROM utilisateurs u
    LEFT JOIN cours c ON u.id = c.enseignant_id
    WHERE u.role = 'enseignant'
    GROUP BY u.id
    ORDER BY u.nom, u.prenom
");

// Inclure le header
include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/global-modern.css?v=<?= time() ?>">
<style>
.teachers-full-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.teachers-full-bg img {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  opacity: 0.18;
  filter: blur(2.5px) grayscale(0.1);
}
.page-hero, .container-fluid, .modern-card, .modern-table, .modern-btn, .btn-modern {
  position: relative;
  z-index: 2;
}
body.theme-enseignants { background: #fff; }
</style>
<div class="teachers-full-bg">
    <img src="Nouveau dossier/enseignant.jpg" alt="Enseignant - arrière-plan décoratif">
</div>
<div class="page-hero">
    <img src="assets/images/teachers.svg" alt="Enseignants Universitaires" class="page-hero-img">
    <h1 class="page-hero-title"><i class="fas fa-chalkboard-teacher"></i> Gestion des Enseignants</h1>
</div>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="fas fa-chalkboard-teacher"></i> Gestion des Enseignants</h1>
        <div>
            <a href="ajouter_enseignant.php" class="modern-btn btn-sm btn-outline-primary" title="Ajouter un Enseignant">
                <i class="fas fa-plus-circle"></i> Ajouter un Enseignant
            </a>
        </div>
    </div>
    
    <div class="card modern-card">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #e65100 0%, #bf360c 100%);">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Liste des Enseignants</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table modern-table table-striped table-hover data-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Date d'ajout</th>
                            <th>Nombre de Cours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enseignants)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucun enseignant enregistré.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($enseignants as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['nom']) ?></td>
                                    <td><?= htmlspecialchars($e['prenom']) ?></td>
                                    <td><?= htmlspecialchars($e['email']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($e['date_creation'])) ?></td>
                                    <td>
                                        <span class="badge bg-secondary rounded-pill"><?= $e['nb_cours'] ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="ajouter_enseignant.php?id=<?= $e['id'] ?>" class="modern-btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="enseignants.php?action=delete&id=<?= $e['id'] ?>" class="modern-btn btn-sm btn-outline-danger btn-delete" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
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
        <!-- Image décorative -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-chalkboard-teacher"></i> Corps Enseignant</h5>
                </div>
                <div class="card-body">
                    <img src="assets/images/teachers.svg" alt="Représentation du corps enseignant" class="decorative-image">
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
