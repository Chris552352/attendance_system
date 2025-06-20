<?php
/**
 * Script d'installation et de configuration pour le système de gestion de présence
 * Utilisez ce script après avoir importé la base de données pour vérifier la configuration
 */

$errors = [];
$success = [];

// Vérification de l'environnement PHP
$phpVersion = phpversion();
$minPhpVersion = '7.4.0';
if (version_compare($phpVersion, $minPhpVersion, '<')) {
    $errors[] = "Version PHP requise : $minPhpVersion ou supérieure. Votre version : $phpVersion";
} else {
    $success[] = "Version PHP compatible : $phpVersion";
}

// Vérification des extensions nécessaires
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    $errors[] = "Extensions PHP manquantes : " . implode(', ', $missingExtensions);
} else {
    $success[] = "Toutes les extensions PHP requises sont installées";
}

// Vérification des droits d'écriture sur les dossiers qui pourraient en avoir besoin
$writableFolders = ['.', 'assets'];
$notWritableFolders = [];
foreach ($writableFolders as $folder) {
    if (!is_writable($folder)) {
        $notWritableFolders[] = $folder;
    }
}

if (!empty($notWritableFolders)) {
    $errors[] = "Les dossiers suivants ne sont pas accessibles en écriture : " . implode(', ', $notWritableFolders);
} else {
    $success[] = "Tous les dossiers nécessaires sont accessibles en écriture";
}

// Tester la connexion à la base de données
$dbConfig = [
    'host' => 'localhost',
    'port' => '3306',
    'dbname' => 'gestion_presence',
    'username' => 'root',
    'password' => ''
];

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
    
    // Vérifier que les tables existent
    $tables = ['utilisateurs', 'etudiants', 'cours', 'inscriptions', 'presences'];
    $missingTables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        $errors[] = "Tables manquantes dans la base de données : " . implode(', ', $missingTables) . ". Avez-vous importé le fichier database/schema_mysql.sql ?";
    } else {
        $success[] = "Connexion à la base de données réussie et toutes les tables sont présentes";
    }
    
    // Vérifier que le compte utilisateur existe
    $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE email = 'chris552352@gmail.com'");
    $userExists = (int)$stmt->fetchColumn();
    
    if ($userExists > 0) {
        $success[] = "Compte utilisateur de test trouvé dans la base de données";
    } else {
        $errors[] = "Compte utilisateur de test non trouvé. Assurez-vous d'avoir importé les données de test.";
    }
    
} catch (PDOException $e) {
    $errors[] = "Erreur de connexion à la base de données : " . $e->getMessage();
}

// Chemin d'URL de base
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptPath != '/') {
    $baseUrl .= $scriptPath;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Système de Gestion de Présence</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e5e5e5;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center">
            <h1>Installation du Système de Gestion de Présence</h1>
            <p class="lead">Vérification de la configuration de votre environnement</p>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <h2>Résultat des vérifications</h2>
                
                <?php if (empty($errors)): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Installation réussie !</h4>
                        <p>Votre environnement est correctement configuré pour exécuter l'application.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Des problèmes ont été détectés</h4>
                        <p>Veuillez corriger les erreurs suivantes avant d'utiliser l'application :</p>
                    </div>
                <?php endif; ?>
                
                <h3>Détails</h3>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        Configurations valides
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($success as $message): ?>
                            <li class="list-group-item text-success">
                                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $message; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            Problèmes à résoudre
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($errors as $message): ?>
                                <li class="list-group-item text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $message; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <h3>Prochaines étapes</h3>
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if (empty($errors)): ?>
                            <p>Tout est prêt ! Vous pouvez maintenant accéder à l'application :</p>
                            <div class="d-grid gap-2">
                                <a href="<?php echo $baseUrl; ?>/login.php" class="btn btn-primary btn-lg">Accéder à l'application</a>
                            </div>
                            <hr>
                            <h4>Identifiants de connexion</h4>
                            <p>Utilisez les identifiants suivants pour vous connecter :</p>
                            <ul>
                                <li><strong>Email:</strong> chris552352@gmail.com</li>
                                <li><strong>Mot de passe:</strong> 552352</li>
                            </ul>
                        <?php else: ?>
                            <p>Après avoir résolu les problèmes listés ci-dessus, rechargez cette page pour vérifier à nouveau votre configuration.</p>
                            <div class="d-grid gap-2">
                                <button onclick="location.reload()" class="btn btn-primary btn-lg">Vérifier à nouveau</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer text-center">
            <p class="text-muted">Système de Gestion de Présence Étudiante &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
    
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>