<?php
// Minimal bootstrap to ensure includes and working dir are absolute-safe (serverless friendly)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
}
chdir(BASE_PATH);

// add project root to include_path so existing relative includes still resolve
set_include_path(get_include_path() . PATH_SEPARATOR . BASE_PATH);

// mark serverless runtime when deployed on Vercel
if (getenv('SERVERLESS_PHP') === '1' || strtolower(getenv('SERVERLESS_PHP')) === 'true') {
    if (!defined('SERVERLESS_PHP')) define('SERVERLESS_PHP', true);
}
?>
