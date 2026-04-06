<?php
/**
 * Hostinger + nextgenconquerors.com
 *
 * 1) hPanel → Email → create a mailbox (e.g. noreply@ or use an existing one like info@).
 *    RESET_MAIL_FROM must be an address Hostinger allows for sending (usually on your domain).
 * 2) Point the domain to this hosting and enable SSL so https works.
 * 3) Upload this file next to forgot_password_process.php on the server.
 */
define('RESET_MAIL_FROM', 'noreply@nextgenconquerors.com');
define('RESET_MAIL_FROM_NAME', 'NEXTGEN CONQUERORS');

/** Password reset links always use this URL (avoids old subdomain links in emails). */
define('SITE_PUBLIC_BASE_URL', 'https://nextgenconquerors.com');
