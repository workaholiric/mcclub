<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$is_included = str_contains($_SERVER['SCRIPT_NAME'], 'referral-router.php') || !str_contains($_SERVER['SCRIPT_NAME'], 'business-plan.php');
$path_prefix = "/";

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/ngc_site_settings.php';

$bp_video_compliance_id = ngc_youtube_id_from_input(ngc_site_setting_get($pdo, 'business_plan_video_compliance'));
$bp_video_passion_id = ngc_youtube_id_from_input(ngc_site_setting_get($pdo, 'business_plan_video_passion'));
$bp_video_earn_id = ngc_youtube_id_from_input(ngc_site_setting_get($pdo, 'business_plan_video_earn'));

$referrer_name = "Admin";
$referrer_id = isset($_COOKIE['mclub_referrer']) ? (int)$_COOKIE['mclub_referrer'] : null;

if ($referrer_id) {
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$referrer_id]);
    $user = $stmt->fetch();
    if ($user) {
        $referrer_name = $user['full_name'];
    }
}

$ref_user = isset($_GET['user']) ? (string) $_GET['user'] : '';
$checkout_href = '/process-payment.php' . ($ref_user !== '' ? '?ref=' . rawurlencode($ref_user) : '');

/** Passion video in hero when set; otherwise show placeholder. Avoid duplicate embed below. */
$passion_in_hero = ($bp_video_passion_id !== '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Plan — NEXTGEN CONQUERORS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              ngc: { orange: '#FF6B00', dark: '#1e1e1e', muted: '#5c5c5c', cream: '#FAFAFA' }
            },
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] }
          }
        }
      }
    </script>
    <style>
      html { scroll-behavior: smooth; }
      .bp-orange-glow { box-shadow: 0 20px 60px -15px rgba(255, 107, 0, 0.35); }
      .bp-card-float { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12); }
      .bp-hero-mesh {
        background:
          radial-gradient(ellipse 80% 50% at 70% -20%, rgba(255, 107, 0, 0.15), transparent),
          radial-gradient(ellipse 60% 40% at 0% 100%, rgba(255, 107, 0, 0.08), transparent),
          linear-gradient(180deg, #ffffff 0%, #FAFAFA 100%);
      }
      .bp-mockup-box {
        background: linear-gradient(145deg, #fff 0%, #f3f4f6 100%);
        border: 1px solid rgba(0,0,0,0.06);
      }
    </style>
</head>
<body class="bg-white text-ngc-dark font-sans antialiased">

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-[100] bg-white/95 backdrop-blur-md border-b border-black/5">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 sm:h-[4.25rem] flex items-center justify-between gap-4">
            <a href="#" class="flex items-center gap-2 shrink-0">
                <img src="/newlogo.png" alt="NEXTGEN CONQUERORS" class="h-9 sm:h-10 w-auto object-contain" />
                <span class="hidden sm:block font-extrabold text-xs sm:text-sm tracking-tight text-ngc-dark leading-tight uppercase">
                    NextGen<br class="sm:hidden" /> Conquerors
                </span>
            </a>
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-ngc-muted">
                <a href="#business-plan" class="hover:text-ngc-orange transition-colors">Business Plan</a>
                <a href="#products" class="hover:text-ngc-orange transition-colors">Products</a>
                <a href="#how-it-works" class="hover:text-ngc-orange transition-colors">How It Works</a>
                <a href="#faq" class="hover:text-ngc-orange transition-colors">FAQ</a>
            </nav>
            <a href="<?php echo htmlspecialchars($checkout_href, ENT_QUOTES, 'UTF-8'); ?>"
               class="shrink-0 inline-flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-lg bg-ngc-orange text-white text-xs sm:text-sm font-bold uppercase tracking-wide hover:bg-orange-600 transition-colors bp-orange-glow">
                Get Started
            </a>
        </div>
    </header>

    <!-- Hero -->
    <section id="business-plan" class="bp-hero-mesh pt-24 sm:pt-28 pb-16 sm:pb-24 px-4 sm:px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-center text-ngc-orange font-bold text-xs sm:text-sm uppercase tracking-[0.2em] mb-4">
                Simple system. Real opportunity. Unlimited potential.
            </p>
            <h1 class="text-center text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-ngc-dark leading-tight max-w-4xl mx-auto mb-5">
                Build Your Online Business the <span class="text-ngc-orange">Plug &amp; Play</span> Way.
            </h1>
            <p class="text-center text-ngc-muted text-base sm:text-lg max-w-2xl mx-auto mb-12 leading-relaxed">
                Get ready-to-sell products, an automated funnel, and a proven system—no tech skills required. Your sponsor <strong class="text-ngc-dark"><?php echo htmlspecialchars($referrer_name, ENT_QUOTES, 'UTF-8'); ?></strong> is here to help you get started.
            </p>

            <div class="grid lg:grid-cols-2 gap-10 lg:gap-12 items-start">
                <!-- Hero video / visual -->
                <div class="relative order-2 lg:order-1">
                    <div class="rounded-2xl overflow-hidden border border-black/5 bg-ngc-dark bp-card-float">
                        <?php if ($passion_in_hero): ?>
                            <div class="relative w-full pt-[56.25%] bg-black">
                                <iframe class="absolute inset-0 w-full h-full"
                                    src="https://www.youtube-nocookie.com/embed/<?php echo htmlspecialchars($bp_video_passion_id, ENT_QUOTES, 'UTF-8'); ?>?rel=0&modestbranding=1"
                                    title="Business overview" allowfullscreen loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="aspect-video flex flex-col items-center justify-center bg-gradient-to-br from-zinc-800 to-zinc-900 text-white p-8">
                                <span class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mb-4 border border-white/20">
                                    <i class="fa-solid fa-play text-2xl text-ngc-orange pl-1"></i>
                                </span>
                                <p class="text-sm text-white/70 text-center font-medium">Your sponsor can share a walkthrough video from the team dashboard (Funnel → admin video).</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right column: mockups + price -->
                <div class="order-1 lg:order-2 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bp-mockup-box rounded-xl p-4 bp-card-float flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-lg bg-ngc-orange/10 text-ngc-orange flex items-center justify-center mb-3">
                                <i class="fa-solid fa-box-open text-xl"></i>
                            </div>
                            <p class="font-bold text-sm text-ngc-dark">Digital Products</p>
                            <p class="text-xs text-ngc-muted mt-1">Ready to promote</p>
                        </div>
                        <div class="bp-mockup-box rounded-xl p-4 bp-card-float flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-lg bg-ngc-orange/10 text-ngc-orange flex items-center justify-center mb-3">
                                <i class="fa-solid fa-funnel-dollar text-xl"></i>
                            </div>
                            <p class="font-bold text-sm text-ngc-dark">Done-For-You Funnel</p>
                            <p class="text-xs text-ngc-muted mt-1">Pages that convert</p>
                        </div>
                        <div class="bp-mockup-box rounded-xl p-4 bp-card-float flex flex-col items-center text-center sm:col-span-2">
                            <div class="w-12 h-12 rounded-lg bg-ngc-orange/10 text-ngc-orange flex items-center justify-center mb-3">
                                <i class="fa-solid fa-book-open text-xl"></i>
                            </div>
                            <p class="font-bold text-sm text-ngc-dark">Step-By-Step Guide</p>
                            <p class="text-xs text-ngc-muted mt-1">Follow the system and take action</p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-ngc-dark text-white p-6 sm:p-8 bp-card-float relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-ngc-orange/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                        <p class="text-xs font-bold uppercase tracking-widest text-ngc-orange mb-2">One-time access</p>
                        <p class="text-3xl sm:text-4xl font-extrabold mb-1">₱1,998</p>
                        <p class="text-sm text-white/70 mb-6">Secure your slot. Start today.</p>
                        <a href="<?php echo htmlspecialchars($checkout_href, ENT_QUOTES, 'UTF-8'); ?>"
                           class="inline-flex items-center gap-2 w-full sm:w-auto justify-center px-6 py-3.5 rounded-lg bg-ngc-orange text-white font-bold text-sm uppercase tracking-wide hover:bg-orange-600 transition-colors">
                            Get Started Now <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-6 pt-2">
                        <div class="flex flex-col items-center w-[4.5rem] text-center">
                            <div class="w-14 h-14 rounded-full border-2 border-ngc-orange/30 flex items-center justify-center text-ngc-orange mb-2">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                            <span class="text-[10px] font-bold text-ngc-dark leading-tight">Low Capital</span>
                        </div>
                        <div class="flex flex-col items-center w-[4.5rem] text-center">
                            <div class="w-14 h-14 rounded-full border-2 border-ngc-orange/30 flex items-center justify-center text-ngc-orange mb-2">
                                <i class="fa-solid fa-plug"></i>
                            </div>
                            <span class="text-[10px] font-bold text-ngc-dark leading-tight">Done-For-You</span>
                        </div>
                        <div class="flex flex-col items-center w-[4.5rem] text-center">
                            <div class="w-14 h-14 rounded-full border-2 border-ngc-orange/30 flex items-center justify-center text-ngc-orange mb-2">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <span class="text-[10px] font-bold text-ngc-dark leading-tight">Unlimited Potential</span>
                        </div>
                        <div class="flex flex-col items-center w-[4.5rem] text-center">
                            <div class="w-14 h-14 rounded-full border-2 border-ngc-orange/30 flex items-center justify-center text-ngc-orange mb-2">
                                <i class="fa-solid fa-seedling"></i>
                            </div>
                            <span class="text-[10px] font-bold text-ngc-dark leading-tight">Beginner Friendly</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Triple P -->
    <section id="products" class="py-16 sm:py-20 px-4 sm:px-6 bg-ngc-cream border-y border-black/5">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-ngc-dark mb-3">Plug &amp; Play Program</h2>
            <p class="text-ngc-muted max-w-xl mx-auto">Everything you need is ready. You just plug in and start.</p>
        </div>
        <div class="max-w-6xl mx-auto grid sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <?php
            $features = [
                ['icon' => 'fa-globe', 'title' => 'Automated Sales Funnel', 'desc' => 'A ready-made website that works while you focus on sharing.'],
                ['icon' => 'fa-download', 'title' => 'Ready-to-Sell Digital Products', 'desc' => 'High-quality offers you can promote with confidence.'],
                ['icon' => 'fa-route', 'title' => 'Step-By-Step Guidance', 'desc' => 'Clear training so you always know the next step.'],
                ['icon' => 'fa-people-group', 'title' => 'Team Support System', 'desc' => 'Community and upline support when you need it.'],
                ['icon' => 'fa-wallet', 'title' => 'Low Capital Entry', 'desc' => 'Start without a heavy upfront investment.'],
            ];
            foreach ($features as $f):
            ?>
            <div class="bg-white rounded-xl p-6 border border-black/5 bp-card-float text-center">
                <div class="w-12 h-12 mx-auto rounded-xl bg-ngc-orange/10 text-ngc-orange flex items-center justify-center mb-4">
                    <i class="fa-solid <?php echo $f['icon']; ?> text-lg"></i>
                </div>
                <h3 class="font-bold text-ngc-dark text-sm mb-2"><?php echo htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="text-xs text-ngc-muted leading-relaxed"><?php echo htmlspecialchars($f['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Earn -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 bg-white">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1 rounded-2xl overflow-hidden border border-ngc-orange/10 min-h-[200px] bg-[#FFF9F1] p-3 sm:p-4 bp-card-float">
                <?php if ($bp_video_earn_id !== ''): ?>
                    <?php echo ngc_youtube_embed_html($bp_video_earn_id); ?>
                <?php else: ?>
                    <div class="rounded-xl min-h-[240px] flex flex-col items-center justify-center p-8 bg-gradient-to-br from-orange-50/80 to-amber-50/80">
                        <div class="w-20 h-20 rounded-full bg-ngc-orange text-white flex items-center justify-center text-3xl mb-4 bp-orange-glow">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </div>
                        <p class="text-sm font-semibold text-ngc-muted text-center">Share your link. Grow your income.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="order-1 lg:order-2">
                <div class="inline-block px-4 py-2 rounded-lg bg-ngc-orange text-white text-xs font-extrabold uppercase tracking-wider mb-4">
                    Earn up to ₱500 per referral
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-ngc-dark mb-6">Earn Through a Simple System.</h2>
                <ul class="space-y-4">
                    <li class="flex gap-3">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-xs"><i class="fa-solid fa-check"></i></span>
                        <span class="text-ngc-muted">Earn up to <strong class="text-ngc-dark">₱500</strong> per successful referral.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-xs"><i class="fa-solid fa-check"></i></span>
                        <span class="text-ngc-muted">No limit on how many people you can invite.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-xs"><i class="fa-solid fa-check"></i></span>
                        <span class="text-ngc-muted">Works for part-time or full-time goals.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="py-16 sm:py-20 px-4 sm:px-6 bg-ngc-cream">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-ngc-dark">3 Simple Steps</h2>
        </div>
        <div class="max-w-5xl mx-auto grid md:grid-cols-3 gap-8 md:gap-4 relative">
            <div class="hidden md:block absolute top-16 left-[16%] right-[16%] h-0.5 bg-ngc-orange/20 -z-0"></div>
            <?php
            $steps = [
                ['n' => '1', 'icon' => 'fa-clipboard-check', 'title' => 'Join the Program', 'desc' => 'Secure your slot and get full access to the system.'],
                ['n' => '2', 'icon' => 'fa-plug-circle-bolt', 'title' => 'Activate Your Tools', 'desc' => 'Use the ready-made funnel and follow the guide.'],
                ['n' => '3', 'icon' => 'fa-share-nodes', 'title' => 'Share & Earn', 'desc' => 'Invite others with your link and build your income.'],
            ];
            foreach ($steps as $s):
            ?>
            <div class="relative z-10 text-center px-4">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-white border-2 border-ngc-orange text-ngc-orange flex items-center justify-center text-2xl mb-4 bp-card-float">
                    <i class="fa-solid <?php echo $s['icon']; ?>"></i>
                </div>
                <p class="text-ngc-orange font-extrabold text-sm mb-2">Step <?php echo $s['n']; ?></p>
                <h3 class="font-bold text-ngc-dark mb-2"><?php echo htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="text-sm text-ngc-muted leading-relaxed"><?php echo htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Differentiation -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 bg-white">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-ngc-dark mb-8">Not Just Another Online Opportunity.</h2>
            <ul class="text-left max-w-xl mx-auto space-y-4 mb-8">
                <li class="flex gap-3 items-start">
                    <i class="fa-solid fa-circle-check text-green-500 mt-1"></i>
                    <div>
                        <span class="font-bold text-ngc-dark">A working model</span>
                        <span class="text-ngc-muted"> — not theory. A clear path from join to share.</span>
                    </div>
                </li>
                <li class="flex gap-3 items-start">
                    <i class="fa-solid fa-circle-check text-green-500 mt-1"></i>
                    <div>
                        <span class="font-bold text-ngc-dark">A complete setup</span>
                        <span class="text-ngc-muted"> — funnel, products, and guidance in one place.</span>
                    </div>
                </li>
                <li class="flex gap-3 items-start">
                    <i class="fa-solid fa-circle-check text-green-500 mt-1"></i>
                    <div>
                        <span class="font-bold text-ngc-dark">A supportive community</span>
                        <span class="text-ngc-muted"> — team and upline when you need help.</span>
                    </div>
                </li>
            </ul>
            <p class="text-ngc-muted text-sm leading-relaxed">No guesswork. No overwhelm. Just follow the system and take action.</p>
        </div>
    </section>

    <?php if ($bp_video_compliance_id !== ''): ?>
    <section class="py-12 px-4 sm:px-6 bg-ngc-cream border-t border-black/5">
        <div class="max-w-4xl mx-auto">
            <?php echo ngc_youtube_embed_html($bp_video_compliance_id); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Compliance -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 bg-white">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-ngc-dark mb-3">Legalities &amp; FDA Compliance</h2>
                <p class="text-ngc-muted max-w-2xl mx-auto text-sm sm:text-base">Your safety is our priority. Elevate Well products are manufactured under strict quality control and aligned with regulatory requirements.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="rounded-xl border border-black/5 bg-ngc-cream p-8 text-center">
                    <div class="w-14 h-14 mx-auto rounded-xl bg-ngc-orange/10 text-ngc-orange flex items-center justify-center mb-4">
                        <i class="fa-solid fa-file-shield text-xl"></i>
                    </div>
                    <h3 class="font-bold text-ngc-dark mb-2">FDA Registered</h3>
                    <p class="text-sm text-ngc-muted">Verified compliance with national health standards with CPR.</p>
                </div>
                <div class="rounded-xl border border-black/5 bg-ngc-cream p-8 text-center">
                    <div class="w-14 h-14 mx-auto rounded-xl bg-ngc-orange/10 text-ngc-orange flex items-center justify-center mb-4">
                        <i class="fa-solid fa-industry text-xl"></i>
                    </div>
                    <h3 class="font-bold text-ngc-dark mb-2">GMP Certified</h3>
                    <p class="text-sm text-ngc-muted">Manufactured in a Good Manufacturing Practice facility.</p>
                </div>
                <div class="rounded-xl border border-black/5 bg-ngc-cream p-8 text-center">
                    <div class="w-14 h-14 mx-auto rounded-xl bg-ngc-orange/10 text-ngc-orange flex items-center justify-center mb-4">
                        <i class="fa-solid fa-flask text-xl"></i>
                    </div>
                    <h3 class="font-bold text-ngc-dark mb-2">Lab Tested</h3>
                    <p class="text-sm text-ngc-muted">Third-party tested for purity and potency.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Passion (headline + optional embed if not in hero) -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 bg-ngc-cream border-t border-black/5">
        <div class="max-w-5xl mx-auto text-center">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-ngc-dark mb-4">
                Where <span class="text-ngc-orange">Passion Meets Opportunity</span> Worldwide
            </h2>
            <?php if (!$passion_in_hero && $bp_video_passion_id !== ''): ?>
                <div class="mt-10 max-w-4xl mx-auto">
                    <?php echo ngc_youtube_embed_html($bp_video_passion_id); ?>
                </div>
            <?php elseif (!$passion_in_hero): ?>
                <div class="mt-10 rounded-2xl border border-black/5 bg-white aspect-video max-w-4xl mx-auto flex items-center justify-center text-ngc-muted text-sm px-6">
                    Video can be added by your admin under Funnel → Business plan videos.
                </div>
            <?php else: ?>
                <p class="mt-6 text-ngc-muted text-sm">Watch the overview above — then take the next step when you’re ready.</p>
            <?php endif; ?>
            <p class="mt-10 text-sm font-medium text-ngc-muted italic">Elevate Well Worldwide Main Office</p>
            <div class="h-1 w-24 mx-auto mt-3 bg-gradient-to-r from-transparent via-ngc-orange to-transparent rounded-full"></div>
        </div>
    </section>

    <!-- Testimonials / FAQ -->
    <section id="faq" class="py-16 sm:py-20 px-4 sm:px-6 bg-white">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-ngc-dark">Real People. Real Results.</h2>
        </div>
        <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-8">
            <?php
            $quotes = [
                ['initial' => 'M', 'name' => 'Michelle D.', 'text' => 'The system is straightforward—I didn’t need to be “techy” to get started.'],
                ['initial' => 'J', 'name' => 'James L.', 'text' => 'Having the funnel ready saved me time. I could focus on sharing and learning.'],
                ['initial' => 'C', 'name' => 'Carla M.', 'text' => 'Support from my upline made the difference when I had questions.'],
            ];
            foreach ($quotes as $q):
            ?>
            <div class="text-center px-4">
                <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-ngc-orange to-orange-600 text-white text-2xl font-extrabold flex items-center justify-center mb-4 border-4 border-orange-100">
                    <?php echo htmlspecialchars($q['initial'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <p class="font-bold text-ngc-dark mb-2"><?php echo htmlspecialchars($q['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="text-sm text-ngc-muted leading-relaxed italic">“<?php echo htmlspecialchars($q['text'], ENT_QUOTES, 'UTF-8'); ?>”</p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="max-w-2xl mx-auto mt-14 space-y-4">
            <h3 class="text-center font-bold text-ngc-dark mb-6">Quick answers</h3>
            <details class="group border border-black/5 rounded-xl bg-ngc-cream px-5 py-4 open:bg-white">
                <summary class="font-semibold text-ngc-dark cursor-pointer list-none flex justify-between items-center">
                    Do I need experience?
                    <i class="fa-solid fa-chevron-down text-ngc-orange group-open:rotate-180 transition-transform text-sm"></i>
                </summary>
                <p class="text-sm text-ngc-muted mt-3 pt-3 border-t border-black/5">No. The program is built for beginners, with step-by-step guidance and a done-for-you funnel.</p>
            </details>
            <details class="group border border-black/5 rounded-xl bg-ngc-cream px-5 py-4 open:bg-white">
                <summary class="font-semibold text-ngc-dark cursor-pointer list-none flex justify-between items-center">
                    What’s included in ₱1,998?
                    <i class="fa-solid fa-chevron-down text-ngc-orange group-open:rotate-180 transition-transform text-sm"></i>
                </summary>
                <p class="text-sm text-ngc-muted mt-3 pt-3 border-t border-black/5">Access to the system, products, and training. Shipping may apply separately—see checkout for current fees.</p>
            </details>
            <details class="group border border-black/5 rounded-xl bg-ngc-cream px-5 py-4 open:bg-white">
                <summary class="font-semibold text-ngc-dark cursor-pointer list-none flex justify-between items-center">
                    How do I get paid for referrals?
                    <i class="fa-solid fa-chevron-down text-ngc-orange group-open:rotate-180 transition-transform text-sm"></i>
                </summary>
                <p class="text-sm text-ngc-muted mt-3 pt-3 border-t border-black/5">Compensation follows your team’s compensation plan. Your sponsor can explain the details for your account.</p>
            </details>
        </div>
    </section>

    <!-- Footer CTA -->
    <section class="py-14 sm:py-16 px-4 sm:px-6 bg-ngc-orange text-white text-center">
        <h2 class="text-xl sm:text-2xl md:text-3xl font-extrabold max-w-3xl mx-auto mb-6 leading-snug">
            Ready to start your Plug &amp; Play business? Join today and get full access for only <span class="whitespace-nowrap">₱1,998</span>.
        </h2>
        <a href="<?php echo htmlspecialchars($checkout_href, ENT_QUOTES, 'UTF-8'); ?>"
           class="inline-flex items-center gap-2 px-8 py-4 rounded-lg bg-ngc-dark text-white font-bold text-sm uppercase tracking-wide hover:bg-zinc-800 transition-colors">
            Join Now <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
        <div class="flex flex-wrap justify-center gap-6 mt-10 text-xs font-semibold text-white/90">
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-lock"></i> One-time payment</span>
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-infinity"></i> Lifetime access</span>
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-bolt"></i> Start immediately</span>
        </div>
        <p class="text-[10px] sm:text-xs text-white/70 max-w-2xl mx-auto mt-10 leading-relaxed">
            Income is not guaranteed; it depends on individual effort, activity, and market factors. This page is for informational purposes and does not constitute a promise of earnings.
        </p>
    </section>

    <footer class="py-8 text-center text-ngc-muted text-xs border-t border-black/5 bg-ngc-cream">
        <a href="/login.php" class="inline-flex items-center gap-2 font-semibold text-ngc-dark hover:text-ngc-orange transition-colors mb-2">
            <i class="fa-solid fa-user"></i> Member Login
        </a>
        <p class="uppercase tracking-widest">&copy; <?php echo date('Y'); ?> NEXTGEN CONQUERORS · Powered by Elevate Well</p>
    </footer>

</body>
</html>
