<?php
/**
 * Configuration base de données pour WAMP (MySQL uniquement)
 */

// Force l'encodage UTF-8
ini_set('default_charset', 'UTF-8');

// Configuration MySQL pour WAMP
$host = 'localhost';
$dbname = 'attendance_system';
$username = 'root';
$password = '';
$port = 3306;

// Variables globales
$pdo = null;
$db_type = 'mysql';

try {
    // Connexion PDO MySQL
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

/**
 * Exécute une requête et retourne les résultats
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
 * Exécute une requête sans retourner de résultats
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
 * Retourne l'ID du dernier enregistrement inséré
 */
function db_last_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * Retourne un seul enregistrement
 */
function db_query_single($sql, $params = []) {
    $result = db_query($sql, $params);
    return $result && count($result) > 0 ? $result[0] : false;
}

/**
 * Retourne le message d'erreur SQL
 */
function db_error() {
    global $pdo;
    $error = $pdo->errorInfo();
    return $error[2] ?? 'Erreur inconnue';
}
?>