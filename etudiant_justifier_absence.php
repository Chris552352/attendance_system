<?php
/**
 * Soumission d'une justification d'absence (étudiant connecté)
 */

session_start();
require_once 'config/database.php';

if (!isset($_SESSION['etudiant_id'])) {
    header('Location: login_etudiant.php');
    exit();
}

$etudiant_id = (int) $_SESSION['etudiant_id'];
$presence_id = isset($_GET['presence_id']) ? (int) $_GET['presence_id'] : 0;

$message = '';
$type = '';

$presence = null;
if ($presence_id > 0) {
    $presence = db_query_single(
        'SELECT p.*, c.nom AS cours_nom, c.code AS cours_code
         FROM presences p
         LEFT JOIN cours c ON p.cours_id = c.id
         WHERE p.id = ? AND p.etudiant_id = ?',
        [$presence_id, $etudiant_id]
    );
}

if (!$presence || $presence['statut'] !== 'absent') {
    $_SESSION['alerte'] = [
        'type' => 'warning',
        'message' => 'Absence introuvable ou déjà enregistrée comme présence.',
    ];
    header('Location: dashboard_etudiant.php');
    exit();
}

if (!empty($presence['justifie'])) {
    $_SESSION['alerte'] = [
        'type' => 'info',
        'message' => 'Cette absence est déjà marquée comme justifiée.',
    ];
    header('Location: dashboard_etudiant.php');
    exit();
}

$existant = null;
try {
    $existant = db_query_single(
        "SELECT id FROM justifications WHERE presence_id = ? AND statut = 'en_attente'",
        [$presence_id]
    );
} catch (Throwable $e) {
    $existant = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texte = trim($_POST['justification'] ?? '');
    if ($texte === '') {
        $message = 'Veuillez saisir une justification.';
        $type = 'danger';
    } elseif ($existant) {
        $message = 'Une justification est déjà en attente de traitement pour cette absence.';
        $type = 'warning';
    } else {
        try {
            if (db_exec(
                "INSERT INTO justifications (presence_id, contenu, statut) VALUES (?, ?, 'en_attente')",
                [$presence_id, $texte]
            )) {
                $_SESSION['alerte'] = [
                    'type' => 'success',
                    'message' => 'Votre justification a été envoyée. Elle sera examinée par l’administration.',
                ];
                header('Location: dashboard_etudiant.php');
                exit();
            }
            $message = 'Erreur lors de l’enregistrement.';
            $type = 'danger';
        } catch (Throwable $e) {
            $message = 'Le service de justification n’est pas disponible (table manquante). Exécutez le script create_justifications_table.php ou contactez l’administrateur.';
            $type = 'danger';
        }
    }
}

$libelle_cours = $presence['cours_nom'] ?: ('Séance #' . (int) $presence['seance_id']);

include 'includes/header_public.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-signature"></i> Justifier une absence</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <?php if ($existant): ?>
                        <div class="alert alert-warning">Une justification est déjà en attente pour cette absence.</div>
                        <a href="dashboard_etudiant.php" class="btn btn-primary">Retour</a>
                    <?php else: ?>
                        <p><strong>Cours :</strong> <?php echo htmlspecialchars($libelle_cours); ?>
                            <?php if (!empty($presence['cours_code'])): ?>
                                (<?php echo htmlspecialchars($presence['cours_code']); ?>)
                            <?php endif; ?>
                        </p>
                        <p><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($presence['date_presence'])); ?></p>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label" for="justification">Motif / pièces (texte)</label>
                                <textarea class="form-control" id="justification" name="justification" rows="5" required placeholder="Expliquez la raison de votre absence (certificat médical, convocation, etc.)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                            <a href="dashboard_etudiant.php" class="btn btn-link">Annuler</a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
