<?php
// agent_review_ticket.php
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
    <title>Agent - Review Ticket</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Review Ticket</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $id = intval($_GET['id'] ?? 0);
        // Fetch ticket without joining insurance_application to avoid errors when schema is not migrated
        $stmt = $pdo->prepare("SELECT t.* FROM tickets t WHERE t.id = ? AND t.agent_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        // If the ticket refers to an application, try to fetch application_code only if the column exists
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
                // If anything goes wrong, don't block the ticket view; show blank application code
                $ticket['application_code'] = '';
            }
        }
        if (!$ticket) {
            echo '<div class="alert alert-danger">Ticket not found or access denied.</div>';
        } else {
        ?>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $subject = trim($_POST['subject'] ?? '');
                $company = trim($_POST['company'] ?? '');
                $notes = trim($_POST['notes'] ?? '');
                $stmtUp = $pdo->prepare("UPDATE tickets SET subject = ?, company = ?, message = ? WHERE id = ? AND agent_id = ?");
                $stmtUp->execute([$subject, $company, $notes, $id, $_SESSION['user_id']]);
                echo '<div class="alert alert-success">Saved.</div>';
            }
        ?>
        <form method="post" action="agent_review_ticket.php?id=<?php echo $id; ?>">
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
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($ticket['application_code'] ?? ''); ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Agent ID</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($ticket['agent_id']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Notes</label>
                <textarea name="notes" class="form-control"><?php echo htmlspecialchars($ticket['message'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($ticket['status']); ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="agent_tickets.php" class="btn btn-link">Back</a>
            <button type="button" class="btn btn-danger float-end">Delete</button>
        </form>
        <?php } ?>
    </div>
</body>
</html>
