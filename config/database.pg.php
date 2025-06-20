<?php
/**
 * Configuration de la base de données PostgreSQL pour Replit
 */

// Utilisation des variables d'environnement de Replit pour PostgreSQL
$host = getenv('PGHOST');
$dbname = getenv('PGDATABASE');
$user = getenv('PGUSER');
$password = getenv('PGPASSWORD');
$port = getenv('PGPORT');

// Chaîne de connexion PDO pour PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO($dsn);
    
    // Configurer PDO pour lancer des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Utiliser le mode fetch associatif par défaut
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

/**
 * Fonction pour exécuter une requête et retourner les résultats
 * 
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête
 * @return array Les résultats de la requête
 */
function db_query($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage());
        return false;
    }
}

/**
 * Fonction pour exécuter une requête sans retourner de résultats
 * 
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête
 * @return bool Succès ou échec de la requête
 */
function db_exec($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage());
        return false;
    }
}

/**
 * Fonction pour obtenir l'ID du dernier enregistrement inséré
 * 
 * @return int L'ID du dernier enregistrement
 */
function db_last_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * Fonction pour obtenir un seul enregistrement
 * 
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête
 * @return array|bool L'enregistrement trouvé ou false
 */
function db_query_single($sql, $params = []) {
    $results = db_query($sql, $params);
    if ($results && count($results) > 0) {
        return $results[0];
    }
    return false;
}
?>