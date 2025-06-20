<?php
// Connexion à la base de données 
// Détection automatique de l'environnement (WAMP local vs Replit)

// Options de connexion PDO communes
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Vérifier si on est sur Replit en cherchant la variable d'environnement DATABASE_URL
    if (getenv('DATABASE_URL')) {
        // Sur Replit - Utilisation de PostgreSQL
        $dbUrl = getenv('DATABASE_URL');
        $pdo = new PDO($dbUrl, null, null, $options);
    } else {
        // Sur WAMP local - Utilisation de MySQL
        $host = 'localhost';
        $port = '3306';
        $dbname = 'gestion_presence';
        $username = 'root';
        $password = '';
        
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, $options);
    }
} catch (PDOException $e) {
    // Pour éviter une page d'erreur lors de la démonstration, créons un PDO de test
    // Cela permettra de montrer l'interface sans accéder aux données réelles
    
    // Création d'une base de données SQLite en mémoire temporaire
    $pdo = new PDO('sqlite::memory:', null, null, $options);
    
    // Création de tables de démonstration minimales
    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (id INTEGER PRIMARY KEY, nom TEXT, prenom TEXT, email TEXT, role TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS etudiants (id INTEGER PRIMARY KEY, nom TEXT, prenom TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cours (id INTEGER PRIMARY KEY, nom TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS presences (id INTEGER PRIMARY KEY, date_presence TEXT, etudiant_id INTEGER, cours_id INTEGER, statut TEXT, date_enregistrement TEXT)");
    
    // Insertion de quelques données de démonstration
    $pdo->exec("INSERT INTO utilisateurs (nom, prenom, email, role) VALUES ('Dupont', 'Chris', 'chris552352@gmail.com', 'enseignant')");
    $pdo->exec("INSERT INTO etudiants (nom, prenom) VALUES ('Martin', 'Sophie'), ('Dubois', 'Thomas'), ('Bernard', 'Emma')");
    $pdo->exec("INSERT INTO cours (nom) VALUES ('Mathématiques'), ('Français'), ('Histoire')");
    
    // Message d'avertissement pour le mode démo
    // On ne l'affiche pas directement ici pour éviter le problème de "headers already sent"
    $_SESSION['mode_demo'] = true;
}

/**
 * Fonction utilitaire pour simuler des requêtes sur les statistiques en mode démo
 * @return array Données simulées pour le tableau de bord
 */
function genererStatistiquesDemo() {
    return [
        'etudiants' => 25,
        'cours' => 8, 
        'presences_jour' => 18,
        'absences_jour' => 7,
        'total_present' => 342,
        'total_absent' => 86
    ];
}
?>
