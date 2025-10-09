<?php
session_start();
// Simple role simulation for demo (replace with real login/session logic)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'guest'; // guest, agent, admin
}
// Database connection
require_once __DIR__ . '/src/config/database.php';
try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Simple role simulation for demo (replace with real login/session logic)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'guest'; // guest, agent, admin
}
$role = $_SESSION['role'];

// Page routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

function navItem($name, $page, $icon = '') {
    $active = (isset($_GET['page']) && $_GET['page'] == $page) ? 'active' : '';
    echo "<li class='nav-item'><a class='nav-link $active' href='?page=$page'>$icon $name</a></li>";
}
?>
    <title>BioWell Insurance Company</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="biowell.css">
</head>
<body>
<?php
if ($role === 'admin') {
    include 'admin_navbar.php';
} elseif ($role === 'agent') {
    include 'agent_navbar.php';
} else {
?>
<nav class="navbar navbar-light">
    <div class="container-fluid d-flex justify-content-between align-items-center py-2">
        <a class="navbar-brand" href="?page=home">Bio-Well Insurance Company</a>
        <div class="top-actions">
            <a class="btn btn-outline-primary me-2" href="?page=signup">Sign Up</a>
            <a class="btn btn-primary" href="?page=login">Log In</a>
        </div>
    </div>
</nav>
<div class="navbar-nav bg-white border-bottom" style="padding:0.5rem 0;">
    <div class="container-fluid d-flex justify-content-center" style="gap:2rem;">
        <?php navItem('Home', 'home', ''); ?>
        <?php navItem('Tickets', 'tickets', ''); ?>
        <?php navItem('Quotations', 'quotations', ''); ?>
    </div>
</div>
<?php } ?>
<div class="container">
    <?php
    // Main content area
    switch ($page) {
        case 'home':
            // Public/Home dashboard - show announcements and events from DB (same source as admin/agent)
            try {
                $stmtA = $pdo->prepare("SELECT id, title, content, category, created_at FROM posts WHERE category IN ('news','promo','update') ORDER BY created_at DESC LIMIT 20");
                $stmtA->execute();
                $announcements = $stmtA->fetchAll(PDO::FETCH_ASSOC);
                $stmtE = $pdo->prepare("SELECT id, title, content, created_at FROM posts WHERE category = 'event' ORDER BY created_at DESC LIMIT 20");
                $stmtE->execute();
                $events = $stmtE->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $announcements = [];
                $events = [];
            }
            ?>
            <div class='home-banner'>
                <div class='banner-img'><span style='font-size:3rem;'>&#128712;</span></div>
            </div>
            <div class='row mt-4'>
                <div class='col-md-6 mb-3'>
                    <div class='d-flex justify-content-between align-items-center mb-2'>
                        <h5 class='text-primary mb-0'>Announcements</h5>
                    </div>
                    <div style='border:1px solid #ddd; border-radius:6px; padding:12px; max-height:360px; overflow:auto;'>
                        <?php if (empty($announcements)) : ?>
                            <div class='text-muted'>No announcements yet.</div>
                        <?php else: ?>
                            <?php foreach ($announcements as $a): ?>
                                <div class='mb-3'>
                                    <h6><?php echo htmlspecialchars($a['title']); ?></h6>
                                    <div class='text-muted' style='font-size:0.9rem;'><?php echo htmlspecialchars(substr($a['content'],0,200)); ?><?php echo strlen($a['content'])>200 ? '...' : ''; ?></div>
                                    <small class='text-secondary'><?php echo htmlspecialchars(date('M j, Y', strtotime($a['created_at']))); ?> — <?php echo htmlspecialchars($a['category']); ?></small>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class='col-md-6 mb-3'>
                    <div class='d-flex justify-content-between align-items-center mb-2'>
                        <h5 class='text-primary mb-0'>Events</h5>
                    </div>
                    <div style='border:1px solid #ddd; border-radius:6px; padding:12px; max-height:360px; overflow:auto;'>
                        <?php if (empty($events)) : ?>
                            <div class='text-muted'>No events yet.</div>
                        <?php else: ?>
                            <?php foreach ($events as $e): ?>
                                <div class='mb-3'>
                                    <h6><?php echo htmlspecialchars($e['title']); ?></h6>
                                    <div class='text-muted' style='font-size:0.9rem;'><?php echo htmlspecialchars(substr($e['content'],0,200)); ?><?php echo strlen($e['content'])>200 ? '...' : ''; ?></div>
                                    <small class='text-secondary'><?php echo htmlspecialchars(date('M j, Y', strtotime($e['created_at']))); ?></small>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            break;
        case 'tickets':
            echo "<h2>Tickets</h2>";
            if ($role == 'guest') {
                echo "<div class='card'><div class='card-body text-center text-danger'><strong>Log In First</strong></div></div>";
            } elseif ($role == 'admin') {
                $stmt = $pdo->query("SELECT t.*, u.full_name FROM tickets t LEFT JOIN users u ON t.created_by_user_id = u.id ORDER BY t.created_at DESC");
                echo "<div class='card'><div class='card-body'>";
                echo "<ul class='list-group'>";
                while ($ticket = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li class='list-group-item'><strong>" . htmlspecialchars($ticket['subject']) . "</strong> by " . htmlspecialchars($ticket['full_name']) . "<br>Status: " . htmlspecialchars($ticket['status']) . "</li>";
                }
                echo "</ul>";
                echo "</div></div>";
            } elseif ($role == 'agent') {
                $stmt = $pdo->prepare("SELECT * FROM tickets WHERE created_by_user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$_SESSION['user_id']]);
                echo "<div class='card'><div class='card-body'>";
                echo "<ul class='list-group'>";
                while ($ticket = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li class='list-group-item'><strong>" . htmlspecialchars($ticket['subject']) . "</strong> <br>Status: " . htmlspecialchars($ticket['status']) . "</li>";
                }
                echo "</ul>";
                echo "</div></div>";
                echo "<a href='?page=create_ticket' class='btn btn-primary mt-2'>Create New Ticket</a>";
            }
            break;
        case 'create_ticket':
            // Ticket creation logic
            $ticketMsg = '';
            if (($role == 'agent' || $role == 'admin') && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject'], $_POST['company'], $_POST['application_id'], $_POST['product'], $_POST['note'], $_POST['agent_id'])) {
                $subject = trim($_POST['subject']);
                $company = trim($_POST['company']);
                $application_id = trim($_POST['application_id']);
                $product = trim($_POST['product']); // Not used in DB insert
                $note = trim($_POST['note']);
                $agent_id = trim($_POST['agent_id']);
                $created_by_user_id = $_SESSION['user_id'];
                // Validate agent_id exists in agents table
                $stmtCheck = $pdo->prepare("SELECT user_id FROM agents WHERE user_id = ?");
                $stmtCheck->execute([$agent_id]);
                if ($stmtCheck->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO tickets (subject, message, status, type, created_by_user_id, company, insurance_application_id, agent_id, created_at) VALUES (?, ?, 'open', 'quotation', ?, ?, ?, ?, NOW())");
                    $stmt->execute([$subject, $note, $created_by_user_id, $company, $application_id ?: null, $agent_id]);
                    $ticketMsg = '<div class=\'alert alert-success\'>Ticket created successfully!</div>';
                } else {
                    $ticketMsg = '<div class=\'alert alert-danger\'>Agent ID not found. Please enter a valid Agent ID.</div>';
                }
            }
            echo "<h2>Create Ticket</h2>";
            if ($ticketMsg) echo $ticketMsg;
            echo "<form method='post'>
                <div class='mb-3'><label>Subject</label><input class='form-control' name='subject' required></div>
                <div class='mb-3'><label>Company</label><input class='form-control' name='company'></div>
                <div class='mb-3'><label>Application ID</label><input class='form-control' name='application_id'></div>
                <div class='mb-3'><label>Product</label><input class='form-control' name='product'></div>
                <div class='mb-3'><label>Note</label><textarea class='form-control' name='note'></textarea></div>
                <button class='btn btn-success'>Submit</button>
            </form>";
            break;
        case 'quotations':
            echo "<h2>Quotations</h2>";
            if ($role == 'guest') {
                echo "<div class='card'><div class='card-body text-center text-danger'><strong>Log In First</strong></div></div>";
            } elseif ($role == 'admin') {
                $stmt = $pdo->query("SELECT ia.*, u.full_name FROM insurance_application ia LEFT JOIN users u ON ia.agent_id = u.id ORDER BY ia.application_date DESC");
                echo "<div class='card'><div class='card-body'>";
                echo "<ul class='list-group'>";
                while ($q = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li class='list-group-item'><strong>Agent:</strong> " . htmlspecialchars($q['full_name']) . "<br>Status: " . htmlspecialchars($q['status']) . "<br>Notes: " . htmlspecialchars($q['notes']) . "</li>";
                }
                echo "</ul>";
                echo "</div></div>";
            } elseif ($role == 'agent') {
                $stmt = $pdo->prepare("SELECT * FROM insurance_application WHERE agent_id = ? ORDER BY application_date DESC");
                $stmt->execute([$_SESSION['user_id']]);
                echo "<div class='card'><div class='card-body'>";
                echo "<ul class='list-group'>";
                while ($q = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li class='list-group-item'><strong>Status:</strong> " . htmlspecialchars($q['status']) . "<br>Notes: " . htmlspecialchars($q['notes']) . "</li>";
                }
                echo "</ul>";
                echo "</div></div>";
                echo "<a href='?page=create_quotation' class='btn btn-primary mt-2'>Request Quotation</a>";
            }
            break;
        case 'create_quotation':
            // Quotation creation logic
            $quotationMsg = '';
            if ($role == 'agent' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_name'], $_POST['client_address'], $_POST['client_email'], $_POST['product'], $_POST['notes'])) {
                $client_name = trim($_POST['client_name']);
                $client_address = trim($_POST['client_address']);
                $client_email = trim($_POST['client_email']);
                $product = trim($_POST['product']);
                $notes = trim($_POST['notes']);
                // Require agent to exist in agents table
                $agent_id = $_SESSION['user_id'];
                $stmtCheck = $pdo->prepare("SELECT user_id FROM agents WHERE user_id = ?");
                $stmtCheck->execute([$agent_id]);
                if ($stmtCheck->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO insurance_application (product_id, agent_id, status, application_date, notes) VALUES (?, ?, 'pending', NOW(), ?)");
                    // For demo, product_id is not resolved from name, so set as NULL
                    $stmt->execute([null, $agent_id, $notes]);
                    $quotationMsg = '<div class=\'alert alert-success\'>Quotation request submitted!</div>';
                } else {
                    $quotationMsg = '<div class=\'alert alert-danger\'>You must be a registered agent to request a quotation.</div>';
                }
            }
            echo "<h2>New Quotation / Application</h2>";
            if ($quotationMsg) echo $quotationMsg;
            echo "<form method='post'>
                <div class='mb-3'><label>Client Name</label><input class='form-control' name='client_name' required></div>
                <div class='mb-3'><label>Client Address</label><input class='form-control' name='client_address'></div>
                <div class='mb-3'><label>Client Email</label><input class='form-control' name='client_email'></div>
                <div class='mb-3'><label>Product</label><input class='form-control' name='product'></div>
                <div class='mb-3'><label>Notes</label><textarea class='form-control' name='notes'></textarea></div>
                <button class='btn btn-success'>Submit</button>
            </form>";
            break;
        case 'agents':
                // Admin view: show all agents and applications
                if ($role == 'admin') {
                    echo "<h2>Agent Management</h2>";
                    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'agent'");
                    echo "<div class='card'><div class='card-body'>";
                    echo "<ul class='list-group'>";
                    while ($agent = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<li class='list-group-item'>" . htmlspecialchars($agent['full_name']) . " - " . htmlspecialchars($agent['email']) . "</li>";
                    }
                    echo "</ul>";
                    echo "</div></div>";
                }
                break;
        case 'products':
                // Admin view: show all products
                if ($role == 'admin') {
                    echo "<h2>Manage Products</h2>";
                    $stmt = $pdo->query("SELECT * FROM insurance_products");
                    echo "<div class='card'><div class='card-body'>";
                    echo "<ul class='list-group'>";
                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<li class='list-group-item'>" . htmlspecialchars($product['name']) . " - " . htmlspecialchars($product['description']) . "</li>";
                    }
                    echo "</ul>";
                    echo "</div></div>";
                }
                break;
        case 'announcements':
            // Shared: Agents and Admins can view announcements
            try {
                $stmt = $pdo->prepare("SELECT id, title, content, category, created_at FROM posts WHERE category IN ('news','promo','update') ORDER BY created_at DESC");
                $stmt->execute();
                $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $posts = [];
            }
            echo "<h2>Announcements</h2>";
            echo "<div class='card'><div class='card-body'>";
            if (empty($posts)) {
                echo "<div class='text-muted'>No announcements yet.</div>";
            } else {
                echo "<ul class='list-group'>";
                foreach ($posts as $p) {
                    echo "<li class='list-group-item'><strong>" . htmlspecialchars($p['title']) . "</strong><br><small class='text-secondary'>" . htmlspecialchars(date('M j, Y', strtotime($p['created_at']))) . " — " . htmlspecialchars($p['category']) . "</small><div class='mt-2 text-muted'>" . htmlspecialchars(substr($p['content'],0,300)) . (strlen($p['content'])>300? '...' : '') . "</div></li>";
                }
                echo "</ul>";
            }
            echo "</div></div>";
            break;
        case 'events':
            // Shared: Agents and Admins can view events
            try {
                $stmt = $pdo->prepare("SELECT id, title, content, created_at FROM posts WHERE category = 'event' ORDER BY created_at DESC");
                $stmt->execute();
                $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $posts = [];
            }
            echo "<h2>Events</h2>";
            echo "<div class='card'><div class='card-body'>";
            if (empty($posts)) {
                echo "<div class='text-muted'>No events yet.</div>";
            } else {
                echo "<ul class='list-group'>";
                foreach ($posts as $p) {
                    echo "<li class='list-group-item'><strong>" . htmlspecialchars($p['title']) . "</strong><br><small class='text-secondary'>" . htmlspecialchars(date('M j, Y', strtotime($p['created_at']))) . "</small><div class='mt-2 text-muted'>" . htmlspecialchars(substr($p['content'],0,300)) . (strlen($p['content'])>300? '...' : '') . "</div></li>";
                }
                echo "</ul>";
            }
            echo "</div></div>";
            break;
        case 'profile':
            // Shared: Agents and Admins can update profile
            if ($role != 'guest') {
                // Fetch user info
                $user_id = $_SESSION['user_id'];
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $successMsg = '';
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'])) {
                    $firstName = trim($_POST['first_name']);
                    $lastName = trim($_POST['last_name']);
                    $email = trim($_POST['email']);
                    $password = $_POST['password'];
                    $fullName = $firstName . ' ' . $lastName;
                    $updateFields = [
                        'full_name' => $fullName,
                        'email' => $email
                    ];
                    if ($password) {
                        $updateFields['password'] = password_hash($password, PASSWORD_DEFAULT);
                    }
                    $setStr = implode(', ', array_map(function($k){ return "$k = :$k"; }, array_keys($updateFields)));
                    $updateFields['id'] = $user_id;
                    $stmt = $pdo->prepare("UPDATE users SET $setStr WHERE id = :id");
                    $stmt->execute($updateFields);
                    $successMsg = '<div class="alert alert-success mt-3">Successfully Saved. Your profile settings have been saved.</div>';
                    // Refresh user info
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                $firstName = '';
                $lastName = '';
                if (!empty($user['full_name'])) {
                    $parts = explode(' ', $user['full_name'], 2);
                    $firstName = $parts[0];
                    $lastName = isset($parts[1]) ? $parts[1] : '';
                }
                echo '<h2>Settings</h2>';
                echo '<div class="row">';
                echo '<div class="col-md-4">';
                echo '<div class="card"><div class="card-body text-center">';
                echo '<div class="mb-2">Profile picture feature disabled.</div>';
                echo '</div></div>';
                echo '</div>';
                echo '<div class="col-md-8">';
                echo '<form method="post">';
                echo '<div class="row mb-3">';
                echo '<div class="col"><label>First Name</label><input type="text" name="first_name" class="form-control" value="' . htmlspecialchars($firstName) . '" required></div>';
                echo '<div class="col"><label>Last Name</label><input type="text" name="last_name" class="form-control" value="' . htmlspecialchars($lastName) . '" required></div>';
                echo '</div>';
                echo '<div class="mb-3"><label>Email Address</label><input type="email" name="email" class="form-control" value="' . htmlspecialchars($user['email']) . '" required></div>';
                echo '<div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></div>';
                echo '<div class="mb-3"><label>' . ($role == 'admin' ? 'Admin ID' : 'Agent ID') . '</label><input type="text" class="form-control" value="' . htmlspecialchars($user['id']) . '" readonly></div>';
                echo '<button type="submit" class="btn btn-primary">Save</button>';
                echo '</form>';
                if ($successMsg) echo $successMsg;
                echo '</div>';  
                echo '</div>';
            }
            break;  
        case 'login':
            if ($role == 'admin') {
                // Admin: show all tickets
                $stmt = $pdo->query("SELECT t.*, u.full_name FROM tickets t LEFT JOIN users u ON t.created_by_user_id = u.id ORDER BY t.created_at DESC");
                echo "<div class='card'><div class='card-body'>";
                echo "<ul class='list-group'>";
                while ($ticket = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li class='list-group-item'><strong>" . htmlspecialchars($ticket['subject']) . "</strong> by " . htmlspecialchars($ticket['full_name']) . "<br>Status: " . htmlspecialchars($ticket['status']) . "</li>";
                }
                echo "</ul>";
                echo "</div></div>";
            } else {
                // Login logic
                $loginError = '';
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'], $_POST['role'])) {
                    $email = trim($_POST['email']);
                    $password = $_POST['password'];
                    $role = strtolower($_POST['role']);
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1");
                    $stmt->execute([$email, $role]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user && password_verify($password, $user['password'])) {
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['user_id'] = $user['id'];
                        header('Location: ?page=home');
                        exit;
                    } else {
                        $loginError = 'Invalid credentials.';
                    }
                }
                echo "<h2>Login</h2>";
                if ($loginError) echo "<div class='alert alert-danger'>$loginError</div>";
                echo "<form method='post'><div class='mb-3'><label>Role</label><select class='form-select' name='role'><option>Agent</option><option>Admin</option></select></div><div class='mb-3'><label>Email</label><input class='form-control' name='email' required></div><div class='mb-3'><label>Password</label><input type='password' class='form-control' name='password' required></div><button class='btn btn-primary'>Log In</button></form>";
            }
            break;
        case 'signup':
            // Signup logic
            $signupMsg = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'], $_POST['role'])) {
                $firstName = trim($_POST['first_name']);
                $lastName = trim($_POST['last_name']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $role = strtolower($_POST['role']); // 'agent' or 'admin'
                $fullName = $firstName . ' ' . $lastName;
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $signupMsg = '<div class="alert alert-danger">Email already registered.</div>';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'active')");
                    $stmt->execute([$email, $hashedPassword, $fullName, $role]);
                    // If agent, also insert into agents table
                    if ($role === 'agent') {
                        $user_id = $pdo->lastInsertId();
                        // generate next agent acct_num like a01, a02
                        $stmtMax = $pdo->query("SELECT acct_num FROM agents WHERE acct_num REGEXP '^a[0-9]+' ORDER BY acct_num DESC LIMIT 1");
                        $last = $stmtMax->fetchColumn();
                        $nextNum = 1;
                        if ($last) {
                            $num = intval(preg_replace('/[^0-9]/', '', $last));
                            $nextNum = $num + 1;
                        }
                        $acct_num = 'a' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
                        $stmtAgent = $pdo->prepare("INSERT INTO agents (user_id, acct_num) VALUES (?, ?)");
                        $stmtAgent->execute([$user_id, $acct_num]);
                    }
                    $signupMsg = '<div class="alert alert-success">Account created! You can now log in.</div>';
                }
            }
            echo "<h2>Sign Up</h2>";
            if ($signupMsg) echo $signupMsg;
            echo "<form method='post'>
                <div class='mb-3'><label>First Name</label><input class='form-control' name='first_name' required></div>
                <div class='mb-3'><label>Last Name</label><input class='form-control' name='last_name' required></div>
                <div class='mb-3'><label>Email</label><input class='form-control' name='email' required></div>
                <div class='mb-3'><label>Password</label><input type='password' class='form-control' name='password' required></div>
                <div class='mb-3'><label>Sign up as:</label>
                    <select name='role' class='form-select' required>
                        <option value='agent'>Agent</option>
                        <option value='admin'>Admin</option>
                    </select>
                </div>
                <button class='btn btn-success'>Submit</button>
            </form>";
            break;
        case 'logout':
            session_destroy();
            header('Location: ?page=login');
            exit;
        default:
            echo "<h2>Page not found</h2>";
    }
    ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="biowell.js"></script>
</body>
</html>
