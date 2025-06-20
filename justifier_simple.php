<?php
/**
 * Page simplifiée pour justifier les absences
 */

// Inclure les fichiers nécessaires
require_once 'includes/functions.php';
require_once 'config/database.php';

// Initialiser les variables
$matricule = '';
$absences = [];
$message = '';
$etudiant = null;

// Traitement du formulaire de recherche d'étudiant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rechercher'])) {
    $matricule = securiser($_POST['matricule']);
    
    // Rechercher l'étudiant
    $etudiant = db_query_single("SELECT * FROM etudiants WHERE matricule = ?", [$matricule]);
    
    if ($etudiant) {
        // Récupérer les absences de l'étudiant
        $absences = db_query(
            "SELECT p.id, p.date_presence, c.nom as cours_nom, c.code as cours_code, p.justifie 
            FROM presences p 
            JOIN cours c ON p.cours_id = c.id 
            WHERE p.etudiant_id = ? AND p.statut = 'absent' 
            ORDER BY p.date_presence DESC",
            [$etudiant['id']]
        );
    } else {
        $message = "Aucun étudiant trouvé avec ce matricule.";
    }
}

// Traitement du formulaire de justification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['justifier'])) {
    $absence_id = (int)$_POST['absence_id'];
    $justification = securiser($_POST['justification']);
    $matricule = securiser($_POST['matricule']);
    
    if (empty($justification)) {
        $message = "Veuillez fournir une justification.";
    } else {
        // Mettre à jour directement la table presences pour simplifier
        $result = db_exec(
            "UPDATE presences SET justification = ?, justifie = TRUE WHERE id = ?",
            [$justification, $absence_id]
        );
        
        if ($result) {
            $message = "Votre absence a été justifiée avec succès.";
            
            // Rechercher à nouveau l'étudiant pour mettre à jour la liste des absences
            $etudiant = db_query_single("SELECT * FROM etudiants WHERE matricule = ?", [$matricule]);
            
            if ($etudiant) {
                // Récupérer les absences de l'étudiant
                $absences = db_query(
                    "SELECT p.id, p.date_presence, c.nom as cours_nom, c.code as cours_code, p.justifie 
                    FROM presences p 
                    JOIN cours c ON p.cours_id = c.id 
                    WHERE p.etudiant_id = ? AND p.statut = 'absent' 
                    ORDER BY p.date_presence DESC",
                    [$etudiant['id']]
                );
            }
        } else {
            $message = "Erreur lors de la justification de l'absence.";
        }
    }
}

// Inclure le header
include 'includes/header_public.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justification d'Absences</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .card-header {
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-clipboard-check"></i> Justification d'Absences</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label for="matricule" class="form-label">Entrez votre matricule</label>
                                <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo $matricule; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="rechercher" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($etudiant): ?>
                        <div class="alert alert-success">
                            <h5><i class="fas fa-user"></i> Étudiant: <?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></h5>
                            <p class="mb-0">Matricule: <?php echo htmlspecialchars($etudiant['matricule']); ?></p>
                        </div>
                        
                        <?php if (empty($absences)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle"></i> Vous n'avez aucune absence à justifier.
                            </div>
                        <?php else: ?>
                            <h4 class="mb-3">Liste de vos absences</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Cours</th>
                                            <th>Statut</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($absences as $absence): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($absence['date_presence'])); ?></td>
                                                <td><?php echo htmlspecialchars($absence['cours_nom'] . ' (' . $absence['cours_code'] . ')'); ?></td>
                                                <td>
                                                    <?php if ($absence['justifie']): ?>
                                                        <span class="badge bg-success">Justifiée</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Non justifiée</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!$absence['justifie']): ?>
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="absence_id" value="<?php echo $absence['id']; ?>">
                                                            <input type="hidden" name="matricule" value="<?php echo $matricule; ?>">
                                                            <div class="mb-3">
                                                                <textarea class="form-control form-control-sm" name="justification" rows="2" placeholder="Entrez votre justification ici" required></textarea>
                                                            </div>
                                                            <button type="submit" name="justifier" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-save"></i> Justifier
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-success"><i class="fas fa-check-circle"></i> Déjà justifiée</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="alert alert-warning mt-3">

                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
