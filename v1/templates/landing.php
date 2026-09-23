<?php
$savedCookieTheme = $_COOKIE['softpay_theme'] ?? 'light';
if (!in_array($savedCookieTheme, ['light', 'dark', 'system'])) {
    $savedCookieTheme = 'light';
}

$appName = app_config('app.name', 'SoftPay');
$fullTitle = 'SoftPay - India’s Premier High-Yield Digital Savings | Earn 0.88% – 1.00% Daily Interest';
$metaDescription = "SoftPay delivers clean, automated compounding savings. Earn 0.88% to 1.00% daily interest with instant NPCI UPI 2.0 deposits, zero lock-in, 24/7 withdrawals, and biometric vault security.";
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($savedCookieTheme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?php echo $fullTitle; ?></title>
  <meta name="description" content="<?php echo $metaDescription; ?>">
  <meta name="keywords" content="SoftPay, digital savings, daily interest, daily interest, high yield savings, upi deposit, 0.88% daily interest, fintech india">
  
  <!-- 0ms Immediate Theme Blocker Script -->
  <script>
    (function() {
      try {
        const saved = localStorage.getItem('softpay_theme') || (document.cookie.match(/(?:^|; )softpay_theme=([^;]*)/) || [])[1];
        if (saved === 'dark' || saved === 'light') {
          document.documentElement.setAttribute('data-theme', saved);
        } else if (saved === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
          document.documentElement.setAttribute('data-theme', 'dark');
        }
      } catch (e) {}
    })();
  </script>

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="shortcut icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="apple-touch-icon" href="<?php echo asset('images/favicon.png'); ?>">

  <link rel="stylesheet" href="<?php echo asset('css/tokens.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/root.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/paytm_theme.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/landing.css'); ?>">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<div class="landing-clean-wrapper">

  <!-- 1. Top Clean Navigation -->
  <header class="clean-nav">
    <div class="clean-nav-inner">
      <a href="<?php echo url('landing'); ?>" class="clean-brand">
        <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay Logo">
        <span class="clean-brand-name">Soft<span class="cyan-highlight">Pay</span></span>
      </a>

      <ul class="clean-nav-menu">
        <li class="clean-nav-item"><a href="#simulator">Daily Interest 0.88% – 1.0%</a></li>
        <li class="clean-nav-item"><a href="#what-matters">How It Works</a></li>
        <li class="clean-nav-item"><a href="#why-softpay">Why SoftPay</a></li>
        <li class="clean-nav-item"><a href="#growth">Insights</a></li>
        <li class="clean-nav-item"><a href="#comparison">Bank Comparison</a></li>
        <li class="clean-nav-item"><a href="#directory">Directory</a></li>
      </ul>

      <div class="clean-nav-actions">
        <!-- Theme Toggle (Hidden as per user request) -->
        <button type="button" class="icon-btn theme-toggle-btn" id="themeToggleBtn" title="Toggle Theme" style="display: none !important; border-radius: 50%; width: 40px; height: 40px;">
          <span class="theme-icon-container">
            <svg class="theme-sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg class="theme-moon-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
          </span>
        </button>

        <a href="<?php echo url('login'); ?>" class="clean-pill-btn outline" style="white-space: nowrap;">
          <?php echo svg_icon('user', '', 15); ?>
          <span>Sign In</span>
        </a>

        <a href="<?php echo url('signup'); ?>" class="clean-pill-btn primary" style="white-space: nowrap;">
          <span>Open Account</span>
        </a>
      </div>
    </div>
  </header>

  <!-- 2. Hero Section (Mobile-Optimized & Ultra-Clear) -->
  <section class="clean-hero-banner">
    <div class="hero-top-nav-strip">
      <div class="hero-category-title">
        Savings at SoftPay
      </div>
      <div class="hero-sub-links">
        <a href="#simulator">Daily Interest</a>
        <a href="#how-it-works">How it Works</a>
        <a href="#what-matters">Safety & Vault</a>
        <a href="#benefits">VIP Tier</a>
        <a href="#comparison">Bank vs SoftPay</a>
      </div>
    </div>

    <div class="hero-center-content">
      <!-- High-Trust Eyebrow Badge -->
      <div class="hero-badge-pill">
        <span class="badge-pulse-dot"></span>
        <span>0.88% – 1.00% Daily Interest • 100% Safe UPI 2.0</span>
      </div>

      <h1 class="hero-headline-large">
        Earn <span class="cyan-highlight">0.88% to 1.00%</span><br>
        Daily Interest on Your Savings
      </h1>

      <p class="hero-subhead-text">
        Turn idle cash into guaranteed daily returns. Automated midnight credit, zero lock-in period, aur kabhi bhi instant UPI withdrawal.
      </p>

      <!-- 3 Key Feature Chips on Mobile & Desktop -->
      <div class="hero-feature-chips">
        <div class="hero-feat-chip">
          <span class="feat-icon">⚡</span>
          <span>Daily Midnight Credit</span>
        </div>
        <div class="hero-feat-chip">
          <span class="feat-icon">🛡️</span>
          <span>NPCI UPI 2.0 Verified</span>
        </div>
        <div class="hero-feat-chip">
          <span class="feat-icon">💸</span>
          <span>Instant 24/7 UPI Payout</span>
        </div>
      </div>

      <!-- Quick Interactive Earnings Preview Card for Mobile -->
      <div class="hero-quick-calc-card">
        <div class="quick-calc-header">
          <div class="d-flex align-items-center gap-2">
            <span class="quick-calc-icon">₹</span>
            <div style="text-align: left;">
              <div class="quick-calc-title">Instant Earnings Calculator</div>
              <div class="quick-calc-sub">Select amount to preview daily returns</div>
            </div>
          </div>
          <span class="badge badge-success quick-calc-badge">Live Rate</span>
        </div>

        <!-- Quick Amount Pills -->
        <div class="quick-calc-chips">
          <button type="button" class="quick-chip" data-amt="500">₹500</button>
          <button type="button" class="quick-chip active" data-amt="1000">₹1,000</button>
          <button type="button" class="quick-chip" data-amt="2500">₹2,500</button>
          <button type="button" class="quick-chip vip-chip" data-amt="5000">₹5,000 (VIP 1%)</button>
          <button type="button" class="quick-chip" data-amt="10000">₹10,000</button>
        </div>

        <!-- Quick Result Matrix -->
        <div class="quick-calc-results">
          <div class="quick-res-item">
            <span class="quick-res-label">Daily Interest</span>
            <span class="quick-res-val highlight-green" id="heroQuickDaily">₹8.80</span>
            <span class="quick-res-sub">Every 24 Hours</span>
          </div>
          <div class="quick-res-divider"></div>
          <div class="quick-res-item">
            <span class="quick-res-label">Monthly Return</span>
            <span class="quick-res-val highlight-cyan" id="heroQuickMonthly">₹264.00</span>
            <span class="quick-res-sub">30 Days Gain</span>
          </div>
          <div class="quick-res-divider"></div>
          <div class="quick-res-item">
            <span class="quick-res-label">vs Bank (3% / yr)</span>
            <span class="quick-res-val highlight-gold" id="heroQuickCompare">10x More</span>
            <span class="quick-res-sub">High-Yield Return</span>
          </div>
        </div>

        <a href="<?php echo url('signup', ['amount' => 1000]); ?>" id="heroQuickCtaBtn" class="hero-quick-cta">
          <span>Start Saving ₹1,000 & Earn Daily Interest</span>
          <span class="cta-arrow">›</span>
        </a>
      </div>

      <!-- Action Buttons -->
      <div class="hero-pill-actions mt-4">
        <a href="<?php echo url('signup'); ?>" class="hero-white-pill">
          <span>Open Free Account (₹100 Min)</span>
          <span style="font-size: 1.2rem; line-height: 1;">›</span>
        </a>
        <a href="#simulator" class="hero-ghost-pill">
          <span>Full Return Simulator</span>
          <span style="font-size: 1.2rem; line-height: 1;">›</span>
        </a>
      </div>

      <!-- Social Proof Strip -->
      <div class="hero-trust-proof-strip">
        <div class="trust-stat"><strong>12,400+</strong> Active Savers</div>
        <div class="trust-stat-dot">•</div>
        <div class="trust-stat"><strong>₹1.4 Cr+</strong> Deposits Managed</div>
        <div class="trust-stat-dot">•</div>
        <div class="trust-stat"><strong>4.9★</strong> User Rating</div>
      </div>
    </div>
  </section>

  <!-- 3. Section 1: Make everyday extraordinary (Reference Image 2) -->
  <section class="clean-section-center">
    <span class="section-eyebrow">Smart Savings</span>

    <h2 class="clean-big-title">
      Make everyday <span class="cyan-highlight">extraordinary</span>
    </h2>

    <p class="clean-lead-para">
      Join forces with the leaders who continue to transform India’s digital savings and compounding landscape. Turn idle cash into guaranteed daily returns every single morning.
    </p>

    <div>
      <a href="#simulator" class="clean-pill-btn outline" style="padding: 10px 26px; font-size: 0.95rem;">
        <span>View All Savings Plans</span>
        <span style="font-size: 1.2rem; line-height: 1;">›</span>
      </a>
    </div>
  </section>

  <!-- 3.5. Mobile-First Quick 3-Step Guide (How It Works) -->
  <section class="how-it-works-section" id="how-it-works">
    <div class="text-center">
      <span class="section-eyebrow">Simple & Transparent</span>
      <h2 class="clean-big-title" style="font-size: 2.3rem;">Get Started in 3 Simple Steps</h2>
      <p class="clean-lead-para">Start earning compounding interest in under 60 seconds with zero paperwork.</p>
    </div>

    <div class="steps-grid">
      <!-- Step 1 -->
      <div class="step-card">
        <div class="step-badge-top">Step 1</div>
        <div class="step-icon-box">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <div class="step-content">
          <h3 class="step-title">1. Create Free Account</h3>
          <p class="step-desc">Sign up with your mobile number in seconds with instant dynamic SMS OTP verification.</p>
          <span class="step-pill">10 Seconds Sign Up</span>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="step-card">
        <div class="step-badge-top">Step 2</div>
        <div class="step-icon-box" style="background: rgba(0, 176, 116, 0.12); color: #00b074;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
        <div class="step-content">
          <h3 class="step-title">2. Add Money via UPI</h3>
          <p class="step-desc">Add minimum ₹100 using PhonePe, GPay, Paytm, or any UPI app. 100% zero fees with instant credit.</p>
          <span class="step-pill" style="background: rgba(0, 176, 116, 0.12); color: #00b074;">Min ₹100 Only</span>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="step-card">
        <div class="step-badge-top">Step 3</div>
        <div class="step-icon-box" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
        <div class="step-content">
          <h3 class="step-title">3. Earn Daily Interest</h3>
          <p class="step-desc">Daily 0.88% – 1.00% interest auto-credited at 00:00 AM midnight. Instant UPI & Bank withdrawals anytime.</p>
          <span class="step-pill" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">Daily Auto-Credit</span>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. Section 2: What matters to you, matters to us. (Reference Images 2 & 3) -->
  <section class="matters-section" id="what-matters">
    <div class="text-center">
      <h2 class="clean-big-title" style="font-size: 2.5rem;">
        What matters to you, <span class="cyan-highlight">matters to us.</span>
      </h2>
      <p style="font-size: 1.05rem; color: var(--lp-text-muted); margin-top: 8px;">
        Designed from the ground up for transparency, sub-second speed, and unbreakable capital security.
      </p>
    </div>

    <!-- 2x2 Clean Visual Card Grid -->
    <div class="matters-grid">
      <!-- Card 1 -->
      <div class="matters-card">
        <img src="<?php echo asset('images/matters_interest.jpg'); ?>" alt="Daily Interest Accrual" class="matters-card-bg-img">
        <div class="matters-card-overlay matters-overlay-1"></div>
        <span class="matters-card-pill">⚡ 00:00 AM Daily Accrual</span>
        <div class="matters-card-play-btn">
          <?php echo svg_icon('trending', '', 24); ?>
        </div>
        <div class="matters-card-bottom">
          <div class="matters-card-title">How 0.88% – 1.00% daily interest works</div>
          <div class="matters-card-sub">Automatic server accrual depositing cash returns into your wallet every 24 hours.</div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="matters-card">
        <img src="<?php echo asset('images/matters_qr_upi.jpg'); ?>" alt="UPI QR Deposits" class="matters-card-bg-img">
        <div class="matters-card-overlay matters-overlay-2"></div>
        <span class="matters-card-pill">🛡️ NPCI UPI 2.0</span>
        <div class="matters-card-play-btn">
          <?php echo svg_icon('deposit', '', 24); ?>
        </div>
        <div class="matters-card-bottom">
          <div class="matters-card-title">Instant dynamic QR deposits with 0 fees</div>
          <div class="matters-card-sub">Pay via Paytm, Google Pay, PhonePe, or BHIM with sub-second 12-digit UTR validation.</div>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="matters-card">
        <img src="<?php echo asset('images/matters_security.jpg'); ?>" alt="Biometric PIN & Pattern Security" class="matters-card-bg-img">
        <div class="matters-card-overlay matters-overlay-3"></div>
        <span class="matters-card-pill">🔒 Biometric Shield</span>
        <div class="matters-card-play-btn">
          <?php echo svg_icon('shield-check', '', 24); ?>
        </div>
        <div class="matters-card-bottom">
          <div class="matters-card-title">4-Digit PIN & 3x3 Pattern Screen Lock</div>
          <div class="matters-card-sub">Zero sensitive data or balances exposed without matching your secret hardware gesture.</div>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="matters-card">
        <img src="<?php echo asset('images/matters_withdraw.jpg'); ?>" alt="24/7 Bank & UPI Withdrawals" class="matters-card-bg-img">
        <div class="matters-card-overlay matters-overlay-4"></div>
        <span class="matters-card-pill">🏛️ 24/7 IMPS Switch</span>
        <div class="matters-card-play-btn">
          <?php echo svg_icon('withdraw', '', 24); ?>
        </div>
        <div class="matters-card-bottom">
          <div class="matters-card-title">Instant on-demand bank & UPI withdrawals</div>
          <div class="matters-card-sub">Fast liquid cashouts directly to your registered bank account or UPI VPA anytime.</div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. Section 3: Where savings meets compounding (Reference Image 4) -->
  <section class="purpose-section" id="why-softpay">
    <div class="purpose-inner">
      <div class="text-center">
        <span class="section-eyebrow">Life at SoftPay</span>

        <h2 class="clean-big-title">
          Where savings <span class="cyan-highlight">meets compounding</span>
        </h2>

        <p class="clean-lead-para">
          Saving doesn't feel like a sacrifice when you're supported by an autonomous high-yield engine, fuelled by daily liquid payouts, and empowered by remarkable benefits.
        </p>
      </div>

      <!-- 3-Column Card Grid with Bottom Overlay and '+' Button -->
      <div class="purpose-grid">
        <!-- Card 1: What you will earn -->
        <div class="purpose-card">
          <div class="purpose-card-visual">
            <img src="<?php echo asset('images/wealth_growth.jpg'); ?>" alt="What you will earn" class="purpose-card-img">
            <div style="position: absolute; top: 20px; left: 22px; font-size: 2.6rem; opacity: 0.35; font-weight: 900; color: #fff; z-index: 2;">01</div>
            <div style="position: absolute; top: 24px; right: 20px; background: rgba(0, 23, 61, 0.72); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.25); padding: 5px 12px; border-radius: 9999px; font-size: 0.78rem; font-weight: 800; color: #fff; z-index: 2;">
              Up to 1.00% / Day
            </div>
          </div>
          <div class="purpose-card-overlay">
            <div class="purpose-card-title">
              What you <br>will earn
            </div>
            <a href="#simulator" class="purpose-plus-btn" title="Calculate Returns">+</a>
          </div>
        </div>

        <!-- Card 2: Who you save with -->
        <div class="purpose-card">
          <div class="purpose-card-visual">
            <img src="<?php echo asset('images/team_savers.jpg'); ?>" alt="Who you save with" class="purpose-card-img">
            <div style="position: absolute; top: 20px; left: 22px; font-size: 2.6rem; opacity: 0.35; font-weight: 900; color: #fff; z-index: 2;">02</div>
            <div style="position: absolute; top: 24px; right: 20px; background: rgba(0, 23, 61, 0.72); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.25); padding: 5px 12px; border-radius: 9999px; font-size: 0.78rem; font-weight: 800; color: #fff; z-index: 2;">
              12,400+ Savers
            </div>
          </div>
          <div class="purpose-card-overlay">
            <div class="purpose-card-title">
              Who you <br>save with
            </div>
            <a href="#what-matters" class="purpose-plus-btn" title="Discover Security">+</a>
          </div>
        </div>

        <!-- Card 3: What you can achieve -->
        <div class="purpose-card">
          <div class="purpose-card-visual purpose-card-visual-3">
            <div class="vault-glow-orb"></div>
            <div style="position: absolute; top: 20px; left: 22px; font-size: 2.6rem; opacity: 0.35; font-weight: 900; color: #fff; z-index: 2;">03</div>
            <div style="position: absolute; top: 24px; right: 20px; background: rgba(0, 23, 61, 0.72); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.25); padding: 5px 12px; border-radius: 9999px; font-size: 0.78rem; font-weight: 800; color: #fff; z-index: 2;">
              320%+ Compounding
            </div>
            <div class="purpose-card-visual-inner">
              <div class="vault-3d-emblem">
                <svg width="62" height="62" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="color: #38bdf8; filter: drop-shadow(0 0 16px rgba(56,189,248,0.6));">
                  <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                  <path d="M2 17l10 5 10-5"></path>
                  <path d="M2 12l10 5 10-5"></path>
                </svg>
                <div style="font-size: 0.85rem; font-weight: 800; color: #ffffff; margin-top: 10px; letter-spacing: 0.05em; text-transform: uppercase;">Self-Multiplying Vault</div>
              </div>
            </div>
          </div>
          <div class="purpose-card-overlay">
            <div class="purpose-card-title">
              What you <br>can achieve
            </div>
            <a href="<?php echo url('signup'); ?>" class="purpose-plus-btn" title="Open Account">+</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. Section 4: Limitless Compounding Bento Grid (Reference Image 1 - New) -->
  <section class="bento-insights-section" id="growth">
    <div class="text-center">
      <span class="section-eyebrow">Our Growth & Proof</span>
      <h2 class="clean-big-title">
        Limitless compounding. <span class="cyan-highlight">Unstoppable growth.</span>
      </h2>
      <p class="clean-lead-para">
        Join us on a journey of automated financial growth and fulfilment as we revolutionize digital savings in India.
      </p>
    </div>

    <div class="bento-grid-layout">
      <!-- Big Hero Card on Left with Authentic Generated Image -->
      <div class="bento-hero-card">
        <img src="<?php echo asset('images/bento_hero.jpg'); ?>" alt="SoftPay Institutional Trust" class="bento-hero-bg-img">
        <div class="bento-hero-overlay"></div>
        <div class="bento-hero-content">
          <div class="d-flex justify-content-end align-items-start">
            <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); padding: 5px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,0.18);">
              <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
            </div>
          </div>

          <div style="text-align: center; margin: 36px 0;">
            <div style="background: rgba(255,255,255,0.12); border: 1.5px solid rgba(255,255,255,0.35); backdrop-filter: blur(12px); border-radius: 20px; padding: 24px; display: inline-block; max-width: 320px; box-shadow: 0 12px 36px rgba(0,0,0,0.25);">
              <div style="font-size: 0.82rem; opacity: 0.88; margin-bottom: 4px;">Made UPI Deposit using SoftPay</div>
              <div style="font-size: 2.2rem; font-weight: 900; letter-spacing: -0.02em;">₹10,000</div>
              <div style="font-size: 0.78rem; color: #86efac; font-weight: 800; margin-top: 4px;">● Auto-Accruing ₹100.00 Daily</div>
            </div>
          </div>

          <div>
            <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.85; margin-bottom: 4px;">Regulatory Compliance</div>
            <div style="font-size: 1.35rem; font-weight: 900; line-height: 1.3;">SoftPay is NPCI UPI 2.0 Verified & Bank-Grade Encrypted</div>
          </div>
        </div>
      </div>

      <!-- 3 Stacked Cards on Right -->
      <div class="bento-stacked-cards">
        <div class="bento-mini-card">
          <div class="bento-thumb-box bento-thumb-1">
            <?php echo svg_icon('trending', '', 26); ?>
          </div>
          <div>
            <div class="bento-tag">Interest Growth</div>
            <div class="bento-title">How 1.00% Daily Compounding Beats Traditional Fixed Deposits 10x</div>
          </div>
        </div>

        <div class="bento-mini-card">
          <div class="bento-thumb-box bento-thumb-2">
            <?php echo svg_icon('shield-check', '', 26); ?>
          </div>
          <div>
            <div class="bento-tag">Security & Vault</div>
            <div class="bento-title">Behind the 3x3 Pattern & 4-Digit PIN Vault: Zero Account Leakage</div>
          </div>
        </div>

        <div class="bento-mini-card">
          <div class="bento-thumb-box bento-thumb-3">
            <?php echo svg_icon('gift', '', 26); ?>
          </div>
          <div>
            <div class="bento-tag">Milestone</div>
            <div class="bento-title">SoftPay Surpasses ₹1.4 Crore in Active Deposits Across 12,400+ Savers</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 7. Section 5: We're invested in your success (Reference Image 5) -->
  <section class="benefits-split-section" id="benefits">
    <!-- Left: Returns Calculator -->
    <div class="benefits-calc-card" id="simulator">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="badge badge-success" style="font-size: 0.72rem; padding: 4px 10px;">⚡ High-Yield Simulator</span>
        <span style="font-size: 0.8rem; color: var(--lp-text-muted); font-weight: 700;">Zero Fees</span>
      </div>

      <h3 style="font-size: 1.6rem; font-weight: 900; color: var(--lp-text-title); margin: 6px 0 8px 0;">
        Calculate Your Daily Cash Returns
      </h3>
      <p style="font-size: 0.88rem; color: var(--lp-text-muted); margin-bottom: 20px;">
        Choose your deposit amount to see immediate daily interest, monthly income, and yearly compounded return.
      </p>

      <!-- Preset Chips -->
      <div class="calc-chips-flex">
        <button type="button" class="clean-chip" data-val="1000">₹1,000</button>
        <button type="button" class="clean-chip" data-val="2500">₹2,500</button>
        <button type="button" class="clean-chip" data-val="5000">₹5,000 (VIP 1.0%)</button>
        <button type="button" class="clean-chip active" data-val="10000">₹10,000</button>
        <button type="button" class="clean-chip" data-val="25000">₹25,000</button>
        <button type="button" class="clean-chip" data-val="50000">₹50,000</button>
      </div>

      <!-- Custom Range Slider with Dynamic Filled Track & Tactile Stepper -->
      <div class="landing-slider-container">
        <div class="landing-slider-track-wrap">
          <input type="range" id="landingCalcRange" class="clean-slider" min="500" max="100000" step="500" value="10000" aria-label="Deposit amount simulator">
        </div>
        <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 0.82rem; font-weight: 700; color: var(--lp-text-muted);">
          <span class="slider-boundary-label">₹500 <small style="font-weight: 500; opacity: 0.8;">(Min)</small></span>
          <div class="landing-slider-value-pill">
            <button type="button" class="slider-step-btn" id="landingStepMinus" aria-label="Decrease amount" title="Decrease ₹500">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
            <span style="color: var(--lp-navy); font-size: 1.25rem; font-weight: 900; letter-spacing: -0.02em;" id="landingSelectedAmountDisplay">₹10,000</span>
            <button type="button" class="slider-step-btn" id="landingStepPlus" aria-label="Increase amount" title="Increase ₹500">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
          </div>
          <span class="slider-boundary-label">₹1,00,000 <small style="font-weight: 500; opacity: 0.8;">(Max)</small></span>
        </div>
      </div>

      <!-- 3-Box Results Grid -->
      <div class="calc-3boxes-grid">
        <div class="calc-mini-box" style="border-top: 3px solid var(--lp-cyan);">
          <div class="calc-mini-label">Rate Plan</div>
          <div class="calc-mini-val" id="landingRateDisplay" style="color: var(--lp-cyan);">1.00% / d</div>
          <span class="badge badge-vip" id="landingTierBadge" style="font-size: 0.65rem; margin-top: 4px;">Business VIP</span>
        </div>

        <div class="calc-mini-box" style="border-top: 3px solid var(--lp-green);">
          <div class="calc-mini-label">Daily Interest</div>
          <div class="calc-mini-val" id="landingDailyReturn" style="color: var(--lp-green);">₹100.00</div>
          <span style="font-size: 0.72rem; color: var(--lp-text-muted);">Every 24h</span>
        </div>

        <div class="calc-mini-box" style="border-top: 3px solid #8b5cf6;">
          <div class="calc-mini-label">Monthly Gain</div>
          <div class="calc-mini-val" id="landingMonthlyReturn" style="color: #8b5cf6;">₹3,000.00</div>
          <span style="font-size: 0.72rem; color: var(--lp-text-muted);">30 Days</span>
        </div>
      </div>

      <!-- SoftPay vs Bank Contrast Strip -->
      <div style="background: var(--lp-bg-subtle); border-radius: 14px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; border: 1px solid var(--lp-border);">
        <div>
          <div style="font-size: 0.74rem; color: var(--lp-text-muted); font-weight: 700;">Traditional Bank (3.5% / yr)</div>
          <div style="color: var(--danger); font-weight: 800; font-size: 0.92rem;" id="calcBankCompDisplay">₹350.00 / Year</div>
        </div>
        <div style="text-align: right;">
          <div style="font-size: 0.74rem; color: var(--lp-text-muted); font-weight: 700;">SoftPay Compounded</div>
          <div style="color: var(--lp-green); font-weight: 900; font-size: 1.05rem;" id="calcSoftPayCompDisplay">₹36,500.00 / Year</div>
        </div>
      </div>

      <!-- Deposit Action Button -->
      <div class="text-center">
        <a href="<?php echo url('signup'); ?>&amount=10000" id="landingCalcDepositBtn" class="clean-pill-btn primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 1rem; border-radius: 14px; background: linear-gradient(135deg, #00b074 0%, #059669 100%);">
          👉 Deposit ₹10,000 & Start Earning ₹100.00 / Day Now
        </a>
      </div>
    </div>

    <!-- Right: Editorial Benefits & Expandable Accordions -->
    <div>
      <span class="section-eyebrow">Benefits</span>

      <h2 class="clean-big-title" style="font-size: 2.6rem; text-align: left; margin: 6px 0 16px 0;">
        We’re invested in your <span class="cyan-highlight">success</span>
      </h2>

      <p style="font-size: 1.05rem; line-height: 1.6; color: var(--lp-text-body); margin-bottom: 24px;">
        The financial well-being and growth of our savers matters to us. Our compounding benefits are designed to support savers across their different financial milestones and unlock true monetary freedom.
      </p>

      <!-- Expandable Accordions -->
      <div>
        <div class="clean-accordion-item active">
          <button type="button" class="clean-accordion-header">
            <span>Automated Daily Midnight Accrual</span>
            <span class="clean-accordion-chevron">⌄</span>
          </button>
          <div class="clean-accordion-body">
            Never wait 90 days for quarterly bank interest. At 00:00:00 AM IST every single night, 0.88% (or 1.00% VIP) interest is credited directly to your withdrawable ledger balance.
          </div>
        </div>

        <div class="clean-accordion-item">
          <button type="button" class="clean-accordion-header">
            <span>Zero Minimum Balance Penalties</span>
            <span class="clean-accordion-chevron">⌄</span>
          </button>
          <div class="clean-accordion-body">
            Unlike commercial banks that slap ₹500 penalties for falling below average monthly balance, SoftPay allows you to start from just ₹100 with zero charges forever.
          </div>
        </div>

        <div class="clean-accordion-item">
          <button type="button" class="clean-accordion-header">
            <span>Biometric PIN & 3x3 Pattern Vault</span>
            <span class="clean-accordion-chevron">⌄</span>
          </button>
          <div class="clean-accordion-body">
            Protect your financial privacy with full-screen hardware-grade session lockout. Without inputting your correct 4-digit PIN or 3x3 touch pattern, all account data remains completely hidden.
          </div>
        </div>

        <div class="clean-accordion-item">
          <button type="button" class="clean-accordion-header">
            <span>5% Instant Direct Referral Bonus</span>
            <span class="clean-accordion-chevron">⌄</span>
          </button>
          <div class="clean-accordion-body">
            Earn instant cash commissions whenever friends or family deposit using your personal invite link, credited directly into your active balance with immediate withdrawal access.
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 8. Section 6: Vibrant Cyan Banner (Reference Image 2 - New) -->
  <section class="cyan-promo-banner">
    <div style="max-width: 860px; margin: 0 auto; position: relative; z-index: 2;">
      <h2 class="cyan-promo-title">
        Looking for a savings platform <br>that’ll value your potential? <br>Look no further.
      </h2>
      <a href="<?php echo url('signup'); ?>" class="hero-white-pill" style="padding: 14px 38px; font-size: 1.05rem;">
        <span>Start Saving Today</span>
        <span style="font-size: 1.2rem; line-height: 1;">›</span>
      </a>
    </div>
  </section>

  <!-- 9. Section 7: Traditional Bank vs SoftPay Comparison Matrix -->
  <section class="clean-compare-section" id="comparison">
    <div class="text-center mb-4">
      <span class="section-eyebrow">Clear Comparison</span>
      <h2 class="clean-big-title" style="font-size: 2.4rem;">Traditional Bank vs SoftPay</h2>
      <p style="font-size: 1.05rem; color: var(--lp-text-muted);">See why smart Indian savers are moving their idle funds to SoftPay.</p>
    </div>

    <div class="compare-table-responsive">
      <table class="clean-compare-table">
        <thead>
          <tr style="background: var(--lp-bg-subtle);">
            <th>Feature</th>
            <th style="color: var(--lp-text-muted);">Traditional Bank Savings</th>
            <th class="softpay-highlight-col">SoftPay Digital Savings</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Annual Interest Return</strong></td>
            <td style="color: var(--danger); font-weight: 700;">2.70% – 3.50% / Year</td>
            <td class="softpay-highlight-col" style="color: var(--lp-green); font-size: 1.05rem;"><strong>320%+ Compounded / Year</strong></td>
          </tr>
          <tr>
            <td><strong>Payout Schedule</strong></td>
            <td>Quarterly (Every 90 Days)</td>
            <td class="softpay-highlight-col"><strong>Daily Automated (Every 24 Hours)</strong></td>
          </tr>
          <tr>
            <td><strong>Minimum Balance Requirement</strong></td>
            <td>₹1,000 – ₹10,000 (Monthly penalties)</td>
            <td class="softpay-highlight-col"><strong>₹100 (Zero Minimum Penalty)</strong></td>
          </tr>
          <tr>
            <td><strong>Business VIP Higher Yield</strong></td>
            <td>Only for ₹50L+ HNI deposits</td>
            <td class="softpay-highlight-col"><strong>1.00% Daily on ₹5,000+ deposits</strong></td>
          </tr>
          <tr>
            <td><strong>Withdrawal Liquidity</strong></td>
            <td>Banking hours & NEFT delays</td>
            <td class="softpay-highlight-col"><strong>Instant 24/7 UPI & Bank Payout</strong></td>
          </tr>
          <tr>
            <td><strong>Biometric Screen Lockout</strong></td>
            <td>Basic SMS OTP (SIM swap risk)</td>
            <td class="softpay-highlight-col"><strong>Biometric 4-Digit PIN & 3x3 Pattern Lock</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- 10. Section 8: Expandable Directory Footer (Reference Images 2 & 3 - New) -->
  <footer class="directory-footer-section" id="directory">
    <div class="directory-container">
      <!-- Accordion Rows (Paytm Official Style) -->
      <div class="directory-category-row active">
        <button type="button" class="directory-category-header">
          <span class="directory-plus-icon">+</span>
          <span>Digital Savings & Daily Interest</span>
        </button>
        <div class="directory-links-panel">
          <a href="#simulator" class="directory-link">Daily 0.88% Base Tier</a>
          <a href="#simulator" class="directory-link">Daily 1.00% Business VIP</a>
          <a href="#simulator" class="directory-link">Returns Simulator</a>
          <a href="<?php echo url('signup'); ?>" class="directory-link">Open High-Yield Account</a>
          <a href="#comparison" class="directory-link">Bank Comparison</a>
        </div>
      </div>

      <div class="directory-category-row">
        <button type="button" class="directory-category-header">
          <span class="directory-plus-icon">+</span>
          <span>Security & Biometric Screen Vault</span>
        </button>
        <div class="directory-links-panel">
          <a href="#what-matters" class="directory-link">4-Digit Hardware PIN</a>
          <a href="#what-matters" class="directory-link">3x3 Pattern Lockout</a>
          <a href="#what-matters" class="directory-link">AES-256 Cryptographic Vault</a>
          <a href="#what-matters" class="directory-link">Session Isolation Protocol</a>
        </div>
      </div>

      <div class="directory-category-row">
        <button type="button" class="directory-category-header">
          <span class="directory-plus-icon">+</span>
          <span>Payments & UPI 2.0 Infrastructure</span>
        </button>
        <div class="directory-links-panel">
          <a href="#what-matters" class="directory-link">Dynamic QR Code</a>
          <a href="#what-matters" class="directory-link">NPCI UPI 2.0 Settlement</a>
          <a href="#what-matters" class="directory-link">12-Digit UTR Sync</a>
          <a href="#what-matters" class="directory-link">Instant 24/7 IMPS Payouts</a>
        </div>
      </div>

      <div class="directory-category-row">
        <button type="button" class="directory-category-header">
          <span class="directory-plus-icon">+</span>
          <span>Company & Regulatory Compliance</span>
        </button>
        <div class="directory-links-panel">
          <a href="<?php echo asset('sitemap.xml'); ?>" class="directory-link">XML Sitemap</a>
          <a href="<?php echo asset('robots.txt'); ?>" class="directory-link">Robots Directive</a>
          <a href="mailto:support@softpay.com" class="directory-link">Corporate Support</a>
          <a href="<?php echo url('login'); ?>" class="directory-link">Member Portal</a>
        </div>
      </div>

      <!-- Bottom Bar with Social Media Icons -->
      <div class="directory-bottom-bar">
        <div>
          &copy; <?php echo date('Y'); ?> SoftPay Technologies India. All rights reserved. Built for secure high-yield wealth generation.
        </div>
        <div class="d-flex align-items-center gap-3">
          <span>Follow Us:</span>
          <div class="social-icons-row">
            <a href="javascript:void(0);" class="social-circle-btn" title="Facebook">f</a>
            <a href="javascript:void(0);" class="social-circle-btn" title="X / Twitter">𝕏</a>
            <a href="javascript:void(0);" class="social-circle-btn" title="YouTube">▶</a>
            <a href="javascript:void(0);" class="social-circle-btn" title="LinkedIn">in</a>
            <a href="javascript:void(0);" class="social-circle-btn" title="Instagram">ig</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Floating Sticky Bottom CTA for Mobile Devices -->
  <div class="mobile-sticky-bottom-bar">
    <div class="mobile-sticky-info">
      <span class="mobile-sticky-title">0.88% – 1.00% Daily Interest</span>
      <span class="mobile-sticky-sub">Start with min ₹100 • 100% Safe</span>
    </div>
    <a href="<?php echo url('signup'); ?>" class="mobile-sticky-btn">
      Open Account ›
    </a>
  </div>

  <!-- Bottom Accent Strip -->
  <div class="bottom-cyan-accent-strip"></div>

</div>

<!-- Interactive Engine Logic Script -->
<script>
$(document).ready(function() {
  function formatINR(val) {
    return '₹' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // 1. Hero Quick Instant Calculator
  function updateHeroQuickCalc(amt) {
    amt = Number(amt) || 1000;
    const isVip = amt >= 5000;
    const rate = isVip ? 1.00 : 0.88;
    const daily = (amt * rate) / 100;
    const monthly = daily * 30;

    $('#heroQuickDaily').text(formatINR(daily));
    $('#heroQuickMonthly').text(formatINR(monthly));
    $('#heroQuickCompare').text(isVip ? '12x More' : '10x More');

    const formattedAmt = '₹' + Number(amt).toLocaleString('en-IN');
    $('#heroQuickCtaBtn').html('<span>Start Saving ' + formattedAmt + ' & Earn Daily Interest</span><span class="cta-arrow">›</span>');
    $('#heroQuickCtaBtn').attr('href', 'index.php?page=signup&amount=' + amt);
  }

  $('.quick-chip').on('click', function() {
    const amt = Number($(this).data('amt'));
    $('.quick-chip').removeClass('active');
    $(this).addClass('active');
    updateHeroQuickCalc(amt);

    // Sync lower calculator
    $('#landingCalcRange').val(amt);
    $('.clean-chip').removeClass('active');
    $(`.clean-chip[data-val="${amt}"]`).addClass('active');
    updateLandingCalc(amt);
  });

  // 2. Full Wealth Simulator Engine with Dynamic Progress Fill
  function updateSliderFill(amount) {
    const slider = document.getElementById('landingCalcRange');
    if (!slider) return;

    const min = Number(slider.min) || 500;
    const max = Number(slider.max) || 100000;
    const clamped = Math.max(min, Math.min(max, Number(amount) || min));
    const pct = Math.max(0, Math.min(100, ((clamped - min) / (max - min)) * 100));

    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const emptyTrack = isDark ? 'rgba(255, 255, 255, 0.12)' : '#e2e8f0';
    const isVip = clamped >= 5000;

    const endColor = isVip ? '#00b074' : '#0099e6';

    slider.style.setProperty('--slider-fill-pct', pct + '%');
    slider.style.background = `linear-gradient(to right, #00BAF2 0%, ${endColor} ${pct}%, ${emptyTrack} ${pct}%, ${emptyTrack} 100%)`;

    if (isVip) {
      slider.classList.add('vip-active');
    } else {
      slider.classList.remove('vip-active');
    }
  }

  function updateLandingCalc(amount) {
    amount = Math.max(500, Math.min(100000, Number(amount) || 500));
    const isVip = amount >= 5000;
    const ratePct = isVip ? 1.00 : 0.88;
    const dailyReturn = (amount * ratePct) / 100;
    const monthlyReturn = dailyReturn * 30;
    const yearlyReturn = dailyReturn * 365;

    // Traditional Bank Comparison (3.5% annually)
    const bankYearlyReturn = (amount * 3.5) / 100;

    $('#landingSelectedAmountDisplay').text('₹' + Number(amount).toLocaleString('en-IN'));
    $('#landingRateDisplay').text(ratePct.toFixed(2) + '% / d');
    $('#landingDailyReturn').text(formatINR(dailyReturn));
    $('#landingMonthlyReturn').text(formatINR(monthlyReturn));

    $('#calcBankCompDisplay').text(formatINR(bankYearlyReturn) + ' / Year');
    $('#calcSoftPayCompDisplay').text(formatINR(yearlyReturn) + ' / Year');

    if (isVip) {
      $('#landingTierBadge').removeClass('badge-info').addClass('badge-vip').text('Business VIP (1.0%)');
    } else {
      $('#landingTierBadge').removeClass('badge-vip').addClass('badge-info').text('Standard (0.88%)');
    }

    $('#landingCalcDepositBtn').text('👉 Deposit ₹' + Number(amount).toLocaleString('en-IN') + ' & Start Earning ' + formatINR(dailyReturn) + ' / Day Now');
    $('#landingCalcDepositBtn').attr('href', 'index.php?page=signup&amount=' + amount);

    // Synchronously fill track behind thumb
    updateSliderFill(amount);
  }

  // Real-time Slider Dragging
  $('#landingCalcRange').on('input change', function() {
    const val = Number($(this).val());
    $('.clean-chip').removeClass('active');
    $(`.clean-chip[data-val="${val}"]`).addClass('active');
    updateLandingCalc(val);

    // Sync hero quick chips if match
    $('.quick-chip').removeClass('active');
    $(`.quick-chip[data-amt="${val}"]`).addClass('active');
    updateHeroQuickCalc(val);
  });

  // Preset Chips
  $('.clean-chip').on('click', function() {
    const val = Number($(this).data('val'));
    $('.clean-chip').removeClass('active');
    $(this).addClass('active');
    $('#landingCalcRange').val(val);
    updateLandingCalc(val);

    // Sync hero quick chips
    $('.quick-chip').removeClass('active');
    $(`.quick-chip[data-amt="${val}"]`).addClass('active');
    updateHeroQuickCalc(val);
  });

  // Stepper Minus / Plus Buttons
  $('#landingStepMinus').on('click', function() {
    let cur = Number($('#landingCalcRange').val()) || 10000;
    let step = cur > 10000 ? 1000 : 500;
    let next = Math.max(500, cur - step);
    $('#landingCalcRange').val(next);
    $('.clean-chip').removeClass('active');
    $(`.clean-chip[data-val="${next}"]`).addClass('active');
    updateLandingCalc(next);

    $('.quick-chip').removeClass('active');
    $(`.quick-chip[data-amt="${next}"]`).addClass('active');
    updateHeroQuickCalc(next);
  });

  $('#landingStepPlus').on('click', function() {
    let cur = Number($('#landingCalcRange').val()) || 10000;
    let step = cur >= 10000 ? 1000 : 500;
    let next = Math.min(100000, cur + step);
    $('#landingCalcRange').val(next);
    $('.clean-chip').removeClass('active');
    $(`.clean-chip[data-val="${next}"]`).addClass('active');
    updateLandingCalc(next);

    $('.quick-chip').removeClass('active');
    $(`.quick-chip[data-amt="${next}"]`).addClass('active');
    updateHeroQuickCalc(next);
  });

  // Initialize Both Calculators
  updateHeroQuickCalc(1000);
  updateLandingCalc(10000);

  // Accordion Toggle
  $('.clean-accordion-header').on('click', function() {
    const item = $(this).closest('.clean-accordion-item');
    const wasActive = item.hasClass('active');
    $('.clean-accordion-item').removeClass('active');
    if (!wasActive) {
      item.addClass('active');
    }
  });

  // Directory Footer Accordion Toggle
  $('.directory-category-header').on('click', function() {
    const row = $(this).closest('.directory-category-row');
    row.toggleClass('active');
  });

  // Light / Dark Mode Toggle
  $('#themeToggleBtn').on('click', function() {
    const cur = document.documentElement.getAttribute('data-theme') || 'light';
    const next = (cur === 'dark') ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    try {
      localStorage.setItem('softpay_theme', next);
      document.cookie = "softpay_theme=" + next + "; path=/; max-age=31536000; SameSite=Lax";
    } catch(e) {}
    setTimeout(function() {
      const curAmt = Number($('#landingCalcRange').val()) || 10000;
      updateSliderFill(curAmt);
    }, 50);
  });
});
</script>

</body>
</html>