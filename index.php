<?php
session_start();
require_once 'db_config.php';

// Default Referrer (Admin)
$referrer_name = "Jhong Rondina";
$referrer_gcash = "0907 112 1528";
$referrer_bank_name = "Union Bank";
$referrer_bank_number = "1094 3003 0490";
$referrer_profile_pic = "https://d1yei2z3i6k35z.cloudfront.net/4624298/697364012e4a0_607442931_122116944915132090_1859400601210278259_n.jpg";

// Check for referral parameter or cookie
$ref_id = isset($_COOKIE['mclub_referrer']) ? (int)$_COOKIE['mclub_referrer'] : null;
if (isset($_GET['ref'])) {
    $ref_username = $_GET['ref'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$ref_username]);
    $res = $stmt->fetch();
    if ($res) {
        $ref_id = $res['id'];
        setcookie('mclub_referrer', $ref_id, time() + (86400 * 30), "/");
    }
}

if ($ref_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$ref_id]);
        $user = $stmt->fetch();
        if ($user) {
            $referrer_name = $user['full_name'];
            if (isset($user['gcash_number']) && $user['gcash_number']) $referrer_gcash = $user['gcash_number'];
            if (isset($user['bank_name']) && $user['bank_name']) $referrer_bank_name = $user['bank_name'];
            if (isset($user['bank_number']) && $user['bank_number']) $referrer_bank_number = $user['bank_number'];
            if (isset($user['profile_pic']) && $user['profile_pic']) $referrer_profile_pic = $user['profile_pic'];
        }
    } catch (PDOException $e) {
        // Database schema might be outdated, skip extended info
        error_log("Referrer query failed: " . $e->getMessage());
    }
}
$display_profile_pic = $referrer_profile_pic ?: 'https://ui-avatars.com/api/?name=' . urlencode($referrer_name) . '&background=f59e0b&color=000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NEXTGEN CONQUERORS</title>
  <link rel="preload" as="style" href="/build/assets/app-BmI9JwnE.css" /><link rel="stylesheet" href="/build/assets/app-BmI9JwnE.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <style>
    /* Hero Background Image */
    .hero-bg {
      position: relative;
      background-image: url('/background.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .hero-bg::before { 
      content: ""; 
      position: absolute; 
      inset: 0; 
      background: linear-gradient( 
        to right, 
        rgba(0,0,0,0.9) 25%, 
        rgba(0,0,0,0.6) 55%, 
        rgba(0,0,0,0.2) 100% 
      ); 
      z-index: 0; 
    } 

    /* Floating animation custom CSS */
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
      100% { transform: translateY(0px); }
    }
    .floating-image {
      animation: float 4s ease-in-out infinite;
    }

    /* Network Net Background Effect */
    .network-bg {
      background-image: 
        radial-gradient(circle at 2px 2px, rgba(249, 115, 22, 0.05) 1px, transparent 0);
      background-size: 40px 40px;
    }
    
    .cyber-grid {
      position: absolute;
      inset: 0;
      background: 
        linear-gradient(to right, rgba(249, 115, 22, 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(249, 115, 22, 0.03) 1px, transparent 1px);
      background-size: 60px 60px;
      mask-image: radial-gradient(ellipse at center, black, transparent 80%);
      pointer-events: none;
    }
  </style>
</head>


<div id="page-loader">
  <div class="loader-container">
    <div class="spinner-wrapper">
      <div class="spinner"></div>
      <div class="spinner-inner"></div>
    </div>
    <p class="loading-text">System Initializing</p>
  </div>
</div>

<style>
/* === PAGE LOADER OVERLAY === */
#page-loader {
  position: fixed;
  inset: 0;
  /* Matching your dark navy theme */
  background: radial-gradient(circle at center, #111827 0%, #0a0f1d 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.8s;
}

#page-loader.hidden {
  opacity: 0;
  visibility: hidden;
}

/* === LOADER CONTAINER === */
#page-loader .loader-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

/* === ADVANCED SPINNER === */
.spinner-wrapper {
  position: relative;
  width: 90px;
  height: 90px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 4px solid rgba(245, 158, 11, 0.1); /* Amber-500 low opacity */
  border-top: 4px solid #f59e0b; /* Amber-500 */
  border-radius: 50%;
  animation: spin 1s cubic-bezier(0.5, 0.1, 0.4, 0.9) infinite;
}

.spinner-inner {
  width: 60%;
  height: 60%;
  border: 3px solid rgba(30, 64, 175, 0.1); /* Blue-800 low opacity */
  border-bottom: 3px solid #3b82f6; /* Blue-500 */
  border-radius: 50%;
  animation: spin-reverse 1.5s linear infinite;
}

/* === LOADING TEXT === */
#page-loader .loading-text {
  color: #f59e0b;
  font-weight: 800;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  font-size: 0.75rem;
  text-align: center;
  /* Using Laravel default font stack from context */
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  opacity: 0.8;
  animation: pulse 2s ease-in-out infinite;
}

/* === ANIMATIONS === */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes spin-reverse {
  0% { transform: rotate(360deg); }
  100% { transform: rotate(0deg); }
}

@keyframes pulse {
  0%, 100% { opacity: 0.4; filter: blur(0.5px); }
  50% { opacity: 1; filter: blur(0px); text-shadow: 0 0 15px rgba(245, 158, 11, 0.5); }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const loader = document.getElementById("page-loader");
  // Slightly longer delay for a premium "heavy" system feel
  setTimeout(() => {
    loader.classList.add("hidden");
  }, 1200);
});
</script>
<body class="bg-[#050505] text-gray-200">

  <nav class="fixed top-0 left-0 w-full z-[100] backdrop-blur-md bg-black/70 border-b border-orange-500/10"> 
   <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center"> 
     
     <!-- Logo Left --> 
     <div class="flex items-center gap-3"> 
       <img src="/newlogo.png" alt="NextGen" class="h-8 w-auto object-contain">
       <span class="font-black text-sm tracking-tighter text-white uppercase leading-tight"> 
         NextGen<br>Conquerors 
       </span> 
     </div> 

     <!-- Nav Links -->
     <div class="hidden lg:flex items-center gap-8 center-nav">
       <a href="#" class="text-[10px] font-bold text-orange-500 uppercase tracking-widest border-b-2 border-orange-500 pb-1">Home</a>
       <a href="#" class="text-[10px] font-bold text-gray-400 hover:text-white transition-colors uppercase tracking-widest pb-1">About</a>
       <a href="#" class="text-[10px] font-bold text-gray-400 hover:text-white transition-colors uppercase tracking-widest pb-1">Products</a>
       <a href="#" class="text-[10px] font-bold text-gray-400 hover:text-white transition-colors uppercase tracking-widest pb-1">Training</a>
       <a href="#" class="text-[10px] font-bold text-gray-400 hover:text-white transition-colors uppercase tracking-widest pb-1">Blog</a>
     </div>
 
     <!-- Right --> 
     <a href="/login.php" 
        class="flex items-center gap-2 px-5 py-2 rounded-lg font-bold text-[10px] uppercase tracking-widest
               border border-white/20 text-white hover:bg-white hover:text-black transition-all"> 
       <i class="fa-solid fa-user text-xs"></i> 
       Member Login 
     </a> 
 
   </div> 
 </nav>

  
  <section class="relative pt-[140px] pb-[100px] overflow-hidden min-h-screen flex items-center hero-bg">
    <div class="cyber-grid opacity-20"></div>
    
    <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] bg-amber-600/5 blur-[130px] rounded-full -z-10"></div>

    <div class="max-w-7xl mx-auto px-6">
      
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        
        
        <div class="text-center md:text-left order-2 md:order-1">
          <h2 class="text-5xl md:text-7xl font-black mb-2 leading-[0.95] text-white uppercase tracking-tight relative z-10"> 
            <span class="text-xs md:text-sm block mb-4 text-orange-500 font-bold tracking-[0.3em]"> 
              PROJECT 1M 
            </span> 
            MULTISTREAM <br> 
            <span class="text-orange-500">SYSTEM</span> 
          </h2>
          <div class="w-12 h-1 bg-orange-500 mb-8 rounded-full relative z-10"></div>

          <p class="text-sm md:text-base text-gray-400 mb-10 max-w-xl mx-auto md:mx-0 leading-relaxed font-medium relative z-10">
            Stop chasing prospects and start attracting them. Leverage our <strong>Automated Sales Funnel System</strong> to build your network marketing business 24/7, even while you sleep.
          </p>

          <div class="flex flex-wrap items-center gap-4 relative z-10">
            <a href="#lead-form"
              class="group flex items-center justify-center gap-3 px-8 py-4 font-black rounded-xl cursor-pointer 
                     text-center w-fit mx-auto md:mx-0 text-black uppercase tracking-widest text-[10px]
                     bg-orange-500 hover:bg-orange-400 transition-all shadow-[0_10px_30px_rgba(249,115,22,0.3)]">
              <i class="fa-solid fa-rocket text-sm"></i>
              <span>Launch Your Funnel Now</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-8 py-4 font-bold text-white uppercase tracking-widest text-[10px] hover:text-orange-500 transition-colors">
              <div class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center group-hover:border-orange-500 transition-colors">
                <i class="fa-solid fa-play ml-1"></i>
              </div>
              Watch Video
            </a>
          </div>
        </div>

        
        <div class="order-1 md:order-2 flex justify-center relative z-10"> 
          <img 
            src="/newlogo.png" 
            alt="NextGen Conquerors" 
            class="w-full max-w-[500px] md:max-w-[600px] 
                   drop-shadow-[0_20px_80px_rgba(249,115,22,0.5)] 
                   floating-image" 
          /> 
        </div>

      </div> 

      
      <div class="mt-24 grid grid-cols-1 sm:grid-cols-3 gap-8 relative z-10">
        
        <div class="p-10 bg-black/40 backdrop-blur-sm rounded-[32px] border border-white/5 hover:border-orange-500/20 transition-all duration-300 group shadow-2xl">
          <div class="w-14 h-14 bg-orange-500 rounded-full flex items-center justify-center mb-8 mx-auto md:mx-0 shadow-[0_0_20px_rgba(249,115,22,0.4)]">
             <i class="fa-solid fa-box text-black text-xl"></i>
          </div>
          <h5 class="text-lg font-black text-white mb-4 text-center md:text-left uppercase tracking-tight">Ready Products</h5>
          <p class="text-xs text-gray-500 text-center md:text-left leading-relaxed font-medium">Hindi ka magsisimula sa zero. Lahat ng kailangan mo — nandito na. Ready-to-sell products para sa iyong negosyo.</p>
        </div>

        
        <div class="p-10 bg-black/40 backdrop-blur-sm rounded-[32px] border border-white/5 hover:border-orange-500/20 transition-all duration-300 group shadow-2xl">
          <div class="w-14 h-14 bg-orange-500 rounded-full flex items-center justify-center mb-8 mx-auto md:mx-0 shadow-[0_0_20px_rgba(249,115,22,0.4)]">
             <i class="fa-solid fa-robot text-black text-xl"></i>
          </div>
          <h5 class="text-lg font-black text-white mb-4 text-center md:text-left uppercase tracking-tight">Automation System</h5>
          <p class="text-xs text-gray-500 text-center md:text-left leading-relaxed font-medium">Chatbot system at auto replies para sa iyong negosyo. Done-for-you automation na gagawin ang trabaho para sa iyo.</p>
        </div>

        
        <div class="p-10 bg-black/40 backdrop-blur-sm rounded-[32px] border border-white/5 hover:border-orange-500/20 transition-all duration-300 group shadow-2xl">
          <div class="w-14 h-14 bg-orange-500 rounded-full flex items-center justify-center mb-8 mx-auto md:mx-0 shadow-[0_0_20px_rgba(249,115,22,0.4)]">
             <i class="fa-solid fa-book-open text-black text-xl"></i>
          </div>
          <h5 class="text-lg font-black text-white mb-4 text-center md:text-left uppercase tracking-tight">Training & Support</h5>
          <p class="text-xs text-gray-500 text-center md:text-left leading-relaxed font-medium">Step-by-step videos at beginner-friendly guide. May dedicated support team na handang tumulong sa iyo.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="lead-form" class="relative py-[100px] bg-[#0a0a0a]">
    <div class="max-w-3xl mx-auto px-6">
      <div class="bg-[#0f0f0f] p-10 rounded-3xl border border-amber-500/20 shadow-[0_0_50px_rgba(245,158,11,0.05)]">
        <div class="text-center mb-10">
          <h3 class="text-3xl font-black text-white mb-4 uppercase tracking-tighter">Secure Your Spot</h3>
          <p class="text-gray-400 italic text-sm">Join the NEXTGEN CONQUERORS community and start your journey today.</p>
        </div>
        
        <form id="leadCaptureForm" action="/process-leads.php" method="POST" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label for="name" class="text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Full Name</label>
              <input type="text" name="name" id="name" required placeholder="Enter your full name"
                     class="w-full bg-[#050505] border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition-all placeholder:text-gray-700">
            </div>
            <div class="space-y-2">
              <label for="email" class="text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Email Address</label>
              <input type="email" name="email" id="email" required placeholder="email@example.com"
                     class="w-full bg-[#050505] border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition-all placeholder:text-gray-700">
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label for="phone" class="text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Phone Number</label>
              <input type="tel" name="phone" id="phone" placeholder="09XX XXX XXXX"
                     class="w-full bg-[#050505] border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition-all placeholder:text-gray-700">
            </div>
            <div class="flex items-end">
              <button type="submit" id="submitBtn" 
                      style="background-color: #f97316; color: #000;"
                      class="w-full font-black py-4 rounded-2xl uppercase tracking-widest text-[10px] hover:opacity-90 transition-all shadow-[0_10px_30px_rgba(249,115,22,0.4)] block">
                Secure Your Spot Now
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <script>
    document.getElementById('leadCaptureForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Processing...';

        const formData = new FormData(this);
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (response.ok) {
                // Lead captured successfully, redirect to payment
                window.location.href = result.redirect;
            } else {
                alert(result.message || 'An error occurred.');
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

  <footer class="bg-[#080808] border-t border-white/5 py-12 text-center">
      <p class="text-xs text-gray-500 uppercase tracking-widest">
        &copy; 2026 NEXTGEN CONQUERORS | Powering the Future of Network Marketing.
      </p>
  </footer>
</body>
</html>