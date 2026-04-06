<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE referrer_id = ?");
$stmt->execute([$user_id]);
$total_leads = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referrer_id = ?");
$stmt->execute([$user_id]);
$total_conversions = $stmt->fetchColumn() ?: 0;

$pending_leads = $total_leads - $total_conversions;
$conversion_rate = $total_leads > 0 ? round(($total_conversions / $total_leads) * 100, 1) : 0;

// Fetch Leads List with Conversion Status
$stmt = $pdo->prepare("
    SELECT l.*, 
           CASE WHEN u.id IS NOT NULL THEN 'Converted' ELSE 'Pending' END as lead_status,
           u.status as user_approval_status
    FROM leads l
    LEFT JOIN users u ON l.email = u.email
    WHERE l.referrer_id = ?
    ORDER BY l.created_at DESC
");
$stmt->execute([$user_id]);
$leads_list = $stmt->fetchAll();

// Fetch current user data for sidebar
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
  <title>Analytics - NEXTGEN CONQUERORS</title>
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
          <a href="mop.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-wallet text-amber-500 w-5"></i><span>Add MOP</span>
          </a>
          <a href="analytics.php" class="sidebar-item sidebar-item-active flex items-center gap-4 px-6 py-3 text-sm font-semibold">
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
        <h1 class="text-4xl font-black text-white tracking-tight">Team Analytics</h1>
        <p class="text-gray-500 text-sm mt-2 max-w-md">Track your team growth and lead conversion performance.</p>
      </header>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="glass-card p-6 rounded-3xl text-center">
          <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-2">Total Leads</p>
          <h3 class="text-4xl font-black text-white"><?php echo $total_leads; ?></h3>
        </div>
        <div class="glass-card p-6 rounded-3xl text-center border-amber-500/20">
          <p class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em] mb-2">Conversions</p>
          <h3 class="text-4xl font-black text-white"><?php echo $total_conversions; ?></h3>
        </div>
        <div class="glass-card p-6 rounded-3xl text-center">
          <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-2">Conversion Rate</p>
          <h3 class="text-4xl font-black text-green-500"><?php echo $conversion_rate; ?>%</h3>
        </div>
        <div class="glass-card p-6 rounded-3xl text-center">
          <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-2">Pending Leads</p>
          <h3 class="text-4xl font-black text-white"><?php echo $pending_leads; ?></h3>
        </div>
      </div>

      <!-- Leads Table -->
      <div class="glass-card rounded-[48px] overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-white/[0.02]">
          <h3 class="text-xl font-black text-white uppercase tracking-tighter">Recent Leads Activity</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-black/20 text-[10px] font-black text-gray-500 uppercase tracking-widest border-b border-white/5">
                <th class="px-8 py-5">Lead Details</th>
                <th class="px-8 py-5 text-center">Status</th>
                <th class="px-8 py-5 text-center">Approval</th>
                <th class="px-8 py-5 text-right">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <?php if (empty($leads_list)): ?>
                <tr><td colspan="4" class="p-20 text-center text-gray-600 italic">No leads activity found yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($leads_list as $lead): ?>
                <tr class="hover:bg-white/[0.01] transition-colors">
                  <td class="px-8 py-6">
                    <p class="font-bold text-white"><?php echo htmlspecialchars($lead['name']); ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($lead['email']); ?></p>
                    <?php if ($lead['phone']): ?>
                      <p class="text-[10px] text-amber-500/50 font-bold mt-1"><?php echo htmlspecialchars($lead['phone']); ?></p>
                    <?php endif; ?>
                  </td>
                  <td class="px-8 py-6 text-center">
                    <?php if ($lead['lead_status'] === 'Converted'): ?>
                      <span class="px-3 py-1 bg-green-500/10 text-green-500 text-[9px] font-black rounded-full uppercase tracking-widest">Converted</span>
                    <?php else: ?>
                      <span class="px-3 py-1 bg-amber-500/10 text-amber-500 text-[9px] font-black rounded-full uppercase tracking-widest">Pending</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-8 py-6 text-center">
                    <?php if ($lead['lead_status'] === 'Converted'): ?>
                      <?php 
                        $approval = $lead['user_approval_status'];
                        $color = $approval === 'approved' ? 'green' : ($approval === 'rejected' ? 'red' : 'amber');
                      ?>
                      <span class="px-3 py-1 bg-<?php echo $color; ?>-500/10 text-<?php echo $color; ?>-500 text-[9px] font-black rounded-full uppercase tracking-widest"><?php echo $approval; ?></span>
                    <?php else: ?>
                      <span class="text-gray-700 text-[9px] font-black uppercase tracking-widest">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-8 py-6 text-right">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest"><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></p>
                    <p class="text-[9px] text-gray-700 mt-1 uppercase"><?php echo date('h:i A', strtotime($lead['created_at'])); ?></p>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

</body>
</html>
