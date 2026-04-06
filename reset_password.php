<?php
require_once 'db_config.php';

$rawToken = isset($_GET['t']) ? trim($_GET['t']) : '';
$tokenOk = false;
$errorMsg = '';

if ($rawToken !== '' && strlen($rawToken) >= 32) {
    $tokenHash = hash('sha256', $rawToken);
    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires IS NOT NULL AND password_reset_expires > NOW() LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $tokenOk = (bool) $stmt->fetch();
    } catch (PDOException $e) {
        error_log('reset_password page: ' . $e->getMessage());
        $errorMsg = 'Database error. Please try again later.';
    }
}

if ($rawToken === '') {
    $errorMsg = 'Missing reset link. Open the link from your email or request a new reset from the login page.';
} elseif (!$tokenOk && $errorMsg === '') {
    $errorMsg = 'This link is invalid or has expired. Request a new password reset from the login page.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Set new password - NEXTGEN CONQUERORS</title>
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-[#050505] text-gray-200 min-h-screen flex items-center justify-center">

  <div class="max-w-md w-full px-6">
    <div class="text-center mb-8">
      <a href="login.php">
        <img src="/newlogo.png" alt="Logo" class="h-32 w-auto mx-auto mb-6 object-contain">
      </a>
      <h2 class="text-3xl font-extrabold text-white">New password</h2>
      <?php if ($tokenOk): ?>
      <p class="text-gray-400 mt-2">Choose a strong password for your account.</p>
      <?php endif; ?>
    </div>

    <div class="bg-[#0f0f0f] p-8 rounded-3xl border border-amber-500/20 shadow-2xl">
      <?php if (!$tokenOk): ?>
      <p class="text-sm text-red-400 mb-6"><?php echo htmlspecialchars($errorMsg); ?></p>
      <a href="forgot_password.php" class="block w-full py-4 text-center font-bold rounded-full text-black bg-gradient-to-r from-amber-600 via-amber-400 to-amber-500">Request new link</a>
      <div class="mt-6 text-center">
        <a href="login.php" class="text-sm text-amber-500 hover:underline">Back to login</a>
      </div>
      <?php else: ?>
      <form id="resetForm" class="space-y-6">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($rawToken, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="space-y-2">
          <label for="password" class="text-sm font-bold text-gray-300 uppercase tracking-wider">New password</label>
          <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password"
                 class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition-all"
                 placeholder="At least 8 characters">
        </div>
        <div class="space-y-2">
          <label for="password_confirm" class="text-sm font-bold text-gray-300 uppercase tracking-wider">Confirm password</label>
          <input type="password" name="password_confirm" id="password_confirm" required minlength="8" autocomplete="new-password"
                 class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition-all">
        </div>
        <div class="pt-4">
          <button type="submit"
                  class="w-full py-4 font-bold rounded-full text-black
                         bg-gradient-to-r from-amber-600 via-amber-400 to-amber-500
                         transition-all duration-300 hover:shadow-[0_0_30px_rgba(251,191,36,0.5)] hover:scale-[1.02]">
            Save password
          </button>
        </div>
      </form>
      <div class="mt-6 text-center">
        <a href="login.php" class="text-sm text-amber-500 hover:underline">Back to login</a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($tokenOk): ?>
  <script>
    document.getElementById('resetForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const p = document.getElementById('password').value;
      const p2 = document.getElementById('password_confirm').value;
      if (p.length < 8) { alert('Password must be at least 8 characters.'); return; }
      if (p !== p2) { alert('Passwords do not match.'); return; }
      const btn = e.target.querySelector('button[type="submit"]');
      const original = btn.innerText;
      btn.innerText = 'Saving...';
      btn.disabled = true;
      try {
        const fd = new FormData(e.target);
        const res = await fetch('reset_password_process.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (res.ok) {
          alert(data.message || 'Password updated.');
          window.location.href = data.redirect || 'login.php';
        } else {
          alert(data.message || 'Could not reset password.');
        }
      } catch (err) {
        alert('Connection error. Please try again.');
      } finally {
        btn.innerText = original;
        btn.disabled = false;
      }
    });
  </script>
  <?php endif; ?>
</body>
</html>
