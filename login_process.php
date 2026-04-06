<?php
header('Content-Type: application/json');
require_once 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Allow login with either email or username
    $login_id = filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING);
    $password = $_POST["password"];

    if (empty($login_id) || empty($password)) {
        http_response_code(400);
        echo json_encode(["message" => "Please fill all fields."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$login_id, $login_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Check account status
        if ($user['status'] === 'pending' && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode([
                "message" => "Your signup was received, but your account is not active yet. Our team is verifying your payment proof. You will be notified by email at the address you registered with once your account is approved. Until then you cannot log in.",
            ]);
            exit;
        }
        if ($user['status'] === 'rejected' && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Your account has been rejected. Please contact support."]);
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role']; 
        
        $redirect = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'dashboard.php';
        echo json_encode(["message" => "Login successful!", "redirect" => $redirect]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid email or password."]);
    }
} else {
    http_response_code(403);
    echo json_encode(["message" => "Invalid request method."]);
}
?>