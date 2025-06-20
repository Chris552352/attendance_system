<?php
// Script tru00e8s simple pour remplacer les caractu00e8res encodu00e9s dans un fichier

// Fonction pour remplacer les caractu00e8res encodu00e9s
function remplacer_caracteres($contenu) {
    // Tableau de correspondance entre caractu00e8res encodu00e9s et caractu00e8res normaux
    $correspondances = [
        'u00e0' => 'u00e0', // à
        'u00e2' => 'u00e2', // â
        'u00e7' => 'u00e7', // ç
        'u00e8' => 'u00e8', // è
        'u00e9' => 'u00e9', // é
        'u00ea' => 'u00ea', // ê
        'u00eb' => 'u00eb', // ë
        'u00ee' => 'u00ee', // î
        'u00ef' => 'u00ef', // ï
        'u00f4' => 'u00f4', // ô
        'u00f9' => 'u00f9', // ù
        'u00fb' => 'u00fb', // û
        'u00fc' => 'u00fc', // ü
        'u00c0' => 'u00c0', // À
        'u00c7' => 'u00c7', // Ç
        'u00c9' => 'u00c9', // É
        'u00ca' => 'u00ca', // Ê
    ];
    
    // Remplacer chaque caractu00e8re encodu00e9 par son u00e9quivalent normal
    foreach ($correspondances as $encode => $normal) {
        $contenu = str_replace($encode, $normal, $contenu);
    }
    
    return $contenu;
}

// Traiter un fichier spu00e9cifique
function traiter_fichier($chemin) {
    if (file_exists($chemin)) {
        $contenu = file_get_contents($chemin);
        $nouveau_contenu = remplacer_caracteres($contenu);
        
        if ($contenu !== $nouveau_contenu) {
            // Cru00e9er une sauvegarde du fichier original
            copy($chemin, $chemin . '.bak');
            
            // u00c9crire le nouveau contenu
            file_put_contents($chemin, $nouveau_contenu);
            return true;
        }
    }
    return false;
}

// Interface utilisateur simple
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'fichier' && isset($_POST['fichier'])) {
        // Traiter un fichier spu00e9cifique
        $fichier = $_POST['fichier'];
        $resultat = traiter_fichier($fichier);
        
        if ($resultat) {
            $message = "Le fichier '$fichier' a u00e9tu00e9 traitu00e9 avec succu00e8s.";
        } else {
            $message = "Aucune modification n'a u00e9tu00e9 apportu00e9e au fichier '$fichier'.";
        }
    } elseif ($action === 'tous') {
        // Traiter tous les fichiers PHP
        $compteur = 0;
        $fichiers = glob('*.php');
        
        foreach ($fichiers as $fichier) {
            if (traiter_fichier($fichier)) {
                $compteur++;
            }
        }
        
        $message = "$compteur fichiers ont u00e9tu00e9 traitu00e9s avec succu00e8s.";
    }
}

// Afficher l'interface utilisateur
?>
<!DOCTYPE html>
<html>
<head>
    <title>Remplacer les caractu00e8res encodu00e9s</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .files {
            margin-top: 20px;
        }
        .files a {
            display: block;
            padding: 5px 0;
            color: #3498db;
            text-decoration: none;
        }
        .files a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Remplacer les caractu00e8res encodu00e9s</h1>
        
        <?php if (isset($message)): ?>
            <div class="message success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Traiter un fichier spu00e9cifique</h2>
            <form method="post">
                <input type="hidden" name="action" value="fichier">
                <div class="form-group">
                    <label for="fichier">Nom du fichier:</label>
                    <input type="text" id="fichier" name="fichier" placeholder="Exemple: justifier_absence.php" required>
                </div>
                <button type="submit">Traiter ce fichier</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Traiter tous les fichiers PHP</h2>
            <form method="post">
                <input type="hidden" name="action" value="tous">
                <p>Cette action va traiter tous les fichiers PHP dans le ru00e9pertoire courant.</p>
                <button type="submit">Traiter tous les fichiers</button>
            </form>
        </div>
        
        <div class="files">
            <h3>Fichiers PHP disponibles:</h3>
            <?php 
            $php_files = glob('*.php');
            foreach ($php_files as $php_file): 
            ?>
                <a href="<?php echo $php_file; ?>"><?php echo htmlspecialchars($php_file); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
