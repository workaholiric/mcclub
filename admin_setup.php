<?php
/**
 * ONE-TIME admin account. See on-screen instructions when you open this file with the correct ?key=
 *
 * The word YOUR_SECRET in documentation was only an example. In the real URL you must use the SAME
 * random string you set as ADMIN_SETUP_KEY below (not the words "YOUR_SECRET").
 */
declare(strict_types=1);

const ADMIN_SETUP_KEY = 'NgC7xK9mQ2vL8pR4wZ11';

function admin_setup_html(string $title, string $bodyHtml): void
{
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(200);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'
        . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
        . '</title><style>body{font-family:system-ui,sans-serif;background:#0a0a0a;color:#e5e7eb;max-width:36rem;margin:2rem auto;padding:1.5rem;line-height:1.5}'
        . 'code{background:#1a1a1a;padding:.15rem .4rem;border-radius:4px;word-break:break-all}'
        . 'a{color:#f59e0b}</style></head><body><h1 style="font-size:1.25rem;margin-top:0">'
        . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
        . '</h1>'
        . $bodyHtml
        . '</body></html>';
    exit;
}

$lockFile = __DIR__ . '/storage/.admin_setup_complete';
if (is_readable($lockFile)) {
    admin_setup_html(
        'Admin setup already used',
        '<p>This one-time setup was already completed. Log in at <a href="login.php">login.php</a>.</p>'
            . '<p>If you need a new admin, use phpMyAdmin or ask your developer. Remove <code>admin_setup.php</code> from the server if it is still there.</p>'
    );
}

if (ADMIN_SETUP_KEY === 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_BEFORE_UPLOAD') {
    admin_setup_html(
        'Configure the secret key first',
        '<p><strong>Open this file in your code editor</strong> and replace the line that says:</p>'
            . '<p><code>CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_BEFORE_UPLOAD</code></p>'
            . '<p>with your own long random secret (letters, numbers — example: <code>NgC7xK9mQ2vL8pR4wZ1</code>).</p>'
            . '<p>Upload <code>admin_setup.php</code> to Hostinger <strong>public_html</strong> (same folder as <code>index.php</code>).</p>'
            . '<p>Then visit:</p>'
            . '<p><code>https://nextgenconquerors.com/admin_setup.php?key=</code><em>your_secret_here</em></p>'
            . '<p>Do <strong>not</strong> type the words <code>YOUR_SECRET</code> unless that is literally what you put in the file.</p>'
    );
}

$providedKey = isset($_GET['key']) ? (string) $_GET['key'] : '';
if ($providedKey !== ADMIN_SETUP_KEY) {
    admin_setup_html(
        'Wrong or missing key — or file is working',
        '<p>If you see this page, PHP found <code>admin_setup.php</code>. Good.</p>'
            . '<ul>'
            . '<li>The address must include <code>?key=</code> followed by the <strong>exact</strong> value of <code>ADMIN_SETUP_KEY</code> in the file (character for character).</li>'
            . '<li>Do not use the placeholder text <code>YOUR_SECRET</code> from the docs unless you set that as your key.</li>'
            . '<li>If your key has special characters, encode them in the URL or use only letters and numbers in the key.</li>'
            . '</ul>'
            . '<p>Example: if your key is <code>NgC7xK9mQ2vL8pR4wZ1</code>, open:</p>'
            . '<p><code>https://nextgenconquerors.com/admin_setup.php?key=NgC7xK9mQ2vL8pR4wZ1</code></p>'
            . '<p>If you get the host’s generic “page can’t be found” instead of this message, the file is <strong>not</strong> on the server — upload it to <code>public_html</code> via File Manager.</p>'
    );
}

require_once __DIR__ . '/db_config.php';

$err = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['key'] ?? '') !== ADMIN_SETUP_KEY) {
        $err = 'Invalid form key. Open the setup page again using the correct URL with ?key=...';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', trim($_POST['username'] ?? ''));
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';

        if ($full_name === '' || !$email || strlen($username) < 3 || strlen($username) > 50) {
            $err = 'Enter full name, valid email, and username (3–50 letters, numbers, or _).';
        } elseif (strlen($pass) < 10) {
            $err = 'Password must be at least 10 characters.';
        } elseif ($pass !== $pass2) {
            $err = 'Passwords do not match.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
                $stmt->execute([$email, $username]);
                if ($stmt->fetch()) {
                    $err = 'That email or username already exists. Use phpMyAdmin to set role=admin on that user, or pick another.';
                } else {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $pdo->prepare(
                        'INSERT INTO users (username, full_name, middle_name, email, phone, shipping_address, gcash_number, bank_name, bank_number, password, role, status, payment_receipt, referrer_id) VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, NULL, ?, \'admin\', \'approved\', NULL, NULL)'
                    )->execute([
                        $username,
                        $full_name,
                        'Admin',
                        $email,
                        '09000000000',
                        'Head Office',
                        $hash,
                    ]);
                    if (!is_dir(__DIR__ . '/storage')) {
                        mkdir(__DIR__ . '/storage', 0755, true);
                    }
                    file_put_contents($lockFile, date('c'));
                    $ok = 'Admin account created. Log in at login.php — then delete admin_setup.php from your hosting now.';
                }
            } catch (PDOException $e) {
                error_log('admin_setup: ' . $e->getMessage());
                $err = 'Database error. Check DB columns match schema (users table).';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin setup (one-time)</title>
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
</head>
<body class="bg-[#050505] text-gray-200 min-h-screen flex items-center justify-center p-6">
  <div class="max-w-md w-full bg-[#0f0f0f] p-8 rounded-3xl border border-amber-500/30">
    <h1 class="text-xl font-bold text-white mb-2">One-time admin setup</h1>
    <p class="text-sm text-gray-500 mb-6">Create your admin login. Delete this file after success.</p>
    <?php if ($ok): ?>
      <p class="text-green-400 text-sm mb-4"><?php echo htmlspecialchars($ok); ?></p>
      <a href="login.php" class="inline-block w-full text-center py-3 rounded-xl bg-amber-500 text-black font-bold">Go to login</a>
    <?php else: ?>
      <?php if ($err): ?><p class="text-red-400 text-sm mb-4"><?php echo htmlspecialchars($err); ?></p><?php endif; ?>
      <form method="post" action="admin_setup.php?<?php echo htmlspecialchars(http_build_query(['key' => ADMIN_SETUP_KEY]), ENT_QUOTES, 'UTF-8'); ?>" class="space-y-4">
        <input type="hidden" name="key" value="<?php echo htmlspecialchars(ADMIN_SETUP_KEY, ENT_QUOTES, 'UTF-8'); ?>">
        <div>
          <label class="text-xs text-gray-400">Full name</label>
          <input name="full_name" required class="w-full mt-1 bg-[#050505] border border-white/10 rounded-lg px-3 py-2 text-white" placeholder="Site Admin">
        </div>
        <div>
          <label class="text-xs text-gray-400">Admin email</label>
          <input type="email" name="email" required class="w-full mt-1 bg-[#050505] border border-white/10 rounded-lg px-3 py-2 text-white" placeholder="you@nextgenconquerors.com">
        </div>
        <div>
          <label class="text-xs text-gray-400">Username (login)</label>
          <input name="username" required minlength="3" pattern="[a-zA-Z0-9_]+" class="w-full mt-1 bg-[#050505] border border-white/10 rounded-lg px-3 py-2 text-white" placeholder="admin">
        </div>
        <div>
          <label class="text-xs text-gray-400">Password (10+ chars)</label>
          <input type="password" name="password" required minlength="10" class="w-full mt-1 bg-[#050505] border border-white/10 rounded-lg px-3 py-2 text-white">
        </div>
        <div>
          <label class="text-xs text-gray-400">Confirm password</label>
          <input type="password" name="password_confirm" required minlength="10" class="w-full mt-1 bg-[#050505] border border-white/10 rounded-lg px-3 py-2 text-white">
        </div>
        <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 text-black font-bold">Create admin</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
