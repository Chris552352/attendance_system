<?php
/**
 * Page de gestion des étudiants
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Supprimer un étudiant
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Vérifier si l'étudiant existe
    $etudiant = db_query_single("SELECT * FROM etudiants WHERE id = ?", [$id]);
    
    if ($etudiant) {
        // Supprimer d'abord les inscriptions et présences associées
        db_exec("DELETE FROM inscriptions WHERE etudiant_id = ?", [$id]);
        db_exec("DELETE FROM presences WHERE etudiant_id = ?", [$id]);
        
        // Puis supprimer l'étudiant
        if (db_exec("DELETE FROM etudiants WHERE id = ?", [$id])) {
            alerte("L'étudiant a été supprimé avec succès.", "success");
        } else {
            alerte("Erreur lors de la suppression de l'étudiant.", "danger");
        }
    } else {
        alerte("Étudiant introuvable.", "danger");
    }
    
    rediriger('etudiants.php');
}

// Récupérer la liste des étudiants avec tous les champs explicitement nommés
$etudiants = db_query("SELECT id, matricule, nom, prenom, email, telephone, date_naissance, adresse FROM etudiants ORDER BY nom, prenom");

// Déboguer les données (commenter en production)
// echo '<pre>'; print_r($etudiants); echo '</pre>';

// Inclure le header
include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/global-modern.css?v=<?= time() ?>">
<style>
.students-full-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.students-full-bg img {
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
body.theme-etudiants { background: #fff; }
</style>
<div class="students-full-bg">
    <img src="Nouveau dossier/school.jpg" alt="Campus universitaire - arrière-plan décoratif">
</div>
<div class="page-hero">
    <img src="assets/images/students.svg" alt="Étudiants Universitaires" class="page-hero-img">
    <h1 class="page-hero-title"><i class="fas fa-user-graduate"></i> Gestion des Étudiants</h1>
</div>

<!-- Inclure le CSS spécifique pour la page étudiants -->
<link rel="stylesheet" href="assets/css/etudiants.css?v=<?= time() ?>">
<link rel="stylesheet" href="assets/css/etudiants-enhanced.css?v=<?= time() ?>">

<div class="student-page-bg">
    <!-- Éléments décoratifs -->
    <div class="decorative-dots dots-top-right"></div>
    <div class="decorative-dots dots-bottom-left"></div>
    
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="student-title student-fade-in"><i class="fas fa-user-graduate"></i> Gestion des Étudiants</h1>
        <div class="student-fade-in" style="animation-delay: 0.2s;">
            <a href="ajouter_etudiant.php" class="btn-add-student fade-in-student" title="Ajouter un Étudiant" style="box-shadow:0 4px 24px #ab47bc55;">
    <i class="fas fa-plus-circle"></i>
    <span class="btn-add-label d-none d-md-inline">Ajouter un Étudiant</span>
</a>
<style>
/* Responsive : texte caché sur desktop, visible sur mobile */
.btn-add-label { margin-left: 10px; font-weight: 600; letter-spacing: 0.5px; }
@media (max-width: 768px) {
  .btn-add-label { display: inline !important; }
  .btn-add-student { width: auto !important; border-radius: 24px !important; padding: 10px 18px !important; }
}
@media (min-width: 769px) {
  .btn-add-label { display: none !important; }
}
</style>
        </div>
    </div>
    
    <!-- Image décorative avec animation -->
    <div class="row mb-4">
        <div class="col-md-12 student-image-container student-fade-in" style="animation-delay: 0.3s;">
            <img src="assets/images/students.svg" alt="Étudiants" class="student-image">
        </div>
    </div>

    <div class="student-card student-fade-in" style="animation-delay: 0.4s;">
        <div class="student-card-header">
            <h5 class="student-card-title"><i class="fas fa-list"></i> Liste des Étudiants</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover data-table student-table">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($etudiants)): ?>
                            <tr>
                                <td colspan="6" class="text-center message-vide">Aucun étudiant enregistré.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($etudiants as $etudiant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($etudiant['matricule'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($etudiant['nom'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($etudiant['prenom'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($etudiant['email'] ?? '') ?></td>
                                    <td><?= isset($etudiant['telephone']) && $etudiant['telephone'] !== null ? htmlspecialchars($etudiant['telephone']) : '' ?></td>
                                    <td>
                                        <a href="ajouter_etudiant.php?id=<?= $etudiant['id'] ?>" class="btn btn-sm btn-edit custom-tooltip" data-tooltip="Modifier cet étudiant">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="etudiants.php?action=delete&id=<?= $etudiant['id'] ?>" class="btn btn-sm btn-delete custom-tooltip" data-tooltip="Supprimer cet étudiant" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
