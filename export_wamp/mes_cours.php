<?php
/**
 * Page de gestion des cours de l'enseignant connecté
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Récupérer la liste des cours de l'enseignant connecté
if (est_admin()) {
    // Les administrateurs voient tous les cours
    $cours = db_query("SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        (SELECT COUNT(*) FROM inscriptions i WHERE i.cours_id = c.id) as nb_etudiants,
        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id) as total_presences,
        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id AND p.statut = 'present') as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        ORDER BY c.nom
    ");
} else {
    // Les enseignants ne voient que leurs cours
    $cours = db_query("SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
        (SELECT COUNT(*) FROM inscriptions i WHERE i.cours_id = c.id) as nb_etudiants,
        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id) as total_presences,
        (SELECT COUNT(*) FROM presences p WHERE p.cours_id = c.id AND p.statut = 'present') as nb_presents
        FROM cours c
        LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
        WHERE c.enseignant_id = ?
        ORDER BY c.nom
    ", [$_SESSION['user_id']]);
}

// Inclure le header
include 'includes/header.php';
?>

<style>
.mes-cours-hero {
  width: 100%;
  background: linear-gradient(90deg,#43e97b 0%,#38f9d7 100%);
  border-radius: 2.2rem;
  box-shadow: 0 8px 32px #43e97b33;
  display: flex;
  align-items: center;
  gap: 2.5rem;
  padding: 2.5rem 2rem 2.5rem 2.5rem;
  margin-bottom: 2.5rem;
  color: #fff;
  overflow: hidden;
  position: relative;
  animation: fadeInHero 1.1s cubic-bezier(.22,1,.36,1);
}
.mes-cours-hero-img {
  height: 120px;
  width: 120px;
  object-fit: contain;
  border-radius: 1.5rem;
  background: #fff;
  box-shadow: 0 2px 32px #fff3;
  padding: 0.7rem;
  margin-right: 1.5rem;
}
.mes-cours-hero-content h1 {
  font-size: 2.3rem;
  font-weight: 900;
  margin-bottom: 0.5rem;
  letter-spacing: 0.03em;
}
.mes-cours-hero-content p {
  font-size: 1.1rem;
  opacity: 0.92;
}
@keyframes fadeInHero {
  from { opacity: 0; transform: translateY(32px) scale(0.97); }
  to { opacity: 1; transform: none; }
}
.mes-cours-list {
  display: flex;
  flex-wrap: wrap;
  gap: 2.2rem;
  justify-content: flex-start;
}
.cours-card-glass {
  background: rgba(255,255,255,0.93);
  border-radius: 2.2rem;
  box-shadow: 0 8px 32px #38f9d733, 0 1.5px 32px #fff2;
  backdrop-filter: blur(6px);
  padding: 2.2rem 2.2rem 1.6rem 2.2rem;
  min-width: 320px;
  max-width: 370px;
  flex: 1 1 320px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: relative;
  margin-bottom: 1.5rem;
  transition: box-shadow 0.18s, transform 0.18s;
  animation: fadeInCoursCard 1.15s cubic-bezier(.22,1,.36,1);
}
.cours-card-glass:hover {
  box-shadow: 0 16px 48px #38f9d777;
  transform: translateY(-2px) scale(1.03);
}
@keyframes fadeInCoursCard {
  from { opacity: 0; transform: translateY(44px) scale(0.96); }
  to { opacity: 1; transform: none; }
}
.cours-card-code {
  position: absolute;
  top: 1.2rem;
  right: 1.3rem;
  background: linear-gradient(90deg,#43e97b 0%,#38f9d7 100%);
  color: #fff;
  font-weight: 700;
  font-size: 1.08rem;
  padding: 0.35em 1.1em;
  border-radius: 1.2em;
  box-shadow: 0 2px 12px #43e97b33;
  letter-spacing: 0.04em;
  animation: badgePop 0.8s cubic-bezier(.22,1,.36,1);
}
@keyframes badgePop {
  from { transform: scale(0.7); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.cours-card-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: #1976d2;
  margin-bottom: 0.2rem;
  display: flex;
  align-items: center;
  gap: 0.7em;
}
.cours-card-title i {
  font-size: 1.3em;
  color: #43e97b;
  filter: drop-shadow(0 2px 8px #38f9d733);
}
.cours-card-desc {
  color: #7b1fa2;
  font-size: 0.99rem;
  margin-bottom: 1.1rem;
  opacity: 0.82;
}
.cours-card-infos {
  display: flex;
  gap: 1.2em;
  margin-bottom: 1.3em;
  flex-wrap: wrap;
}
.cours-card-info {
  display: flex;
  align-items: center;
  gap: 0.5em;
  font-size: 1.03em;
  color: #1976d2;
  font-weight: 600;
  background: #e3f2fd;
  border-radius: 1em;
  padding: 0.18em 0.8em;
}
.cours-card-info i {
  color: #43e97b;
  font-size: 1.1em;
}
.cours-card-actions {
  margin-top: auto;
  display: flex;
  gap: 0.7em;
}
.cours-card-actions .btn {
  border-radius: 1.2em;
  font-size: 1.1em;
  font-weight: 700;
  box-shadow: 0 2px 12px #38f9d733;
  transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
}
.cours-card-actions .btn:hover {
  background: linear-gradient(90deg,#1976d2,#7b1fa2 90%);
  color: #fff;
  box-shadow: 0 8px 24px #1976d288;
  transform: translateY(-2px) scale(1.04);
}
@media (max-width: 991px) {
  .mes-cours-list { flex-direction: column; gap: 1.3rem; }
  .cours-card-glass { min-width: 100%; max-width: 100%; }
  .mes-cours-hero { flex-direction: column; gap: 1.2rem; text-align: center; }
  .mes-cours-hero-img { margin: 0 auto 1rem auto; }
}
</style>

<div class="mes-cours-hero">
    <img src="assets/images/courses.svg" alt="Cours" class="mes-cours-hero-img">
    <div class="mes-cours-hero-content">
        <h1><i class="fas fa-book"></i> Mes Cours</h1>
        <p>Retrouvez ici tous vos cours, suivez la présence et accédez rapidement à la liste des étudiants.</p>
        <?php if (est_admin()): ?>
        <a href="ajouter_cours.php" class="btn btn-light btn-lg mt-2" style="border-radius:1.3em;font-weight:700;box-shadow:0 2px 12px #38f9d733;">
            <i class="fas fa-plus-circle"></i> Ajouter un Cours
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="mes-cours-list">
<?php if (empty($cours)): ?>
    <div class="alert alert-info w-100 text-center" style="border-radius:1.3em;font-size:1.1em;">
        <i class="fas fa-info-circle"></i> Aucun cours enregistré.
    </div>
<?php else: ?>
    <?php foreach ($cours as $c): ?>
    <div class="cours-card-glass">
        <div class="cours-card-code">#<?= htmlspecialchars($c['code']) ?></div>
        <div class="cours-card-title"><i class="fas fa-chalkboard"></i> <?= htmlspecialchars($c['nom']) ?></div>
        <div class="cours-card-desc"><?= htmlspecialchars($c['description']) ?></div>
        <div class="cours-card-infos">
            <?php if (est_admin()): ?>
            <div class="cours-card-info"><i class="fas fa-user-graduate"></i> <?= $c['enseignant_id'] ? htmlspecialchars($c['enseignant_nom'] . ' ' . $c['enseignant_prenom']) : '<span class=\'text-muted\'>Non assigné</span>' ?></div>
            <?php endif; ?>
            <div class="cours-card-info"><i class="fas fa-users"></i> <?= $c['nb_etudiants'] ?> étudiants</div>
            <div class="cours-card-info"><i class="fas fa-check-circle"></i> <?= $c['nb_presents'] ?> présents</div>
            <div class="cours-card-info"><i class="fas fa-times-circle"></i> <?= $c['total_presences'] - $c['nb_presents'] ?> absents</div>
        </div>
        <div class="cours-card-actions">
            <a href="presence.php?cours_id=<?= $c['id'] ?>" class="btn btn-outline-success" title="Marquer les présences">
                <i class="fas fa-clipboard-check"></i> Présence
            </a>
            <a href="liste_etudiants.php?cours_id=<?= $c['id'] ?>" class="btn btn-outline-info" title="Liste des étudiants">
                <i class="fas fa-users"></i> Étudiants
            </a>
            <?php if (est_admin()): ?>
            <a href="ajouter_cours.php?id=<?= $c['id'] ?>" class="btn btn-outline-primary" title="Modifier">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="cours.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-outline-danger btn-delete" title="Supprimer">
                <i class="fas fa-trash-alt"></i> Supprimer
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
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
