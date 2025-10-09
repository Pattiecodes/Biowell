<?php
// admin_quotations.php
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
    <title>Admin - Quotations</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Quotations</h3>
        <div class="text-end mb-3">
            <a href="admin_create_quotation.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Create New Quotation</a>
        </div>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Handle delete
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmtDel = $pdo->prepare("DELETE FROM insurance_application WHERE id = ?");
            $stmtDel->execute([$delete_id]);
        }
        // Fetch quotations with product info
        $stmt = $pdo->query("SELECT ia.*, ip.name AS product_name, u.full_name AS agent_name FROM insurance_application ia LEFT JOIN insurance_products ip ON ia.product_id = ip.id LEFT JOIN users u ON ia.agent_id = u.id ORDER BY ia.application_date DESC");
        echo '<div class="row">';
        while ($q = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="col-12 mb-3">';
            echo '<div class="card">';
            echo '<div class="card-body d-flex align-items-center">';
            // removed small placeholder image for quotation listing
            echo '<div class="flex-grow-1">';
            echo '<h5>' . htmlspecialchars($q['product_name'] ?? 'No Product') . ' - ' . htmlspecialchars($q['agent_name'] ?? 'Agent') . '</h5>';
            echo '<div>Status: ' . htmlspecialchars($q['status']) . '</div>';
            echo '<div>Notes: ' . htmlspecialchars($q['notes']) . '</div>';
            echo '<a href="admin_review_quotation.php?id=' . $q['id'] . '" class="btn btn-outline-secondary btn-sm mt-2">Edit / Review</a>';
            echo '</div>';
            echo '<form method="post" action="admin_quotations.php" style="margin:0;">';
            echo '<input type="hidden" name="delete_id" value="' . $q['id'] . '"><button type="submit" class="btn btn-outline-danger ms-3"><i class="bi bi-trash"></i> Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        ?>
    </div>
</body>
</html>
