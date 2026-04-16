<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'financial';
$db_port = (int) (getenv('DB_PORT') ?: 3306);

$db = mysqli_connect($host, $user, $pass, $db_name, $db_port);

if (!$db) {
    die('Connection failed: ' . mysqli_connect_error());
}
?>
