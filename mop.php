<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gcash_number = strip_tags(trim($_POST["gcash_number"]));
    $bank_name = strip_tags(trim($_POST["bank_name"]));
    $bank_number = strip_tags(trim($_POST["bank_number"]));

    try {
        $stmt = $pdo->prepare("UPDATE users SET gcash_number = ?, bank_name = ?, bank_number = ? WHERE id = ?");
        $stmt->execute([$gcash_number, $bank_name, $bank_number, $user_id]);
        $success_msg = "Payment methods updated successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error updating payment methods: " . $e->getMessage();
    }
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$full_name = $user['full_name'];
$username = $user['username'];
$role = $user['role'];
$profile_pic = $user['profile_pic'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=f59e0b&color=000';

if (is_readable(__DIR__ . '/referral_signup_notify.php')) {
    require __DIR__ . '/referral_signup_notify.php';
} else {
    $pending_referrals_notify = 0;
    $new_signups_since_referrals_page = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment Methods - NEXTGEN CONQUERORS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <style>
    .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
    .sidebar-item-active { background: linear-gradient(to right, rgba(245, 158, 11, 0.1), transparent); border-left: 4px solid #f59e0b; color: #fff; }
    .glass-card { 
      background: rgba(10, 10, 10, 0.6); 
      backdrop-filter: blur(20px); 
      border: 1px solid rgba(255,255,255,0.03);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .custom-input {
      background: rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }
    .custom-input:focus {
      border-color: rgba(245, 158, 11, 0.5);
      background: rgba(0, 0, 0, 0.5);
    }
  </style>
</head>
<body class="bg-[#050505] text-gray-200 font-sans">

  <div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0a0a0a] border-r border-white/5 flex flex-col fixed h-full z-50">
      <div class="p-4 flex flex-col items-center gap-3 border-b border-white/5">
        <img src="/newlogo.png" alt="NextGen" class="w-32 h-auto object-contain">
        <span class="font-bold text-xs tracking-tight text-white uppercase leading-tight text-center">NextGen<br>Conquerors</span>
      </div>

      <div class="flex-1 py-6 overflow-y-auto">
        <div class="px-6 mb-4"><p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Main Menu</p></div>
        <nav class="space-y-1">
          <a href="dashboard.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-gauge-high text-amber-500 w-5"></i><span>Dashboard</span>
          </a>
          <?php if ($role === 'admin'): ?>
          <a href="admin_dashboard.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-user-shield text-amber-500 w-5"></i><span>Admin Panel</span>
          </a>
          <?php endif; ?>
          <a href="funnel.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-rocket text-amber-500 w-5"></i><span>Funnel</span>
          </a>
          <a href="<?php echo $role === 'admin' ? 'admin_dashboard.php' : 'referrals.php'; ?>" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-users-line text-amber-500 w-5"></i><span>Signup List</span>
            <?php if ($role !== 'admin' && $pending_referrals_notify > 0): ?>
            <span class="ml-auto min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-amber-500 text-black text-[10px] font-black"><?php echo $pending_referrals_notify > 99 ? '99+' : (int) $pending_referrals_notify; ?></span>
            <?php elseif ($role === 'admin' && $pending_referrals_notify > 0): ?>
            <span class="ml-auto min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-amber-500 text-black text-[10px] font-black"><?php echo $pending_referrals_notify > 99 ? '99+' : (int) $pending_referrals_notify; ?></span>
            <?php endif; ?>
          </a>
          <a href="mop.php" class="sidebar-item sidebar-item-active flex items-center gap-4 px-6 py-3 text-sm font-semibold">
            <i class="fa-solid fa-wallet text-amber-500 w-5"></i><span>Add MOP</span>
          </a>
          <a href="analytics.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-chart-simple text-amber-500 w-5"></i><span>Analytics</span>
          </a>
        </nav>
        <div class="px-6 mt-10 mb-4"><p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Settings</p></div>
        <nav class="space-y-1">
          <a href="profile.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-circle-user text-amber-500 w-5"></i><span>My Profile</span>
          </a>
          <a href="logout.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-right-from-bracket text-red-500 w-5"></i><span>Logout</span>
          </a>
        </nav>
      </div>

      <div class="p-6 border-t border-white/5 bg-[#080808]">
        <div class="flex items-center gap-3">
          <img src="<?php echo htmlspecialchars($profile_pic); ?>" class="w-8 h-8 rounded-full object-cover">
          <div class="overflow-hidden">
            <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($full_name); ?></p>
            <p class="text-[10px] text-gray-500 truncate uppercase"><?php echo htmlspecialchars($role); ?></p>
          </div>
        </div>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8 md:p-12 lg:p-16">
      <header class="mb-12">
        <h1 class="text-4xl font-black text-white tracking-tight">Payment Methods</h1>
        <p class="text-gray-500 text-sm mt-2 max-w-md">Manage where you receive your team commissions.</p>
      </header>

      <?php if ($success_msg): ?>
        <div class="mb-8 p-4 bg-green-500/10 border border-green-500/20 text-green-500 rounded-2xl text-sm font-bold flex items-center gap-3">
          <i class="fa-solid fa-circle-check"></i><?php echo $success_msg; ?>
        </div>
      <?php endif; ?>

      <?php if ($error_msg): ?>
        <div class="mb-8 p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl text-sm font-bold flex items-center gap-3">
          <i class="fa-solid fa-circle-xmark"></i><?php echo $error_msg; ?>
        </div>
      <?php endif; ?>

      <form action="mop.php" method="POST" class="max-w-3xl">
        <div class="glass-card p-8 md:p-10 rounded-[48px] relative overflow-hidden">
          <div class="grid grid-cols-1 gap-8">
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">GCASH Number</label>
              <div class="relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-600 text-xs font-bold">PH</span>
                <input type="text" name="gcash_number" value="<?php echo htmlspecialchars($user['gcash_number'] ?? ''); ?>" placeholder="09XX XXX XXXX"
                       class="custom-input w-full rounded-2xl pl-12 pr-6 py-4 text-sm text-white focus:outline-none">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Bank Name</label>
                <input type="text" name="bank_name" value="<?php echo htmlspecialchars($user['bank_name'] ?? ''); ?>" placeholder="e.g. BDO, BPI, Unionbank"
                       class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none">
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Account Number</label>
                <input type="text" name="bank_number" value="<?php echo htmlspecialchars($user['bank_number'] ?? ''); ?>" placeholder="XXXX-XXXX-XX"
                       class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none">
              </div>
            </div>
          </div>

          <div class="mt-10 pt-10 border-t border-white/5">
            <button type="submit" 
                    class="w-full md:w-auto px-10 py-4 bg-amber-500 text-black text-xs font-black rounded-full uppercase tracking-widest hover:bg-amber-400 transition-all">
              Save Payment Methods
            </button>
          </div>
        </div>
      </form>
    </main>
  </div>

</body>
</html>
