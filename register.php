<?php
header('Content-Type: application/json');
require_once 'db_config.php';
require_once __DIR__ . '/ngc_validators.php';
require_once __DIR__ . '/ngc_mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['message' => 'Invalid request method.']);
    exit;
}

$first_name = strip_tags(trim($_POST['first_name'] ?? ''));
$middle_name = strip_tags(trim($_POST['middle_name'] ?? ''));
$last_name = strip_tags(trim($_POST['last_name'] ?? ''));
$full_name = trim(preg_replace('/\s+/', ' ', $first_name . ' ' . $middle_name . ' ' . $last_name));
$email = trim($_POST['email'] ?? '');
$phone_raw = trim($_POST['phone'] ?? '');
$shipping_address = strip_tags(trim($_POST['shipping_address'] ?? ''));
$user_gcash = strip_tags(trim($_POST['user_gcash'] ?? ''));
$user_bank_name = strip_tags(trim($_POST['user_bank_name'] ?? ''));
$user_bank_number = strip_tags(trim($_POST['user_bank_number'] ?? ''));
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if ($first_name === '' || $middle_name === '' || $last_name === '' || $email === '' || $phone_raw === '' || $shipping_address === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Please complete every field: first name, middle name, last name, email, phone, shipping address, passwords, and payment proof.']);
    exit;
}

if ($err = ngc_validate_email_strict($email)) {
    http_response_code(400);
    echo json_encode(['message' => $err]);
    exit;
}

$phone = ngc_normalize_ph_mobile($phone_raw);
if ($phone === null) {
    http_response_code(400);
    echo json_encode(['message' => ngc_validate_ph_phone($phone_raw)]);
    exit;
}

if ($password === '' || strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['message' => 'Please create a password of at least 8 characters. You will use this to log in after approval.']);
    exit;
}
if ($password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['message' => 'Password and confirmation do not match.']);
    exit;
}

if ($err = ngc_validate_payment_receipt_upload('payment_receipt')) {
    http_response_code(400);
    echo json_encode(['message' => $err]);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['message' => 'Email already registered. Please login instead.']);
    exit;
}

$target_dir = 'storage/payments/';
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_extension = strtolower(pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION));
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($file_extension, $allowedExt, true)) {
    http_response_code(400);
    echo json_encode(['message' => 'Payment proof must be JPG, PNG, GIF, or WebP.']);
    exit;
}

$file_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
$target_file = $target_dir . $file_name;

if (!move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $target_file)) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to save payment receipt. Please try again.']);
    exit;
}

$base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $first_name . $last_name));
$username = $base_username ?: 'user';
$count = 1;
while (true) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if (!$stmt->fetch()) {
        break;
    }
    $username = $base_username . $count++;
}

$referrer_id = isset($_POST['referrer_id']) && $_POST['referrer_id'] !== '' ? (int) $_POST['referrer_id'] : null;
if (!$referrer_id) {
    $referrer_id = isset($_COOKIE['mclub_referrer']) ? (int) $_COOKIE['mclub_referrer'] : null;
}
if ($referrer_id <= 0) {
    $referrer_id = null;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, full_name, middle_name, email, phone, shipping_address, gcash_number, bank_name, bank_number, password, role, status, payment_receipt, referrer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'user\', \'pending\', ?, ?)'
    );
    $stmt->execute([
        $username,
        $full_name,
        $middle_name,
        $email,
        $phone,
        $shipping_address,
        $user_gcash,
        $user_bank_name,
        $user_bank_number,
        $hashed_password,
        $target_file,
        $referrer_id,
    ]);

    $user_id = $pdo->lastInsertId();

    if ($referrer_id) {
        $stmt = $pdo->prepare('INSERT INTO leads (name, email, phone, referrer_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$full_name, $email, $phone, $referrer_id]);
    }

    $mailOk = ngc_send_mail(
        $email,
        'Application received — NEXTGEN CONQUERORS',
        ngc_mail_signup_received_body($full_name)
    );
    if (!$mailOk) {
        error_log('register.php: signup confirmation mail() failed for ' . $email);
    }

    $success_message = "Your application was submitted successfully.\n\n"
        . "We sent a confirmation email to {$email}. Our team will verify your payment proof.\n"
        . "You will receive another email when your account is approved.\n\n"
        . "Keep the password you chose — you will need it to log in after approval.";

    echo json_encode([
        'status' => 'success',
        'message' => $success_message,
        'redirect' => 'registration-success.php?' . http_build_query(['email' => $email]),
    ]);
} catch (PDOException $e) {
    if (file_exists($target_file)) {
        @unlink($target_file);
    }
    error_log('register.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Registration failed. Please try again later.']);
}
