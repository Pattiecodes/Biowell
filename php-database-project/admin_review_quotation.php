<?php
// admin_review_quotation.php
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
    <title>Admin - Review Quotation</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Review Quotation / Application</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $id = intval($_GET['id'] ?? 0);
        // handle POST update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_name = trim($_POST['client_name'] ?? '');
            $client_address = trim($_POST['client_address'] ?? '');
            $client_email = trim($_POST['client_email'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $status = trim($_POST['status'] ?? 'pending');
            $agent_id = intval($_POST['agent_id'] ?? 0);
            $stmtUp = $pdo->prepare("UPDATE insurance_application SET client_name = ?, client_address = ?, client_email = ?, notes = ?, status = ?, agent_id = ? WHERE id = ?");
            $stmtUp->execute([$client_name, $client_address, $client_email, $notes, $status, $agent_id, $id]);
            echo '<div class="alert alert-success">Quotation updated.</div>';
        }
        $stmt = $pdo->prepare("SELECT ia.*, ip.name AS product_name, a.acct_num FROM insurance_application ia LEFT JOIN insurance_products ip ON ia.product_id = ip.id LEFT JOIN agents a ON ia.agent_id = a.user_id WHERE ia.id = ?");
        $stmt->execute([$id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$app) {
            echo '<div class="alert alert-danger">Quotation not found.</div>';
        } else {
            // fetch agents list
            $agentsList = $pdo->query("SELECT user_id, acct_num FROM agents ORDER BY acct_num ASC")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <form method="post" action="admin_review_quotation.php?id=<?php echo $id; ?>">
            <div class="mb-3">
                <label>Client Name</label>
                <input type="text" name="client_name" class="form-control" value="<?php echo htmlspecialchars($app['client_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Client Address</label>
                <input type="text" name="client_address" class="form-control" value="<?php echo htmlspecialchars($app['client_address']); ?>">
            </div>
            <div class="mb-3">
                <label>Client Email</label>
                <input type="email" name="client_email" class="form-control" value="<?php echo htmlspecialchars($app['client_email']); ?>">
            </div>
            <div class="mb-3">
                <label>Product</label>
                <div class="form-control-plaintext"><?php echo htmlspecialchars($app['product_name'] ?? ''); ?></div>
            </div>
            <div class="mb-3">
                <label>Agent ID</label>
                <select name="agent_id" class="form-select">
                    <?php foreach ($agentsList as $ag): ?>
                        <option value="<?php echo $ag['user_id']; ?>" <?php if ($ag['user_id'] == $app['agent_id']) echo 'selected'; ?>><?php echo htmlspecialchars($ag['acct_num']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Notes</label>
                <textarea name="notes" class="form-control"><?php echo htmlspecialchars($app['notes'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label>Admin Review</label>
                <select name="status" class="form-select">
                    <option value="pending" <?php if ($app['status']=='pending') echo 'selected'; ?>>Pending</option>
                    <option value="approved" <?php if ($app['status']=='approved') echo 'selected'; ?>>Approved</option>
                    <option value="rejected" <?php if ($app['status']=='rejected') echo 'selected'; ?>>Rejected</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="admin_quotations.php" class="btn btn-link">Back</a>
            <button type="button" class="btn btn-danger float-end">Delete</button>
        </form>
        <?php } ?>
    </div>
</body>
</html>
