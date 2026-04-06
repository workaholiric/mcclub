<?php
session_start();
require_once 'db_config.php';

$username = isset($_GET['user']) ? $_GET['user'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 'landing';

// LOGGING for debugging (remove after fix)
// error_log("Referral Router: username=$username, page=$page");

// Verify the username exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    // Set referral cookie for 30 days
    setcookie('mclub_referrer', $user['id'], time() + (86400 * 30), "/");

    if ($page === 'business-plan') {
        // Use an internal include to keep the clean URL (.../admin/business-plan)
        $_GET['user'] = $username;
        $business_plan_file = __DIR__ . '/business-plan.php';
        if (file_exists($business_plan_file)) {
            include $business_plan_file;
            exit;
        } else {
            // Fallback redirect if file not found
            header("Location: /business-plan.php?user=" . urlencode($username));
            exit;
        }
    } else {
        // Redirect to the main landing page
        header("Location: /index.php?ref=" . urlencode($username));
        exit;
    }
} else {
    // If username is actually a file that exists but wasn't caught by htaccess
    if (file_exists(__DIR__ . '/' . $username . '.php')) {
        include __DIR__ . '/' . $username . '.php';
        exit;
    }
    // Default fallback
    header("Location: /index.php");
    exit;
}
?>