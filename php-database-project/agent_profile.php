<?php
// agent_profile.php
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
    <title>Agent - Profile</title>
    <link rel="stylesheet" href="biowell.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'agent_navbar.php'; ?>
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
                $agent_id = $_SESSION['user_id'];
                $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->prepare("SELECT u.email, u.password, u.full_name, a.user_id FROM users u JOIN agents a ON u.id = a.user_id WHERE u.id = ?");
                $stmt->execute([$agent_id]);
                $agent = $stmt->fetch(PDO::FETCH_ASSOC);
                $first_name = $last_name = '';
                if ($agent && !empty($agent['full_name'])) {
                    $names = explode(' ', $agent['full_name'], 2);
                    $first_name = $names[0];
                    $last_name = isset($names[1]) ? $names[1] : '';
                }
                ?>
                <form method="post">
                    <div class="row mb-3">
                        <div class="col">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>">
                        </div>
                        <div class="col">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($agent['email'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" value="<?php echo htmlspecialchars($agent['password'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label>Agent ID</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($agent['user_id'] ?? ''); ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
                <?php
                // Handle POST after the form (without avatar support)
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $first = trim($_POST['first_name'] ?? '');
                    $last = trim($_POST['last_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $fullName = trim($first . ' ' . $last);
                    try {
                        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $updateFields = ['full_name' => $fullName, 'email' => $email];
                        if ($password) {
                            $updateFields['password'] = password_hash($password, PASSWORD_DEFAULT);
                        }
                        $setStr = implode(', ', array_map(function($k){ return "$k = :$k"; }, array_keys($updateFields)));
                        $updateFields['id'] = $agent_id;
                        $stmt = $pdo->prepare("UPDATE users SET $setStr WHERE id = :id");
                        $stmt->execute($updateFields);
                        echo '<div class="alert alert-success mt-3">Successfully Saved. Your profile settings have been saved.</div>';
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger mt-3">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
