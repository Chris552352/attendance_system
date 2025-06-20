<?php
/**
 * Page de gestion des cours
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Supprimer un cours
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Vérifier si le cours existe
    $cours = db_query_single("SELECT * FROM cours WHERE id = ?", [$id]);
    
    if ($cours) {
        // Supprimer d'abord les inscriptions et présences associées
        db_exec("DELETE FROM inscriptions WHERE cours_id = ?", [$id]);
        db_exec("DELETE FROM presences WHERE cours_id = ?", [$id]);
        
        // Puis supprimer le cours
        if (db_exec("DELETE FROM cours WHERE id = ?", [$id])) {
            alerte("Le cours a été supprimé avec succès.", "success");
        } else {
            alerte("Erreur lors de la suppression du cours.", "danger");
        }
    } else {
        alerte("Cours introuvable.", "danger");
    }
    
    rediriger('cours.php');
}

// Récupérer la liste des cours avec l'enseignant et le nombre d'étudiants
$cours = db_query("
    SELECT c.*, 
           u.nom as enseignant_nom, 
           u.prenom as enseignant_prenom,
           COUNT(DISTINCT i.etudiant_id) as nb_etudiants
    FROM cours c
    LEFT JOIN utilisateurs u ON c.enseignant_id = u.id AND u.role = 'enseignant'
    LEFT JOIN inscriptions i ON c.id = i.cours_id
    GROUP BY c.id, c.nom, c.code, c.description, c.enseignant_id, u.nom, u.prenom
    ORDER BY c.nom
");

// Inclure le header
include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/global-modern.css?v=<?= time() ?>">
<style>
.courses-full-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.courses-full-bg img {
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
body.theme-cours { background: #fff; }
</style>
<div class="courses-full-bg">
    <img src="Nouveau dossier/Capterra-creation-cours-en-ligne.png" alt="Cours en ligne - arrière-plan décoratif">
</div>
<div class="page-hero">
    <img src="assets/images/courses.svg" alt="Cours Universitaires" class="page-hero-img">
    <h1 class="page-hero-title"><i class="fas fa-book"></i> Gestion des Cours</h1>
</div>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="fas fa-book"></i> Gestion des Cours</h1>
        <div>
            <a href="ajouter_cours.php" class="modern-btn btn btn-primary">
                <i class="fas fa-plus-circle"></i> Ajouter un Cours
            </a>
        </div>
    </div>

    <div class="modern-card card">
        <div class="card-header bg-dark text-white">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Liste des Cours</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="modern-table table table-striped table-hover data-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Nom du Cours</th>
                            <th>Enseignant</th>
                            <th>Étudiants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cours)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucun cours enregistré.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cours as $c): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($c['code']) ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($c['nom']) ?></strong>
                                        <?php if (!empty($c['description'])): ?>
                                            <button class="modern-btn btn btn-sm btn-link p-0 ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($c['description']) ?>">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($c['enseignant_id']): ?>
                                            <?= htmlspecialchars($c['enseignant_nom'] . ' ' . $c['enseignant_prenom']) ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Non assigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="fas fa-user-graduate"></i> <?= $c['nb_etudiants'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="liste_etudiants.php?cours_id=<?= $c['id'] ?>" class="modern-btn btn btn-sm btn-outline-info" title="Liste des étudiants">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="presence.php?cours_id=<?= $c['id'] ?>" class="modern-btn btn btn-sm btn-outline-success" title="Marquer les présences">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                            <a href="ajouter_cours.php?id=<?= $c['id'] ?>" class="modern-btn btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="cours.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer">
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
                    <h5 class="card-title mb-0"><i class="fas fa-book"></i> Représentation des Cours</h5>
                </div>
                <div class="card-body">
                    <img src="assets/images/courses.svg" alt="Représentation visuelle des cours" class="decorative-image">
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
