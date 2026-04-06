<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['message' => 'Invalid request.']);
    exit;
}

$rawToken = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

if ($rawToken === '' || strlen($rawToken) < 32) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid or expired reset link. Request a new one from the login page.']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['message' => 'Password must be at least 8 characters.']);
    exit;
}

if ($password !== $passwordConfirm) {
    http_response_code(400);
    echo json_encode(['message' => 'Password and confirmation do not match.']);
    exit;
}

$tokenHash = hash('sha256', $rawToken);

try {
    $stmt = $pdo->prepare(
        'SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires IS NOT NULL AND password_reset_expires > NOW() LIMIT 1'
    );
    $stmt->execute([$tokenHash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('reset_password: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Something went wrong. Please try again later.']);
    exit;
}

if (!$row) {
    http_response_code(400);
    echo json_encode(['message' => 'This reset link is invalid or has expired. Request a new password reset from the login page.']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    $upd = $pdo->prepare(
        'UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?'
    );
    $upd->execute([$hashed, $row['id']]);
} catch (PDOException $e) {
    error_log('reset_password save: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Could not update password. Please try again.']);
    exit;
}

echo json_encode([
    'message' => 'Your password was updated. You can log in now.',
    'redirect' => 'login.php',
]);
