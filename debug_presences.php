<?php
require_once 'config/database.php';
header('Content-Type: text/html; charset=utf-8');
echo '<h2>Contenu brut de la table presences (20 dernières lignes)</h2>';
try {
    $rows = db_query('SELECT * FROM presences ORDER BY id DESC LIMIT 20');
    if ($rows && count($rows) > 0) {
        echo '<table border="1" cellpadding="5"><tr>';
        foreach(array_keys($rows[0]) as $col) echo '<th>' . htmlspecialchars($col) . '</th>';
        echo '</tr>';
        foreach($rows as $row) {
            echo '<tr>';
            foreach($row as $cell) echo '<td>' . htmlspecialchars($cell === null ? '' : $cell) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Aucune donnée trouvée dans la table presences.</p>';
    }
} catch (Exception $e) {
    echo '<b>Erreur SQL :</b> ' . htmlspecialchars($e->getMessage());
}
?>
