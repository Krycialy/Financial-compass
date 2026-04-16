<?php
$is_render = getenv('RENDER') !== false;

$host = getenv('DB_HOST') ?: ($is_render ? '' : '127.0.0.1');
$user = getenv('DB_USER') ?: ($is_render ? '' : 'root');
$pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: ($is_render ? '' : 'financial');
$db_port = (int) (getenv('DB_PORT') ?: 3306);

if ($is_render && ($host === '' || $user === '' || $db_name === '')) {
    http_response_code(500);
    die('Database is not configured on Render. Set DB_HOST, DB_USER, DB_PASS, DB_NAME, and DB_PORT in Environment Variables.');
}

try {
    $db = mysqli_connect($host, $user, $pass, $db_name, $db_port);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('Database connection failed. Check Render env vars and database allowlist.');
}

if (!$db) {
    http_response_code(500);
    die('Database connection failed.');
}
?>
