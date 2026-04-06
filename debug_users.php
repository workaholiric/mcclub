<?php
require_once 'db_config.php';
$emails = ['workaholiric@gmail.com', 'crismabutas0428@gmail.com', 'gmail@gmail.com'];
foreach ($emails as $email) {
    $stmt = $pdo->prepare("SELECT id, username, email, status, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    echo "Email: $email\n";
    if ($user) {
        print_r($user);
    } else {
        echo "User not found!\n";
    }
    echo "-------------------\n";
}
?>
