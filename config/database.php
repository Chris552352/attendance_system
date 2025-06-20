<?php
// Force l'encodage en UTF-8 pour toute l'application
ini_set('default_charset', 'UTF-8');

/**
 * Configuration unifiée de la base de données (PostgreSQL sur Replit, MySQL sur WAMP)
 */

// Variables globales pour la connexion
$db_type = null;
$pdo = null;
$mysqli = null;

// Détecter l'environnement (Replit ou local)
if (getenv('REPL_ID') || getenv('REPL_SLUG') || getenv('PGHOST')) {
    // Nous sommes sur Replit, utiliser PostgreSQL
    $db_type = 'postgresql';
    
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
        die("Erreur de connexion à la base de données PostgreSQL: " . $e->getMessage());
    }
} else {
    // Configuration pour WAMPSERVER (environnement local)
    $db_type = 'mysql';
    $host = 'localhost';
    $dbname = 'attendance_system';
    $user = 'root';  // Utilisateur par défaut de WAMP
    $password = '';  // Mot de passe par défaut généralement vide sur WAMP
    $port = 3306;    // Port par défaut de MySQL

    // Connexion à la base de données MySQL avec MySQLi
    $mysqli = new mysqli($host, $user, $password, $dbname, $port);

    // Vérifier la connexion
    if ($mysqli->connect_error) {
        die("Erreur de connexion à la base de données MySQL: " . $mysqli->connect_error);
    }

    // Définir l'encodage des caractères
    $mysqli->set_charset("utf8mb4");
}

/**
 * Fonction pour exécuter une requête et retourner les résultats
 * 
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête
 * @return array Les résultats de la requête
 */
function db_query($sql, $params = []) {
    global $db_type, $pdo, $mysqli;
    
    if ($db_type === 'postgresql') {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur SQL (PostgreSQL): " . $e->getMessage());
            return false;
        }
    } else {
        try {
            $stmt = $mysqli->prepare($sql);
            
            if (!empty($params)) {
                // Construire les types de paramètres
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                }
                
                // Bind des paramètres
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            
            return $rows;
        } catch (Exception $e) {
            error_log("Erreur SQL (MySQL): " . $e->getMessage());
            return false;
        }
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
    global $db_type, $pdo, $mysqli;
    
    if ($db_type === 'postgresql') {
        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur SQL (PostgreSQL): " . $e->getMessage());
            return false;
        }
    } else {
        try {
            $stmt = $mysqli->prepare($sql);
            
            if (!empty($params)) {
                // Construire les types de paramètres
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                }
                
                // Bind des paramètres
                $stmt->bind_param($types, ...$params);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erreur SQL (MySQL): " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Fonction pour obtenir l'ID du dernier enregistrement inséré
 * 
 * @return int L'ID du dernier enregistrement
 */
function db_last_insert_id() {
    global $db_type, $pdo, $mysqli;
    
    if ($db_type === 'postgresql') {
        return $pdo->lastInsertId();
    } else {
        return $mysqli->insert_id;
    }
}

/**
 * Fonction pour obtenir un seul enregistrement
 * 
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête
 * @return array|bool L'enregistrement trouvé ou false
 */
function db_query_single($sql, $params = []) {
    $result = db_query($sql, $params);
    
    if ($result && count($result) > 0) {
        return $result[0];
    }
    
    return false;
}

/**
 * Fonction pour obtenir la dernière erreur SQL
 * 
 * @return string Le message d'erreur
 */
function db_error() {
    global $db_type, $pdo, $mysqli;
    
    if ($db_type === 'postgresql') {
        if ($pdo) {
            $error = $pdo->errorInfo();
            return $error[2];
        }
    } else {
        if ($mysqli) {
            return $mysqli->error;
        }
    }
    
    return 'Erreur inconnue';
}

?>