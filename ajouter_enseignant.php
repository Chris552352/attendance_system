<?php
/**
 * Page d'ajout/modification d'un enseignant
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification et les droits d'administrateur
require_admin();

// Variables pour le formulaire
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'modifier' : 'ajouter';
$enseignant = [
    'nom' => '',
    'prenom' => '',
    'email' => ''
];

// Variable pour stocker les informations de connexion
$login_info = null;

// Mode modification: récupérer les données de l'enseignant
if ($mode === 'modifier') {
    $result = db_query_single("SELECT * FROM utilisateurs WHERE id = ? AND role = 'enseignant'", [$id]);
    
    if (!$result) {
        alerte("Enseignant introuvable.", "danger");
        rediriger('enseignants.php');
    }
    
    $enseignant = $result;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données du formulaire
    $enseignant['nom'] = securiser($_POST['nom'] ?? '');
    $enseignant['prenom'] = securiser($_POST['prenom'] ?? '');
    $enseignant['email'] = securiser($_POST['email'] ?? '');
    
    // Validation
    $erreurs = [];
    
    if (empty($enseignant['nom'])) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    
    if (empty($enseignant['prenom'])) {
        $erreurs[] = "Le prénom est obligatoire.";
    }
    
    if (empty($enseignant['email'])) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($enseignant['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide.";
    } elseif (email_existe($enseignant['email'], 'utilisateurs', $id)) {
        $erreurs[] = "Cet email est déjà utilisé par un autre utilisateur.";
    }
    
    // Si pas d'erreurs, enregistrer l'enseignant
    if (empty($erreurs)) {
        if ($mode === 'ajouter') {
            try {
                // Générer un mot de passe aléatoire
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer un nouvel enseignant dans la table utilisateurs
                $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
                        VALUES (?, ?, ?, ?, 'enseignant')";
                $params = [
                    $enseignant['nom'], $enseignant['prenom'], $enseignant['email'], $password_hash
                ];
                
                if (db_exec($sql, $params)) {
                    $user_id = db_last_insert_id();
                    
                    // Stocker les informations de connexion pour affichage
                    $login_info = [
                        'email' => $enseignant['email'],
                        'password' => $password
                    ];
                    
                    alerte("L'enseignant a été ajouté avec succès.", "success");
                } else {
                    alerte("Erreur lors de l'ajout de l'enseignant.", "danger");
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'ajout de l'enseignant: " . $e->getMessage());
                alerte("Erreur lors de l'ajout de l'enseignant: " . $e->getMessage(), "danger");
            }
        } else {
            // Mettre à jour un enseignant existant
            $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? WHERE id = ? AND role = 'enseignant'";
            $params = [
                $enseignant['nom'], $enseignant['prenom'], $enseignant['email'], $id
            ];
            
            if (db_exec($sql, $params)) {
                alerte("L'enseignant a été mis à jour avec succès.", "success");
                rediriger('enseignants.php');
            } else {
                alerte("Erreur lors de la mise à jour de l'enseignant.", "danger");
            }
        }
    }
}

// Inclure le header
include 'includes/header.php';

// Débogage - Afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<style>
.ajout-enseignant-header {
  width: 100%;
  margin-bottom: 2.5rem;
  position: relative;
  z-index: 1;
  background: rgba(255,255,255,0.78);
  box-shadow: 0 8px 32px 0 #1976d255, 0 1.5px 32px 0 #fff2;
  border-radius: 2.2rem;
  padding: 2.5rem 3rem 2.5rem 3rem;
  display: flex;
  align-items: center;
  gap: 2.5rem;
  backdrop-filter: blur(10px);
  animation: fadeInAjoutEnsHeader 1s cubic-bezier(.22,1,.36,1);
  overflow: hidden;
}
.ajout-enseignant-header-img {
  height: 140px;
  width: 140px;
  object-fit: cover;
  border-radius: 1.5rem;
  box-shadow: 0 2px 32px #1976d233;
  background: #fff;
  padding: 0.7rem;
  filter: blur(0.5px) brightness(1.08);
}
.ajout-enseignant-hero {
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}
.ajout-enseignant-hero i {
  font-size: 2.8rem;
  color: #1976d2;
  filter: drop-shadow(0 2px 8px #1976d255);
}
.ajout-enseignant-hero h1 {
  margin: 0; font-size: 2.2rem; font-weight: 800; color: #0d47a1;
  text-shadow: 0 2px 8px #fff5;
  letter-spacing: 0.5px;
}
@keyframes fadeInAjoutEnsHeader {
  from { opacity: 0; transform: translateY(32px) scale(0.97); }
  to { opacity: 1; transform: none; }
}
.ajout-enseignant-card {
  border-radius: 2.2rem;
  background: rgba(255,255,255,0.92);
  box-shadow: 0 8px 32px 0 #1976d255, 0 1.5px 32px 0 #fff2;
  backdrop-filter: blur(10px);
  padding: 2.7rem 2.7rem 2.2rem 2.7rem;
  margin-bottom: 2rem;
  position: relative; z-index: 2;
  border: 2.5px solid transparent;
  animation: fadeInAjoutEns 1.2s cubic-bezier(.22,1,.36,1);
  transition: box-shadow 0.2s, border 0.5s;
  border-image: linear-gradient(120deg, #1976d2 20%, #7b1fa2 80%) 1;
}
.ajout-enseignant-card:hover {
  box-shadow: 0 16px 48px 0 #1976d288, 0 1.5px 32px 0 #fff3;
  border: 2.5px solid #7b1fa2;
}
@keyframes fadeInAjoutEns {
  from { opacity: 0; transform: translateY(40px) scale(0.98); }
  to { opacity: 1; transform: none; }
}
.btn.ajout-enseignant-action {
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
.btn.ajout-enseignant-action:hover {
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
<div class="ajout-enseignant-header">
    <img src="Nouveau dossier/enseignant.jpg" class="ajout-enseignant-header-img" alt="Décor enseignant">
    <div class="ajout-enseignant-hero">
        <i class="fas fa-chalkboard-teacher"></i>
        <h1><?php echo $mode === 'ajouter' ? 'Ajouter un enseignant' : 'Modifier un enseignant'; ?></h1>
    </div>
</div>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">
            <i class="fas fa-chalkboard-teacher"></i> 
            <?php echo $mode === 'ajouter' ? 'Ajouter un enseignant' : 'Modifier l\'enseignant'; ?>
        </h1>
        <div>
            <a href="enseignants.php" class="btn btn-outline-secondary">
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
            <h4><i class="fas fa-key"></i> Informations de connexion</h4>
            <p>L'enseignant a u00e9té ajouté avec succès. Voici ses informations de connexion :</p>
            <ul>
                <li><strong>Email:</strong> <?php echo htmlspecialchars($login_info['email']); ?></li>
                <li><strong>Mot de passe:</strong> <?php echo htmlspecialchars($login_info['password']); ?></li>
            </ul>
            <p class="mb-0"><small>Veuillez communiquer ces informations u00e0 l'enseignant. Il pourra se connecter et changer son mot de passe ultérieurement.</small></p>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="ajout-enseignant-card">
    <form method="POST" action="" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6">
                <div class="ajout-enseignant-floating-label">
                    <input type="text" id="nom" name="nom" class="form-control" placeholder=" " value="<?php echo htmlspecialchars($enseignant['nom']); ?>" required>
                    <label for="nom">Nom <span class="text-danger">*</span></label>
                    <i class="fa fa-user input-icon"></i>
                    <div class="invalid-feedback">Veuillez entrer un nom.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ajout-enseignant-floating-label">
                    <input type="text" id="prenom" name="prenom" class="form-control" placeholder=" " value="<?php echo htmlspecialchars($enseignant['prenom']); ?>" required>
                    <label for="prenom">Prénom <span class="text-danger">*</span></label>
                    <i class="fa fa-user-edit input-icon"></i>
                    <div class="invalid-feedback">Veuillez entrer un prénom.</div>
                </div>
            </div>
        </div>
        <div class="ajout-enseignant-floating-label">
            <input type="email" id="email" name="email" class="form-control" placeholder=" " value="<?php echo htmlspecialchars($enseignant['email']); ?>" required>
            <label for="email">Email <span class="text-danger">*</span></label>
            <i class="fa fa-envelope input-icon"></i>
            <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
        </div>
        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-ens-anim">
                <i class="fas fa-save"></i> <?php echo $mode === 'ajouter' ? 'Ajouter l\'enseignant' : 'Enregistrer les modifications'; ?>
            </button>
        </div>
    </form>
</div>
        </div>

        <div class="col-md-4">
    <div class="enseignant-avatar-block">
        <img src="assets/images/teacher.svg" alt="Enseignant" class="enseignant-avatar-img">
        <div style="font-weight:700; color:#1976d2; font-size:1.15rem; letter-spacing:0.5px;">Enseignant</div>
    </div>
</div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
