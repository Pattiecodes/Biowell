<?php
// admin_review_ticket.php
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
    <title>Admin - Review Ticket</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Review Ticket</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $id = intval($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = trim($_POST['subject'] ?? '');
            $company = trim($_POST['company'] ?? '');
            $application_id = intval($_POST['application_id'] ?? 0) ?: null;
            $agent_id = intval($_POST['agent_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $status = trim($_POST['status'] ?? 'open');
            $stmtUp = $pdo->prepare("UPDATE tickets SET subject = ?, company = ?, insurance_application_id = ?, agent_id = ?, message = ?, status = ? WHERE id = ?");
            $stmtUp->execute([$subject, $company, $application_id, $agent_id, $notes, $status, $id]);
            echo '<div class="alert alert-success">Ticket updated.</div>';
        }
        // Fetch ticket without joining insurance_application to avoid errors when schema is not migrated
        $stmt = $pdo->prepare("SELECT t.* FROM tickets t WHERE t.id = ?");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        // If the ticket has an application reference, try to fetch application_code safely
        if ($ticket && !empty($ticket['insurance_application_id'])) {
            try {
                $colCheck = $pdo->prepare("SELECT COUNT(1) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insurance_application' AND COLUMN_NAME = 'application_code'");
                $colCheck->execute();
                $hasCol = (bool)$colCheck->fetchColumn();
                if ($hasCol) {
                    $stmtApp = $pdo->prepare("SELECT application_code FROM insurance_application WHERE id = ? LIMIT 1");
                    $stmtApp->execute([$ticket['insurance_application_id']]);
                    $appRow = $stmtApp->fetch(PDO::FETCH_ASSOC);
                    $ticket['application_code'] = $appRow['application_code'] ?? '';
                } else {
                    $ticket['application_code'] = '';
                }
            } catch (Exception $e) {
                $ticket['application_code'] = '';
            }
        }
        if (!$ticket) {
            echo '<div class="alert alert-danger">Ticket not found.</div>';
        } else {
            $agentsList = $pdo->query("SELECT user_id, acct_num FROM agents ORDER BY acct_num ASC")->fetchAll(PDO::FETCH_ASSOC);
            // Fetch applications; if application_code column missing, fall back to id-only labels
            $applications = [];
            try {
                $colCheck = $pdo->prepare("SELECT COUNT(1) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insurance_application' AND COLUMN_NAME = 'application_code'");
                $colCheck->execute();
                $hasCol = (bool)$colCheck->fetchColumn();
                if ($hasCol) {
                    $applications = $pdo->query("SELECT id, application_code FROM insurance_application ORDER BY application_date DESC")->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    // no application_code column — return ids only
                    $apps = $pdo->query("SELECT id FROM insurance_application ORDER BY application_date DESC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($apps as $a) {
                        $applications[] = ['id' => $a['id'], 'application_code' => 'ID:' . $a['id']];
                    }
                }
            } catch (Exception $e) {
                // ignore and leave applications empty
                $applications = [];
            }
        ?>
        <form method="post" action="admin_review_ticket.php?id=<?php echo $id; ?>">
            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" value="<?php echo htmlspecialchars($ticket['subject'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label>Company</label>
                <input type="text" name="company" class="form-control" value="<?php echo htmlspecialchars($ticket['company'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Application</label>
                <select name="application_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($applications as $app): ?>
                        <option value="<?php echo $app['id']; ?>" <?php if ($app['id'] == $ticket['insurance_application_id']) echo 'selected'; ?>><?php echo htmlspecialchars($app['application_code']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Agent ID</label>
                <select name="agent_id" class="form-select">
                    <?php foreach ($agentsList as $ag): ?>
                        <option value="<?php echo $ag['user_id']; ?>" <?php if ($ag['user_id'] == $ticket['agent_id']) echo 'selected'; ?>><?php echo htmlspecialchars($ag['acct_num']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Notes</label>
                <textarea name="notes" class="form-control"><?php echo htmlspecialchars($ticket['message'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label>Admin Review</label>
                <select name="status" class="form-select">
                    <option value="open" <?php if ($ticket['status']=='open') echo 'selected'; ?>>Open</option>
                    <option value="in_progress" <?php if ($ticket['status']=='in_progress') echo 'selected'; ?>>In Progress</option>
                    <option value="closed" <?php if ($ticket['status']=='closed') echo 'selected'; ?>>Closed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="admin_tickets.php" class="btn btn-link">Back</a>
            <button type="button" class="btn btn-danger float-end">Delete</button>
        </form>
        <?php } ?>
    </div>
</body>
</html>
