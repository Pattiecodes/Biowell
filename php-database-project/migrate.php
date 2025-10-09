<?php
// migrate.php
// Run this once (CLI: php migrate.php) to apply safe, idempotent schema and seed updates.
require_once __DIR__ . '/src/config/database.php';
try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

function columnExists(PDO $pdo, $table, $column) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return $stmt->fetchColumn() > 0;
}

$tasks = [];

// Ensure columns in insurance_application
$iaTable = 'insurance_application';
$cols = [
    'client_name' => "VARCHAR(255) NOT NULL",
    'client_address' => "VARCHAR(255) NOT NULL",
    'client_email' => "VARCHAR(255) NOT NULL",
    'application_code' => "VARCHAR(255) NULL",
];
foreach ($cols as $col => $def) {
    if (!columnExists($pdo, $iaTable, $col)) {
        try {
            $pdo->exec("ALTER TABLE `$iaTable` ADD COLUMN `$col` $def");
            $tasks[] = "Added column $iaTable.$col";
        } catch (Exception $e) {
            $tasks[] = "Failed to add $iaTable.$col: " . $e->getMessage();
        }
    } else {
        $tasks[] = "Column $iaTable.$col already exists";
    }
}

// Ensure unique index on application_code
try {
    $idxCheck = $pdo->query("SHOW INDEX FROM `$iaTable` WHERE Key_name = 'idx_application_code'")->fetchAll();
    if (count($idxCheck) === 0) {
        $pdo->exec("ALTER TABLE `$iaTable` ADD UNIQUE INDEX idx_application_code (application_code)");
        $tasks[] = "Added unique index idx_application_code on $iaTable.application_code";
    } else {
        $tasks[] = "Index idx_application_code already exists";
    }
} catch (Exception $e) {
    $tasks[] = "Index check/create failed: " . $e->getMessage();
}

// Ensure products VIP and Regular with correct PHP prices
$products = [
    ['name' => 'VIP', 'company' => 'BioWell', 'desc' => 'VIP insurance product - PHP 2,500.00/month', 'premium' => 2500.00],
    ['name' => 'Regular', 'company' => 'BioWell', 'desc' => 'Regular insurance product - PHP 600.00/month', 'premium' => 600.00],
];
foreach ($products as $p) {
    $stmt = $pdo->prepare("SELECT id, premium FROM insurance_products WHERE name = ? LIMIT 1");
    $stmt->execute([$p['name']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        // update premium/description if different
        if (floatval($row['premium']) !== floatval($p['premium'])) {
            $u = $pdo->prepare("UPDATE insurance_products SET premium = ?, description = ? WHERE id = ?");
            $u->execute([$p['premium'], $p['desc'], $row['id']]);
            $tasks[] = "Updated product {$p['name']} premium to {$p['premium']}";
        } else {
            $tasks[] = "Product {$p['name']} exists with correct premium";
        }
    } else {
        $ins = $pdo->prepare("INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$p['name'], $p['company'], $p['desc'], $p['premium'], 'Standard coverage', 'All clients']);
        $tasks[] = "Inserted product {$p['name']}";
    }
}

// Ensure agents.acct_num exists for existing agents without it
try {
    $stmt = $pdo->query("SELECT user_id FROM agents WHERE acct_num IS NULL OR acct_num = ''");
    $missing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($missing as $uid) {
        $stmtMax = $pdo->query("SELECT acct_num FROM agents WHERE acct_num REGEXP '^a[0-9]+' ORDER BY acct_num DESC LIMIT 1");
        $last = $stmtMax->fetchColumn();
        $nextNum = 1;
        if ($last) { $num = intval(preg_replace('/[^0-9]/', '', $last)); $nextNum = $num + 1; }
        $acct_num = 'a' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
        $up = $pdo->prepare("UPDATE agents SET acct_num = ? WHERE user_id = ?");
        $up->execute([$acct_num, $uid]);
        $tasks[] = "Set acct_num $acct_num for agent user_id $uid";
    }
} catch (Exception $e) {
    $tasks[] = "Failed to ensure acct_num: " . $e->getMessage();
}

// Report
echo "Migration report:\n";
foreach ($tasks as $t) {
    echo " - " . $t . "\n";
}

echo "\nDone. If you ran this via CLI, open your app or phpMyAdmin to confirm schema and data." . PHP_EOL;

?>