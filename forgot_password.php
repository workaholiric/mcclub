<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot password - NEXTGEN CONQUERORS</title>
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-[#050505] text-gray-200 min-h-screen flex items-center justify-center">

  <div class="max-w-md w-full px-6">
    <div class="text-center mb-8">
      <a href="login.php">
        <img src="/newlogo.png" alt="Logo" class="h-32 w-auto mx-auto mb-6 object-contain">
      </a>
      <h2 class="text-3xl font-extrabold text-white">Forgot password</h2>
      <p class="text-gray-400 mt-2">Enter the email you used to register. We will send a reset link if your account is approved.</p>
    </div>

    <div class="bg-[#0f0f0f] p-8 rounded-3xl border border-amber-500/20 shadow-2xl">
      <form id="forgotForm" class="space-y-6">
        <div class="space-y-2">
          <label for="email" class="text-sm font-bold text-gray-300 uppercase tracking-wider">Email</label>
          <input type="email" name="email" id="email" required autocomplete="email"
                 class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition-all"
                 placeholder="you@example.com">
        </div>
        <div class="pt-4">
          <button type="submit"
                  class="w-full py-4 font-bold rounded-full text-black
                         bg-gradient-to-r from-amber-600 via-amber-400 to-amber-500
                         transition-all duration-300 hover:shadow-[0_0_30px_rgba(251,191,36,0.5)] hover:scale-[1.02]">
            Send reset link
          </button>
        </div>
      </form>
      <div class="mt-6 text-center">
        <a href="login.php" class="text-sm text-amber-500 hover:underline">Back to login</a>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('forgotForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = e.target.querySelector('button[type="submit"]');
      const original = btn.innerText;
      btn.innerText = 'Sending...';
      btn.disabled = true;
      try {
        const fd = new FormData(e.target);
        const res = await fetch('forgot_password_process.php', { method: 'POST', body: fd });
        const data = await res.json();
        alert(data.message || 'Request processed.');
      } catch (err) {
        alert('Connection error. Please try again.');
      } finally {
        btn.innerText = original;
        btn.disabled = false;
      }
    });
  </script>
</body>
</html>
