<?php
// admin_products.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Products</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Manage Products</h3>
        <div class="mb-3">
            <a href="admin_create_product.php" class="btn btn-success">Add New Product</a>
        </div>
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
            echo '<a href="admin_edit_product.php?id=' . $prod['id'] . '" class="btn btn-outline-secondary btn-sm">Edit</a>';
            echo '</div>';
            echo '<form method="post" action="admin_products.php" style="margin:0;">';
            echo '<input type="hidden" name="delete_id" value="' . $prod['id'] . '"><button type="submit" class="btn btn-outline-danger ms-3"><i class="bi bi-trash"></i> Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
        </div>
    </div>
</body>
</html>
