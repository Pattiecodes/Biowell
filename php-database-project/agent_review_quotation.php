<?php
// agent_review_quotation.php
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
    <title>Agent - Review Quotation</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Review Quotation / Application</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $id = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT ia.*, ip.name AS product_name FROM insurance_application ia LEFT JOIN insurance_products ip ON ia.product_id = ip.id WHERE ia.id = ? AND ia.agent_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$app) {
            echo '<div class="alert alert-danger">Quotation not found or access denied.</div>';
        } else {
        ?>
        <?php
            // handle agent updates (do not allow status change)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $client_name = trim($_POST['client_name'] ?? '');
                $client_address = trim($_POST['client_address'] ?? '');
                $client_email = trim($_POST['client_email'] ?? '');
                $notes = trim($_POST['notes'] ?? '');
                $stmtUp = $pdo->prepare("UPDATE insurance_application SET client_name = ?, client_address = ?, client_email = ?, notes = ? WHERE id = ? AND agent_id = ?");
                $stmtUp->execute([$client_name, $client_address, $client_email, $notes, $id, $_SESSION['user_id']]);
                echo '<div class="alert alert-success">Saved.</div>';
            }

        ?>
        <form method="post" action="agent_review_quotation.php?id=<?php echo $id; ?>">
            <div class="mb-3">
                <label>Client Name</label>
                <input type="text" name="client_name" class="form-control" value="<?php echo htmlspecialchars($app['client_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label>Client Address</label>
                <input type="text" name="client_address" class="form-control" value="<?php echo htmlspecialchars($app['client_address'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Client Email</label>
                <input type="email" name="client_email" class="form-control" value="<?php echo htmlspecialchars($app['client_email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Product</label>
                <div class="form-control-plaintext"><?php echo htmlspecialchars($app['product_name'] ?? ''); ?></div>
            </div>
            <div class="mb-3">
                <label>Agent ID</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($app['agent_id']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Notes</label>
                <textarea name="notes" class="form-control"><?php echo htmlspecialchars($app['notes'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($app['status']); ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="agent_quotations.php" class="btn btn-link">Back</a>
            <button type="button" class="btn btn-danger float-end">Delete</button>
        </form>
        <?php } ?>
    </div>
</body>
</html>
