<?php
// admin_create_ticket.php
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
    <title>Admin - Create Ticket</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Create a Ticket</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $msg = '';
        // Fetch agents and existing applications to populate the form
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $agents = [];
        $stmtAgents = $pdo->query("SELECT a.user_id, u.full_name FROM agents a JOIN users u ON a.user_id = u.id ORDER BY u.full_name ASC");
        while ($ar = $stmtAgents->fetch(PDO::FETCH_ASSOC)) {
            $agents[] = $ar;
        }
        // Check whether application_code column exists and fetch applications accordingly
        $applications = [];
        $colStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insurance_application' AND COLUMN_NAME = 'application_code'");
        $colStmt->execute();
        $hasAppCode = $colStmt->fetchColumn() > 0;
        if ($hasAppCode) {
            $stmtApps = $pdo->query("SELECT id, application_code, client_name FROM insurance_application ORDER BY application_date DESC LIMIT 200");
        } else {
            $stmtApps = $pdo->query("SELECT id, client_name FROM insurance_application ORDER BY application_date DESC LIMIT 200");
        }
        while ($ap = $stmtApps->fetch(PDO::FETCH_ASSOC)) {
            $applications[] = $ap;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = trim($_POST['subject'] ?? '');
            $company = trim($_POST['company'] ?? '');
            $application_id = intval($_POST['application_id'] ?? 0) ?: null;
            $agent_id = intval($_POST['agent_id'] ?? 0) ?: null;
            $notes = trim($_POST['notes'] ?? '');
            if ($subject && $agent_id) {
                // validate agent exists
                $chk = $pdo->prepare('SELECT COUNT(*) FROM agents WHERE user_id = ?');
                $chk->execute([$agent_id]);
                if ($chk->fetchColumn() == 0) {
                    $msg = '<div class="alert alert-danger">Selected agent does not exist. Please choose a valid agent.</div>';
                } else {
                    try {
                        $created_by_user_id = $_SESSION['user_id'] ?? null;
                        $stmt = $pdo->prepare("INSERT INTO tickets (subject, company, insurance_application_id, agent_id, message, status, created_at, created_by_user_id) VALUES (?, ?, ?, ?, ?, 'open', NOW(), ?)");
                        $stmt->execute([$subject, $company, $application_id, $agent_id, $notes, $created_by_user_id]);
                        header('Location: admin_tickets.php');
                        exit();
                    } catch (PDOException $e) {
                        $msg = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
            } else {
                $msg = '<div class="alert alert-danger">Subject and Agent selection are required.</div>';
            }
        }
        ?>
        <?php if ($msg) echo $msg; ?>
        <form method="post">
            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Company</label>
                <input type="text" name="company" class="form-control">
            </div>
            <div class="mb-3">
                <label>Application (optional)</label>
                <?php if (count($applications) === 0): ?>
                    <div class="form-text">No applications available to attach.</div>
                    <select class="form-select" disabled><option value="">No applications</option></select>
                <?php else: ?>
                    <select name="application_id" class="form-select">
                        <option value="">None</option>
                        <?php foreach ($applications as $ap): ?>
                            <option value="<?php echo (int)$ap['id']; ?>"><?php echo htmlspecialchars($hasAppCode ? ($ap['application_code'] . ' - ' . $ap['client_name']) : ('ID:' . $ap['id'] . ' - ' . $ap['client_name'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label>Agent</label>
                <?php if (count($agents) === 0): ?>
                    <div class="alert alert-warning">No agents available. Please add/approve agents first.</div>
                    <select class="form-select" disabled><option value="">No agents</option></select>
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
            <a href="admin_tickets.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</body>
</html>
