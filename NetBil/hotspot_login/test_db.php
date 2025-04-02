<?php
require 'db_connect.php';

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Database is working!</h2>";
    echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Query Failed</h2>";
    echo "<pre>Error: " . $e->getMessage() . "</pre>";
}