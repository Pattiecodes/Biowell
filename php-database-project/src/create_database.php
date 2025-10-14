<?php
require_once 'config/database.php';

try {
    // create a new pdo instance
    $pdo = new PDO("mysql:host={$dbHost};charset=utf8", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // sql statement to create a new database
    $sql = "CREATE DATABASE IF NOT EXISTS {$dbName}";

    // execute the sql statement
    $pdo->exec($sql);
    echo "Database '{$dbName}' created successfully.";
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage();
}
?>