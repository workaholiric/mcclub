<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db_config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$full_name = $user['full_name'];
$username = $user['username'];
$role = $user['role'];

if (is_readable(__DIR__ . '/referral_signup_notify.php')) {
    require __DIR__ . '/referral_signup_notify.php';
} else {
    $pending_referrals_notify = 0;
    $new_signups_since_referrals_page = 0;
}

// Detect domain for links
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$domain";

// Fetch Stats for current user
$user_id = $_SESSION['user_id'];

if ($role === 'admin') {
    // Global stats for Admin
    $stmt_conv = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $conversions_count = $stmt_conv->fetchColumn() ?: 0;

    $stmt_leads = $pdo->query("SELECT COUNT(*) FROM leads WHERE email NOT IN (SELECT email FROM users)");
    $pending_leads_count = $stmt_leads->fetchColumn() ?: 0;
} else {
    // Personal stats for User
    $stmt_conv = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referrer_id = ?");
    $stmt_conv->execute([$user_id]);
    $conversions_count = $stmt_conv->fetchColumn() ?: 0;

    $stmt_leads = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE referrer_id = ? AND email NOT IN (SELECT email FROM users)");
    $stmt_leads->execute([$user_id]);
    $pending_leads_count = (int) $stmt_leads->fetchColumn();
}

// 3. Total Leads (Everyone: Pending Leads + Converted Members)
$total_leads_count = $pending_leads_count + $conversions_count;

// 4. Calculate Conversion Rate
$conversion_rate = 0;
if ($total_leads_count > 0) {
    $conversion_rate = round(($conversions_count / $total_leads_count) * 100);
}

// 5. System Status (Check DB and storage accessibility)
$system_status = "ACTIVE";
$is_storage_writable = is_writable('storage/payments') ? true : false;
if (!$is_storage_writable) {
    $system_status = "MAINTENANCE";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - NEXTGEN CONQUERORS</title>
  <!-- Main CSS (Tailwind) -->
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <style>
    .sidebar-item-active {
      background: linear-gradient(to right, rgba(245, 158, 11, 0.1), transparent);
      border-left: 4px solid #f59e0b;
      color: #fff;
    }
    .sidebar-item {
      transition: all 0.3s ease;
    }
    .sidebar-item:hover {
      background: rgba(255, 255, 255, 0.05);
      color: #fff;
    }
  </style>
</head>
<body class="bg-[#050505] text-gray-200 font-sans">

  <div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0a0a0a] border-r border-white/5 flex flex-col fixed h-full z-50">
      <!-- Logo Section -->
      <div class="p-4 flex flex-col items-center gap-3 border-b border-white/5">
        <img src="/newlogo.png" alt="NextGen" class="w-32 h-auto object-contain">
        <span class="font-bold text-xs tracking-tight text-white uppercase leading-tight text-center">NextGen<br>Conquerors</span>
      </div>

      <!-- Navigation -->
      <div class="flex-1 py-6 overflow-y-auto">
        <div class="px-6 mb-4">
          <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Main Menu</p>
        </div>
        
        <nav class="space-y-1">
          <a href="dashboard.php" class="sidebar-item sidebar-item-active flex items-center gap-4 px-6 py-3 text-sm font-semibold">
            <i class="fa-solid fa-gauge-high text-amber-500 w-5"></i>
            <span>Dashboard</span>
          </a>
          <?php if ($role === 'admin'): ?>
          <a href="admin_dashboard.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-user-shield text-amber-500 w-5"></i>
            <span>Admin Panel</span>
          </a>
          <?php endif; ?>
          <a href="funnel.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-rocket text-amber-500 w-5"></i>
            <span>Funnel</span>
          </a>
          <a href="referrals.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-users-line text-amber-500 w-5"></i>
            <span>Signup List</span>
            <?php if ($role !== 'admin' && $pending_referrals_notify > 0): ?>
            <span class="ml-auto min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-amber-500 text-black text-[10px] font-black"><?php echo $pending_referrals_notify > 99 ? '99+' : (int) $pending_referrals_notify; ?></span>
            <?php elseif ($role === 'admin' && $pending_referrals_notify > 0): ?>
            <span class="ml-auto min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-amber-500 text-black text-[10px] font-black"><?php echo $pending_referrals_notify > 99 ? '99+' : (int) $pending_referrals_notify; ?></span>
            <?php endif; ?>
          </a>
          <a href="mop.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-wallet text-amber-500 w-5"></i>
            <span>Add MOP</span>
          </a>
          <a href="analytics.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-chart-simple text-amber-500 w-5"></i>
            <span>Analytics</span>
          </a>
        </nav>

        <div class="px-6 mt-10 mb-4">
          <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Settings</p>
        </div>
        
        <nav class="space-y-1">
          <a href="profile.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-circle-user text-amber-500 w-5"></i>
            <span>My Profile</span>
          </a>
          <a href="logout.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-right-from-bracket text-red-500 w-5"></i>
            <span>Logout</span>
          </a>
        </nav>
      </div>

      <!-- User Info Bottom -->
      <a href="profile.php" class="p-6 border-t border-white/5 bg-[#080808] block hover:bg-white/[0.03] transition-all">
        <div class="flex items-center gap-3">
          <?php 
            $user_profile_pic = $user['profile_pic'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=f59e0b&color=000';
          ?>
          <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" class="w-8 h-8 rounded-full object-cover">
          <div class="overflow-hidden">
            <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($full_name); ?></p>
            <p class="text-[10px] text-gray-500 truncate uppercase"><?php echo htmlspecialchars($role); ?></p>
          </div>
        </div>
      </a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8 md:p-12">
      <header class="flex justify-between items-center mb-10">
        <div>
          <h1 class="text-3xl font-extrabold text-white">Dashboard Overview</h1>
          <p class="text-gray-500 text-sm mt-1">Welcome back, <?php echo htmlspecialchars($full_name); ?>!</p>
        </div>
        <div class="flex items-center gap-4">
          <button class="bg-[#0a0a0a] border border-white/5 px-4 py-2 rounded-xl text-xs font-bold text-gray-400 hover:text-white transition-all">
            <i class="fa-regular fa-calendar mr-2"></i> <?php echo date('M d, Y'); ?>
          </button>
        </div>
      </header>

      <?php if ($role === 'admin' && $pending_referrals_notify > 0): ?>
      <div class="mb-8 p-5 rounded-2xl border border-amber-500/30 bg-amber-500/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black"><i class="fa-solid fa-bell"></i></span>
          <div>
            <p class="text-sm font-black text-amber-400 uppercase tracking-wider">Action needed</p>
            <p class="text-sm text-gray-200 mt-1">
              <strong class="text-white"><?php echo (int) $pending_referrals_notify; ?></strong> member signup(s) are pending payment verification.
              <?php if ($new_signups_since_referrals_page > 0): ?>
                <span class="text-amber-300 font-semibold"><?php echo (int) $new_signups_since_referrals_page; ?> new</span> since your last Admin Panel visit.
              <?php endif; ?>
            </p>
          </div>
        </div>
        <a href="admin_dashboard.php" class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-amber-500 text-black text-xs font-black uppercase tracking-wider hover:bg-amber-400 transition-colors">Open admin panel</a>
      </div>
      <?php elseif ($role !== 'admin' && $pending_referrals_notify > 0): ?>
      <div class="mb-8 p-5 rounded-2xl border border-amber-500/30 bg-amber-500/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black"><i class="fa-solid fa-bell"></i></span>
          <div>
            <p class="text-sm font-black text-amber-400 uppercase tracking-wider">New activity on your link</p>
            <p class="text-sm text-gray-200 mt-1">
              <strong class="text-white"><?php echo (int) $pending_referrals_notify; ?></strong> signup(s) from your referral link need payment review.
              <?php if ($new_signups_since_referrals_page > 0): ?>
                <span class="text-amber-300 font-semibold"><?php echo (int) $new_signups_since_referrals_page; ?> new</span> since you last opened Signup List.
              <?php endif; ?>
            </p>
          </div>
        </div>
        <a href="referrals.php" class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-amber-500 text-black text-xs font-black uppercase tracking-wider hover:bg-amber-400 transition-colors">Review signup list</a>
      </div>
      <?php endif; ?>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
          <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fa-solid fa-users text-6xl text-amber-500"></i>
          </div>
          <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Leads</p>
          <h3 class="text-4xl font-black text-white"><?php echo $total_leads_count; ?></h3>
          <p class="text-green-500 text-[10px] font-bold mt-2"><i class="fa-solid fa-arrow-up mr-1"></i> Current status</p>
        </div>
        
        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
          <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fa-solid fa-chart-line text-6xl text-amber-500"></i>
          </div>
          <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Conversions</p>
          <h3 class="text-4xl font-black text-white"><?php echo $conversion_rate; ?>%</h3>
          <p class="text-amber-500 text-[10px] font-bold mt-2"><i class="fa-solid fa-minus mr-1"></i> Growth Rate</p>
        </div>

        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
          <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fa-solid fa-bolt text-6xl text-amber-500"></i>
          </div>
          <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">System Status</p>
          <h3 class="text-4xl font-black text-green-500 uppercase"><?php echo $system_status; ?></h3>
          <p class="text-gray-500 text-[10px] font-bold mt-2">All funnels operational</p>
        </div>
      </div>

      <!-- Links Card -->
      <div class="bg-[#0a0a0a] rounded-3xl border border-white/5 overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-[#0d0d0d]">
          <h2 class="text-xl font-bold text-white">Your Funnel Links</h2>
          <button class="px-4 py-2 bg-amber-500 text-black text-[10px] font-black rounded-full hover:bg-amber-400 transition-colors uppercase tracking-widest">Copy All</button>
        </div>
        <div class="p-8 space-y-6">
          <div class="group">
            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Your Referral Link</p>
            <div class="flex items-center justify-between bg-[#050505] p-4 rounded-2xl border border-white/5 group-hover:border-amber-500/30 transition-all">
              <p class="text-sm text-gray-300 font-medium"><?php echo "$base_url/$username"; ?></p>
              <button onclick="copyToClipboard('<?php echo "$base_url/$username"; ?>')" class="text-gray-500 hover:text-amber-500 transition-colors"><i class="fa-regular fa-copy"></i></button>
            </div>
          </div>
          
          <div class="group">
            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Business Plan Link</p>
            <div class="flex items-center justify-between bg-[#050505] p-4 rounded-2xl border border-white/5 group-hover:border-amber-500/30 transition-all">
              <p class="text-sm text-gray-300 font-medium"><?php echo "$base_url/$username/business-plan"; ?></p>
              <button onclick="copyToClipboard('<?php echo "$base_url/$username/business-plan"; ?>')" class="text-gray-500 hover:text-amber-500 transition-colors"><i class="fa-regular fa-copy"></i></button>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>

  <script>
    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        alert('Link copied to clipboard!');
      });
    }
  </script>

</body>
</html>