<?php
header('Content-Type: application/json');
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"] ?? ''));
    $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST["phone"] ?? ''));

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please enter a valid name and email."]);
        exit;
    }

    $referrer_id = isset($_COOKIE['mclub_referrer']) ? (int)$_COOKIE['mclub_referrer'] : null;
    if ($referrer_id <= 0) {
        $referrer_id = null;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, referrer_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $referrer_id]);
    } catch (PDOException $e) {
        error_log("Lead capture failed: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Could not save your details. Please try again later."]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "message" => "Lead captured! Redirecting to payment...",
        "redirect" => "/process-payment.php"
    ]);
    exit;
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>