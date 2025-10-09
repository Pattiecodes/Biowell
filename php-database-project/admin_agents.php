<?php
// admin_agents.php
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
    <title>Admin - Manage Agents</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Agent Management</h3>
        <?php
        require_once __DIR__ . '/src/config/database.php';
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Handle create and delete
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_agent'])) {
            $first = trim($_POST['first_name'] ?? '');
            $last = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($first && $last && $email && $password) {
                // check existing
                $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmtCheck->execute([$email]);
                if ($stmtCheck->fetch()) {
                    $createMsg = '<div class="alert alert-danger">Email already used.</div>';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'agent';
                    $full = $first . ' ' . $last;
                    $stmtIns = $pdo->prepare("INSERT INTO users (email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'active')");
                    $stmtIns->execute([$email, $hashed, $full, $role]);
                    $newUserId = $pdo->lastInsertId();
                    // generate acct_num
                    $stmtMax = $pdo->query("SELECT acct_num FROM agents WHERE acct_num REGEXP '^a[0-9]+' ORDER BY acct_num DESC LIMIT 1");
                    $last = $stmtMax->fetchColumn();
                    $nextNum = 1;
                    if ($last) { $num = intval(preg_replace('/[^0-9]/', '', $last)); $nextNum = $num + 1; }
                    $acct_num = 'a' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
                    $stmtA = $pdo->prepare("INSERT INTO agents (user_id, acct_num) VALUES (?, ?)");
                    $stmtA->execute([$newUserId, $acct_num]);
                    $createMsg = '<div class="alert alert-success">Agent created: ' . htmlspecialchars($acct_num) . '</div>';
                }
            } else {
                $createMsg = '<div class="alert alert-danger">All fields required.</div>';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmtDel = $pdo->prepare("DELETE FROM agents WHERE user_id = ?");
            $stmtDel->execute([$delete_id]);
            $stmtDelUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmtDelUser->execute([$delete_id]);
        }
        if (isset($createMsg)) echo $createMsg;
    $stmt = $pdo->query("SELECT a.*, u.full_name, u.email FROM agents a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.user_id ASC");
        echo '<div class="row">';
    // Create form
    echo '<div class="card mb-4"><div class="card-body"><h5>Create Agent</h5><form method="post" class="row g-2">';
    echo '<div class="col"><input class="form-control" name="first_name" placeholder="First name" required></div>';
    echo '<div class="col"><input class="form-control" name="last_name" placeholder="Last name" required></div>';
    echo '<div class="col"><input class="form-control" name="email" placeholder="Email" required></div>';
    echo '<div class="col"><input class="form-control" type="password" name="password" placeholder="Password" required></div>';
    echo '<div class="col-12 mt-2"><button class="btn btn-success" name="create_agent">Create Agent</button></div>';
    echo '</form></div></div>';
        while ($agent = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="col-12 mb-3">';
            echo '<div class="card">';
            echo '<div class="card-body d-flex align-items-center">';
            // removed small placeholder image for agent listing
            echo '<div class="flex-grow-1">';
            echo '<h5>' . htmlspecialchars($agent['full_name'] ?? 'Agent') . '</h5>';
            echo '<p>Agent ID: ' . htmlspecialchars($agent['acct_num'] ?? $agent['user_id']) . ' | Email: ' . htmlspecialchars($agent['email']) . '</p>';
            echo '<a href="admin_edit_agent.php?id=' . $agent['user_id'] . '" class="btn btn-outline-secondary btn-sm">Edit</a>';
            echo '</div>';
            echo '<form method="post" action="admin_agents.php" style="margin:0;">';
            echo '<input type="hidden" name="delete_id" value="' . $agent['user_id'] . '"><button type="submit" class="btn btn-outline-danger ms-3"><i class="bi bi-trash"></i> Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        ?>
    </div>
</body>
</html>
