<?php
// admin_create_product.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/src/config/database.php';
$pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $issuing_company = trim($_POST['issuing_company'] ?? '');
    $premium = isset($_POST['premium']) ? (float)$_POST['premium'] : null;
    $coverage = trim($_POST['coverage'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eligibility = trim($_POST['eligibility'] ?? '');

    if ($name) {
        $stmt = $pdo->prepare('INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $issuing_company, $description, $premium, $coverage, $eligibility]);
        header('Location: admin_products.php');
        exit();
    } else {
        $msg = '<div class="alert alert-danger">Product name is required.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Create Product</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Create Product</h3>
        <?php if ($msg) echo $msg; ?>
        <form method="post">
            <div class="mb-3">
                <label>Product Name</label>
                <input name="name" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Issuing Company</label>
                <input name="issuing_company" type="text" class="form-control">
            </div>
            <div class="mb-3">
                <label>Premium (PHP)</label>
                <input name="premium" type="number" step="0.01" class="form-control">
            </div>
            <div class="mb-3">
                <label>Coverage</label>
                <textarea name="coverage" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Eligibility</label>
                <input name="eligibility" type="text" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="admin_products.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</body>
</html>