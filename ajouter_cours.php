<?php
/**
 * Page d'ajout/modification d'un cours
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Variables pour le formulaire
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'modifier' : 'ajouter';
$cours = [
    'nom' => '',
    'code' => '',
    'description' => '',
    'enseignant_id' => null
];

// Récupérer la liste des enseignants (depuis la table utilisateurs)
$enseignants = db_query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'enseignant' ORDER BY nom, prenom");

// Mode modification: récupérer les données du cours
if ($mode === 'modifier') {
    $result = db_query_single("SELECT * FROM cours WHERE id = ?", [$id]);
    
    if (!$result) {
        alerte("Cours introuvable.", "danger");
        rediriger('cours.php');
    }
    
    $cours = $result;
}

// Variable pour stocker les informations de connexion de l'enseignant
$login_info = null;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données du formulaire
    $cours['nom'] = securiser($_POST['nom'] ?? '');
    $cours['code'] = securiser($_POST['code'] ?? '');
    $cours['description'] = securiser($_POST['description'] ?? '');
    
    // Traitement de l'ajout d'un nouvel enseignant si nécessaire
    if (isset($_POST['enseignant_id']) && $_POST['enseignant_id'] === 'nouveau' && 
        !empty($_POST['nouvel_enseignant_nom']) && 
        !empty($_POST['nouvel_enseignant_prenom']) && 
        !empty($_POST['nouvel_enseignant_email'])) {
        
        $nouvel_enseignant = [
            'nom' => securiser($_POST['nouvel_enseignant_nom']),
            'prenom' => securiser($_POST['nouvel_enseignant_prenom']),
            'email' => securiser($_POST['nouvel_enseignant_email'])
        ];
        
        // Vérifier si l'email existe déjà
        if (email_existe($nouvel_enseignant['email'], 'utilisateurs')) {
            $erreurs[] = "Cet email est déjà utilisé par un autre utilisateur.";
        } else {
            // Générer un mot de passe aléatoire
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer le nouvel enseignant
            $sql_enseignant = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
                               VALUES (?, ?, ?, ?, 'enseignant')";
            $params_enseignant = [
                $nouvel_enseignant['nom'], $nouvel_enseignant['prenom'], $nouvel_enseignant['email'], $password_hash
            ];
            
            if (db_exec($sql_enseignant, $params_enseignant)) {
                // Récupérer l'ID du nouvel enseignant
                $cours['enseignant_id'] = db_last_insert_id();
                
                // Stocker les informations de connexion pour affichage
                $login_info = [
                    'email' => $nouvel_enseignant['email'],
                    'password' => $password,
                    'nom' => $nouvel_enseignant['nom'],
                    'prenom' => $nouvel_enseignant['prenom']
                ];
            } else {
                $erreurs[] = "Erreur lors de l'ajout de l'enseignant.";
            }
        }
    } else {
        // Utiliser l'enseignant sélectionné dans le menu déroulant
        $cours['enseignant_id'] = !empty($_POST['enseignant_id']) && $_POST['enseignant_id'] !== 'nouveau' ? (int)$_POST['enseignant_id'] : null;
    }
    
    // Validation
    $erreurs = [];
    
    if (empty($cours['nom'])) {
        $erreurs[] = "Le nom du cours est obligatoire.";
    }
    
    if (empty($cours['code'])) {
        $erreurs[] = "Le code du cours est obligatoire.";
    }
    
    // Vérifier si le code existe déjà (sauf pour le cours actuel en mode modification)
    $code_check = db_query_single("SELECT id FROM cours WHERE code = ? AND id != ?", [$cours['code'], $id]);
    if ($code_check) {
        $erreurs[] = "Ce code de cours est déjà utilisé.";
    }
    
    // Si pas d'erreurs, enregistrer le cours
    if (empty($erreurs)) {
        if ($mode === 'ajouter') {
            // Insérer un nouveau cours
            $sql = "INSERT INTO cours (nom, code, description, enseignant_id) VALUES (?, ?, ?, ?)";
            $params = [
                $cours['nom'], $cours['code'], $cours['description'], $cours['enseignant_id']
            ];
            
            if (db_exec($sql, $params)) {
                alerte("Le cours a été ajouté avec succès.", "success");
                rediriger('cours.php');
            } else {
                alerte("Erreur lors de l'ajout du cours.", "danger");
            }
        } else {
            // Mettre à jour un cours existant
            $sql = "UPDATE cours SET nom = ?, code = ?, description = ?, enseignant_id = ? WHERE id = ?";
            $params = [
                $cours['nom'], $cours['code'], $cours['description'], $cours['enseignant_id'], $id
            ];
            
            if (db_exec($sql, $params)) {
                alerte("Le cours a été mis à jour avec succès.", "success");
                rediriger('cours.php');
            } else {
                alerte("Erreur lors de la mise à jour du cours.", "danger");
            }
        }
    }
}

// Inclure le header
include 'includes/header.php';
?>

<style>
.ajout-cours-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.ajout-cours-bg img {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  opacity: 0.13;
  filter: blur(2.5px) grayscale(0.08);
}
.ajout-cours-hero {
  margin-bottom: 2rem;
  padding: 2rem 1rem 1.5rem 1rem;
  border-radius: 2rem;
  background: rgba(255,255,255,0.68);
  box-shadow: 0 8px 32px 0 rgba(123,31,162,0.18);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; gap: 1.5rem;
  position: relative; z-index: 2;
}
.ajout-cours-hero i {
  font-size: 2.5rem;
  color: #1976d2;
  filter: drop-shadow(0 2px 8px #1976d255);
}
.ajout-cours-hero h1 {
  margin: 0; font-size: 2.1rem; font-weight: 700; color: #0d47a1;
  text-shadow: 0 2px 8px #fff5;
}
.ajout-cours-card {
  border-radius: 2rem;
  background: rgba(255,255,255,0.88);
  box-shadow: 0 8px 32px 0 #1976d255, 0 1.5px 32px 0 #fff2;
  backdrop-filter: blur(8px);
  padding: 2.5rem 2.5rem 2rem 2.5rem;
  margin-bottom: 2rem;
  position: relative; z-index: 2;
  border: 2.5px solid transparent;
  animation: fadeInAjoutCours 1.2s cubic-bezier(.22,1,.36,1);
  transition: box-shadow 0.2s, border 0.5s;
  border-image: linear-gradient(120deg, #1976d2 20%, #7b1fa2 80%) 1;
}
.ajout-cours-card:hover {
  box-shadow: 0 16px 48px 0 #1976d288, 0 1.5px 32px 0 #fff3;
  border: 2.5px solid #7b1fa2;
}
@keyframes fadeInAjoutCours {
  from { opacity: 0; transform: translateY(40px) scale(0.98); }
  to { opacity: 1; transform: none; }
}
.btn.ajout-cours-action {
  border-radius: 2.5rem;
  font-weight: 700;
  font-size: 1.15rem;
  padding: 0.8rem 2.5rem;
  background: linear-gradient(90deg,#1976d2,#7b1fa2 90%);
  color: #fff;
  border: none;
  box-shadow: 0 4px 16px #1976d255;
  transition: background 0.22s, color 0.18s, box-shadow 0.22s, transform 0.18s;
  position: relative;
  overflow: hidden;
}
.btn.ajout-cours-action:hover {
  background: linear-gradient(90deg,#7b1fa2 10%,#1976d2 100%);
  color: #fff;
  box-shadow: 0 8px 24px #7b1fa288;
  transform: translateY(-2px) scale(1.03);
}
.alert {
  animation: fadeInAlert 1s;
  border-radius: 1.5rem;
  box-shadow: 0 2px 12px #f4433622;
}
@keyframes fadeInAlert {
  from { opacity: 0; transform: translateY(-18px); }
  to { opacity: 1; transform: none; }
}
</style>
<div class="ajout-cours-bg">
    <img src="Nouveau dossier/capcompetences-plandecours-644.jpg" alt="Décor ajout cours">
</div>
<div class="ajout-cours-hero">
    <i class="fas fa-book"></i>
    <h1><?php echo $mode === 'ajouter' ? 'Ajouter un cours' : 'Modifier le cours'; ?></h1>
</div>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">
            <i class="fas fa-book"></i> 
            <?php echo $mode === 'ajouter' ? 'Ajouter un cours' : 'Modifier le cours'; ?>
        </h1>
        <div>
            <a href="cours.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <?php if (isset($erreurs) && !empty($erreurs)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?php echo $erreur; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($login_info): ?>
        <div class="alert alert-success">
            <h4><i class="fas fa-user-plus"></i> Nouvel enseignant ajouté</h4>
            <p>L'enseignant <strong><?php echo htmlspecialchars($login_info['nom'] . ' ' . $login_info['prenom']); ?></strong> a u00e9té ajouté avec succès et assigné u00e0 ce cours.</p>
            <div class="card mt-2">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-key"></i> Informations de connexion</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($login_info['email']); ?></li>
                        <li><strong>Mot de passe:</strong> <?php echo htmlspecialchars($login_info['password']); ?></li>
                    </ul>
                    <p class="mt-2 mb-0"><small>Veuillez communiquer ces informations u00e0 l'enseignant. Il pourra se connecter et changer son mot de passe ultérieurement.</small></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <?php echo $mode === 'ajouter' ? 'Nouveau cours' : 'Modifier les informations'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="nom" class="form-label">Nom du cours <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($cours['nom']); ?>" required>
                                <div class="invalid-feedback">Veuillez entrer un nom de cours.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="code" class="form-label">Code du cours <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($cours['code']); ?>" required>
                                <div class="invalid-feedback">Veuillez entrer un code de cours.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="enseignant_id" class="form-label">Enseignant responsable</label>
                            <div class="input-group">
                                <select class="form-select" id="enseignant_id" name="enseignant_id">
                                    <option value="">-- Sélectionner un enseignant --</option>
                                    <option value="nouveau" style="font-weight: bold; color: #0d6efd;">+ Ajouter un nouvel enseignant</option>
                                    <?php foreach ($enseignants as $e): ?>
                                        <option value="<?php echo $e['id']; ?>" <?php echo $cours['enseignant_id'] == $e['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($e['nom'] . ' ' . $e['prenom'] . ' (' . $e['email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-text">L'enseignant sélectionné aura accès à ce cours et pourra marquer les présences.</div>
                        </div>
                        
                        <!-- Formulaire d'ajout d'enseignant (caché par défaut) -->
                        <div id="nouvel-enseignant-form" class="card mb-3" style="display: none;">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> Ajouter un nouvel enseignant</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nouvel_enseignant_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nouvel_enseignant_nom" name="nouvel_enseignant_nom" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nouvel_enseignant_prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nouvel_enseignant_prenom" name="nouvel_enseignant_prenom" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="nouvel_enseignant_email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="nouvel_enseignant_email" name="nouvel_enseignant_email" required>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Un mot de passe aléatoire sera généré automatiquement et affiché après l'enregistrement.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description du cours</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($cours['description']); ?></textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $mode === 'ajouter' ? 'Ajouter le cours' : 'Enregistrer les modifications'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <?php echo $mode === 'ajouter' ? 'Nouveau cours' : 'Modifier les informations'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mt-3">
                        <img src="https://source.unsplash.com/random/400x300/?cameroon,lecture" alt="Cours" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>

<script>
// JavaScript pour afficher/masquer le formulaire d'ajout d'enseignant
document.addEventListener('DOMContentLoaded', function() {
    const enseignantSelect = document.getElementById('enseignant_id');
    const nouvelEnseignantForm = document.getElementById('nouvel-enseignant-form');
    const nouvelEnseignantNom = document.getElementById('nouvel_enseignant_nom');
    const nouvelEnseignantPrenom = document.getElementById('nouvel_enseignant_prenom');
    const nouvelEnseignantEmail = document.getElementById('nouvel_enseignant_email');
    
    // Fonction pour afficher/masquer le formulaire
    function toggleNouvelEnseignantForm() {
        if (enseignantSelect.value === 'nouveau') {
            nouvelEnseignantForm.style.display = 'block';
            nouvelEnseignantNom.required = true;
            nouvelEnseignantPrenom.required = true;
            nouvelEnseignantEmail.required = true;
        } else {
            nouvelEnseignantForm.style.display = 'none';
            nouvelEnseignantNom.required = false;
            nouvelEnseignantPrenom.required = false;
            nouvelEnseignantEmail.required = false;
        }
    }
    
    // Exécuter au chargement de la page
    toggleNouvelEnseignantForm();
    
    // Ajouter un écouteur d'événement pour le changement de sélection
    enseignantSelect.addEventListener('change', toggleNouvelEnseignantForm);
});
</script>
