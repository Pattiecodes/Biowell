<?php
// agent_products.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent - Products</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Products</h3>
        <div class="row">
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SELECT * FROM insurance_products ORDER BY name ASC");
        while ($prod = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="col-12 mb-3">';
            echo '<div class="card">';
            echo '<div class="card-body d-flex align-items-center">';
            // removed small placeholder image for product listing
            echo '<div class="flex-grow-1">';
            echo '<h5>' . htmlspecialchars($prod['name']) . '</h5>';
            echo '<p>' . htmlspecialchars($prod['issuing_company']) . ' | PHP ' . number_format($prod['premium'], 2) . ' / month | ' . htmlspecialchars($prod['coverage']) . '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
        </div>
    </div>
</body>
</html>
