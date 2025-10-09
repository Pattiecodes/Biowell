<?php
// admin_create_quotation.php
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
    <title>Admin - Create Quotation</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>New Quotation / Application</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $msg = '';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $products = [];
        $stmtProducts = $pdo->query("SELECT id, name, issuing_company, premium FROM insurance_products ORDER BY name ASC");
        while ($row = $stmtProducts->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        $noProducts = count($products) === 0;
        // Fetch agents for dropdown (join to users to get full name)
        $agents = [];
        $stmtAgents = $pdo->query("SELECT a.user_id, u.full_name FROM agents a JOIN users u ON a.user_id = u.id ORDER BY u.full_name ASC");
        while ($ar = $stmtAgents->fetch(PDO::FETCH_ASSOC)) {
            $agents[] = $ar;
        }
        $noAgents = count($agents) === 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_name = trim($_POST['client_name'] ?? '');
            $client_address = trim($_POST['client_address'] ?? '');
            $client_email = trim($_POST['client_email'] ?? '');
            $product_id = intval($_POST['product_id'] ?? 0);
            $agent_id = intval($_POST['agent_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            if ($client_name && $product_id && $agent_id) {
                // Validate agent exists to avoid FK violation
                $chk = $pdo->prepare('SELECT COUNT(*) FROM agents WHERE user_id = ?');
                $chk->execute([$agent_id]);
                if ($chk->fetchColumn() == 0) {
                    $msg = '<div class="alert alert-danger">Selected agent does not exist. Please pick a valid agent.</div>';
                } else {
                $stmt = $pdo->prepare("INSERT INTO insurance_application (product_id, agent_id, client_name, client_address, client_email, status, application_date, notes) VALUES (?, ?, ?, ?, ?, 'pending', NOW(), ?)");
                $stmt->execute([$product_id, $agent_id, $client_name, $client_address, $client_email, $notes]);
                header('Location: admin_quotations.php');
                exit();
                }
            } else {
                $msg = '<div class="alert alert-danger">Client Name, Product, and Agent ID are required.</div>';
            }
        }
        ?>
        <?php if ($msg) echo $msg; ?>
        <form method="post">
            <div class="mb-3">
                <label>Client Name</label>
                <input type="text" name="client_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Client Address</label>
                <input type="text" name="client_address" class="form-control">
            </div>
            <div class="mb-3">
                <label>Client Email</label>
                <input type="email" name="client_email" class="form-control">
            </div>
            <div class="mb-3">
                <label>Product</label>
                <select name="product_id" class="form-select" required <?php if ($noProducts) echo 'disabled'; ?>>
                    <option value="">Select a product</option>
                    <?php foreach ($products as $prod): ?>
                        <option value="<?php echo $prod['id']; ?>">
                            <?php echo htmlspecialchars($prod['name']); ?> (<?php echo htmlspecialchars($prod['issuing_company']); ?>, PHP <?php echo number_format($prod['premium'], 2); ?>/month)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Agent</label>
                <?php if ($noAgents): ?>
                    <div class="alert alert-warning">No agents available. Please add/approve an agent before creating quotations.</div>
                    <select class="form-select" disabled>
                        <option value="">No agents</option>
                    </select>
                <?php else: ?>
                    <select name="agent_id" class="form-select" required>
                        <option value="">Select an agent</option>
                        <?php foreach ($agents as $ag): ?>
                            <option value="<?php echo (int)$ag['user_id']; ?>"><?php echo htmlspecialchars($ag['full_name']); ?> (ID: <?php echo (int)$ag['user_id']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label>Notes</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="admin_quotations.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</body>
</html>
