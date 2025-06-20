<?php
/**
 * Page de profil utilisateur
 */

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'config/database.php';

// Vérifier l'authentification
require_auth();

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$user = db_query_single("SELECT * FROM utilisateurs WHERE id = ?", [$user_id]);

if (!$user) {
    alerte("Erreur lors de la récupération des informations utilisateur.", "danger");
    rediriger('accueil.php');
}

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données du formulaire
    $nom = securiser($_POST['nom'] ?? '');
    $email = securiser($_POST['email'] ?? '');
    
    // Validation
    $erreurs = [];
    
    if (empty($nom)) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    
    if (empty($email)) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide.";
    } elseif ($email !== $user['email'] && email_existe($email, 'utilisateurs', $user_id)) {
        $erreurs[] = "Cet email est déjà utilisé par un autre utilisateur.";
    }
    
    // Si pas d'erreurs, mettre à jour le profil
    if (empty($erreurs)) {
        $sql = "UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?";
        $params = [$nom, $email, $user_id];
        
        if (db_exec($sql, $params)) {
            // Mettre à jour les informations de session
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_email'] = $email;
            
            alerte("Votre profil a été mis à jour avec succès.", "success");
            rediriger('profil.php');
        } else {
            alerte("Erreur lors de la mise à jour du profil.", "danger");
        }
    }
}

// Inclure le header
include 'includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');
body, .profil-card, .page-hero-profil { font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif; }

.profil-full-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.profil-full-bg img {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  opacity: 0.14;
  filter: blur(2.5px) grayscale(0.08);
}
.page-hero-profil {
  margin-bottom: 2rem;
  padding: 2rem 1rem 1.5rem 1rem;
  border-radius: 2rem;
  background: rgba(255,255,255,0.68);
  box-shadow: 0 8px 32px 0 rgba(25,118,210,0.18);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; gap: 1.5rem;
  position: relative; z-index: 2;
}
.page-hero-profil i {
  font-size: 2.5rem;
  color: #1976d2;
  filter: drop-shadow(0 2px 8px #1976d255);
}
.page-hero-profil h1 {
  margin: 0; font-size: 2.1rem; font-weight: 700; color: #0d47a1;
  text-shadow: 0 2px 8px #fff5;
}
.profil-card {
  border-radius: 2.5rem;
  background: rgba(255,255,255,0.89);
  box-shadow: 0 8px 32px 0 #1976d255, 0 1.5px 32px 0 #fff2;
  backdrop-filter: blur(8px);
  padding: 2.5rem 2.5rem 2rem 2.5rem;
  margin-bottom: 2rem;
  position: relative; z-index: 2;
  border: 2.5px solid transparent;
  animation: fadeInProfil 1.2s cubic-bezier(.22,1,.36,1);
  transition: box-shadow 0.2s, border 0.5s;
  border-image: linear-gradient(120deg, #1976d2 20%, #7b1fa2 80%) 1;
}
.profil-card:hover {
  box-shadow: 0 16px 48px 0 #1976d288, 0 1.5px 32px 0 #fff3;
  border: 2.5px solid #7b1fa2;
}
@keyframes fadeInProfil {
  from { opacity: 0; transform: translateY(40px) scale(0.98); }
  to { opacity: 1; transform: none; }
}

.btn.profil-action {
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
.btn.profil-action i {
  animation: rotateIcon 2.5s infinite linear;
  margin-right: 8px;
}
@keyframes rotateIcon {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.btn.profil-action:hover {
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

/* Avatar & profil info modern */
.profile-card {
  background: transparent;
  border: none;
  box-shadow: none;
  text-align: center;
  padding: 0;
}
.profile-header {
  margin-bottom: 1.2rem;
}
.profile-avatar {
  width: 110px; height: 110px;
  margin: 0 auto 1.2rem auto;
  border-radius: 50%;
  background: linear-gradient(135deg, #1976d2 60%, #7b1fa2 100%);
  box-shadow: 0 4px 24px #7b1fa255, 0 1.5px 12px #fff2;
  display: flex; align-items: center; justify-content: center;
  position: relative;
  border: 4px solid #fff;
  transition: box-shadow 0.2s, transform 0.22s;
  animation: avatarFloat 2.5s infinite alternate cubic-bezier(.5,0,.5,1);
}
.profile-avatar i {
  font-size: 4.5rem;
  color: #fff;
  filter: drop-shadow(0 2px 8px #7b1fa255);
}
@keyframes avatarFloat {
  from { transform: translateY(0); }
  to { transform: translateY(-8px) scale(1.04); }
}
.profile-avatar .badge-role {
  position: absolute;
  bottom: 0; right: -10px;
  background: linear-gradient(90deg,#7b1fa2,#1976d2);
  color: #fff;
  border-radius: 1.2rem;
  padding: 0.4em 1em;
  font-size: 0.95em;
  font-weight: 700;
  box-shadow: 0 2px 8px #1976d244;
  animation: pulse-badge 2.2s infinite;
  border: 2px solid #fff;
}
.profile-header h4 {
  font-weight: 700; color: #7b1fa2; margin-bottom: 0.2rem; letter-spacing: 0.5px; font-size: 1.4rem;
}
.profile-header p {
  color: #444; font-size: 1.07rem; margin-bottom: 0.7rem;
}
.profile-body {
  margin-top: 0.5rem;
}
.profile-info-item {
  margin-bottom: 1rem;
  color: #222;
  font-size: 1.08rem;
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
}

/* Floating label form modern */
.profil-card form .form-floating > .form-control,
.profil-card form .form-floating > .form-select {
  border-radius: 1.3rem;
  border: 2px solid #1976d2;
  background: rgba(255,255,255,0.95);
  box-shadow: 0 1.5px 12px #1976d211;
  font-size: 1.11rem;
  padding-left: 2.3rem;
  transition: border 0.18s, box-shadow 0.18s;
}
.profil-card form .form-floating > .form-control:focus {
  border-color: #7b1fa2;
  box-shadow: 0 0 0 2px #7b1fa244;
}
.profil-card form .form-floating > label {
  color: #1976d2;
  font-weight: 600;
  left: 2.1rem;
}
.profil-card form .input-icon {
  position: absolute;
  left: 1.1rem; top: 50%;
  transform: translateY(-50%);
  color: #7b1fa2;
  font-size: 1.2rem;
  opacity: 0.75;
}

/* Responsive */
@media (max-width: 767px) {
  .profil-card { padding: 1.1rem 0.7rem; }
  .profile-avatar { width: 80px; height: 80px; }
  .page-hero-profil { flex-direction: column; text-align: center; gap: 0.5rem; }
}
</style>
<div class="profil-full-bg">
    <img src="Nouveau dossier/profil.png" alt="Décor profil">
</div>
<div class="page-hero-profil">
    <i class="fas fa-user-circle"></i>
    <h1>Mon Profil</h1>
</div>
<div class="profil-card fade-in">
    <div class="row">
        <div class="col-md-2 mb-4 mb-md-0">
            <div class="card profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div style="margin-bottom:0.5rem">
                        <h2 style="font-size:1.7rem;font-weight:700;color:#4a148c;line-height:1.2;letter-spacing:0.5px;">
                            <?php echo htmlspecialchars($user['nom']); ?>
                        </h2>
                    </div>
                    <span class="badge-role badge-role-anim badge bg-gradient" style="display:inline-block;font-size:1.05em;margin-bottom:0.6rem;">
                        <i class="fas fa-user-tag"></i> <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                    </span>
                    <div style="font-size:1.04rem;color:#666;margin-bottom:0.2rem;word-break:break-all;">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                    <div style="font-size:0.96rem;color:#aaa;">
                        Cet email est utilisé pour la connexion et la récupération de compte.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div class="mb-4" style="display:flex;align-items:center;gap:0.7rem;justify-content:center;">
    <i class="fas fa-user-edit" style="color:#7b1fa2;font-size:1.5rem;"></i>
    <span style="font-size:1.28rem;font-weight:700;color:#7b1fa2;letter-spacing:0.2px;">Modifier mes informations</span>
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
        <div class="col-md-4">
            <div class="card profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($user['nom']); ?></h4>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="profile-body">
                    <div class="profile-info-item">
                        <strong><i class="fas fa-user-tag"></i> Rôle:</strong>
                        <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                    </div>
                    <div class="profile-info-item">
                        <strong><i class="fas fa-calendar-alt"></i> Compte créé le:</strong>
                        <span><?php echo isset($user['date_creation']) ? date('d/m/Y', strtotime($user['date_creation'])) : 'N/A'; ?></span>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="change_password.php" class="btn btn-outline-primary">
                            <i class="fas fa-key"></i> Changer mon mot de passe
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-edit"></i> Modifier mon profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate style="margin-top:1.2rem;">
    <div class="form-floating mb-3 position-relative">
        <span class="input-icon"><i class="fas fa-user"></i></span>
        <input type="text" class="form-control ps-5" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" placeholder="Nom complet" required>
        <label for="nom">Nom complet</label>
        <div class="form-text">Votre nom complet affiché dans le système.</div>
        <div class="invalid-feedback">Veuillez entrer votre nom complet.</div>
    </div>
    <div class="form-floating mb-3 position-relative">
        <span class="input-icon"><i class="fas fa-envelope"></i></span>
        <input type="email" class="form-control ps-5" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Adresse email" required>
        <label for="email">Adresse email</label>
        <div class="form-text">Adresse utilisée pour la connexion et la récupération de compte.</div>
        <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
    </div>
    <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn profil-action">
            <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
    </div>
</form>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                            <div class="invalid-feedback">Veuillez entrer votre nom complet.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <input type="text" class="form-control" id="role" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" disabled>
                            <div class="form-text">Le rôle ne peut pas être modifié.</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activités récentes -->
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        
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
