<?php
session_start();
require_once 'db_config.php';

// Referrer from cookie/URL — no default bank/GCash (must come from sponsor profile)
$referrer_id = isset($_COOKIE['mclub_referrer']) ? (int)$_COOKIE['mclub_referrer'] : null;
$referrer_name = '';
$referrer_gcash = '';
$referrer_bank_name = '';
$referrer_bank_number = '';
$referrer_profile_pic = '';

// 1. Check for 'ref' parameter in URL first (Backup)
if (isset($_GET['ref'])) {
    $ref_username = $_GET['ref'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$ref_username]);
    $res = $stmt->fetch();
    if ($res) {
        $referrer_id = $res['id'];
        setcookie('mclub_referrer', $referrer_id, time() + (86400 * 30), "/");
    }
}

// 2. If no 'ref' in URL, check the cookie
if (!$referrer_id) {
    $referrer_id = isset($_COOKIE['mclub_referrer']) ? (int)$_COOKIE['mclub_referrer'] : null;
}

if ($referrer_id) {
    $stmt = $pdo->prepare('SELECT full_name, gcash_number, bank_name, bank_number, profile_pic FROM users WHERE id = ?');
    $stmt->execute([$referrer_id]);
    $user = $stmt->fetch();
    if ($user) {
        $referrer_name = trim((string) ($user['full_name'] ?? ''));
        $referrer_gcash = trim((string) ($user['gcash_number'] ?? ''));
        $referrer_bank_name = trim((string) ($user['bank_name'] ?? ''));
        $referrer_bank_number = trim((string) ($user['bank_number'] ?? ''));
        $referrer_profile_pic = trim((string) ($user['profile_pic'] ?? ''));
    } else {
        $referrer_id = null;
    }
}

$has_gcash = $referrer_gcash !== '';
$has_bank = $referrer_bank_number !== '';
$has_any_payment = $has_gcash || $has_bank;
$display_referrer_label = $referrer_name !== '' ? $referrer_name : 'Your sponsor';
$avatar_name = $referrer_name !== '' ? $referrer_name : 'Sponsor';
$display_profile_pic = $referrer_profile_pic !== ''
    ? $referrer_profile_pic
    : 'https://ui-avatars.com/api/?name=' . rawurlencode($avatar_name) . '&background=f59e0b&color=000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NEXTGEN CONQUERORS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        .glass-card {
            background: rgba(15, 15, 15, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .admin-header {
            background: linear-gradient(to bottom, #1a2236, #0a0a0a);
            border-radius: 24px 24px 0 0;
        }
        .payment-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            color: #000;
        }
        .copy-btn {
            background: #f3f4f6;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .input-field {
            background: #ffffff;
            border-radius: 12px;
            padding: 12px 20px;
            color: #000;
            width: 100%;
            font-size: 14px;
            border: 1px solid #e5e7eb;
        }
        .input-field::placeholder {
            color: #9ca3af;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 12px;
        }
        .upload-area {
            border: 2px dashed #e5e7eb;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            background: #ffffff;
        }
        .submit-btn {
            background: #2563eb;
            color: #fff;
            font-weight: 800;
            padding: 16px;
            border-radius: 16px;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s;
        }
        .submit-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-[#f3f4f6] text-gray-900 font-sans selection:bg-blue-500/30">

    <main class="max-w-2xl mx-auto px-4 py-12">
        
        <!-- Package Inclusions Section -->
        <div class="mb-12 text-center">
            <img src="https://d1yei2z3i6k35z.cloudfront.net/4624298/69ae98eedd751_JoyfulPresidentsDayFacebookSharedImage-MadewithPosterMyWall41.jpg" alt="Package Inclusions" class="w-full rounded-3xl shadow-2xl mb-8">
            
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4 tracking-tighter">Don’t Miss Out!<br><span class="text-blue-600">Become a Partner Today</span></h2>
            
            <!-- Total Amount Banner -->
            <div class="bg-[#0a0f1d] p-8 rounded-[40px] shadow-2xl border border-blue-500/20 my-10 transform hover:scale-[1.02] transition-all">
                <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.4em] mb-3">Total Amount</p>
                <h3 class="text-5xl md:text-6xl font-black text-[#fbbf24] tracking-tighter mb-4">₱2,198</h3>
                <div class="inline-block px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/30">
                    <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">(₱1,998 PACKAGE + ₱200 SHIPPING FEE)</p>
                </div>
            </div>

            <!-- Step by Step Guide -->
            <div class="mt-12 text-left">
                <p class="text-center text-[10px] font-black text-green-600 uppercase tracking-[0.3em] mb-4">Step-by-Step Guide</p>
                <h4 class="text-center text-xl font-black text-gray-900 mb-8 tracking-tight">How to Process My Membership?</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Step 1 -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-black text-sm shrink-0">1</div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-900 leading-none mb-1">Send Payment</p>
                            <p class="text-[9px] text-gray-400 leading-tight"><?php echo $has_any_payment ? 'Send payment to the accounts listed below.' : 'Get payment instructions from your sponsor (below), then pay and save your receipt.'; ?></p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-black text-sm shrink-0">2</div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-900 leading-none mb-1">Save Receipt</p>
                            <p class="text-[9px] text-gray-400 leading-tight">Take a clear screenshot or photo of your receipt.</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-black text-sm shrink-0">3</div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-900 leading-none mb-1">Shipping Info</p>
                            <p class="text-[9px] text-gray-400 leading-tight">Fill out your complete delivery information correctly.</p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-black text-sm shrink-0">4</div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-900 leading-none mb-1">Upload & Submit</p>
                            <p class="text-[9px] text-gray-400 leading-tight">Upload your proof and click the submit button.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sponsor payment details (only values saved in profile — no defaults) -->
        <div class="bg-[#0a0a0a] rounded-[32px] overflow-hidden mb-8 shadow-2xl">
            <div class="admin-header p-8 flex items-center gap-6">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($display_profile_pic); ?>" class="w-20 h-20 rounded-full object-cover border-2 border-blue-500" alt="">
                    <?php if ($has_any_payment): ?>
                    <div class="absolute -bottom-1 -right-1 bg-blue-500 w-6 h-6 rounded-full flex items-center justify-center text-white text-[10px] border-2 border-[#1a2236]">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="bg-blue-900/50 text-blue-400 text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full border border-blue-500/30">Your sponsor</span>
                    <h2 class="text-2xl font-black text-white uppercase mt-2"><?php echo htmlspecialchars($display_referrer_label); ?></h2>
                    <?php if ($has_any_payment): ?>
                    <p class="text-gray-400 text-xs mt-1">Send payment only to the account details your sponsor added below.</p>
                    <?php elseif ($referrer_id): ?>
                    <p class="text-amber-200/90 text-sm mt-2 font-semibold">This sponsor has not added GCash or bank details yet. Please contact them directly for how to pay before you submit.</p>
                    <?php else: ?>
                    <p class="text-amber-200/90 text-sm mt-2 font-semibold">Open this page from your sponsor’s link so their payment details appear. If you don’t have a link, contact the person who invited you.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-8 bg-white">
                <?php if ($has_any_payment): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if ($has_bank): ?>
                <div class="border border-gray-100 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <span class="font-black uppercase tracking-wider text-sm"><?php echo htmlspecialchars($referrer_bank_name !== '' ? $referrer_bank_name : 'Bank transfer'); ?></span>
                    </div>
                    <div class="space-y-4">
                        <?php if ($referrer_name !== ''): ?>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Account name</p>
                            <p class="font-bold text-gray-800"><?php echo htmlspecialchars($referrer_name); ?></p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Account number</p>
                            <div class="flex items-center justify-between mt-1 bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="font-bold tracking-wider"><?php echo htmlspecialchars($referrer_bank_number); ?></span>
                                <button type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($referrer_bank_number, ENT_QUOTES, 'UTF-8'); ?>')" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
                    <?php endif; ?>

                    <?php if ($has_gcash): ?>
                <div class="border border-gray-100 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </div>
                        <span class="font-black uppercase tracking-wider text-sm">GCash</span>
                    </div>
                    <div class="space-y-4">
                        <?php if ($referrer_name !== ''): ?>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Account name</p>
                            <p class="font-bold text-gray-800"><?php echo htmlspecialchars($referrer_name); ?></p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">GCash number</p>
                            <div class="flex items-center justify-between mt-1 bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="font-bold tracking-wider"><?php echo htmlspecialchars($referrer_gcash); ?></span>
                                <button type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($referrer_gcash, ENT_QUOTES, 'UTF-8'); ?>')" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50 p-8 text-center">
                    <i class="fa-solid fa-circle-info text-amber-600 text-2xl mb-3"></i>
                    <p class="text-gray-800 font-bold text-sm">No payment account is shown here yet.</p>
                    <p class="text-gray-600 text-xs mt-2 max-w-md mx-auto">Contact your sponsor for GCash or bank instructions. They can add them under <strong>Add MOP</strong> in their dashboard so they appear on this page.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Delivery Details Section -->
        <div class="bg-[#0a0a0a] rounded-[32px] overflow-hidden shadow-2xl">
            <div class="p-6 text-center border-b border-white/5">
                <h3 class="text-white font-black uppercase tracking-[0.2em]">Delivery Details</h3>
            </div>
            
            <form id="paymentForm" class="p-8 bg-white space-y-6">
                <input type="hidden" name="referrer_id" value="<?php echo $referrer_id; ?>">
                <p class="text-[11px] text-gray-500 leading-relaxed">All fields are required. Use a real email (we will confirm by mail). Philippines mobile only: 11 digits starting with 09.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="first_name" placeholder="First Name *" required maxlength="120" class="input-field">
                    <input type="text" name="middle_name" placeholder="Middle Name *" required maxlength="120" class="input-field">
                    <input type="text" name="last_name" placeholder="Last Name *" required maxlength="120" class="input-field">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="email" name="email" placeholder="Email Address *" required autocomplete="email" class="input-field">
                    <input type="tel" name="phone" id="reg_phone" placeholder="09XX XXX XXXX (11 digits) *" required maxlength="20" inputmode="numeric" autocomplete="tel" class="input-field" title="Philippines mobile: 11 digits starting with 09">
                </div>

                <div class="space-y-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Create your login password</p>
                    <p class="text-[11px] text-gray-500 leading-relaxed">Use this same password after your payment is verified and your account is approved. Minimum 8 characters.</p>
                    <input type="password" name="password" id="reg_password" placeholder="Password (8+ characters)" required minlength="8" autocomplete="new-password" class="input-field">
                    <input type="password" name="password_confirm" id="reg_password_confirm" placeholder="Confirm password" required minlength="8" autocomplete="new-password" class="input-field">
                </div>

                <textarea name="shipping_address" placeholder="Complete Shipping Address" required rows="2" class="input-field resize-none"></textarea>

                <div class="space-y-4 pt-4 border-t border-gray-100">
                    <p class="text-[10px] font-black text-orange-600 uppercase tracking-widest">Attachment: Proof of Payment</p>
                    
                    <label for="payment_receipt" class="upload-area block cursor-pointer hover:bg-gray-50 transition-all">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300">
                                <i class="fa-solid fa-camera text-2xl"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-800">Upload Screenshot</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">PNG, JPG up to 2MB</p>
                            </div>
                        </div>
                        <input type="file" name="payment_receipt" id="payment_receipt" class="hidden" accept="image/jpeg,image/png,image/webp,image/gif" required onchange="updateUploadLabel(this)">
                    </label>
                    <p id="file-name" class="text-center text-xs text-blue-600 font-bold hidden"></p>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Confirm & Submit Order
                </button>
            </form>
        </div>

    </main>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            });
        }

        function updateUploadLabel(input) {
            const fileName = input.files[0]?.name;
            const label = document.getElementById('file-name');
            if (fileName) {
                label.textContent = "Selected: " + fileName;
                label.classList.remove('hidden');
            }
        }

        function normalizePhPhone(raw) {
            const d = String(raw).replace(/\D/g, '');
            if (d.length === 12 && d.startsWith('63')) return '0' + d.slice(2);
            if (d.length === 11 && d.startsWith('09')) return d;
            return null;
        }

        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btn = document.getElementById('submitBtn');
            const originalText = btn.textContent;
            const fn = form.first_name.value.trim();
            const mn = form.middle_name.value.trim();
            const ln = form.last_name.value.trim();
            const em = form.email.value.trim();
            const addr = form.shipping_address.value.trim();
            const fileInput = document.getElementById('payment_receipt');
            if (!fn || !mn || !ln || !em || !addr) {
                alert('Please fill in every field, including middle name and shipping address.');
                return;
            }
            const ph = normalizePhPhone(document.getElementById('reg_phone').value);
            if (!ph) {
                alert('Enter a valid Philippines mobile number: 11 digits starting with 09 (e.g. 09171234567).');
                return;
            }
            document.getElementById('reg_phone').value = ph;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) {
                alert('Please enter a valid email address.');
                return;
            }
            if (!fileInput.files || !fileInput.files.length) {
                alert('Please attach your payment proof image before submitting.');
                return;
            }
            const pw = document.getElementById('reg_password').value;
            const pw2 = document.getElementById('reg_password_confirm').value;
            if (pw.length < 8) {
                alert('Password must be at least 8 characters.');
                return;
            }
            if (pw !== pw2) {
                alert('Password and confirmation do not match.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Processing...';

            const formData = new FormData(this);

            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    body: formData
                });

                let result;
                try {
                    result = await response.json();
                } catch (parseErr) {
                    alert('Server error: invalid response. Please try again or contact support.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                    return;
                }

                if (response.ok) {
                    const msg = result.message || 'Your application was submitted successfully.';
                    alert(msg);
                    window.location.href = result.redirect || 'registration-success.php';
                } else {
                    alert(result.message || 'An error occurred during submission.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Connection error. Please try again.');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    </script>
</body>
</html>