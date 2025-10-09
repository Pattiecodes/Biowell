<?php
// admin_create_post.php
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
    $category = trim($_POST['category'] ?? 'news');
    if ($title && $content) {
        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("INSERT INTO posts (title, content, category, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $content, $category, $_SESSION['user_id']]);
            header('Location: admin_home.php');
            exit();
        } catch (PDOException $e) {
            $msg = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
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
    <title>Admin - Create Post</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Create Post</h3>
        <?php if (!empty($msg)) echo $msg; ?>
        <form method="post">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Content</label>
                <textarea name="content" class="form-control" rows="6" required></textarea>
            </div>
            <div class="mb-3">
                <label>Category</label>
                <select name="category" class="form-select">
                    <option value="news">News</option>
                    <option value="promo">Promo</option>
                    <option value="update">Update</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Publish</button>
            <a href="admin_home.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</body>
</html>
