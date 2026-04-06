<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db_config.php';

// Security Check: Must be logged in as Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle AJAX Approval/Rejection Requests
if (isset($_POST['action']) && isset($_POST['id'])) {
    // Clear any previous output buffers to ensure only JSON is sent
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'approve') {
            $upd = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role = 'user' AND status = 'pending'");
            $upd->execute([$id]);
            if ($upd->rowCount() < 1) {
                echo json_encode(["success" => false, "message" => "User is not pending or already processed."]);
                exit;
            }
            $stmt = $pdo->prepare("SELECT email, username, full_name FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $approvedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($approvedUser) {
                require_once __DIR__ . '/ngc_mail.php';
                $loginUrl = ngc_public_base_url() . '/login.php';
                $body = ngc_mail_approval_body($approvedUser['full_name'], $approvedUser['username'], $loginUrl);
                if (!ngc_send_mail($approvedUser['email'], 'Your account is approved — NEXTGEN CONQUERORS', $body)) {
                    error_log('admin approve: mail failed for user id ' . $id);
                }
            }

            $pending = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending' AND role = 'user'")->fetchColumn();
            $approved = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'approved' AND role = 'user'")->fetchColumn();

            echo json_encode([
                "success" => true,
                "message" => "User approved successfully!",
                "stats" => [
                    "pending" => (int)$pending,
                    "approved" => (int)$approved
                ]
            ]);
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role = 'user' AND status = 'pending'");
            $stmt->execute([$id]);
            if ($stmt->rowCount() < 1) {
                echo json_encode(["success" => false, "message" => "User is not pending or already processed."]);
                exit;
            }
            
            // Recalculate stats for response
            $pending = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending' AND role = 'user'")->fetchColumn();
            $rejected = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'rejected' AND role = 'user'")->fetchColumn();
            
            echo json_encode([
                "success" => true, 
                "message" => "User rejected.",
                "stats" => [
                    "pending" => (int)$pending,
                    "rejected" => (int)$rejected
                ]
            ]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid action."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// Fetch Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0;
$total_pending = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending' AND role = 'user'")->fetchColumn() ?: 0;
$total_approved = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'approved' AND role = 'user'")->fetchColumn() ?: 0;
$total_rejected = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'rejected' AND role = 'user'")->fetchColumn() ?: 0;
$total_leads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn() ?: 0;

// Fetch all members with referrer (who signed up on whose link)
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$stmt = $pdo->prepare(
    "SELECT u.*,
            ref.id AS referrer_user_id,
            ref.full_name AS referrer_full_name,
            ref.username AS referrer_username,
            ref.email AS referrer_email
     FROM users u
     LEFT JOIN users ref ON u.referrer_id = ref.id
     WHERE u.role = 'user'
       AND (
         u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?
         OR ref.full_name LIKE ? OR ref.username LIKE ? OR ref.email LIKE ?
       )
     ORDER BY u.created_at DESC"
);
$stmt->execute([$search, $search, $search, $search, $search, $search]);
$all_users = $stmt->fetchAll();

// Fetch All Leads with Referrer Info
$stmt = $pdo->prepare("SELECT l.*, u.full_name as referrer_name FROM leads l LEFT JOIN users u ON l.referrer_id = u.id WHERE l.name LIKE ? OR l.email LIKE ? ORDER BY l.created_at DESC");
$stmt->execute([$search, $search]);
$all_leads = $stmt->fetchAll();

// Fetch current user data for sidebar
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

$role = $current_user['role'];
if (is_readable(__DIR__ . '/referral_signup_notify.php')) {
    require __DIR__ . '/referral_signup_notify.php';
} else {
    $pending_referrals_notify = 0;
    $new_signups_since_referrals_page = 0;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['admin_signups_seen_at'] = time();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Control - NEXTGEN CONQUERORS</title>
  <!-- Main CSS (Tailwind) -->
  <link rel="stylesheet" href="build/assets/app-BmI9JwnE.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <style>
    .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
    .sidebar-item-active { background: linear-gradient(to right, rgba(245, 158, 11, 0.1), transparent); border-left: 4px solid #f59e0b; color: #fff; }
    .action-btn { transition: all 0.2s ease; }
    .action-btn:active { transform: scale(0.95); }
    
    /* Receipt Modal Styles */
    #receiptModal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
    #receiptModal img { max-width: 100%; max-height: 90vh; border-radius: 10px; border: 4px solid #f59e0b; }
    #receiptModal .close-btn { position: absolute; top: 20px; right: 20px; color: white; font-size: 30px; cursor: pointer; }
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
        <div class="px-6 mb-4">
          <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Main Menu</p>
        </div>
        <nav class="space-y-1">
          <a href="dashboard.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-gauge-high text-amber-500 w-5"></i>
            <span>Dashboard</span>
          </a>
          <a href="admin_dashboard.php" class="sidebar-item sidebar-item-active flex items-center gap-4 px-6 py-3 text-sm font-semibold">
            <i class="fa-solid fa-user-shield text-amber-500 w-5"></i>
            <span>Admin Panel</span>
          </a>
          <a href="funnel.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-rocket text-amber-500 w-5"></i>
            <span>Funnel</span>
          </a>
          <a href="referrals.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-users-line text-amber-500 w-5"></i>
            <span>Signup List</span>
            <span id="side-pending-count" class="ml-auto bg-amber-500 text-black text-[10px] px-2 py-0.5 rounded-full font-black"><?php echo $total_pending; ?></span>
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
        <div class="px-6 mt-10 mb-4"><p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Settings</p></div>
        <nav class="space-y-1">
          <a href="profile.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-circle-user text-amber-500 w-5"></i><span>My Profile</span>
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
            $user_profile_pic = $current_user['profile_pic'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($current_user['full_name']) . '&background=f59e0b&color=000';
          ?>
          <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" class="w-8 h-8 rounded-full object-cover">
          <div class="overflow-hidden">
            <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($current_user['full_name']); ?></p>
            <p class="text-[10px] text-gray-500 truncate uppercase">Admin</p>
          </div>
        </div>
      </a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8 md:p-12">
      <header class="flex justify-between items-center mb-10">
        <div>
          <h1 class="text-3xl font-extrabold text-white">Admin Dashboard</h1>
          <p class="text-gray-500 text-sm mt-1">Verify payments, manage members, and see who referred each signup.</p>
        </div>
        <div class="flex items-center gap-4">
          <form action="admin_dashboard.php" method="GET" class="relative">
            <input type="text" name="search" placeholder="Member, email, username, or referrer..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   class="bg-[#0a0a0a] border border-white/5 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-amber-500/50 w-64 transition-all">
            <button type="submit" class="absolute right-3 top-2.5 text-gray-500 hover:text-white">
              <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
            </button>
          </form>
        </div>
      </header>

      <?php if ($total_pending > 0): ?>
      <div class="mb-8 p-5 rounded-2xl border border-amber-500/30 bg-amber-500/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black"><i class="fa-solid fa-bell"></i></span>
          <div>
            <p class="text-sm font-black text-amber-400 uppercase tracking-wider">Signup notifications</p>
            <p class="text-sm text-gray-200 mt-1">
              <strong class="text-white"><?php echo (int) $total_pending; ?></strong> member(s) submitted via referral links and need payment verification.
              <?php if (!empty($new_signups_since_referrals_page) && (int) $new_signups_since_referrals_page > 0): ?>
                <span class="text-amber-300 font-semibold"><?php echo (int) $new_signups_since_referrals_page; ?> new</span> since your last visit to this panel.
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-white/5">
          <p class="text-gray-500 text-xs font-bold uppercase mb-1">Total Members</p>
          <h3 class="text-4xl font-black text-white"><?php echo $total_users; ?></h3>
        </div>
        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-amber-500/20">
          <p class="text-amber-500 text-xs font-bold uppercase mb-1">Pending</p>
          <h3 id="stat-pending-count" class="text-4xl font-black text-white"><?php echo $total_pending; ?></h3>
        </div>
        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-white/5">
          <p class="text-gray-500 text-xs font-bold uppercase mb-1">Approved</p>
          <h3 id="stat-approved-count" class="text-4xl font-black text-green-500"><?php echo $total_approved; ?></h3>
        </div>
        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-white/5">
          <p class="text-gray-500 text-xs font-bold uppercase mb-1">Total Leads</p>
          <h3 class="text-4xl font-black text-amber-500"><?php echo $total_leads; ?></h3>
        </div>
      </div>

      <!-- Tab Switcher -->
      <div class="flex gap-4 mb-8">
        <button onclick="switchTab('signups')" id="tab-btn-signups" 
                class="px-6 py-3 rounded-2xl text-sm font-bold transition-all bg-amber-500 text-black">Signups (<?php echo count($all_users); ?>)</button>
        <button onclick="switchTab('leads')" id="tab-btn-leads" 
                class="px-6 py-3 rounded-2xl text-sm font-bold transition-all bg-[#0a0a0a] text-gray-500 border border-white/5 hover:border-amber-500/50">Leads (<?php echo count($all_leads); ?>)</button>
      </div>

      <!-- SIGNUP LIST -->
      <div id="signup-list" class="bg-[#0a0a0a] rounded-3xl border border-white/5 overflow-hidden shadow-2xl tab-content">
        <div class="p-6 bg-[#0d0d0d] border-b border-white/5 flex items-center gap-3">
          <i class="fa-solid fa-users text-amber-500"></i>
          <h2 class="text-xl font-bold text-white">All Signups / Member List</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-[#080808] border-b border-white/5 text-gray-500 text-[10px] font-black uppercase tracking-widest">
                <th class="p-6">Member (signed up)</th>
                <th class="p-6 min-w-[200px]">Referred by</th>
                <th class="p-6 text-center">Receipt</th>
                <th class="p-6 text-center">Status</th>
                <th class="p-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <?php if (empty($all_users)): ?>
                <tr id="empty-row"><td colspan="5" class="p-20 text-center text-gray-600 italic">No signups found.</td></tr>
              <?php endif; ?>
              <?php foreach ($all_users as $user): ?>
                <tr id="user-row-<?php echo $user['id']; ?>" class="hover:bg-white/[0.01] transition-all">
                  <td class="p-6">
                    <p class="font-bold text-white"><?php echo htmlspecialchars($user['full_name']); ?></p>
                    <p class="text-xs text-gray-400 font-mono">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if (!empty($user['phone'])): ?>
                      <p class="text-[10px] text-amber-500/70 mt-0.5"><i class="fa-solid fa-phone mr-1"></i><?php echo htmlspecialchars($user['phone']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['shipping_address'])): ?>
                      <p class="text-[9px] text-gray-400 mt-2 max-w-xs"><i class="fa-solid fa-truck-fast mr-1"></i><?php echo htmlspecialchars($user['shipping_address']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['gcash_number']) || !empty($user['bank_number'])): ?>
                      <div class="mt-2 pt-2 border-t border-white/5 space-y-1">
                        <?php if ($user['gcash_number']): ?>
                          <p class="text-[9px] text-blue-400 font-bold uppercase tracking-widest"><i class="fa-solid fa-wallet mr-1"></i>GCash: <?php echo htmlspecialchars($user['gcash_number']); ?></p>
                        <?php endif; ?>
                        <?php if ($user['bank_number']): ?>
                          <p class="text-[9px] text-blue-400 font-bold uppercase tracking-widest"><i class="fa-solid fa-building-columns mr-1"></i><?php echo htmlspecialchars($user['bank_name']); ?>: <?php echo htmlspecialchars($user['bank_number']); ?></p>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                    <p class="text-[9px] text-gray-600 mt-2"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                  </td>
                  <td class="p-6 align-top">
                    <?php if (!empty($user['referrer_user_id'])): ?>
                      <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-3">
                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1">Signed up via link</p>
                        <p class="font-bold text-white text-sm"><?php echo htmlspecialchars($user['referrer_full_name'] ?? ''); ?></p>
                        <p class="text-xs text-gray-400 font-mono">@<?php echo htmlspecialchars($user['referrer_username'] ?? ''); ?></p>
                        <?php if (!empty($user['referrer_email'])): ?>
                          <p class="text-[10px] text-gray-500 mt-1"><?php echo htmlspecialchars($user['referrer_email']); ?></p>
                        <?php endif; ?>
                        <p class="text-[9px] text-gray-600 mt-2">Referrer ID: <?php echo (int) $user['referrer_user_id']; ?></p>
                      </div>
                    <?php else: ?>
                      <div class="rounded-xl border border-white/10 bg-white/[0.02] p-3">
                        <p class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Referrer</p>
                        <p class="text-sm text-gray-500">No referral on record</p>
                        <p class="text-[10px] text-gray-600 mt-1">Direct checkout or cookie not set when they paid.</p>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="p-6 text-center">
                    <?php if ($user['payment_receipt']): ?>
                      <div class="flex flex-col items-center gap-2">
                        <img src="<?php echo htmlspecialchars($user['payment_receipt']); ?>" 
                             onclick="showReceipt('<?php echo htmlspecialchars($user['payment_receipt']); ?>')"
                             class="w-16 h-16 object-cover rounded-lg border border-white/10 cursor-pointer hover:border-amber-500 transition-all" 
                             alt="Receipt Thumbnail">
                        <a href="<?php echo htmlspecialchars($user['payment_receipt']); ?>" target="_blank" 
                           class="text-[9px] text-amber-500 font-bold uppercase hover:underline">
                          Open Full View
                        </a>
                      </div>
                    <?php else: ?>
                      <div class="flex flex-col items-center gap-1 text-red-500/50">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                        <span class="text-[9px] font-black uppercase">Missing Receipt</span>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="p-6 text-center">
                    <?php 
                      $status = $user['status'];
                      $color = 'gray';
                      if ($status === 'approved') $color = 'green';
                      if ($status === 'rejected') $color = 'red';
                      if ($status === 'pending') $color = 'amber';
                    ?>
                    <span class="px-3 py-1 bg-<?php echo $color; ?>-500/10 text-<?php echo $color; ?>-500 text-[9px] font-black rounded-full uppercase">
                      <?php echo $status; ?>
                    </span>
                  </td>
                  <td class="p-6 text-right space-x-2">
                    <?php if ($status === 'pending'): ?>
                      <button onclick="handleUser(<?php echo $user['id']; ?>, 'approve')" 
                              class="action-btn px-5 py-2 bg-green-500 text-black text-[10px] font-black rounded-full uppercase tracking-widest hover:bg-green-400">Approve</button>
                      <button onclick="handleUser(<?php echo $user['id']; ?>, 'reject')" 
                              class="action-btn px-5 py-2 bg-red-500/10 text-red-500 text-[10px] font-black rounded-full uppercase tracking-widest hover:bg-red-500 hover:text-white">Reject</button>
                    <?php else: ?>
                      <span class="text-[10px] text-gray-600 italic uppercase">Processed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- LEADS LIST (Hidden by default) -->
      <div id="leads-list" class="bg-[#0a0a0a] rounded-3xl border border-white/5 overflow-hidden shadow-2xl hidden tab-content">
        <div class="p-6 bg-[#0d0d0d] border-b border-white/5 flex items-center gap-3">
          <i class="fa-solid fa-bolt text-amber-500"></i>
          <h2 class="text-xl font-bold text-white">Generated Leads</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-[#080808] border-b border-white/5 text-gray-500 text-[10px] font-black uppercase tracking-widest">
                <th class="p-6">Lead Name</th>
                <th class="p-6">Contact Info</th>
                <th class="p-6">Referrer</th>
                <th class="p-6 text-right">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <?php if (empty($all_leads)): ?>
                <tr><td colspan="4" class="p-20 text-center text-gray-600 italic">No leads found.</td></tr>
              <?php endif; ?>
              <?php foreach ($all_leads as $lead): ?>
                <tr class="hover:bg-white/[0.01] transition-all">
                  <td class="p-6">
                    <p class="font-bold text-white"><?php echo htmlspecialchars($lead['name']); ?></p>
                  </td>
                  <td class="p-6">
                    <p class="text-xs text-gray-300"><?php echo htmlspecialchars($lead['email']); ?></p>
                    <?php if (!empty($lead['phone'])): ?>
                      <p class="text-[10px] text-amber-500/70 mt-0.5"><?php echo htmlspecialchars($lead['phone']); ?></p>
                    <?php endif; ?>
                  </td>
                  <td class="p-6">
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 bg-amber-500/10 rounded-full flex items-center justify-center text-[10px] font-bold text-amber-500">
                        <?php echo strtoupper(substr($lead['referrer_name'] ?: '?', 0, 1)); ?>
                      </div>
                      <span class="text-xs text-gray-400"><?php echo htmlspecialchars($lead['referrer_name'] ?: 'Direct'); ?></span>
                    </div>
                  </td>
                  <td class="p-6 text-right">
                    <p class="text-[10px] text-gray-500 uppercase"><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></p>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <div id="receiptModal" onclick="closeReceipt()">
    <span class="close-btn">&times;</span>
    <img id="receiptImg" src="" alt="Full Receipt">
  </div>

  <script>
    function switchTab(tab) {
      // Hide all contents
      document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
      // Show selected content
      document.getElementById(`${tab}-list`).classList.remove('hidden');
      
      // Update buttons
      const btnSignups = document.getElementById('tab-btn-signups');
      const btnLeads = document.getElementById('tab-btn-leads');
      
      if (tab === 'signups') {
        btnSignups.classList.add('bg-amber-500', 'text-black');
        btnSignups.classList.remove('bg-[#0a0a0a]', 'text-gray-500', 'border', 'border-white/5');
        btnLeads.classList.remove('bg-amber-500', 'text-black');
        btnLeads.classList.add('bg-[#0a0a0a]', 'text-gray-500', 'border', 'border-white/5');
      } else {
        btnLeads.classList.add('bg-amber-500', 'text-black');
        btnLeads.classList.remove('bg-[#0a0a0a]', 'text-gray-500', 'border', 'border-white/5');
        btnSignups.classList.remove('bg-amber-500', 'text-black');
        btnSignups.classList.add('bg-[#0a0a0a]', 'text-gray-500', 'border', 'border-white/5');
      }
    }

    function showReceipt(src) {
      document.getElementById('receiptImg').src = src;
      document.getElementById('receiptModal').style.display = 'flex';
    }

    function closeReceipt() {
      document.getElementById('receiptModal').style.display = 'none';
    }

    async function handleUser(userId, action) {
      console.log('Action requested:', action, 'for user:', userId);
      
      if (!confirm(`Are you sure you want to ${action} this user?`)) {
        console.log('Action cancelled by user');
        return;
      }

      const formData = new FormData();
      formData.append('id', userId);
      formData.append('action', action);

      try {
        const response = await fetch('admin_dashboard.php', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const result = await response.json();
        
        if (result.success) {
          // Update the UI dynamically
          const row = document.getElementById(`user-row-${userId}`);
          const statusCell = row.querySelector('td:nth-child(3)');
          const actionCell = row.querySelector('td:nth-child(4)');
          
          const color = action === 'approve' ? 'green' : 'red';
          statusCell.innerHTML = `<span class="px-3 py-1 bg-${color}-500/10 text-${color}-500 text-[9px] font-black rounded-full uppercase">${action}d</span>`;
          actionCell.innerHTML = `<span class="text-[10px] text-gray-600 italic uppercase">Processed</span>`;
          
          // Update stats
          if (result.stats) {
            document.getElementById('stat-pending-count').textContent = result.stats.pending;
            document.getElementById('side-pending-count').textContent = result.stats.pending;
            if (result.stats.approved) document.getElementById('stat-approved-count').textContent = result.stats.approved;
            if (result.stats.rejected) document.getElementById('stat-rejected-count').textContent = result.stats.rejected;
          }
          
          // Optional: toast notification instead of alert
          console.log('Success:', result.message);
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        console.error('Fetch error:', error);
        alert('An error occurred. Please try again.');
      }
    }
  </script>
</body>
</html>