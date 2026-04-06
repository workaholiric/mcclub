<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle AJAX Approval/Rejection Requests
if (isset($_POST['action']) && isset($_POST['id'])) {
    // Security check: Only allow if the user being approved is referred by the current user
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    // Check if this user is a referral of the current user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND referrer_id = ?");
    $stmt->execute([$id, $user_id]);
    $is_referral = $stmt->fetch();

    if (!$is_referral) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Unauthorized action."]);
        exit;
    }

    // Clear any previous output buffers
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json');
    
    try {
        if ($action === 'approve') {
            $upd = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND referrer_id = ? AND role = 'user' AND status = 'pending'");
            $upd->execute([$id, $user_id]);
            if ($upd->rowCount() < 1) {
                echo json_encode(["success" => false, "message" => "This referral is not pending or could not be approved."]);
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
                    error_log('referrals approve: mail failed for user id ' . $id);
                }
            }
            echo json_encode(["success" => true, "message" => "Referral approved successfully!"]);
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND referrer_id = ? AND role = 'user' AND status = 'pending'");
            $stmt->execute([$id, $user_id]);
            if ($stmt->rowCount() < 1) {
                echo json_encode(["success" => false, "message" => "This referral is not pending or could not be rejected."]);
                exit;
            }
            echo json_encode(["success" => true, "message" => "Referral rejected."]);
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

// Fetch User's Referrals
$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE referrer_id = ? AND role = 'user'
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$referrals = $stmt->fetchAll();

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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['referrals_list_seen_at'] = time();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Referrals - NEXTGEN CONQUERORS</title>
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
          <a href="referrals.php" class="sidebar-item sidebar-item-active flex items-center gap-4 px-6 py-3 text-sm font-semibold">
            <i class="fa-solid fa-users-line text-amber-500 w-5"></i><span>Signup List</span>
            <?php if ($role !== 'admin' && $pending_referrals_notify > 0): ?>
            <span class="ml-auto min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-amber-500 text-black text-[10px] font-black"><?php echo $pending_referrals_notify > 99 ? '99+' : (int) $pending_referrals_notify; ?></span>
            <?php endif; ?>
          </a>
          <a href="mop.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
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
        <h1 class="text-4xl font-black text-white tracking-tight">My Referrals</h1>
        <p class="text-gray-500 text-sm mt-2 max-w-md">People who registered using your referral link. You can verify their payments and approve them here.</p>
      </header>

      <?php if ($role !== 'admin' && $pending_referrals_notify > 0): ?>
      <div class="mb-8 p-5 rounded-2xl border border-amber-500/30 bg-amber-500/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black"><i class="fa-solid fa-bell"></i></span>
          <div>
            <p class="text-sm font-black text-amber-400 uppercase tracking-wider">Signup activity on your link</p>
            <p class="text-sm text-gray-200 mt-1">
              You have <strong class="text-white"><?php echo (int) $pending_referrals_notify; ?></strong> signup(s) waiting for payment review.
              <?php if ($new_signups_since_referrals_page > 0): ?>
                <span class="text-amber-300 font-semibold"><?php echo (int) $new_signups_since_referrals_page; ?> new</span> since you last opened this list.
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="glass-card rounded-[48px] overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-white/[0.02]">
          <h3 class="text-xl font-black text-white uppercase tracking-tighter">Referral List</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-black/20 text-[10px] font-black text-gray-500 uppercase tracking-widest border-b border-white/5">
                <th class="px-8 py-5">Full Name</th>
                <th class="px-8 py-5 text-center">Receipt</th>
                <th class="px-8 py-5 text-center">Status</th>
                <th class="px-8 py-5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <?php if (empty($referrals)): ?>
                <tr><td colspan="4" class="p-20 text-center text-gray-600 italic">You haven't referred anyone yet. Start sharing your link!</td></tr>
              <?php endif; ?>
              <?php foreach ($referrals as $ref): ?>
                <tr id="user-row-<?php echo $ref['id']; ?>" class="hover:bg-white/[0.01] transition-colors">
                  <td class="px-8 py-6">
                    <p class="font-bold text-white"><?php echo htmlspecialchars($ref['full_name']); ?></p>
                    <p class="text-xs text-gray-400 font-mono">@<?php echo htmlspecialchars($ref['username']); ?></p>
                    <p class="text-xs text-gray-300"><?php echo htmlspecialchars($ref['email']); ?></p>
                    <?php if ($ref['phone']): ?>
                      <p class="text-[10px] text-amber-500/50 font-bold mt-1"><?php echo htmlspecialchars($ref['phone']); ?></p>
                    <?php endif; ?>
                  </td>
                  <td class="px-8 py-6 text-center">
                    <?php if ($ref['payment_receipt']): ?>
                      <img src="<?php echo htmlspecialchars($ref['payment_receipt']); ?>" 
                           onclick="showReceipt('<?php echo htmlspecialchars($ref['payment_receipt']); ?>')"
                           class="w-12 h-12 object-cover rounded-lg border border-white/10 cursor-pointer hover:border-amber-500 transition-all mx-auto" 
                           alt="Receipt">
                    <?php else: ?>
                      <span class="text-gray-600 text-[10px] uppercase font-bold">No Receipt</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-8 py-6 text-center">
                    <?php 
                      $status = $ref['status'];
                      $color = $status === 'approved' ? 'green' : ($status === 'rejected' ? 'red' : 'amber');
                    ?>
                    <span id="status-badge-<?php echo $ref['id']; ?>" class="px-3 py-1 bg-<?php echo $color; ?>-500/10 text-<?php echo $color; ?>-500 text-[9px] font-black rounded-full uppercase tracking-widest"><?php echo $status; ?></span>
                  </td>
                  <td class="px-8 py-6 text-right">
                    <div id="action-cell-<?php echo $ref['id']; ?>">
                      <?php if ($status === 'pending'): ?>
                        <div class="flex justify-end gap-2">
                          <button onclick="handleReferral(<?php echo $ref['id']; ?>, 'approve')" 
                                  class="action-btn px-4 py-2 bg-green-500 text-black text-[9px] font-black rounded-full uppercase tracking-widest hover:bg-green-400">Approve</button>
                          <button onclick="handleReferral(<?php echo $ref['id']; ?>, 'reject')" 
                                  class="action-btn px-4 py-2 bg-red-500/10 text-red-500 text-[9px] font-black rounded-full uppercase tracking-widest hover:bg-red-500 hover:text-white">Reject</button>
                        </div>
                      <?php else: ?>
                        <span class="text-[10px] text-gray-600 italic uppercase">Processed</span>
                      <?php endif; ?>
                    </div>
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
    function showReceipt(src) {
      document.getElementById('receiptImg').src = src;
      document.getElementById('receiptModal').style.display = 'flex';
    }

    function closeReceipt() {
      document.getElementById('receiptModal').style.display = 'none';
    }

    async function handleReferral(userId, action) {
      if (!confirm(`Are you sure you want to ${action} this referral?`)) return;

      const formData = new FormData();
      formData.append('id', userId);
      formData.append('action', action);

      try {
        const response = await fetch('referrals.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          const badge = document.getElementById(`status-badge-${userId}`);
          const actionCell = document.getElementById(`action-cell-${userId}`);
          
          const color = action === 'approve' ? 'green' : 'red';
          badge.className = `px-3 py-1 bg-${color}-500/10 text-${color}-500 text-[9px] font-black rounded-full uppercase tracking-widest`;
          badge.textContent = action + 'd';
          actionCell.innerHTML = `<span class="text-[10px] text-gray-600 italic uppercase">Processed</span>`;
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        alert('An error occurred. Please try again.');
      }
    }
  </script>
</body>
</html>
