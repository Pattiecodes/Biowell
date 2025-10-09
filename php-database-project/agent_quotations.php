<?php
// agent_quotations.php
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
    <title>Agent - Quotations</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Quotations</h3>
        <div class="text-end mb-3">
            <a href="agent_create_quotation.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Create New Quotation</a>
        </div>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $user_id = $_SESSION['user_id'];
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Handle delete
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmtDel = $pdo->prepare("DELETE FROM insurance_application WHERE id = ? AND agent_id = ?");
            $stmtDel->execute([$delete_id, $user_id]);
        }
        $stmt = $pdo->prepare("SELECT ia.*, ip.name AS product_name FROM insurance_application ia LEFT JOIN insurance_products ip ON ia.product_id = ip.id WHERE ia.agent_id = ? ORDER BY ia.application_date DESC");
        $stmt->execute([$user_id]);
        while ($q = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="col-12 mb-3">';
            echo '<div class="card">';
            echo '<div class="card-body d-flex align-items-center">';
            // removed small placeholder image for quotation listing
            echo '<div class="flex-grow-1">';
            echo '<h5>Product: ' . htmlspecialchars($q['product_name'] ?? 'No Product') . '</h5>';
            echo '<div>Client Name: ' . htmlspecialchars($q['client_name'] ?? '-') . '</div>';
            echo '<div>Client Email: ' . htmlspecialchars($q['client_email'] ?? '-') . '</div>';
            echo '<div>Client Address: ' . htmlspecialchars($q['client_address'] ?? '-') . '</div>';
            echo '<div>Status: ' . htmlspecialchars($q['status']) . '</div>';
            echo '<div>Notes: ' . htmlspecialchars($q['notes'] ?? '-') . '</div>';
            echo '<a href="agent_review_quotation.php?id=' . $q['id'] . '" class="btn btn-outline-secondary btn-sm mt-2">Edit</a>';
            echo '</div>';
            echo '<form method="post" action="agent_quotations.php" style="margin:0;">';
            echo '<input type="hidden" name="delete_id" value="' . $q['id'] . '"><button type="submit" class="btn btn-outline-danger ms-3"><i class="bi bi-trash"></i> Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
