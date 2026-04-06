<?php
$registered_email = '';
if (!empty($_GET['email'])) {
    $e = filter_var(trim($_GET['email']), FILTER_VALIDATE_EMAIL);
    if ($e) {
        $registered_email = $e;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Application received - NEXTGEN CONQUERORS</title>
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      background-color: #050505;
      color: #e5e7eb;
      font-family: 'Inter', sans-serif;
    }
    .success-card {
      background: #0a0a0a;
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 2rem;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .status-badge {
      background: rgba(245, 158, 11, 0.1);
      border: 1px solid rgba(245, 158, 11, 0.2);
      color: #f59e0b;
      font-size: 0.65rem;
      font-weight: 800;
      letter-spacing: 0.1em;
      padding: 0.5rem 1.5rem;
      border-radius: 9999px;
      text-transform: uppercase;
    }
    .icon-circle {
      width: 80px;
      height: 80px;
      background: #f59e0b;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      box-shadow: 0 0 30px rgba(245, 158, 11, 0.3);
    }
    .info-box {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.05);
      padding: 1.25rem;
      border-radius: 1rem;
      color: #d1d5db;
      font-size: 0.875rem;
      line-height: 1.6;
      text-align: left;
    }
    .email-highlight {
      color: #f59e0b;
      font-weight: 700;
      word-break: break-all;
    }
    .btn-return {
      background: linear-gradient(to right, #d97706, #f59e0b);
      color: #000;
      font-weight: 800;
      padding: 1.25rem;
      border-radius: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      width: 100%;
    }
    .btn-return:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

  <div class="max-w-lg w-full success-card p-10 md:p-12 text-center">
    <div class="mb-8">
      <div class="icon-circle">
        <i class="fa-solid fa-circle-check text-black text-3xl"></i>
      </div>
    </div>

    <div class="mb-6">
      <span class="status-badge">Submitted — pending payment review</span>
    </div>

    <h1 class="text-3xl md:text-4xl font-black text-white mb-6 tracking-tight">Application received</h1>

    <div class="info-box mb-8 space-y-4">
      <p>Thank you. Your details and payment proof were submitted <strong class="text-white">successfully</strong>.</p>
      <p>Our team will <strong class="text-white">verify your payment</strong>. We sent a <strong class="text-white">confirmation email</strong><?php if ($registered_email): ?> to <span class="email-highlight"><?php echo htmlspecialchars($registered_email, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>. When you are approved, you will receive a <strong class="text-white">second email</strong> with login details.</p>
      <p class="text-sm text-gray-400">Until approval, you <strong class="text-amber-500/90">cannot log in</strong> yet. Keep the password you created at checkout.</p>
    </div>

    <div class="mb-8">
      <a href="login.php" class="btn-return">
        <i class="fa-solid fa-arrow-left"></i>
        Return to login
      </a>
    </div>

    <p class="text-[10px] text-gray-600 font-bold uppercase tracking-[0.2em]">
      Typical review time: 24–48 hours
    </p>
  </div>

</body>
</html>
