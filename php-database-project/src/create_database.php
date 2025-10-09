<?php
require_once 'config/database.php';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host={$dbHost};charset=utf8", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL statement to create a new database
    $sql = "CREATE DATABASE IF NOT EXISTS {$dbName}";

    // Execute the SQL statement
    $pdo->exec($sql);
    echo "Database '{$dbName}' created successfully.";
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage();
}
?>