<?php
$pending_referrals_notify = 0;
$new_signups_since_referrals_page = 0;

if (!isset($pdo) || empty($_SESSION['user_id'])) {
    return;
}

$uid = (int) $_SESSION['user_id'];
$role_for_notify = $role ?? ($_SESSION['role'] ?? 'user');

try {
    if ($role_for_notify === 'admin') {
        $pending_referrals_notify = (int) $pdo->query(
            "SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'pending'"
        )->fetchColumn();
        $seen = isset($_SESSION['admin_signups_seen_at']) ? (int) $_SESSION['admin_signups_seen_at'] : 0;
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'pending' AND UNIX_TIMESTAMP(created_at) > ?"
        );
        $stmt->execute([$seen]);
        $new_signups_since_referrals_page = (int) $stmt->fetchColumn();
    } else {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE referrer_id = ? AND role = 'user' AND status = 'pending'"
        );
        $stmt->execute([$uid]);
        $pending_referrals_notify = (int) $stmt->fetchColumn();

        $seen = isset($_SESSION['referrals_list_seen_at']) ? (int) $_SESSION['referrals_list_seen_at'] : 0;
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE referrer_id = ? AND role = 'user' AND status = 'pending' AND UNIX_TIMESTAMP(created_at) > ?"
        );
        $stmt->execute([$uid, $seen]);
        $new_signups_since_referrals_page = (int) $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log('referral_signup_notify: ' . $e->getMessage());
}
