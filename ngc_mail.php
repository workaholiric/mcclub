<?php

function ngc_mail_config_load(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    if (is_readable(__DIR__ . '/mail_config.local.php')) {
        require_once __DIR__ . '/mail_config.local.php';
    }
}

function ngc_public_base_url(): string
{
    ngc_mail_config_load();
    if (defined('SITE_PUBLIC_BASE_URL') && is_string(SITE_PUBLIC_BASE_URL) && SITE_PUBLIC_BASE_URL !== '') {
        return rtrim(SITE_PUBLIC_BASE_URL, '/');
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $protocol = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return $protocol . '://' . $host;
}

function ngc_send_mail(string $to, string $subject, string $body): bool
{
    ngc_mail_config_load();
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $fromEmail = defined('RESET_MAIL_FROM') ? RESET_MAIL_FROM : ('noreply@' . preg_replace('/^www\./', '', explode(':', $host)[0]));
    $fromName = defined('RESET_MAIL_FROM_NAME') ? RESET_MAIL_FROM_NAME : 'NEXTGEN CONQUERORS';
    $fromNameEnc = function_exists('mb_encode_mimeheader')
        ? mb_encode_mimeheader($fromName, 'UTF-8', 'B', "\r\n")
        : $fromName;
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: ' . $fromNameEnc . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . PHP_VERSION,
    ];
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

function ngc_mail_signup_received_body(string $full_name): string
{
    $home = ngc_public_base_url();
    return "Hi {$full_name},\r\n\r\n"
        . "Thank you for applying to NEXTGEN CONQUERORS.\r\n\r\n"
        . "We received your details and payment proof. Your application is pending review.\r\n"
        . "Please wait for approval — we will email you again when your account is active.\r\n\r\n"
        . "Keep the password you created at checkout; you will need it to log in after approval.\r\n\r\n"
        . "— NEXTGEN CONQUERORS\r\n"
        . $home . "\r\n";
}

function ngc_mail_approval_body(string $full_name, string $username, string $loginUrl): string
{
    return "Hi {$full_name},\r\n\r\n"
        . "Congratulations! Your NEXTGEN CONQUERORS account has been approved.\r\n\r\n"
        . "Log in here:\r\n{$loginUrl}\r\n\r\n"
        . "You can sign in with your email address or this username: {$username}\r\n"
        . "Password: the one you set when you submitted your order.\r\n\r\n"
        . "Welcome to the team,\r\n"
        . "NEXTGEN CONQUERORS\r\n";
}
