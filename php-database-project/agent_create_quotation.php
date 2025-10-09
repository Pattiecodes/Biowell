<?php
// agent_create_quotation.php
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
    <title>Agent - Create Quotation</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <h3>New Quotation / Application</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $msg = '';
        $agent_id = $_SESSION['user_id'];
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Fetch products for dropdown
        // Ensure VIP and Regular products exist
        $stmtCheckVIP = $pdo->prepare("SELECT COUNT(*) FROM insurance_products WHERE name = 'VIP'");
        $stmtCheckVIP->execute();
        if ($stmtCheckVIP->fetchColumn() == 0) {
            $stmtInsertVIP = $pdo->prepare("INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility) VALUES ('VIP', 'BioWell', 'VIP insurance product', 1000.00, 'Full coverage', 'All clients')");
            $stmtInsertVIP->execute();
        }
        $stmtCheckRegular = $pdo->prepare("SELECT COUNT(*) FROM insurance_products WHERE name = 'Regular'");
        $stmtCheckRegular->execute();
        if ($stmtCheckRegular->fetchColumn() == 0) {
            $stmtInsertRegular = $pdo->prepare("INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility) VALUES ('Regular', 'BioWell', 'Regular insurance product', 500.00, 'Standard coverage', 'All clients')");
            $stmtInsertRegular->execute();
        }
        $products = [];
        $stmtProducts = $pdo->query("SELECT id, name, issuing_company, premium FROM insurance_products ORDER BY name ASC");
        while ($row = $stmtProducts->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        $noProducts = count($products) === 0;
        // Check agent exists
        $stmtCheck = $pdo->prepare("SELECT user_id FROM agents WHERE user_id = ?");
        $stmtCheck->execute([$agent_id]);
        if ($stmtCheck->rowCount() === 0) {
            $msg = '<div class="alert alert-danger">Error: Agent record not found. Please contact admin.</div>';
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $client_name = trim($_POST['client_name'] ?? '');
                $client_address = trim($_POST['client_address'] ?? '');
                $client_email = trim($_POST['client_email'] ?? '');
                $product_id = intval($_POST['product_id'] ?? 0);
                $notes = trim($_POST['notes'] ?? '');
                if ($client_name && $product_id) {
                    // Check if application_code column exists in the DB; migration may be pending
                    $colStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insurance_application' AND COLUMN_NAME = 'application_code'");
                    $colStmt->execute();
                    $hasAppCode = $colStmt->fetchColumn() > 0;

                    if ($hasAppCode) {
                        // Generate application_code: ClientSurname_index
                        $surname = preg_replace('/[^A-Za-z]/', '', strtoupper(explode(' ', $client_name)[0] ?? 'CLIENT'));
                        // count existing for this surname
                        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM insurance_application WHERE application_code LIKE ?");
                        $likePattern = $surname . '_%';
                        $stmtCount->execute([$likePattern]);
                        $index = $stmtCount->fetchColumn() + 1;
                        $application_code = $surname . '_' . $index;
                        $stmt = $pdo->prepare("INSERT INTO insurance_application (product_id, agent_id, client_name, client_address, client_email, application_code, status, application_date, notes) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)");
                        $stmt->execute([$product_id, $agent_id, $client_name, $client_address, $client_email, $application_code, $notes]);
                    } else {
                        // application_code column not present yet; insert without it to avoid fatal errors
                        $stmt = $pdo->prepare("INSERT INTO insurance_application (product_id, agent_id, client_name, client_address, client_email, status, application_date, notes) VALUES (?, ?, ?, ?, ?, 'pending', NOW(), ?)");
                        $stmt->execute([$product_id, $agent_id, $client_name, $client_address, $client_email, $notes]);
                    }
                    header('Location: agent_quotations.php');
                    exit();
                } else {
                    $msg = '<div class="alert alert-danger">Client Name and Product are required.</div>';
                }
            }
        }
        ?>
        <?php if ($msg) echo $msg; ?>
    <?php if ($noProducts) echo '<div class="alert alert-warning">No products found. Please contact admin to add products.</div>'; ?>
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
                <label>Agent ID</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($agent_id); ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Notes</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="agent_quotations.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</body>
</html>
