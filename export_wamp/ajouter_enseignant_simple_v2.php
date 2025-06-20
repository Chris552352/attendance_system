<?php
/**
 * Script simplifié pour ajouter un enseignant dans la base de données attendance_system
 */

// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion directe u00e0 la base de données
$host = 'localhost';
$dbname = 'attendance_system';
$user = 'root';
$password = '';

// Initialiser les variables
$message = '';
$success = false;
$login_info = null;

// Connexion directe u00e0 MySQL
try {
    $mysqli = new mysqli($host, $user, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erreur de connexion u00e0 la base de données: " . $mysqli->connect_error);
    }
    
    // Définir l'encodage des caractères
    $mysqli->set_charset("utf8mb4");
    
    $db_connected = true;
    $message_connexion = "Connexion u00e0 la base de données réussie.";
} catch (Exception $e) {
    $db_connected = false;
    $message_connexion = "Erreur: " . $e->getMessage();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db_connected) {
    // Récupérer les données du formulaire
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validation simple
    if (empty($nom) || empty($prenom) || empty($email)) {
        $message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse email n'est pas valide.";
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt_check = $mysqli->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                $message = "Cet email est déjà utilisé par un autre utilisateur.";
            } else {
                // Générer un mot de passe aléatoire
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer l'enseignant dans la base de données
                $stmt_insert = $mysqli->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'enseignant')");
                $stmt_insert->bind_param("ssss", $nom, $prenom, $email, $password_hash);
                
                if ($stmt_insert->execute()) {
                    $success = true;
                    $message = "L'enseignant a u00e9té ajouté avec succès.";
                    $login_info = [
                        'email' => $email,
                        'password' => $password
                    ];
                } else {
                    $message = "Erreur lors de l'ajout de l'enseignant: " . $stmt_insert->error;
                }
            }
        } catch (Exception $e) {
            $message = "Exception: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un enseignant - Version simplifiée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .ajout-enseignant-header {
            width: 100%;
            margin-bottom: 2rem;
            padding: 0;
            position: relative;
            z-index: 2;
        }
        .ajout-enseignant-header-img {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
            border-radius: 1.5rem 1.5rem 0 0;
            filter: blur(0.5px) brightness(0.92) contrast(1.03);
            box-shadow: 0 8px 32px 0 #1976d255;
        }
        .ajout-enseignant-hero {
            position: absolute;
            left: 2rem; top: 2rem;
            background: rgba(255,255,255,0.68);
            box-shadow: 0 8px 32px 0 rgba(25,118,210,0.18);
            border-radius: 2rem;
            padding: 1.2rem 2.2rem 1.2rem 1.8rem;
            display: flex; align-items: center; gap: 1.3rem;
            backdrop-filter: blur(6px);
        }
        .ajout-enseignant-hero i {
            font-size: 2.5rem;
            color: #1976d2;
            filter: drop-shadow(0 2px 8px #1976d255);
        }
        .ajout-enseignant-hero h1 {
            margin: 0; font-size: 2rem; font-weight: 700; color: #0d47a1;
            text-shadow: 0 2px 8px #fff5;
        }
        .ajout-enseignant-card {
            border-radius: 2rem;
            background: rgba(255,255,255,0.88);
            box-shadow: 0 8px 32px 0 #1976d255, 0 1.5px 32px 0 #fff2;
            backdrop-filter: blur(8px);
            padding: 2.5rem 2.5rem 2rem 2.5rem;
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
</head>
<body>
    <div class="ajout-enseignant-header">
        <img src="Nouveau dossier/76301033-mechanical-engineer-at-work-technical-drawings-paper-with-technical-drawings-and-diagrams.jpg" class="ajout-enseignant-header-img" alt="Décor enseignant">
        <div class="ajout-enseignant-hero">
            <i class="fas fa-chalkboard-teacher"></i>
            <h1>Ajouter un enseignant (Version simplifiée)</h1>
        </div>
    </div>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="ajout-enseignant-card shadow">
                    <?php if (!$db_connected): ?>
                        <div class="alert alert-danger">
                            <strong>Erreur de connexion u00e0 la base de données:</strong> <?php echo $message_connexion; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <strong>Base de données:</strong> <?php echo $message_connexion; ?>
                        </div>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                                <?php echo $message; ?>
                    <div class="card-body">
                        <?php if (!$db_connected): ?>
                            <div class="alert alert-danger">
                                <strong>Erreur de connexion u00e0 la base de données:</strong> <?php echo $message_connexion; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <strong>Base de données:</strong> <?php echo $message_connexion; ?>
                            </div>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($login_info): ?>
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-key"></i> Informations de connexion</h5>
                                    <p>L'enseignant peut se connecter avec les identifiants suivants:</p>
                                    <ul>
                                        <li><strong>Email:</strong> <?php echo htmlspecialchars($login_info['email']); ?></li>
                                        <li><strong>Mot de passe:</strong> <?php echo htmlspecialchars($login_info['password']); ?></li>
                                    </ul>
                                    <p class="mb-0"><small>Veuillez communiquer ces informations u00e0 l'enseignant.</small></p>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Ajouter l'enseignant
                                    </button>
                                    <a href="enseignants.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Retour u00e0 la liste
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
