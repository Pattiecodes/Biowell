<?php
// agent_tickets.php
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
    <title>Agent - Tickets</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Tickets</h3>
        <div class="text-end mb-3">
            <a href="agent_create_ticket.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Create New Ticket</a>
        </div>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $user_id = $_SESSION['user_id'];
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Handle delete
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmtDel = $pdo->prepare("DELETE FROM tickets WHERE id = ? AND agent_id = ?");
            $stmtDel->execute([$delete_id, $user_id]);
        }
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE agent_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        while ($ticket = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="col-12 mb-3">';
            echo '<div class="card">';
            echo '<div class="card-body d-flex align-items-center">';
            // removed small placeholder image for ticket listing
            echo '<div class="flex-grow-1">';
            echo '<h5>' . htmlspecialchars($ticket['subject']) . '</h5>';
            echo '<div>Progress - ' . htmlspecialchars($ticket['status']) . '</div>';
            echo '<a href="agent_review_ticket.php?id=' . $ticket['id'] . '" class="btn btn-outline-secondary btn-sm mt-2">Edit</a>';
            echo '</div>';
            echo '<form method="post" action="agent_tickets.php" style="margin:0;">';
            echo '<input type="hidden" name="delete_id" value="' . $ticket['id'] . '"><button type="submit" class="btn btn-outline-danger ms-3"><i class="bi bi-trash"></i> Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
