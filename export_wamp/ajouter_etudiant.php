<?php
/**
 * Page d'ajout/modification d'un étudiant
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Variables pour le formulaire
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'modifier' : 'ajouter';
$etudiant = [
    'nom' => '',
    'prenom' => '',
    'matricule' => '',
    'email' => '',
    'telephone' => '',
    'date_naissance' => '',
    'adresse' => '',
    'cours_ids' => []
];

// Récupérer la liste des cours
$cours = db_query("SELECT id, nom, code FROM cours ORDER BY nom");

// Mode modification: récupérer les données de l'étudiant
if ($mode === 'modifier') {
    $result = db_query_single("SELECT * FROM etudiants WHERE id = ?", [$id]);
    
    if (!$result) {
        alerte("Étudiant introuvable.", "danger");
        rediriger('etudiants.php');
    }
    
    // Assurez-vous que tous les champs sont définis, même s'ils sont NULL dans la base de données
    $etudiant = array_merge($etudiant, $result);
    
    // Récupérer les cours auxquels l'étudiant est inscrit
    $inscriptions = db_query("SELECT cours_id FROM inscriptions WHERE etudiant_id = ?", [$id]);
    $etudiant['cours_ids'] = array_column($inscriptions, 'cours_id');
    
    // Déboguer les valeurs récupérées pour vérifier
    // echo '<pre>'; print_r($etudiant); echo '</pre>';
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données du formulaire
    $etudiant['nom'] = securiser($_POST['nom'] ?? '');
    $etudiant['prenom'] = securiser($_POST['prenom'] ?? '');
    $etudiant['email'] = securiser($_POST['email'] ?? '');
    $etudiant['telephone'] = securiser($_POST['telephone'] ?? '');
    $etudiant['date_naissance'] = securiser($_POST['date_naissance'] ?? '');
    $etudiant['adresse'] = securiser($_POST['adresse'] ?? '');
    $etudiant['cours_ids'] = isset($_POST['cours']) ? $_POST['cours'] : [];
    
    // Générer un matricule si c'est un nouvel étudiant
    if ($mode === 'ajouter') {
        $etudiant['matricule'] = generer_id_etudiant();
    }
    
    // Validation
    $erreurs = [];
    
    if (empty($etudiant['nom'])) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    
    if (empty($etudiant['prenom'])) {
        $erreurs[] = "Le prénom est obligatoire.";
    }
    
    if (empty($etudiant['email'])) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($etudiant['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide.";
    } elseif (email_existe($etudiant['email'], 'etudiants', $id)) {
        $erreurs[] = "Cet email est déjà utilisé par un autre étudiant.";
    }
    
    // Si pas d'erreurs, enregistrer l'étudiant
    if (empty($erreurs)) {
        if ($mode === 'ajouter') {
            // Insérer un nouvel étudiant
            $fields = ['nom', 'prenom', 'matricule', 'email'];
            $params = [$etudiant['nom'], $etudiant['prenom'], $etudiant['matricule'], $etudiant['email']];
            
            // Ajouter les champs optionnels s'ils existent dans la table
            $optional_fields = ['telephone', 'date_naissance', 'adresse'];
            foreach ($optional_fields as $field) {
                $fields[] = $field;
                $params[] = isset($etudiant[$field]) ? $etudiant[$field] : '';
            }
            
            // Construire la requête d'insertion dynamiquement
            $placeholders = array_fill(0, count($fields), '?');
            $sql = "INSERT INTO etudiants (" . implode(', ', $fields) . ") 
                   VALUES (" . implode(', ', $placeholders) . ")";
            if (db_exec($sql, $params)) {
                $etudiant_id = db_last_insert_id();
                
                // Ajouter les inscriptions aux cours
                foreach ($etudiant['cours_ids'] as $cours_id) {
                    db_exec("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)", [$etudiant_id, $cours_id]);
                }
                
                alerte("L'étudiant a été ajouté avec succès.", "success");
                rediriger('etudiants.php');
            } else {
                alerte("Erreur lors de l'ajout de l'étudiant.", "danger");
            }
        } else {
            // Mettre à jour un étudiant existant
            $fields = ['nom', 'prenom', 'matricule', 'email'];
            $params = [$etudiant['nom'], $etudiant['prenom'], $etudiant['matricule'], $etudiant['email']];
            
            // Ajouter les champs optionnels s'ils existent dans la table
            $optional_fields = ['telephone', 'date_naissance', 'adresse'];
            foreach ($optional_fields as $field) {
                $fields[] = $field;
                $params[] = isset($etudiant[$field]) ? $etudiant[$field] : '';
            }
            
            // Construire la requête de mise à jour dynamiquement
            $set_clauses = [];
            foreach ($fields as $field) {
                $set_clauses[] = "$field = ?";
            }
            
            $sql = "UPDATE etudiants SET " . implode(', ', $set_clauses) . " WHERE id = ?";
            $params[] = $id; // Ajouter l'ID à la fin des paramètres
            if (db_exec($sql, $params)) {
                // Supprimer les anciennes inscriptions
                db_exec("DELETE FROM inscriptions WHERE etudiant_id = ?", [$id]);
                
                // Ajouter les nouvelles inscriptions
                foreach ($etudiant['cours_ids'] as $cours_id) {
                    db_exec("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)", [$id, $cours_id]);
                }
                
                alerte("L'étudiant a été mis à jour avec succès.", "success");
                rediriger('etudiants.php');
            } else {
                alerte("Erreur lors de la mise à jour de l'étudiant.", "danger");
            }
        }
    }
}

// Inclure le header
include 'includes/header.php';
?>

<!-- Inclure le CSS amélioré pour la page ajouter étudiant -->
<link rel="stylesheet" href="assets/css/ajouter-etudiant-enhanced.css?v=<?= time() ?>">

<style>
.add-student-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.add-student-bg img {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  opacity: 0.13;
  filter: blur(2.5px) grayscale(0.08);
}
.add-student-hero {
  margin-bottom: 2rem;
  padding: 2rem 1rem 1.5rem 1rem;
  border-radius: 2rem;
  background: rgba(255,255,255,0.68);
  box-shadow: 0 8px 32px 0 rgba(123,31,162,0.18);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; gap: 1.5rem;
  position: relative; z-index: 2;
}
.add-student-hero i {
  font-size: 2.5rem;
  color: #7b1fa2;
  filter: drop-shadow(0 2px 8px #7b1fa255);
}
.add-student-hero h1 {
  margin: 0; font-size: 2.1rem; font-weight: 700; color: #4a148c;
  text-shadow: 0 2px 8px #fff5;
}
.add-student-card {
  border-radius: 2rem;
  background: rgba(255,255,255,0.88);
  box-shadow: 0 8px 32px 0 #7b1fa255, 0 1.5px 32px 0 #fff2;
  backdrop-filter: blur(8px);
  padding: 2.5rem 2.5rem 2rem 2.5rem;
  margin-bottom: 2rem;
  position: relative; z-index: 2;
  border: 2.5px solid transparent;
  animation: fadeInProfil 1.2s cubic-bezier(.22,1,.36,1);
  transition: box-shadow 0.2s, border 0.5s;
  border-image: linear-gradient(120deg, #7b1fa2 20%, #4a148c 80%) 1;
}
.add-student-card:hover {
  box-shadow: 0 16px 48px 0 #7b1fa288, 0 1.5px 32px 0 #fff3;
  border: 2.5px solid #4a148c;
}
@keyframes fadeInProfil {
  from { opacity: 0; transform: translateY(40px) scale(0.98); }
  to { opacity: 1; transform: none; }
}
.btn.add-student-action {
  border-radius: 2.5rem;
  font-weight: 700;
  font-size: 1.15rem;
  padding: 0.8rem 2.5rem;
  background: linear-gradient(90deg,#7b1fa2,#4a148c 90%);
  color: #fff;
  border: none;
  box-shadow: 0 4px 16px #7b1fa255;
  transition: background 0.22s, color 0.18s, box-shadow 0.22s, transform 0.18s;
  position: relative;
  overflow: hidden;
}
.btn.add-student-action:hover {
  background: linear-gradient(90deg,#4a148c 10%,#7b1fa2 100%);
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
<div class="add-student-bg">
    <img src="Nouveau dossier/school.jpg" alt="Décor ajout étudiant">
</div>
<div class="add-student-hero">
    <i class="fas fa-user-plus"></i>
    <h1><?php echo $mode === 'ajouter' ? 'Ajouter un étudiant' : 'Modifier un étudiant'; ?></h1>
</div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="add-student-title form-animation">
            <i class="fas fa-user-graduate"></i> 
            <?php echo $mode === 'ajouter' ? 'Ajouter un étudiant' : 'Modifier l\'étudiant'; ?>
        </h1>
        <div class="form-animation" style="animation-delay: 0.2s;">
            <a href="etudiants.php" class="btn-back">
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

    <div class="row">
        <div class="col-md-8">
                </form>
            </div>
            <script>
            function previewStudentPhoto(event) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('studentPhotoPreview').src = e.target.result;
                }
                if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
            }
            </script>
            <style>
            .student-photo-upload {text-align:center;}
            .student-photo-label {font-weight:600;font-size:1.1rem;color:#1976d2;display:block;margin-bottom:0.5rem;}
            .student-photo-label i {margin-right:0.3em;}
            .student-photo-tooltip {font-size:0.9em;color:#7b1fa2;margin-left:0.5em;}
            .student-photo-preview-wrapper {display:flex;justify-content:center;align-items:center;}
            .student-photo-input {display:none;}
            .student-photo-preview-area {position:relative;cursor:pointer;display:inline-block;transition:box-shadow 0.2s;}
            .student-photo-preview-img {width:120px;height:120px;object-fit:cover;border-radius:50%;box-shadow:0 4px 24px #1976d233;border:4px solid #fff;transition:box-shadow 0.2s;}
            .student-photo-preview-area:hover .student-photo-preview-img {box-shadow:0 8px 32px #7b1fa244;}
            .student-photo-edit-overlay {position:absolute;bottom:0;left:0;right:0;background:rgba(25,118,210,0.72);color:#fff;font-size:0.95em;padding:0.3em 0;border-radius:0 0 50% 50%;opacity:0;transition:opacity 0.18s;}
            .student-photo-preview-area:hover .student-photo-edit-overlay {opacity:1;}
            </style>

            <div class="add-student-card form-animation" style="animation-delay: 0.3s;">
                <div class="add-student-card-header">
                    <h5 class="add-student-card-title">
                        <i class="fas fa-user-edit"></i> <?php echo $mode === 'ajouter' ? 'Nouvel étudiant' : 'Modifier les informations'; ?>
                    </h5>
                </div>
                <div class="add-student-card-body">
                    <form action="" method="post">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        
                        <div class="row">
                            <div class="col-md-6 form-group form-animation form-animation-delay-1">
                                <label for="nom" class="form-label required-field">Nom</label>
                                <input type="text" class="form-control <?= isset($erreurs['nom']) ? 'is-invalid' : '' ?>" id="nom" name="nom" value="<?= htmlspecialchars($etudiant['nom']) ?>" required>
                                <span class="focus-border"></span>
                                <?php if (isset($erreurs['nom'])): ?>
                                    <div class="error-message"><?= $erreurs['nom'] ?></div>
                                <?php endif; ?>

                            </div>
                            
                            <div class="col-md-6 form-group form-animation form-animation-delay-1">
                                <label for="prenom" class="form-label required-field">Prénom</label>
                                <input type="text" class="form-control <?= isset($erreurs['prenom']) ? 'is-invalid' : '' ?>" id="prenom" name="prenom" value="<?= htmlspecialchars($etudiant['prenom']) ?>" required>
                                <span class="focus-border"></span>
                                <?php if (isset($erreurs['prenom'])): ?>
                                    <div class="error-message"><?= $erreurs['prenom'] ?></div>
                                <?php endif; ?>

                            </div>
                        </div>

                        <?php if ($mode === 'modifier'): ?>
                            <div class="mb-3">
                                <label for="matricule" class="form-label">Matricule</label>
                                <input type="text" class="form-control" id="matricule" value="<?php echo htmlspecialchars($etudiant['matricule']); ?>" disabled>
                                <div class="form-text">Le matricule est généré automatiquement et ne peut pas être modifié.</div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($etudiant['email']); ?>" required>
                            <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                        </div>

                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo isset($etudiant['telephone']) ? htmlspecialchars($etudiant['telephone']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="date_naissance" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo isset($etudiant['date_naissance']) ? htmlspecialchars($etudiant['date_naissance']) : ''; ?>">
                        </div>

                        <div class="form-group form-animation" style="animation-delay: 0.4s;">
                            <label for="adresse" class="form-label">Adresse</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea class="form-control <?= isset($erreurs['adresse']) ? 'is-invalid' : '' ?>" id="adresse" name="adresse" rows="3"><?= htmlspecialchars($etudiant['adresse'] ?? '') ?></textarea>
                            </div>
                            <span class="focus-border"></span>
                            <?php if (isset($erreurs['adresse'])): ?>
                                <div class="error-message"><?= $erreurs['adresse'] ?></div>
                            <?php endif; ?>

                        </div>

                        <div class="form-group form-animation" style="animation-delay: 0.45s;">
                            <label for="cours" class="form-label">Cours <span class="tooltip-icon" data-tooltip="Sélectionnez les cours auxquels l'étudiant est inscrit">?</span></label>
                            <div class="courses-list">
                                <?php foreach ($cours as $c): ?>
                                <label class="custom-checkbox">
                                    <?php echo htmlspecialchars($c['nom'] . ' (' . $c['code'] . ')'); ?>
                                    <input type="checkbox" name="cours[]" value="<?php echo $c['id']; ?>" <?php echo in_array($c['id'], $etudiant['cours_ids']) ? 'checked' : ''; ?> >
                                    <span class="checkmark"></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <small class="help-text">Cochez les cours auxquels l'étudiant est inscrit</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end form-animation" style="animation-delay: 0.5s;">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save"></i> <?php echo $mode === 'ajouter' ? 'Ajouter l\'étudiant' : 'Enregistrer les modifications'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Colonne de droite vide pour l'équilibre visuel -->
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
