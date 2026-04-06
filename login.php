<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - NEXTGEN CONQUERORS</title>
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-[#050505] text-gray-200 min-h-screen flex items-center justify-center">

  <div class="max-w-md w-full px-6">
    <div class="text-center mb-8">
      <a href="index.php">
        <img src="/newlogo.png" alt="Logo" class="h-32 w-auto mx-auto mb-6 object-contain">
      </a>
      <h2 class="text-3xl font-extrabold text-white">Welcome back</h2>
      <p class="text-gray-400 mt-2">NEXTGEN CONQUERORS — member &amp; admin login</p>
      <p class="text-[11px] text-gray-600 mt-1">Use your email or username. Admins are sent to the Admin Panel after sign-in.</p>
      <p class="text-[10px] text-gray-600 mt-2">Tip: open this page on your real domain (e.g. nextgenconquerors.com/login.php).</p>
    </div>

    <div class="bg-[#0f0f0f] p-8 rounded-3xl border border-amber-500/20 shadow-2xl">
      <form id="loginForm" action="login_process.php" method="POST" class="space-y-6">
        <div class="space-y-2">
          <label for="email" class="text-sm font-bold text-gray-300 uppercase tracking-wider">Email or Username</label>
          <input type="text" name="email" id="email" required 
                 class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition-all">
        </div>
        <div class="space-y-2">
          <div class="flex justify-between items-center gap-2">
            <label for="password" class="text-sm font-bold text-gray-300 uppercase tracking-wider">Password</label>
            <a href="forgot_password.php" class="text-xs font-semibold text-amber-500 hover:underline">Forgot password?</a>
          </div>
          <input type="password" name="password" id="password" required 
                 class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition-all">
        </div>
        <div class="pt-4">
          <button type="submit" 
                  class="w-full py-4 font-bold rounded-full text-black
                         bg-gradient-to-r from-amber-600 via-amber-400 to-amber-500 
                         transition-all duration-300 hover:shadow-[0_0_30px_rgba(251,191,36,0.5)] hover:scale-[1.02]">
            Sign in
          </button>
        </div>
      </form>
      <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
          New member? <a href="process-payment.php" class="text-amber-500 hover:underline">Complete checkout</a>
        </p>
      </div>
    </div>
  </div>

  <script>
    const form = document.getElementById('loginForm');
    form.onsubmit = async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerText;
      submitBtn.innerText = 'Authenticating...';
      submitBtn.disabled = true;

      const formData = new FormData(form);
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (response.ok) {
          window.location.href = result.redirect || 'dashboard.php';
        } else {
          alert(result.message || 'Login failed.');
        }
      } catch (error) {
        alert('An error occurred. Please try again later.');
      } finally {
        submitBtn.innerText = originalText;
        submitBtn.disabled = false;
      }
    };
  </script>
</body>
</html>