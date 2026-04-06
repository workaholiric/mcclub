<?php
session_start();
require_once 'db_config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

if (is_readable(__DIR__ . '/referral_signup_notify.php')) {
    require __DIR__ . '/referral_signup_notify.php';
} else {
    $pending_referrals_notify = 0;
    $new_signups_since_referrals_page = 0;
}

require_once __DIR__ . '/ngc_site_settings.php';
$bp_vid_compliance_raw = ngc_site_setting_get($pdo, 'business_plan_video_compliance');
$bp_vid_passion_raw = ngc_site_setting_get($pdo, 'business_plan_video_passion');
$bp_vid_earn_raw = ngc_site_setting_get($pdo, 'business_plan_video_earn');

// Detect domain for links
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$domain";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Funnel - NEXTGEN CONQUERORS</title>
  <!-- Main CSS (Tailwind) -->
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <style>
    .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
    .sidebar-item-active { background: linear-gradient(to right, rgba(245, 158, 11, 0.1), transparent); border-left: 4px solid #f59e0b; color: #fff; }
  </style>
</head>
<body class="bg-[#050505] text-gray-200 font-sans">

  <div class="flex min-h-screen">
    <!-- SIDEBAR (Same as Dashboard) -->
    <aside class="w-64 bg-[#0a0a0a] border-r border-white/5 flex flex-col fixed h-full z-50">
      <div class="p-4 flex flex-col items-center gap-3 border-b border-white/5">
        <img src="/newlogo.png" alt="NextGen" class="w-32 h-auto object-contain">
        <span class="font-bold text-xs tracking-tight text-white uppercase leading-tight text-center">NextGen<br>Conquerors</span>
      </div>

      <div class="flex-1 py-6 overflow-y-auto">
        <div class="px-6 mb-4">
          <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Main Menu</p>
        </div>
        
        <nav class="space-y-1">
          <a href="dashboard.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-gauge-high text-amber-500 w-5"></i>
            <span>Dashboard</span>
          </a>
          <?php if ($role === 'admin'): ?>
          <a href="admin_dashboard.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-user-shield text-amber-500 w-5"></i>
            <span>Admin Panel</span>
          </a>
          <?php endif; ?>
          <a href="funnel.php" class="sidebar-item sidebar-item-active flex items-center gap-4 px-6 py-3 text-sm font-semibold">
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
            $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $u_data = $stmt->fetch();
            $user_profile_pic = $u_data['profile_pic'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=f59e0b&color=000';
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
      <header class="mb-10">
        <h1 class="text-3xl font-extrabold text-white">Marketing Funnel</h1>
        <p class="text-gray-500 text-sm mt-1">Share these links to grow your team.</p>
        <?php if (!empty($_GET['videos_saved'])): ?>
          <p class="text-sm text-green-500 mt-3 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i> Business plan videos updated. All members’ business-plan links now use the new embeds.</p>
        <?php endif; ?>
      </header>

      <div class="max-w-4xl space-y-8">
        <!-- Main Landing Page Link (Clean) -->
        <div class="bg-[#0a0a0a] p-8 rounded-3xl border border-white/5 shadow-2xl">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
              <i class="fa-solid fa-link text-xl"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-white">Your Clean Referral Link</h3>
              <p class="text-xs text-gray-500 mt-1">Sends prospects to the NEXTGEN CONQUERORS main page.</p>
            </div>
          </div>
          
          <div class="flex items-center gap-4">
            <div class="flex-1 bg-[#050505] p-4 rounded-2xl border border-white/5 font-mono text-sm text-amber-400">
              <?php echo "$base_url/$username"; ?>
            </div>
            <button onclick="copyToClipboard('<?php echo "$base_url/$username"; ?>')" 
                    class="px-6 py-4 bg-amber-500 text-black font-bold rounded-2xl hover:bg-amber-400 transition-all">
              Copy Link
            </button>
          </div>
        </div>

        <!-- Business Plan Link (Clean) -->
        <div class="bg-[#0a0a0a] p-8 rounded-3xl border border-white/5 shadow-2xl">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
              <i class="fa-solid fa-briefcase text-xl"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-white">Business Plan Link</h3>
              <p class="text-xs text-gray-500 mt-1">Sends prospects directly to your personalized business plan.</p>
            </div>
          </div>
          
          <div class="flex items-center gap-4">
            <div class="flex-1 bg-[#050505] p-4 rounded-2xl border border-white/5 font-mono text-sm text-amber-400">
              <?php echo "$base_url/$username/business-plan"; ?>
            </div>
            <button onclick="copyToClipboard('<?php echo "$base_url/$username/business-plan"; ?>')" 
                    class="px-6 py-4 bg-amber-500 text-black font-bold rounded-2xl hover:bg-amber-400 transition-all">
              Copy Link
            </button>
          </div>
        </div>

        <?php if ($role === 'admin'): ?>
        <div class="bg-[#0a0a0a] p-8 rounded-3xl border border-amber-500/30 shadow-2xl">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-amber-500/20 rounded-2xl flex items-center justify-center text-amber-500">
              <i class="fa-brands fa-youtube text-xl"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-white">Business plan page videos</h3>
              <p class="text-xs text-gray-500 mt-1">Applies to every member’s <code class="text-amber-500/80">/username/business-plan</code> page. Paste a YouTube link or 11-character video ID.</p>
            </div>
          </div>
          <form action="save_business_plan_videos.php" method="post" class="space-y-5">
            <div>
              <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Video above “Legalities &amp; FDA Compliance”</label>
              <input type="text" name="video_compliance" value="<?php echo htmlspecialchars($bp_vid_compliance_raw); ?>"
                     class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-amber-500/50"
                     placeholder="https://www.youtube.com/watch?v=VIDEO_ID">
            </div>
            <div>
              <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Video below “Where Passion Meets Opportunity Worldwide”</label>
              <input type="text" name="video_passion" value="<?php echo htmlspecialchars($bp_vid_passion_raw); ?>"
                     class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-amber-500/50"
                     placeholder="https://youtu.be/...">
            </div>
            <div>
              <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Video in “Earn Through a Simple System” (left column)</label>
              <input type="text" name="video_earn" value="<?php echo htmlspecialchars($bp_vid_earn_raw); ?>"
                     class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-amber-500/50"
                     placeholder="https://www.youtube.com/watch?v=VIDEO_ID">
            </div>
            <p class="text-[11px] text-gray-600">Leave blank and save to remove that embed. Uses youtube-nocookie embed (responsive 16:9).</p>
            <button type="submit" class="px-6 py-3 bg-amber-500 text-black font-black rounded-xl text-sm uppercase tracking-wider hover:bg-amber-400 transition-colors">
              Save videos
            </button>
          </form>
        </div>
        <?php endif; ?>
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