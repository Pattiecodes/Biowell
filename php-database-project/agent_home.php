<?php
// agent_home.php
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
    <title>Agent - Home</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
    <div class="container mt-4">
        <?php
        require_once __DIR__ . '/src/config/database.php';
        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Fetch announcements (news/promo/update)
            $stmtA = $pdo->prepare("SELECT id, title, content, category, created_at FROM posts WHERE category IN ('news','promo','update') ORDER BY created_at DESC LIMIT 20");
            $stmtA->execute();
            $announcements = $stmtA->fetchAll(PDO::FETCH_ASSOC);
            // Fetch events
            $stmtE = $pdo->prepare("SELECT id, title, content, created_at FROM posts WHERE category = 'event' ORDER BY created_at DESC LIMIT 20");
            $stmtE->execute();
            $events = $stmtE->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $announcements = [];
            $events = [];
        }
        ?>

        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="text-primary mb-0">Announcements</h5>
                </div>
                <div style="border:1px solid #ddd; border-radius:6px; padding:12px; max-height:360px; overflow:auto; text-align:left;">
                    <?php if (empty($announcements)) : ?>
                        <div class="text-muted">No announcements yet.</div>
                    <?php else: ?>
                        <?php foreach ($announcements as $a): ?>
                            <div class="mb-3">
                                <h6><?php echo htmlspecialchars($a['title']); ?></h6>
                                <div class="text-muted" style="font-size:0.9rem;"><?php echo htmlspecialchars(substr($a['content'],0,200)); ?><?php echo strlen($a['content'])>200 ? '...' : ''; ?></div>
                                <small class="text-secondary"><?php echo htmlspecialchars(date('M j, Y', strtotime($a['created_at']))); ?> — <?php echo htmlspecialchars($a['category']); ?></small>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="text-primary mb-0">Events</h5>
                </div>
                <div style="border:1px solid #ddd; border-radius:6px; padding:12px; max-height:360px; overflow:auto; text-align:left;">
                    <?php if (empty($events)) : ?>
                        <div class="text-muted">No events yet.</div>
                    <?php else: ?>
                        <?php foreach ($events as $e): ?>
                            <div class="mb-3">
                                <h6><?php echo htmlspecialchars($e['title']); ?></h6>
                                <div class="text-muted" style="font-size:0.9rem;"><?php echo htmlspecialchars(substr($e['content'],0,200)); ?><?php echo strlen($e['content'])>200 ? '...' : ''; ?></div>
                                <small class="text-secondary"><?php echo htmlspecialchars(date('M j, Y', strtotime($e['created_at']))); ?></small>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
