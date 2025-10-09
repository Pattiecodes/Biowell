<?php
// agent_create_ticket.php
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
    <title>Agent - Create Ticket</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Create a Ticket</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $msg = '';
        // Fetch agent's applications for dropdown
        $agentApplications = [];
        try {
            $pdoTmp = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
            $pdoTmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Check whether application_code column exists
            $colCheck = $pdoTmp->prepare("SELECT COUNT(1) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insurance_application' AND COLUMN_NAME = 'application_code'");
            $colCheck->execute();
            $hasCol = (bool)$colCheck->fetchColumn();
            if ($hasCol) {
                $stmtApps = $pdoTmp->prepare("SELECT id, application_code FROM insurance_application WHERE agent_id = ? ORDER BY application_date DESC");
                $stmtApps->execute([$_SESSION['user_id']]);
                while ($r = $stmtApps->fetch(PDO::FETCH_ASSOC)) {
                    $agentApplications[] = $r;
                }
            } else {
                // Fall back to id-only list
                $stmtApps = $pdoTmp->prepare("SELECT id FROM insurance_application WHERE agent_id = ? ORDER BY application_date DESC");
                $stmtApps->execute([$_SESSION['user_id']]);
                while ($r = $stmtApps->fetch(PDO::FETCH_ASSOC)) {
                    $agentApplications[] = ['id' => $r['id'], 'application_code' => 'ID:' . $r['id']];
                }
            }
        } catch (PDOException $e) {
            // ignore
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = trim($_POST['subject'] ?? '');
            $company = trim($_POST['company'] ?? '');
            $application_id = trim($_POST['application_id'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $agent_id = $_SESSION['user_id'];
            $created_by_user_id = $_SESSION['user_id'];
            if ($subject) {
                try {
                    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    // Check agent existence in agents table
                    $stmtCheck = $pdo->prepare("SELECT user_id FROM agents WHERE user_id = ?");
                    $stmtCheck->execute([$agent_id]);
                    if ($stmtCheck->rowCount() === 0) {
                        $msg = '<div class="alert alert-danger">Error: Agent record not found. Please contact admin.</div>';
                    } else {
                        // Validate insurance_application_id - ensure it belongs to this agent
                        $valid_application_id = null;
                        if ($application_id !== '') {
                            $stmtApp = $pdo->prepare("SELECT id FROM insurance_application WHERE id = ? AND agent_id = ?");
                            $stmtApp->execute([$application_id, $agent_id]);
                            if ($stmtApp->rowCount() > 0) {
                                $valid_application_id = $application_id;
                            } else {
                                $msg = '<div class="alert alert-warning">Warning: Application ID not found among your applications. Ticket will be created without linking to an application.</div>';
                            }
                        }
                        $stmt = $pdo->prepare("INSERT INTO tickets (subject, company, insurance_application_id, agent_id, message, status, created_at, created_by_user_id) VALUES (?, ?, ?, ?, ?, 'open', NOW(), ?)");
                        $stmt->execute([$subject, $company, $valid_application_id, $agent_id, $notes, $created_by_user_id]);
                        header('Location: agent_tickets.php');
                        exit();
                    }
                } catch (PDOException $e) {
                    $msg = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">Subject is required.</div>';
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
                <label>Application (your applications)</label>
                <select name="application_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($agentApplications as $app): ?>
                        <option value="<?php echo $app['id']; ?>"><?php echo htmlspecialchars($app['application_code']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Notes</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="agent_tickets.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</body>
</html>
