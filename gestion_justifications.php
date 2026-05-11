<?php
/**
 * Page de gestion des justifications d'absences
 * Permet aux enseignants de valider ou rejeter les justifications
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/justification_schema.php';

// Vérifier l'authentification
require_auth();

ensure_justification_messaging_schema();

// Initialiser les variables
$message = '';
$message_type = '';
$justifications = [];

$filtre_statut = 'tous';
if (isset($_GET['statut'])) {
    $filtre_statut = $_GET['statut'];
} elseif (isset($_POST['_filtre_statut'])) {
    $filtre_statut = (string) $_POST['_filtre_statut'];
}
$filtre_cours = 0;
if (isset($_GET['cours_id'])) {
    $filtre_cours = (int) $_GET['cours_id'];
} elseif (isset($_POST['_filtre_cours_id'])) {
    $filtre_cours = (int) $_POST['_filtre_cours_id'];
}

// Traitement POST : message staff, validation ou rejet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'message_staff') {
        $justification_id = (int) ($_POST['justification_id'] ?? 0);
        $msg_staff = trim((string) ($_POST['message_staff'] ?? ''));
        if ($justification_id <= 0 || $msg_staff === '') {
            $message = 'Veuillez saisir un message.';
            $message_type = 'danger';
        } else {
            $row = db_query_single(
                "SELECT j.id, c.enseignant_id, c.id as cours_id
                 FROM justifications j
                 INNER JOIN presences p ON j.presence_id = p.id
                 INNER JOIN cours c ON p.cours_id = c.id
                 WHERE j.id = ?",
                [$justification_id]
            );
            $peut = false;
            if ($row) {
                if (est_admin()) {
                    $peut = true;
                } elseif (est_enseignant_du_cours((int) $row['cours_id'])) {
                    $peut = true;
                }
            }
            if (!$peut) {
                $message = "Vous n'avez pas le droit d'envoyer un message sur cette justification.";
                $message_type = 'danger';
            } else {
                try {
                    db_exec(
                        'INSERT INTO justification_messages (justification_id, sender_type, sender_id, message) VALUES (?, ?, ?, ?)',
                        [$justification_id, 'staff', $_SESSION['user_id'], $msg_staff]
                    );
                    $message = 'Message envoyé.';
                    $message_type = 'success';
                } catch (Throwable $e) {
                    $message = "Erreur lors de l'envoi du message.";
                    $message_type = 'danger';
                }
            }
        }
    } elseif ($action === 'valider' || $action === 'rejeter') {
        $justification_id = (int) ($_POST['justification_id'] ?? 0);
        $commentaire = securiser($_POST['commentaire'] ?? '');

        $peut_traiter = false;
        if ($justification_id > 0) {
            $row = db_query_single(
                "SELECT j.id, c.enseignant_id
                 FROM justifications j
                 INNER JOIN presences p ON j.presence_id = p.id
                 INNER JOIN cours c ON p.cours_id = c.id
                 WHERE j.id = ?",
                [$justification_id]
            );
            if ($row) {
                if (est_admin()) {
                    $peut_traiter = true;
                } elseif (est_enseignant() && (int) $row['enseignant_id'] === (int) $_SESSION['user_id']) {
                    $peut_traiter = true;
                }
            }
        }

        if (!$peut_traiter) {
            $message = "Vous n'avez pas le droit de valider ou rejeter cette justification.";
            $message_type = 'danger';
        } else {
            $statut = ($action === 'valider') ? 'validee' : 'rejetee';

            $result = db_exec(
                'UPDATE justifications SET statut = ?, validee_par = ?, date_validation = NOW(), commentaire = ? WHERE id = ?',
                [$statut, $_SESSION['user_id'], $commentaire, $justification_id]
            );

            if ($result && $action === 'valider') {
                $presence_row = db_query_single('SELECT presence_id FROM justifications WHERE id = ?', [$justification_id]);
                if ($presence_row) {
                    db_exec('UPDATE presences SET justifie = TRUE WHERE id = ?', [$presence_row['presence_id']]);
                }
            }

            if ($result) {
                $message = 'La justification a été ' . ($action === 'valider' ? 'validée' : 'rejetée') . ' avec succès.';
                $message_type = 'success';
            } else {
                $message = 'Erreur lors de la mise à jour de la justification.';
                $message_type = 'danger';
            }
        }
    }
}

// Récupérer les cours de l'enseignant pour le filtre
if ($_SESSION['user_role'] === 'admin') {
    $cours = db_query("SELECT id, nom, code FROM cours ORDER BY nom");
} else {
    $cours = db_query("SELECT id, nom, code FROM cours WHERE enseignant_id = ? ORDER BY nom", [$_SESSION['user_id']]);
}

// Construire la requête pour récupérer les justifications
$sql = "SELECT j.*, p.date_presence, p.statut as presence_statut, p.justifie,
               e.nom as etudiant_nom, e.prenom as etudiant_prenom, e.matricule,
               c.nom as cours_nom, c.code as cours_code, c.enseignant_id as cours_enseignant_id,
               u.nom as validateur_nom
        FROM justifications j
        JOIN presences p ON j.presence_id = p.id
        JOIN etudiants e ON p.etudiant_id = e.id
        JOIN cours c ON p.cours_id = c.id
        LEFT JOIN utilisateurs u ON j.validee_par = u.id";

$params = [];
$where_clauses = [];

// Filtrer par statut
if ($filtre_statut !== 'tous') {
    $where_clauses[] = "j.statut = ?";
    $params[] = $filtre_statut;
}

// Filtrer par cours
if ($filtre_cours > 0) {
    $where_clauses[] = "p.cours_id = ?";
    $params[] = $filtre_cours;
}

// Filtrer par enseignant si ce n'est pas un admin
if ($_SESSION['user_role'] !== 'admin') {
    $where_clauses[] = "c.enseignant_id = ?";
    $params[] = $_SESSION['user_id'];
}

// Ajouter les clauses WHERE à la requête
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Ajouter l'ordre de tri
$sql .= " ORDER BY j.date_soumission DESC";

// Exécuter la requête
$justifications = db_query($sql, $params);

$messagesByJustif = [];
if (!empty($justifications)) {
    $ids = [];
    foreach ($justifications as $jj) {
        $ids[] = (int) $jj['id'];
    }
    $ids = array_values(array_unique(array_filter($ids)));
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        try {
            $msgs = db_query(
                "SELECT jm.*,
                        u.prenom AS staff_prenom, u.nom AS staff_nom,
                        et.prenom AS etu_prenom, et.nom AS etu_nom
                 FROM justification_messages jm
                 LEFT JOIN utilisateurs u ON jm.sender_type = 'staff' AND jm.sender_id = u.id
                 LEFT JOIN etudiants et ON jm.sender_type = 'etudiant' AND jm.sender_id = et.id
                 WHERE jm.justification_id IN ($placeholders)
                 ORDER BY jm.justification_id ASC, jm.created_at ASC",
                $ids
            );
            foreach ($msgs as $m) {
                $jid = (int) $m['justification_id'];
                $messagesByJustif[$jid][] = $m;
            }
        } catch (Throwable $e) {
        }
    }
}

// Inclure le header
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/global-modern.css?v=<?= time() ?>">
<style>
.justif-full-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.justif-full-bg img {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  opacity: 0.13;
  filter: blur(2.5px) grayscale(0.05);
}
.page-hero-justif {
  margin-bottom: 2rem;
  padding: 2rem 1rem 1.5rem 1rem;
  border-radius: 2rem;
  background: rgba(255,255,255,0.6);
  box-shadow: 0 8px 32px 0 rgba(31,38,135,0.18);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; gap: 1.5rem;
  position: relative; z-index: 2;
}
.page-hero-justif i {
  font-size: 2.5rem;
  color: #1976d2;
  filter: drop-shadow(0 2px 8px #1976d255);
}
.page-hero-justif h1 {
  margin: 0; font-size: 2.1rem; font-weight: 700; color: #0d47a1;
  text-shadow: 0 2px 8px #fff5;
}
.table.justif-table thead {
  background: linear-gradient(90deg,#1976d2 40%,#2196f3 100%);
  color: #fff;
}
.table.justif-table th, .table.justif-table td {
  border-radius: 0.7rem;
  vertical-align: middle;
}
.table.justif-table tr {
  transition: background 0.15s;
}
.table.justif-table tr:hover {
  background: #e3f2fd;
}
.btn.justif-action {
  border-radius: 2rem;
  font-weight: 600;
  transition: background 0.18s, color 0.18s, box-shadow 0.18s;
  box-shadow: 0 2px 8px #1976d244;
}
.btn.justif-action:hover {
  background: #1976d2;
  color: #fff;
}
.badge.justif-badge {
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
.justif-msg-thread {
  max-height: 320px;
  overflow-y: auto;
  padding: 0.5rem;
  background: #fafafa;
  border-radius: 0.75rem;
  border: 1px solid #e3e3e3;
}
.justif-msg {
  margin-bottom: 0.75rem;
  padding: 0.5rem 0.85rem;
  border-radius: 0.85rem;
  max-width: 94%;
  word-break: break-word;
}
.justif-msg.staff {
  margin-left: auto;
  background: #e3f2fd;
  border: 1px solid #90caf9;
}
.justif-msg.student {
  margin-right: auto;
  background: #fff;
  border: 1px solid #e0e0e0;
}
.justif-msg-meta {
  font-size: 0.78rem;
  color: #666;
  margin-bottom: 0.3rem;
}
</style>
<div class="justif-full-bg">
    <img src="Nouveau dossier/images.png" alt="Décor justifications">
</div>

<div class="page-hero-justif">
    <i class="fas fa-clipboard-check"></i>
    <h1>Gestion des Justifications</h1>
</div>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filtres -->
    <div class="card mb-4" style="border: none; box-shadow: 0 4px 12px rgba(25, 118, 210, 0.2);">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);">
            <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="tous" <?php echo $filtre_statut === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                        <option value="en_attente" <?php echo $filtre_statut === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                        <option value="validee" <?php echo $filtre_statut === 'validee' ? 'selected' : ''; ?>>Validée</option>
                        <option value="rejetee" <?php echo $filtre_statut === 'rejetee' ? 'selected' : ''; ?>>Rejetée</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="cours_id" class="form-label">Cours</label>
                    <select class="form-select" id="cours_id" name="cours_id">
                        <option value="0" <?php echo $filtre_cours === 0 ? 'selected' : ''; ?>>Tous les cours</option>
                        <?php foreach ($cours as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filtre_cours === (int)$c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['nom'] . ' (' . $c['code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn" style="background-color: #1976d2; color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Liste des justifications -->
    <div class="card" style="border: none; box-shadow: 0 4px 12px rgba(25, 118, 210, 0.2);">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Liste des Justifications</h5>
        </div>
        <div class="card-body">
            <?php if (empty($justifications)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucune justification trouvée avec les critères sélectionnés.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead style="background: linear-gradient(to right, #1976d2, #2196f3); color: white;">
                            <tr>
                                <th>Date Absence</th>
                                <th>Étudiant</th>
                                <th>Cours</th>
                                <th>Justification</th>
                                <th>Date Soumission</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($justifications as $j): ?>
                                <?php
                                $jid = (int) $j['id'];
                                $thread = $messagesByJustif[$jid] ?? [];
                                $peut_traiter_ui = $j['statut'] === 'en_attente'
                                    && (est_admin() || (est_enseignant() && (int) $j['cours_enseignant_id'] === (int) $_SESSION['user_id']));
                                $peut_message = est_admin()
                                    || (est_enseignant() && (int) $j['cours_enseignant_id'] === (int) $_SESSION['user_id']);
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($j['date_presence'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($j['etudiant_prenom'] . ' ' . $j['etudiant_nom']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($j['matricule']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($j['cours_nom'] . ' (' . $j['cours_code'] . ')'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#justificationModal<?php echo $j['id']; ?>">
                                            <i class="fas fa-eye"></i> Voir
                                        </button>
                                        
                                        <!-- Modal pour afficher la justification -->
                                        <div class="modal fade" id="justificationModal<?php echo $j['id']; ?>" tabindex="-1" aria-labelledby="justificationModalLabel<?php echo $j['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="background-color: #1976d2; color: white;">
                                                        <h5 class="modal-title" id="justificationModalLabel<?php echo $j['id']; ?>">Justification d'absence</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Étudiant :</strong> <?php echo htmlspecialchars($j['etudiant_prenom'] . ' ' . $j['etudiant_nom']); ?></p>
                                                        <p><strong>Cours :</strong> <?php echo htmlspecialchars($j['cours_nom'] . ' (' . $j['cours_code'] . ')'); ?></p>
                                                        <p><strong>Date d'absence :</strong> <?php echo date('d/m/Y', strtotime($j['date_presence'])); ?></p>
                                                        <p><strong>Date de soumission :</strong> <?php echo date('d/m/Y H:i', strtotime($j['date_soumission'])); ?></p>
                                                        <?php if (!empty($j['piece_path'])): ?>
                                                            <p class="mb-2">
                                                                <a class="btn btn-sm btn-outline-secondary" href="telecharger_piece_justification.php?id=<?php echo (int) $j['id']; ?>" target="_blank" rel="noopener">
                                                                    <i class="fas fa-paperclip"></i> Télécharger la pièce jointe
                                                                    <?php if (!empty($j['piece_nom_original'])): ?>
                                                                        <span class="text-muted">(<?php echo htmlspecialchars($j['piece_nom_original']); ?>)</span>
                                                                    <?php endif; ?>
                                                                </a>
                                                            </p>
                                                        <?php endif; ?>
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-light">Contenu de la justification</div>
                                                            <div class="card-body">
                                                                <?php
                                                                if (empty($j['contenu'])) {
                                                                    echo "<span class='text-muted'>Aucun motif saisi</span>";
                                                                } else {
                                                                    echo nl2br(htmlspecialchars($j['contenu']));
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>

                                                        <h6 class="mt-3"><i class="fas fa-comments"></i> Messagerie</h6>
                                                        <div class="justif-msg-thread mb-3">
                                                            <?php if (empty($thread)): ?>
                                                                <p class="text-muted small mb-0">Aucun échange pour l’instant.</p>
                                                            <?php else: ?>
                                                                <?php foreach ($thread as $m): ?>
                                                                    <?php
                                                                    $is_staff = ($m['sender_type'] ?? '') === 'staff';
                                                                    if ($is_staff) {
                                                                        $who = trim(($m['staff_prenom'] ?? '') . ' ' . ($m['staff_nom'] ?? ''));
                                                                        if ($who === '') {
                                                                            $who = 'Équipe';
                                                                        }
                                                                    } else {
                                                                        $who = trim(($m['etu_prenom'] ?? '') . ' ' . ($m['etu_nom'] ?? ''));
                                                                        if ($who === '') {
                                                                            $who = $j['etudiant_prenom'] . ' ' . $j['etudiant_nom'];
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <div class="justif-msg <?php echo $is_staff ? 'staff' : 'student'; ?>">
                                                                        <div class="justif-msg-meta">
                                                                            <?php echo $is_staff ? '<i class="fas fa-user-tie"></i>' : '<i class="fas fa-user-graduate"></i>'; ?>
                                                                            <?php echo htmlspecialchars($who); ?>
                                                                            · <?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?>
                                                                        </div>
                                                                        <div><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if ($peut_message): ?>
                                                            <form method="post" action="">
                                                                <input type="hidden" name="_filtre_statut" value="<?php echo htmlspecialchars($filtre_statut); ?>">
                                                                <input type="hidden" name="_filtre_cours_id" value="<?php echo (int) $filtre_cours; ?>">
                                                                <input type="hidden" name="justification_id" value="<?php echo (int) $j['id']; ?>">
                                                                <input type="hidden" name="action" value="message_staff">
                                                                <div class="mb-2">
                                                                    <label class="form-label" for="message_staff_<?php echo (int) $j['id']; ?>">Répondre à l’étudiant</label>
                                                                    <textarea class="form-control" id="message_staff_<?php echo (int) $j['id']; ?>" name="message_staff" rows="3" required placeholder="Votre message…"></textarea>
                                                                </div>
                                                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Envoyer</button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($j['statut'] !== 'en_attente'): ?>
                                                            <div class="mt-3">
                                                                <p><strong>Statut :</strong> 
                                                                    <?php if ($j['statut'] === 'validee'): ?>
                                                                        <span class="badge bg-success">Validée</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger">Rejetée</span>
                                                                    <?php endif; ?>
                                                                </p>
                                                                <p><strong>Traitée par :</strong> <?php echo htmlspecialchars($j['validateur_nom'] ?? 'Non spécifié'); ?></p>
                                                                <p><strong>Date de traitement :</strong> <?php echo $j['date_validation'] ? date('d/m/Y H:i', strtotime($j['date_validation'])) : 'Non spécifié'; ?></p>
                                                                <?php if (!empty($j['commentaire'])): ?>
                                                                    <div class="card">
                                                                        <div class="card-header bg-light">Commentaire de décision</div>
                                                                        <div class="card-body">
                                                                            <?php echo nl2br(htmlspecialchars($j['commentaire'])); ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($j['date_soumission'])); ?></td>
                                    <td>
                                        <?php if ($j['statut'] === 'en_attente'): ?>
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        <?php elseif ($j['statut'] === 'validee'): ?>
                                            <span class="badge bg-success">Validée</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejetée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($peut_traiter_ui): ?>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#validerModal<?php echo $j['id']; ?>">
                                                <i class="fas fa-check"></i> Valider
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejeterModal<?php echo $j['id']; ?>">
                                                <i class="fas fa-times"></i> Rejeter
                                            </button>
                                            
                                            <!-- Modal pour valider -->
                                            <div class="modal fade" id="validerModal<?php echo $j['id']; ?>" tabindex="-1" aria-labelledby="validerModalLabel<?php echo $j['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title" id="validerModalLabel<?php echo $j['id']; ?>">Valider la justification</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="_filtre_statut" value="<?php echo htmlspecialchars($filtre_statut); ?>">
                                                            <input type="hidden" name="_filtre_cours_id" value="<?php echo (int) $filtre_cours; ?>">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="justification_id" value="<?php echo $j['id']; ?>">
                                                                <input type="hidden" name="action" value="valider">
                                                                <p>Êtes-vous sûr de vouloir valider cette justification d'absence ?</p>
                                                                <div class="mb-3">
                                                                    <label for="commentaire<?php echo $j['id']; ?>_valider" class="form-label">Commentaire (optionnel)</label>
                                                                    <textarea class="form-control" id="commentaire<?php echo $j['id']; ?>_valider" name="commentaire" rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-success">Valider</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Modal pour rejeter -->
                                            <div class="modal fade" id="rejeterModal<?php echo $j['id']; ?>" tabindex="-1" aria-labelledby="rejeterModalLabel<?php echo $j['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title" id="rejeterModalLabel<?php echo $j['id']; ?>">Rejeter la justification</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="_filtre_statut" value="<?php echo htmlspecialchars($filtre_statut); ?>">
                                                            <input type="hidden" name="_filtre_cours_id" value="<?php echo (int) $filtre_cours; ?>">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="justification_id" value="<?php echo $j['id']; ?>">
                                                                <input type="hidden" name="action" value="rejeter">
                                                                <p>Êtes-vous sûr de vouloir rejeter cette justification d'absence ?</p>
                                                                <div class="mb-3">
                                                                    <label for="commentaire<?php echo $j['id']; ?>_rejeter" class="form-label">Motif du rejet</label>
                                                                    <textarea class="form-control" id="commentaire<?php echo $j['id']; ?>_rejeter" name="commentaire" rows="3" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-danger">Rejeter</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($j['statut'] === 'en_attente'): ?>
                                            <span class="text-muted small">En attente</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="fas fa-check"></i> Déjà traitée
                                            </button>
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
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>

<!-- Bootstrap JS (nécessaire pour les modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
