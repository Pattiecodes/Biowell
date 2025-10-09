<?php
// admin_edit_product.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/src/config/database.php';
$pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Determine product id
$productId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($productId <= 0) {
    header('Location: admin_products.php');
    exit();
}

// Handle POST actions: save or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $delStmt = $pdo->prepare('DELETE FROM insurance_products WHERE id = ?');
        $delStmt->execute([$productId]);
        header('Location: admin_products.php');
        exit();
    }
    if (isset($_POST['save'])) {
        $name = $_POST['name'] ?? '';
        $issuing_company = $_POST['issuing_company'] ?? '';
        $premium = $_POST['premium'] ?? null;
        $coverage = $_POST['coverage'] ?? '';
        $description = $_POST['description'] ?? '';
        $eligibility = $_POST['eligibility'] ?? '';
        $update = $pdo->prepare('UPDATE insurance_products SET name = ?, issuing_company = ?, premium = ?, coverage = ?, description = ?, eligibility = ? WHERE id = ?');
        $update->execute([$name, $issuing_company, $premium, $coverage, $description, $eligibility, $productId]);
        header('Location: admin_products.php');
        exit();
    }
}

// Fetch product data for the form
$stmt = $pdo->prepare('SELECT * FROM insurance_products WHERE id = ?');
$stmt->execute([$productId]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$prod) {
    // Not found
    header('Location: admin_products.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Edit Product</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Edit Product</h3>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo (int)$prod['id']; ?>">
            <div class="mb-3">
                <label>Product Name</label>
                <input name="name" type="text" class="form-control" value="<?php echo htmlspecialchars($prod['name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label>Issuing Company</label>
                <input name="issuing_company" type="text" class="form-control" value="<?php echo htmlspecialchars($prod['issuing_company'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Premium (PHP)</label>
                <input name="premium" type="number" step="0.01" class="form-control" value="<?php echo htmlspecialchars($prod['premium'] ?? '0.00'); ?>">
            </div>
            <div class="mb-3">
                <label>Coverage</label>
                <textarea name="coverage" class="form-control"><?php echo htmlspecialchars($prod['coverage'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control"><?php echo htmlspecialchars($prod['description'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label>Eligibility</label>
                <input name="eligibility" type="text" class="form-control" value="<?php echo htmlspecialchars($prod['eligibility'] ?? ''); ?>">
            </div>
            <button type="submit" name="save" class="btn btn-primary">Save</button>
            <a href="admin_products.php" class="btn btn-link">Cancel</a>
            <button type="submit" name="delete" class="btn btn-danger float-end" onclick="return confirm('Delete this product? This action cannot be undone.');">Delete</button>
        </form>
    </div>
</body>
</html>
