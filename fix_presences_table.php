<?php
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Correction de la table presences</h2>";
    
    // 1. Supprimer les contraintes existantes
    echo "<p>1. Suppression des contraintes existantes...</p>";
    $pdo->exec("ALTER TABLE presences DROP FOREIGN KEY presences_ibfk_2");
    $pdo->exec("ALTER TABLE presences DROP INDEX cours_id");
    
    // 2. Rendre cours_id nullable
    echo "<p>2. Modification de la colonne cours_id...</p>";
    $pdo->exec("ALTER TABLE presences MODIFY cours_id int(11) NULL");
    
    // 3. Ajouter une nouvelle contrainte pour cours_id (nullable)
    echo "<p>3. Ajout de la nouvelle contrainte pour cours_id...</p>";
    $pdo->exec("ALTER TABLE presences ADD CONSTRAINT presences_ibfk_2 FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE SET NULL");
    
    // 4. Vérifier la structure finale
    echo "<p>4. Vérification de la structure...</p>";
    $stmt = $pdo->query("DESCRIBE presences");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Structure de la table presences :</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Correction terminée avec succès !</h3>";
    echo "<p>La table presences peut maintenant fonctionner avec le système QR (seance_id uniquement) ou avec le système classique (cours_id).</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erreur :</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 