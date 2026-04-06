<?php
// Get referral parameter
$ref = isset($_GET['ref']) ? htmlspecialchars($_GET['ref'], ENT_QUOTES, 'UTF-8') : 'admin';
$paymentLink = "https://nextgenconquerors.com/process-payment.php?ref=" . $ref;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NextGen Conquerors — Plug & Play Business</title>
<meta name="description" content="Build your online business the Plug & Play way. Ready-to-sell products, automated funnel, and a proven system for only ₱1,998.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#fafafa;--fg:#1f1f1f;--card:#fff;--muted:#f7f5f3;--muted-fg:#5c5c5c;
  --primary:#ff6600;--primary-fg:#fff;--border:#ebebeb;
  --dark-bg:#1f1f1f;--dark-fg:#f5f5f5;
}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--fg);-webkit-font-smoothing:antialiased;line-height:1.5}
img{max-width:100%;display:block}
a{text-decoration:none;color:inherit}

.text-gradient{background:linear-gradient(135deg,#ff6600,#ff8533);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.btn-cta{display:inline-flex;align-items:center;gap:8px;justify-content:center;background:linear-gradient(135deg,#ff6600,#ff8533);color:#fff;font-weight:700;padding:14px 24px;border-radius:10px;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;transition:all .3s;box-shadow:0 20px 60px -15px rgba(255,102,0,.35);border:none;cursor:pointer;width:100%}
.btn-cta:hover{transform:translateY(-2px);box-shadow:0 25px 60px -15px rgba(255,102,0,.45)}
.card-float{box-shadow:0 25px 50px -12px rgba(0,0,0,.1)}
.section{padding:56px 16px}
.section-muted{background:var(--muted)}
.section-card{background:var(--card)}
.container{max-width:1100px;margin:0 auto}
.text-center{text-align:center}
h1{font-size:clamp(1.75rem,5vw,3rem);font-weight:800;line-height:1.15}
h2{font-size:clamp(1.5rem,4vw,2.25rem);font-weight:800;line-height:1.2}
.sub{color:var(--muted-fg);font-size:14px;max-width:600px;margin:0 auto}

/* Hero */
.hero{padding:24px 16px 48px;background:radial-gradient(ellipse 80% 50% at 70% -20%,rgba(255,102,0,.12),transparent),radial-gradient(ellipse 60% 40% at 0% 100%,rgba(255,102,0,.06),transparent),linear-gradient(180deg,#fff,#faf8f6)}
.hero .badge{display:inline-block;color:var(--primary);font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.2em;margin-bottom:16px}
.hero h1{max-width:700px;margin:0 auto 16px}
.hero .desc{color:var(--muted-fg);font-size:14px;max-width:520px;margin:0 auto 32px;line-height:1.7}

/* Video */
.video-wrap{border-radius:16px;overflow:hidden;border:1px solid var(--border);background:var(--fg);margin:0 auto 32px;max-width:640px}
.video-wrap .ratio{position:relative;width:100%;padding-top:56.25%}
.video-wrap iframe{position:absolute;inset:0;width:100%;height:100%;border:none}

/* Feature cards */
.feat-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:420px;margin:0 auto 12px}
.feat-single{max-width:420px;margin:0 auto 24px}
.feat-card{background:var(--card);border-radius:12px;padding:16px;border:1px solid var(--border);display:flex;flex-direction:column;align-items:center;text-align:center}
.feat-card .icon{width:48px;height:48px;border-radius:10px;background:rgba(255,102,0,.1);display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:8px}
.feat-card .title{font-weight:700;font-size:14px}
.feat-card .sub{font-size:12px;color:var(--muted-fg);margin-top:2px}

/* Pricing card */
.pricing{background:var(--dark-bg);border-radius:16px;padding:24px 32px;position:relative;overflow:hidden;max-width:420px;margin:0 auto 32px}
.pricing .glow{position:absolute;top:0;right:0;width:128px;height:128px;background:rgba(255,102,0,.2);border-radius:50%;filter:blur(48px);transform:translate(50%,-50%)}
.pricing .label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;color:var(--primary);margin-bottom:8px;position:relative}
.pricing .price{font-size:clamp(1.75rem,5vw,2.5rem);font-weight:800;color:var(--dark-fg);margin-bottom:4px;position:relative}
.pricing .note{font-size:14px;color:rgba(245,245,245,.7);margin-bottom:20px;position:relative}

/* Trust icons */
.trust{display:flex;flex-wrap:wrap;justify-content:center;gap:20px}
.trust-item{display:flex;flex-direction:column;align-items:center;width:72px;text-align:center}
.trust-item .circle{width:56px;height:56px;border-radius:50%;border:2px solid rgba(255,102,0,.3);display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:8px;background:var(--card)}
.trust-item span{font-size:10px;font-weight:700;line-height:1.3}

/* Plug & Play grid */
.pp-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
@media(min-width:640px){.pp-grid{grid-template-columns:repeat(3,1fr)}}
@media(min-width:1024px){.pp-grid{grid-template-columns:repeat(5,1fr)}}
.pp-card{background:var(--card);border-radius:12px;padding:20px;border:1px solid var(--border);text-align:center}
.pp-card .ic{width:48px;height:48px;margin:0 auto 12px;border-radius:10px;background:rgba(255,102,0,.1);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:20px}
.pp-card h3{font-weight:700;font-size:14px;margin-bottom:4px}
.pp-card p{font-size:12px;color:var(--muted-fg);line-height:1.5}

/* Earning */
.earn-grid{display:grid;gap:40px;align-items:center}
@media(min-width:768px){.earn-grid{grid-template-columns:1fr 1fr}}
.earn-badge{display:inline-block;padding:8px 16px;border-radius:8px;background:var(--primary);color:#fff;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:16px}
.check-list{list-style:none;display:flex;flex-direction:column;gap:16px}
.check-list li{display:flex;gap:12px;align-items:flex-start;font-size:14px;color:var(--muted-fg)}
.check-list li .ck{flex-shrink:0;width:24px;height:24px;border-radius:50%;background:#22c55e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;margin-top:2px}
.check-list strong{color:var(--fg)}
.earn-visual{border-radius:16px;overflow:hidden;border:1px solid rgba(255,102,0,.1);background:#FFF9F1;padding:12px}
.earn-visual-inner{border-radius:12px;min-height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px;background:linear-gradient(to bottom right,rgba(255,237,213,.8),rgba(254,243,199,.8))}
.earn-visual .big-icon{width:80px;height:80px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;margin-bottom:16px;box-shadow:0 20px 60px -15px rgba(255,102,0,.35)}

/* Steps */
.steps-grid{display:grid;gap:32px;max-width:800px;margin:0 auto}
@media(min-width:768px){.steps-grid{grid-template-columns:repeat(3,1fr);gap:16px}}
.step{text-align:center;padding:0 16px}
.step .num{width:64px;height:64px;margin:0 auto 16px;border-radius:16px;background:var(--card);border:2px solid var(--primary);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800}
.step .label{color:var(--primary);font-weight:800;font-size:13px;margin-bottom:8px}
.step h3{font-weight:700;margin-bottom:8px}
.step p{font-size:14px;color:var(--muted-fg);line-height:1.6}

/* Why different */
.why-list{list-style:none;max-width:520px;margin:0 auto;display:flex;flex-direction:column;gap:16px;text-align:left}
.why-list li{display:flex;gap:12px;align-items:flex-start;font-size:14px}
.why-list .dot{flex-shrink:0;width:20px;height:20px;color:#22c55e;margin-top:2px}

/* Legal */
.legal-grid{display:grid;gap:16px}
@media(min-width:768px){.legal-grid{grid-template-columns:repeat(3,1fr)}}
.legal-card{border-radius:12px;border:1px solid var(--border);background:var(--muted);padding:24px 32px;text-align:center}
.legal-card .ic{width:56px;height:56px;margin:0 auto 16px;border-radius:12px;background:rgba(255,102,0,.1);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:22px}
.legal-card h3{font-weight:700;margin-bottom:8px}
.legal-card p{font-size:14px;color:var(--muted-fg)}

/* Testimonials */
.testi-grid{display:grid;gap:24px}
@media(min-width:768px){.testi-grid{grid-template-columns:repeat(3,1fr)}}
.testi{text-align:center;padding:0 16px}
.testi .avatar{width:80px;height:80px;margin:0 auto 16px;border-radius:50%;background:linear-gradient(to bottom right,#ff6600,#ea580c);color:#fff;font-size:24px;font-weight:800;display:flex;align-items:center;justify-content:center;border:4px solid #ffedd5}
.testi .name{font-weight:700;margin-bottom:8px}
.testi .quote{font-size:14px;color:var(--muted-fg);font-style:italic;line-height:1.6}

/* FAQ */
.faq-item{border:1px solid var(--border);border-radius:12px;background:var(--muted);overflow:hidden;margin-bottom:12px}
.faq-q{width:100%;background:none;border:none;padding:16px 20px;font-family:inherit;font-size:14px;font-weight:600;text-align:left;cursor:pointer;display:flex;justify-content:space-between;align-items:center;color:var(--fg)}
.faq-q:hover{background:var(--card)}
.faq-a{padding:0 20px 16px;font-size:14px;color:var(--muted-fg);border-top:1px solid var(--border);display:none}
.faq-item.open .faq-a{display:block;padding-top:12px}
.faq-item.open .faq-q .arrow{transform:rotate(180deg)}
.faq-q .arrow{transition:transform .2s}

/* Final CTA */
.final-cta{background:var(--primary);color:#fff;padding:56px 16px;text-align:center}
.final-cta h2{color:#fff;max-width:640px;margin:0 auto 24px}
.final-cta .btn-inv{display:inline-flex;align-items:center;gap:8px;padding:16px 32px;border-radius:10px;background:var(--fg);color:var(--card);font-weight:700;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;transition:opacity .2s}
.final-cta .btn-inv:hover{opacity:.9}
.final-cta .meta{display:flex;flex-wrap:wrap;justify-content:center;gap:24px;margin-top:32px;font-size:12px;font-weight:600;opacity:.9}
.final-cta .disc{font-size:10px;opacity:.6;max-width:520px;margin:32px auto 0;line-height:1.6}

/* Sticky CTA */
.sticky-cta{position:fixed;bottom:0;left:0;right:0;z-index:50;padding:8px 16px 16px;background:linear-gradient(to top,var(--bg),var(--bg),transparent)}
@media(min-width:640px){.sticky-cta{display:none}}

/* Passion */
.divider{height:4px;width:96px;margin:12px auto 0;border-radius:4px;background:linear-gradient(to right,transparent,var(--primary),transparent)}
</style>
</head>
<body>

<!-- HERO -->
<section class="hero">
<div class="container text-center">
  <p class="badge">Simple system. Real opportunity. Unlimited potential.</p>
  <h1>Build Your Online Business the <span class="text-gradient">Plug &amp; Play</span> Way.</h1>
  <p class="desc">Get ready-to-sell products, an automated funnel, and a proven system—no tech skills required.</p>

  <div class="video-wrap card-float">
    <div class="ratio">
      <iframe src="https://www.youtube-nocookie.com/embed/bhhlKxUxKAE?rel=0&modestbranding=1" title="Business overview" allowfullscreen loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
    </div>
  </div>

  <div class="feat-grid">
    <div class="feat-card card-float"><div class="icon">📦</div><div class="title">Digital Products</div><div class="sub">Ready to promote</div></div>
    <div class="feat-card card-float"><div class="icon">⚡</div><div class="title">Done-For-You Funnel</div><div class="sub">Pages that convert</div></div>
  </div>
  <div class="feat-single">
    <div class="feat-card card-float"><div class="icon">📘</div><div class="title">Step-By-Step Guide</div><div class="sub">Follow the system and take action</div></div>
  </div>

  <div class="pricing">
    <div class="glow"></div>
    <p class="label">One-time access</p>
    <p class="price">₱1,998</p>
    <p class="note">Secure your slot. Start today.</p>
    <a href="<?php echo $paymentLink; ?>" class="btn-cta">Get Started Now →</a>
  </div>

  <div class="trust">
    <div class="trust-item"><div class="circle">💰</div><span>Low Capital</span></div>
    <div class="trust-item"><div class="circle">🔧</div><span>Done-For-You</span></div>
    <div class="trust-item"><div class="circle">📈</div><span>Unlimited Potential</span></div>
    <div class="trust-item"><div class="circle">🌱</div><span>Beginner Friendly</span></div>
  </div>
</div>
</section>

<!-- PLUG & PLAY -->
<section class="section section-muted" style="border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
<div class="container">
  <h2 class="text-center" style="margin-bottom:8px">Plug &amp; Play Program</h2>
  <p class="sub text-center" style="margin-bottom:40px">Everything you need is ready. You just plug in and start.</p>
  <div class="pp-grid">
    <div class="pp-card card-float"><div class="ic">🌐</div><h3>Automated Sales Funnel</h3><p>A ready-made website that works while you focus on sharing.</p></div>
    <div class="pp-card card-float"><div class="ic">📦</div><h3>Ready-to-Sell Digital Products</h3><p>High-quality offers you can promote with confidence.</p></div>
    <div class="pp-card card-float"><div class="ic">📖</div><h3>Step-By-Step Guidance</h3><p>Clear training so you always know the next step.</p></div>
    <div class="pp-card card-float"><div class="ic">👥</div><h3>Team Support System</h3><p>Community and upline support when you need it.</p></div>
    <div class="pp-card card-float"><div class="ic">💳</div><h3>Low Capital Entry</h3><p>Start without a heavy upfront investment.</p></div>
  </div>
</div>
</section>

<!-- EARNING -->
<section class="section section-card">
<div class="container">
  <div class="earn-grid">
    <div class="earn-visual card-float" style="order:2">
      <div class="earn-visual-inner">
        <div class="big-icon">📱</div>
        <p style="font-size:14px;font-weight:600;color:var(--muted-fg);text-align:center">Share your link. Grow your income.</p>
      </div>
    </div>
    <div style="order:1">
      <div class="earn-badge">Unlimited ₱500 Pairing Bonus</div>
      <h2 style="margin-bottom:24px">Earn Through a Simple System.</h2>
      <ul class="check-list">
        <li><span class="ck">✓</span><span><strong>₱500 Pairing Bonus</strong> — unlimited pairs, unlimited earnings.</span></li>
        <li><span class="ck">✓</span><span><strong>No Flush Out</strong> — your points carry over and never expire.</span></li>
        <li><span class="ck">✓</span><span><strong>5th Pair Bonus</strong> — earn extra rewards on every 5th pair match.</span></li>
        <li><span class="ck">✓</span><span><strong>Automated Spillover</strong> — your upline's recruits fill your downline automatically.</span></li>
        <li><span class="ck">✓</span><span><strong>No limit</strong> on how many people you can invite.</span></li>
      </ul>
    </div>
  </div>
</div>
</section>

<!-- STEPS -->
<section class="section section-muted">
<div class="container">
  <h2 class="text-center" style="margin-bottom:40px">3 Simple Steps</h2>
  <div class="steps-grid">
    <div class="step"><div class="num">1</div><div class="label">Step 1</div><h3>Join the Program</h3><p>Secure your slot and get full access to the system.</p></div>
    <div class="step"><div class="num">2</div><div class="label">Step 2</div><h3>Activate Your Tools</h3><p>Use the ready-made funnel and follow the guide.</p></div>
    <div class="step"><div class="num">3</div><div class="label">Step 3</div><h3>Share &amp; Earn</h3><p>Invite others with your link and build your income.</p></div>
  </div>
</div>
</section>

<!-- WHY DIFFERENT -->
<section class="section section-card">
<div class="container">
  <h2 class="text-center" style="margin-bottom:32px">Not Just Another Online Opportunity.</h2>
  <ul class="why-list">
    <li><span class="dot">✔</span><span><strong>A working model</strong> — not theory. A clear path from join to share.</span></li>
    <li><span class="dot">✔</span><span><strong>A complete setup</strong> — funnel, products, and guidance in one place.</span></li>
    <li><span class="dot">✔</span><span><strong>A supportive community</strong> — team and upline when you need help.</span></li>
  </ul>
  <p class="sub text-center" style="margin-top:32px">No guesswork. No overwhelm. Just follow the system and take action.</p>
</div>
</section>

<!-- LEGAL -->
<section class="section section-card">
<div class="container">
  <h2 class="text-center" style="margin-bottom:8px">Legalities &amp; FDA Compliance</h2>
  <p class="sub text-center" style="margin-bottom:40px">Your safety is our priority. Elevate Well products are manufactured under strict quality control and aligned with regulatory requirements.</p>
  <div class="legal-grid">
    <div class="legal-card"><div class="ic">🛡️</div><h3>FDA Registered</h3><p>Verified compliance with national health standards with CPR.</p></div>
    <div class="legal-card"><div class="ic">🏆</div><h3>GMP Certified</h3><p>Manufactured in a Good Manufacturing Practice facility.</p></div>
    <div class="legal-card"><div class="ic">🧪</div><h3>Lab Tested</h3><p>Third-party tested for purity and potency.</p></div>
  </div>
</div>
</section>

<!-- PASSION -->
<section class="section section-muted" style="border-top:1px solid var(--border)">
<div class="container text-center">
  <h2>Where <span class="text-gradient">Passion Meets Opportunity</span> Worldwide</h2>
  <p class="sub" style="margin-top:24px">Watch the overview above — then take the next step when you're ready.</p>
  <p style="margin-top:32px;font-size:14px;font-weight:500;color:var(--muted-fg);font-style:italic">Elevate Well Worldwide Main Office</p>
  <div class="divider"></div>
</div>
</section>

<!-- TESTIMONIALS -->
<section class="section section-muted">
<div class="container">
  <h2 class="text-center" style="margin-bottom:40px">Real People. Real Results.</h2>
  <div class="testi-grid">
    <div class="testi"><div class="avatar">M</div><div class="name">Michelle D.</div><div class="quote">"The system is straightforward—I didn't need to be 'techy' to get started."</div></div>
    <div class="testi"><div class="avatar">J</div><div class="name">James L.</div><div class="quote">"Having the funnel ready saved me time. I could focus on sharing and learning."</div></div>
    <div class="testi"><div class="avatar">C</div><div class="name">Carla M.</div><div class="quote">"Support from my upline made the difference when I had questions."</div></div>
  </div>
</div>
</section>

<!-- FAQ -->
<section class="section section-card">
<div class="container" style="max-width:640px">
  <h3 class="text-center" style="font-weight:700;font-size:18px;margin-bottom:24px">Quick Answers</h3>
  <div class="faq-item">
    <button class="faq-q" onclick="this.parentElement.classList.toggle('open')">Do I need experience? <span class="arrow">▼</span></button>
    <div class="faq-a">No. The program is built for beginners, with step-by-step guidance and a done-for-you funnel.</div>
  </div>
  <div class="faq-item">
    <button class="faq-q" onclick="this.parentElement.classList.toggle('open')">What's included in ₱1,998? <span class="arrow">▼</span></button>
    <div class="faq-a">Access to the system, products, and training. Shipping may apply separately—see checkout for current fees.</div>
  </div>
  <div class="faq-item">
    <button class="faq-q" onclick="this.parentElement.classList.toggle('open')">How do I get paid for referrals? <span class="arrow">▼</span></button>
    <div class="faq-a">Compensation follows your team's compensation plan. Your sponsor can explain the details for your account.</div>
  </div>
</div>
</section>

<!-- FINAL CTA -->
<section class="final-cta">
  <h2>Ready to start your Plug &amp; Play business? Join today and get full access for only ₱1,998.</h2>
  <a href="<?php echo $paymentLink; ?>" class="btn-inv">Join Now →</a>
  <div class="meta">
    <span>🔒 One-time payment</span>
    <span>♾️ Lifetime access</span>
    <span>⚡ Start immediately</span>
  </div>
  <p class="disc">Income is not guaranteed; it depends on individual effort, activity, and market factors. This page is for informational purposes and does not constitute a promise of earnings.</p>
</section>

<!-- STICKY CTA (mobile) -->
<div class="sticky-cta">
  <a href="<?php echo $paymentLink; ?>" class="btn-cta">GET STARTED — ₱1,998 →</a>
</div>

<div style="height:72px"></div>
</body>
</html>
