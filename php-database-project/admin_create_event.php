<?php
// admin_create_event.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/src/config/database.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    if ($title && $content) {
        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Ensure 'event' exists in posts.category enum. If not, alter the column to include it.
            $col = $pdo->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'category'");
            $col->execute();
            $colType = $col->fetchColumn();
            if ($colType !== false && strpos($colType, "'event'") === false) {
                // Attempt to alter enum to add 'event'
                $alterSql = "ALTER TABLE posts MODIFY category ENUM('news','promo','update','event') NOT NULL";
                $pdo->exec($alterSql);
            }
            // Save in posts table with category 'event' and include event date inside content or as part of content
            $fullContent = $content . "\n\nEvent Date: " . $event_date;
            $stmt = $pdo->prepare("INSERT INTO posts (title, content, category, user_id, created_at) VALUES (?, ?, 'event', ?, NOW())");
            $stmt->execute([$title, $fullContent, $_SESSION['user_id']]);
            header('Location: admin_home.php');
            exit();
        } catch (PDOException $e) {
            $msg = '<div class="alert alert-danger">Error saving event: ' . htmlspecialchars($e->getMessage()) . '</div>';
        } catch (Exception $e) {
            $msg = '<div class="alert alert-danger">Unexpected error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $msg = '<div class="alert alert-danger">Title and content are required.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Create Event</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Create Event</h3>
        <?php if (!empty($msg)) echo $msg; ?>
        <form method="post">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Content / Description</label>
                <textarea name="content" class="form-control" rows="6" required></textarea>
            </div>
            <div class="mb-3">
                <label>Event Date</label>
                <input type="date" name="event_date" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Publish Event</button>
            <a href="admin_home.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</body>
</html>
