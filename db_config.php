<?php
$local = __DIR__ . '/db_config.local.php';
if (!is_readable($local)) {
    header('Content-Type: text/plain; charset=utf-8', true, 503);
    die(
        "Database not configured.\n\n" .
        "Copy db_config.example.php to db_config.local.php and set your Hostinger MySQL " .
        "host, database name, user, and password from hPanel → Databases.\n"
    );
}
require $local;

if (!isset($db_host, $db_name, $db_user, $db_pass)) {
    die('db_config.local.php must define $db_host, $db_name, $db_user, and $db_pass.');
}

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    error_log('DB connection: ' . $e->getMessage());
    die('Database Connection Failed. Check db_config.local.php and hPanel MySQL credentials.');
}
