<?php
// local defaults (xampp development)
$local_db_host = 'localhost';
$local_db_port = '3306';
$local_db_name = 'biowell_insurance';
$local_db_user = 'root';
$local_db_pass = '';

// prefer environment variables (vercel / serverless). fall back to local values.
$dbHost = getenv('DB_HOST') !== false ? getenv('DB_HOST') : $local_db_host;
$dbPort = getenv('DB_PORT') !== false ? getenv('DB_PORT') : $local_db_port;
$dbName = getenv('DB_NAME') !== false ? getenv('DB_NAME') : $local_db_name;
$dbUsername = getenv('DB_USER') !== false ? getenv('DB_USER') : $local_db_user;
$dbPassword = getenv('DB_PASS') !== false ? getenv('DB_PASS') : $local_db_pass;

// build dsn and create pdo instance helper (optional), but most files in project
// expect these variables. use utf8mb4 and non-persistent connections for serverless.
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
	$pdo = new PDO($dsn, $dbUsername, $dbPassword, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_PERSISTENT => false,
	]);
} catch (PDOException $e) {
	// in dev, surface a helpful message. in production, vercel will show logs.
	echo "Database connection error: " . htmlspecialchars($e->getMessage());
	exit;
}
?>