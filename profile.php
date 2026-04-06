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
    if (!empty($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $error_msg = "Please fill in all password fields.";
        } elseif (strlen($new_password) < 8) {
            $error_msg = "New password must be at least 8 characters.";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "New password and confirmation do not match.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $row = $stmt->fetch();
                if (!$row || !password_verify($current_password, $row['password'])) {
                    $error_msg = "Current password is incorrect.";
                } else {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $user_id]);
                    $success_msg = "Password updated successfully.";
                }
            } catch (PDOException $e) {
                $error_msg = "Could not update password. Please try again.";
                error_log("Password change failed: " . $e->getMessage());
            }
        }
    } else {
        $full_name = strip_tags(trim($_POST["full_name"]));
        $gcash_number = strip_tags(trim($_POST["gcash_number"]));
        $bank_name = strip_tags(trim($_POST["bank_name"]));
        $bank_number = strip_tags(trim($_POST["bank_number"]));

        try {
            // Update basic info
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, gcash_number = ?, bank_name = ?, bank_number = ? WHERE id = ?");
            $stmt->execute([$full_name, $gcash_number, $bank_name, $bank_number, $user_id]);
            $_SESSION['full_name'] = $full_name;

            // Handle Profile Picture Upload
            if (!empty($_FILES['profile_pic']['name'])) {
                $target_dir = "storage/profile_photos/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
                $file_name = "profile_" . $user_id . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    $stmt->execute([$target_file, $user_id]);
                }
            }

            $success_msg = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error updating profile: " . $e->getMessage();
        }
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
  <title>My Profile - NEXTGEN CONQUERORS</title>
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
    .input-group:focus-within label { color: #f59e0b; }
    .custom-input {
      background: rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }
    .custom-input:focus {
      border-color: rgba(245, 158, 11, 0.5);
      background: rgba(0, 0, 0, 0.5);
      box-shadow: 0 0 20px rgba(245, 158, 11, 0.05);
    }
  </style>
</head>
<body class="bg-[#050505] text-gray-200 font-sans selection:bg-amber-500/30">

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
          <a href="referrals.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
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
          <a href="analytics.php" class="sidebar-item flex items-center gap-4 px-6 py-3 text-sm font-semibold text-gray-400">
            <i class="fa-solid fa-chart-simple text-amber-500 w-5"></i><span>Analytics</span>
          </a>
        </nav>

        <div class="px-6 mt-10 mb-4"><p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Settings</p></div>
        <nav class="space-y-1">
          <a href="profile.php" class="sidebar-item sidebar-item-active flex items-center gap-4 px-6 py-3 text-sm font-semibold">
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
        <h1 class="text-4xl font-black text-white tracking-tight">Profile Settings</h1>
        <p class="text-gray-500 text-sm mt-2 max-w-md">Update your personal information and payment methods to ensure you receive your commissions correctly.</p>
      </header>

      <?php if ($success_msg): ?>
        <div class="mb-8 p-4 bg-green-500/10 border border-green-500/20 text-green-500 rounded-2xl text-sm font-bold flex items-center gap-3 animate-pulse">
          <i class="fa-solid fa-circle-check"></i><?php echo $success_msg; ?>
        </div>
      <?php endif; ?>

      <?php if ($error_msg): ?>
        <div class="mb-8 p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl text-sm font-bold flex items-center gap-3">
          <i class="fa-solid fa-circle-xmark"></i><?php echo $error_msg; ?>
        </div>
      <?php endif; ?>

      <div class="max-w-5xl grid grid-cols-1 lg:grid-cols-12 gap-8">
      <form action="profile.php" method="POST" enctype="multipart/form-data" class="contents">

        <!-- Left Column: Avatar & Quick Info -->
        <div class="lg:col-span-4 space-y-8">
          <div class="glass-card p-10 rounded-[48px] text-center relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-amber-500/50 to-transparent"></div>
            
            <div class="relative inline-block mb-8">
              <div class="w-32 h-32 rounded-full p-1 bg-gradient-to-tr from-amber-500 to-transparent">
                <img id="preview" src="<?php echo htmlspecialchars($profile_pic); ?>" class="w-full h-full rounded-full object-cover bg-[#050505]">
              </div>
              <label for="profile_pic" class="absolute bottom-0 right-0 w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center text-black cursor-pointer hover:scale-110 transition-all shadow-[0_0_20px_rgba(245,158,11,0.3)]">
                <i class="fa-solid fa-camera text-sm"></i>
              </label>
              <input type="file" name="profile_pic" id="profile_pic" class="hidden" accept="image/*" onchange="previewImage(this)">
            </div>
            
            <h3 class="text-2xl font-black text-white uppercase tracking-tighter"><?php echo htmlspecialchars($username); ?></h3>
            <div class="inline-block mt-2 px-4 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-[10px] font-black text-amber-500 uppercase tracking-widest">
              <?php echo $role; ?> Account
            </div>
            
            <div class="mt-10 pt-8 border-t border-white/5 grid grid-cols-2 gap-4">
              <div class="text-left">
                <p class="text-[9px] font-black text-gray-600 uppercase tracking-widest">Joined</p>
                <p class="text-xs font-bold text-gray-400 mt-1"><?php echo date('M Y', strtotime($user['created_at'])); ?></p>
              </div>
              <div class="text-left">
                <p class="text-[9px] font-black text-gray-600 uppercase tracking-widest">Status</p>
                <p class="text-xs font-bold text-green-500 mt-1 uppercase italic">Active</p>
              </div>
            </div>
          </div>

          <!-- Live Preview Hint -->
          <div class="bg-blue-500/5 border border-blue-500/10 p-6 rounded-3xl">
            <div class="flex items-center gap-3 mb-3">
              <i class="fa-solid fa-circle-info text-blue-400"></i>
              <p class="text-xs font-black text-blue-400 uppercase tracking-widest">Dynamic Sync</p>
            </div>
            <p class="text-[11px] text-gray-500 leading-relaxed">Your payment details update in <strong>real-time</strong> across your referral funnel. Ensure they are 100% accurate to avoid payout delays.</p>
          </div>
        </div>

        <!-- Right Column: Main Form -->
        <div class="lg:col-span-8 space-y-8">
          
          <!-- Section: Identity -->
          <div class="glass-card p-8 md:p-10 rounded-[48px] relative overflow-hidden">
            <div class="flex items-center gap-4 mb-10">
              <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                <i class="fa-solid fa-id-card-clip text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tighter">Public Identity</h3>
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">This name appears on your checkout page</p>
              </div>
            </div>

            <div class="input-group space-y-2">
              <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1 transition-colors">Full Display Name</label>
              <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required
                     class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none">
            </div>
          </div>

          <!-- Section: Financials -->
          <div class="glass-card p-8 md:p-10 rounded-[48px] relative overflow-hidden">
            <div class="flex items-center gap-4 mb-10">
              <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                <i class="fa-solid fa-vault text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tighter">Payout Channels</h3>
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Where you receive your team commissions</p>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="input-group space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">GCASH Number</label>
                <div class="relative">
                  <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-600 text-xs font-bold">PH</span>
                  <input type="text" name="gcash_number" value="<?php echo htmlspecialchars($user['gcash_number'] ?? ''); ?>" placeholder="09XX XXX XXXX"
                         class="custom-input w-full rounded-2xl pl-12 pr-6 py-4 text-sm text-white focus:outline-none">
                </div>
              </div>

              <div class="input-group space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Bank Name</label>
                <input type="text" name="bank_name" value="<?php echo htmlspecialchars($user['bank_name'] ?? ''); ?>" placeholder="e.g. BDO, BPI, UnionBank"
                       class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none">
              </div>

              <div class="md:col-span-2 input-group space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Bank Account Number</label>
                <input type="text" name="bank_number" value="<?php echo htmlspecialchars($user['bank_number'] ?? ''); ?>" placeholder="XXXX XXXX XXXX XXXX"
                       class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none tracking-widest font-mono">
              </div>
            </div>
          </div>

          <button type="submit" class="group relative w-full py-6 bg-amber-500 text-black font-black rounded-3xl uppercase tracking-[0.2em] text-sm overflow-hidden transition-all hover:shadow-[0_20px_40px_rgba(245,158,11,0.2)] active:scale-[0.98]">
            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
            <span class="relative flex items-center justify-center gap-3">
              <i class="fa-solid fa-cloud-arrow-up animate-bounce"></i>
              Deploy Profile Updates
            </span>
          </button>

        </div>
      </form>

      <div class="hidden lg:block lg:col-span-4" aria-hidden="true"></div>
      <div class="lg:col-span-8">
          <div class="glass-card p-8 md:p-10 rounded-[48px] relative overflow-hidden">
            <div class="flex items-center gap-4 mb-10">
              <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                <i class="fa-solid fa-key text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tighter">Account Password</h3>
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Change your login password anytime</p>
              </div>
            </div>

            <form action="profile.php" method="POST" class="space-y-6" autocomplete="on">
              <input type="hidden" name="change_password" value="1">
              <div class="input-group space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Current password</label>
                <input type="password" name="current_password" required autocomplete="current-password"
                       class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none">
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="input-group space-y-2">
                  <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">New password</label>
                  <input type="password" name="new_password" required minlength="8" autocomplete="new-password"
                         class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none"
                         placeholder="At least 8 characters">
                </div>
                <div class="input-group space-y-2">
                  <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Confirm new password</label>
                  <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"
                         class="custom-input w-full rounded-2xl px-6 py-4 text-sm text-white focus:outline-none">
                </div>
              </div>
              <button type="submit" class="group relative w-full py-4 bg-white/5 border border-white/10 text-amber-500 font-black rounded-2xl uppercase tracking-[0.15em] text-xs overflow-hidden transition-all hover:bg-amber-500/10 hover:border-amber-500/30 active:scale-[0.99]">
                <span class="relative flex items-center justify-center gap-2">
                  <i class="fa-solid fa-lock"></i>
                  Update password
                </span>
              </button>
            </form>
          </div>
      </div>
      </div>
    </main>
  </div>

  <script>
    function previewImage(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>
</html>