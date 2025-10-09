<?php
// admin_tickets.php
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
    <title>Admin - Tickets</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Tickets</h3>
        <div class="text-end mb-3">
            <a href="admin_create_ticket.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Create New Ticket</a>
        </div>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->query("SELECT * FROM tickets ORDER BY created_at DESC");
            while ($ticket = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="col-12 mb-3">';
                echo '<div class="card">';
                echo '<div class="card-body d-flex align-items-center">';
                // removed small placeholder image for ticket listing
                echo '<div class="flex-grow-1">';
                echo '<h5>' . htmlspecialchars($ticket['subject']) . '</h5>';
                echo '<div>Company: ' . htmlspecialchars($ticket['company']) . ' | Agent ID: ' . htmlspecialchars($ticket['agent_id']) . '</div>';
                echo '<div>Status: ' . htmlspecialchars($ticket['status']) . '</div>';
                echo '<div>Notes: ' . htmlspecialchars($ticket['message']) . '</div>';
                echo '<a href="admin_review_ticket.php?id=' . $ticket['id'] . '" class="btn btn-outline-secondary btn-sm mt-2">Edit / Review</a>';
                echo '</div>';
                echo '<button class="btn btn-outline-danger ms-3"><i class="bi bi-trash"></i> Delete</button>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error loading tickets: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</body>
</html>
