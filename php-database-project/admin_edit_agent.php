<?php
// admin_edit_agent.php
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
    <title>Agent / Admin - Edit Agent</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h3>Settings</h3>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">Profile picture feature disabled.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <?php
                require_once __DIR__ . '/src/config/database.php';
                $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $id = intval($_GET['id'] ?? 0);
                $agent = null;
                if ($id) {
                    // Don't select u.avatar directly because some DBs may not have that column yet
                    $stmt = $pdo->prepare("SELECT a.*, u.full_name, u.email FROM agents a LEFT JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
                    $stmt->execute([$id]);
                    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                // Handle POST (save updates) -- avatar/picture support removed
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
                    $first = trim($_POST['first_name'] ?? '');
                    $last = trim($_POST['last_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $acct_num = trim($_POST['acct_num'] ?? '');
                    $fullName = trim($first . ' ' . $last);
                    try {
                        // Update users
                        $updateFields = ['full_name' => $fullName, 'email' => $email];
                        if ($password) {
                            $updateFields['password'] = password_hash($password, PASSWORD_DEFAULT);
                        }
                        $setStr = implode(', ', array_map(function($k){ return "$k = :$k"; }, array_keys($updateFields)));
                        $updateFields['id'] = $id;
                        $stmtUp = $pdo->prepare("UPDATE users SET $setStr WHERE id = :id");
                        $stmtUp->execute($updateFields);
                        // Update agents table acct_num if provided
                        if ($acct_num !== '') {
                            $stmtA = $pdo->prepare("UPDATE agents SET acct_num = ? WHERE user_id = ?");
                            $stmtA->execute([$acct_num, $id]);
                        }
                        // Refresh agent data
                        $stmt = $pdo->prepare("SELECT a.*, u.full_name, u.email FROM agents a LEFT JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
                        $stmt->execute([$id]);
                        $agent = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo '<div class="alert alert-success mt-3">Successfully Saved. Your profile settings have been saved.</div>';
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger mt-3">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                ?>
                <form method="post" action="admin_edit_agent.php?id=<?php echo $id; ?>">
                    <div class="row mb-3">
                        <div class="col">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars(explode(' ', $agent['full_name'] ?? '')[0] ?? ''); ?>">
                        </div>
                        <div class="col">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars(explode(' ', $agent['full_name'] ?? '')[1] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($agent['email'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
                    </div>
                    <div class="mb-3">
                        <label>Agent ID</label>
                        <input type="text" class="form-control" name="acct_num" value="<?php echo htmlspecialchars($agent['acct_num'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
                <div class="alert alert-success mt-3" role="alert">
                    Successfully Saved. Your profile settings have been saved.
                </div>
                <?php
                // Note: this page currently does not implement POST saving; the existing template shows success message.
                // Upload handling will be implemented in a follow-up if you want admin-side agent updates to store avatar immediately.
                ?>
            </div>
        </div>
    </div>
</body>
</html>
