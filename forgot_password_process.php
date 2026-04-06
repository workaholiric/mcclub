<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_config.php';

$genericMessage = 'If an account exists for this email and it is approved, we sent password reset instructions. Check your inbox and spam folder.';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['message' => 'Invalid request.']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    echo json_encode(['message' => 'Please enter a valid email address.']);
    exit;
}

if (is_readable(__DIR__ . '/mail_config.local.php')) {
    require_once __DIR__ . '/mail_config.local.php';
}

try {
    $stmt = $pdo->prepare('SELECT id, email, full_name, status, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('forgot_password: ' . $e->getMessage());
    echo json_encode(['message' => $genericMessage]);
    exit;
}

if (!$user) {
    echo json_encode(['message' => $genericMessage]);
    exit;
}

$canReset = ($user['role'] === 'admin') || ($user['status'] === 'approved');
if (!$canReset) {
    echo json_encode(['message' => $genericMessage]);
    exit;
}

$rawToken = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $rawToken);
$expires = date('Y-m-d H:i:s', time() + 3600);

try {
    $upd = $pdo->prepare('UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?');
    $upd->execute([$tokenHash, $expires, $user['id']]);
} catch (PDOException $e) {
    error_log('forgot_password update: ' . $e->getMessage());
    $driverCode = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
    $msg = $e->getMessage();
    $missingColumn = $driverCode === 1054 || stripos($msg, 'Unknown column') !== false;
    http_response_code(500);
    echo json_encode([
        'message' => $missingColumn
            ? 'Password reset needs a database update. In Hostinger: phpMyAdmin → your database → SQL → run the file sql/add_password_reset_columns.sql (ALTER TABLE users, add two columns). Then try again.'
            : 'Could not process request. If this continues, contact support.',
    ]);
    exit;
}

if (defined('SITE_PUBLIC_BASE_URL') && is_string(SITE_PUBLIC_BASE_URL) && SITE_PUBLIC_BASE_URL !== '') {
    $resetUrl = rtrim(SITE_PUBLIC_BASE_URL, '/') . '/reset_password.php?t=' . rawurlencode($rawToken);
} else {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $protocol = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $basePath = ($dir === '' || $dir === '.') ? '' : $dir;
    $resetUrl = $protocol . '://' . $host . $basePath . '/reset_password.php?t=' . rawurlencode($rawToken);
}

$subject = 'Reset your NEXTGEN CONQUERORS password';
$body = "Hi " . ($user['full_name'] ?: 'there') . ",\r\n\r\n";
$body .= "We received a request to reset the password for your account.\r\n\r\n";
$body .= "Open this link within 1 hour to choose a new password:\r\n";
$body .= $resetUrl . "\r\n\r\n";
$body .= "If you did not request this, you can ignore this email.\r\n\r\n";
$body .= "— NEXTGEN CONQUERORS\r\n";

$hostForDefault = $_SERVER['HTTP_HOST'] ?? 'localhost';
$fromEmail = defined('RESET_MAIL_FROM') ? RESET_MAIL_FROM : ('noreply@' . preg_replace('/^www\./', '', explode(':', $hostForDefault)[0]));
$fromName = defined('RESET_MAIL_FROM_NAME') ? RESET_MAIL_FROM_NAME : 'NEXTGEN CONQUERORS';
$fromNameEnc = function_exists('mb_encode_mimeheader')
    ? mb_encode_mimeheader($fromName, 'UTF-8', 'B', "\r\n")
    : 'NEXTGEN CONQUERORS';

$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/plain; charset=UTF-8',
    'From: ' . $fromNameEnc . ' <' . $fromEmail . '>',
    'Reply-To: ' . $fromEmail,
    'X-Mailer: PHP/' . PHP_VERSION,
];

$sent = @mail($user['email'], $subject, $body, implode("\r\n", $headers));
if (!$sent) {
    error_log('forgot_password: mail() failed for user id ' . $user['id']);
}

echo json_encode(['message' => $genericMessage]);
