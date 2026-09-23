<?php
$user = $user ?? auth_user();
$hasPin = !empty($user['pin_hash']);
$hasPattern = !empty($user['pattern_hash']);

// Default view: prioritize PIN if set, else Pattern
$initialMode = $hasPin ? 'pin' : 'pattern';

$savedCookieTheme = $_COOKIE['softpay_theme'] ?? 'light';
if (!in_array($savedCookieTheme, ['light', 'dark', 'system'])) {
    $savedCookieTheme = 'light';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($savedCookieTheme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
  <title>App Locked - <?php echo htmlspecialchars(app_config('app.name', 'SoftPay')); ?></title>
  <link rel="icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="shortcut icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="apple-touch-icon" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/tokens.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/root.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/paytm_theme.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/pattern_lock.css'); ?>">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    body {
      background: var(--bg-main);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .lock-screen-shell {
      width: 100%;
      max-width: 420px;
      margin: auto;
    }
    .lock-avatar-ring {
      width: 76px;
      height: 76px;
      border-radius: 50%;
      margin: 0 auto 12px auto;
      padding: 3px;
      background: linear-gradient(135deg, #002970 0%, #00BAF2 100%);
      box-shadow: 0 6px 20px rgba(0, 186, 242, 0.35);
      position: relative;
    }
    .lock-avatar-inner {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: var(--bg-card);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    .lock-badge-icon {
      position: absolute;
      bottom: -2px;
      right: -2px;
      width: 24px;
      height: 24px;
      background: #002970;
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid var(--bg-main);
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .lock-dots-row {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin: 20px 0 24px 0;
    }
    .lock-dot {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: var(--bg-main);
      border: 2px solid var(--border-color);
      transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .lock-dot.active {
      background: linear-gradient(135deg, #002970 0%, #00BAF2 100%);
      border-color: #00BAF2;
      transform: scale(1.25);
      box-shadow: 0 0 12px rgba(0, 186, 242, 0.6);
    }
    .lock-dot.success {
      background: #22c55e !important;
      border-color: #22c55e !important;
      box-shadow: 0 0 14px rgba(34, 197, 94, 0.8) !important;
    }
    .lock-dot.error {
      background: #ef4444 !important;
      border-color: #ef4444 !important;
      box-shadow: 0 0 14px rgba(239, 68, 68, 0.8) !important;
    }
    .lock-keypad-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px 18px;
      max-width: 280px;
      margin: 0 auto;
    }
    .lock-key {
      height: 60px;
      border-radius: 50%;
      border: 1px solid var(--border-color);
      background: var(--bg-card);
      color: var(--text-primary);
      font-size: 1.4rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: var(--shadow-sm);
      transition: all 0.12s ease;
      user-select: none;
      -webkit-tap-highlight-color: transparent;
    }
    .lock-key:hover {
      background: var(--primary-light);
      border-color: var(--secondary);
      transform: translateY(-2px);
    }
    .lock-key:active {
      transform: scale(0.92);
      background: var(--primary);
      color: #fff;
    }
    .lock-key.action-key {
      font-size: 1rem;
      color: var(--text-secondary);
      background: transparent;
      border-color: transparent;
      box-shadow: none;
    }
    .lock-key.action-key:hover {
      background: var(--bg-main);
      color: var(--text-primary);
    }
    @keyframes lockShake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-10px); }
      40%, 80% { transform: translateX(10px); }
    }
    .shake-animation {
      animation: lockShake 0.4s ease-in-out;
    }

    /* 3x3 Pattern Lock Dedicated Styles */
    .pattern-wrapper {
      position: relative;
      width: 250px;
      height: 250px;
      margin: 0 auto 12px auto;
      user-select: none;
      touch-action: none;
    }
    .pattern-svg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 2;
    }
    .pattern-line {
      stroke: #00BAF2;
      stroke-width: 4.5;
      stroke-linecap: round;
      stroke-linejoin: round;
      opacity: 0.9;
      filter: drop-shadow(0 0 4px rgba(0, 186, 242, 0.5));
    }
    .pattern-line.success {
      stroke: #22c55e !important;
      filter: drop-shadow(0 0 6px rgba(34, 197, 94, 0.7)) !important;
    }
    .pattern-line.error {
      stroke: #ef4444 !important;
      filter: drop-shadow(0 0 6px rgba(239, 68, 68, 0.7)) !important;
    }
    .pattern-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      grid-template-rows: repeat(3, 1fr);
      width: 100%;
      height: 100%;
      position: relative;
      z-index: 3;
    }
    .pattern-node {
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      -webkit-tap-highlight-color: transparent;
      user-select: none;
    }
    .pattern-dot {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--bg-card);
      border: 3px solid var(--border-color);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .pattern-dot::after {
      content: '';
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--text-muted);
      transition: all 0.18s ease;
    }
    .pattern-node:hover .pattern-dot {
      border-color: #00BAF2;
      transform: scale(1.1);
    }
    .pattern-node.selected .pattern-dot,
    .pattern-node.active .pattern-dot {
      background: linear-gradient(135deg, #002970 0%, #00BAF2 100%) !important;
      border-color: #00BAF2 !important;
      transform: scale(1.3);
      box-shadow: 0 0 16px rgba(0, 186, 242, 0.75) !important;
    }
    .pattern-node.selected .pattern-dot::after,
    .pattern-node.active .pattern-dot::after {
      background: #ffffff !important;
    }
    .pattern-node.success .pattern-dot {
      background: #22c55e !important;
      border-color: #16a34a !important;
      transform: scale(1.3);
      box-shadow: 0 0 16px rgba(34, 197, 94, 0.8) !important;
    }
    .pattern-node.success .pattern-dot::after {
      background: #ffffff !important;
    }
    .pattern-node.error .pattern-dot {
      background: #ef4444 !important;
      border-color: #991b1b !important;
      transform: scale(1.3);
      box-shadow: 0 0 16px rgba(239, 68, 68, 0.8) !important;
    }
    .pattern-node.error .pattern-dot::after {
      background: #ffffff !important;
    }
    [data-theme="dark"] .pattern-dot {
      background: #1e293b;
      border-color: #475569;
    }
    [data-theme="dark"] .pattern-dot::after {
      background: #94a3b8;
    }
    [data-theme="dark"] .pattern-node.selected .pattern-dot,
    [data-theme="dark"] .pattern-node.active .pattern-dot {
      background: #0284c7 !important;
      border-color: #38bdf8 !important;
    }
  </style>
</head>
<body>

<div class="lock-screen-shell">
  <!-- Brand & User Header -->
  <div class="text-center mb-3">
    <div class="lock-avatar-ring">
      <div class="lock-avatar-inner">
        <?php if (!empty($user['avatar'])): ?>
          <?php if (strpos($user['avatar'], '<svg') === 0): ?>
            <?php echo $user['avatar']; ?>
          <?php else: ?>
            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
          <?php endif; ?>
        <?php else: ?>
          <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay" style="width: 100%; height: 100%; object-fit: cover;">
        <?php endif; ?>
      </div>
      <div class="lock-badge-icon">
        <?php echo svg_icon('lock', '', 13); ?>
      </div>
    </div>

    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0 0 2px 0;">
      SoftPay App Locked
    </h2>
    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
      Welcome back, <strong><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></strong>
    </p>
  </div>

  <div class="card" style="box-shadow: 0 10px 30px rgba(0, 41, 112, 0.1); padding: 24px 20px;">
    <?php if ($hasPin && $hasPattern): ?>
      <!-- Dual Mode Switcher Tabs -->
      <div class="preset-chips justify-content-center mb-3">
        <button type="button" class="preset-chip <?php echo ($initialMode === 'pin') ? 'active' : ''; ?>" id="lockTabPin">
          <?php echo svg_icon('lock', '', 13); ?> 4-Digit PIN
        </button>
        <button type="button" class="preset-chip <?php echo ($initialMode === 'pattern') ? 'active' : ''; ?>" id="lockTabPattern">
          <?php echo svg_icon('sparkles', '', 13); ?> 3x3 Pattern
        </button>
      </div>
    <?php endif; ?>

    <!-- 1. PIN Keypad View -->
    <div id="lockPinSection" style="<?php echo ($initialMode === 'pin') ? 'display: block;' : 'display: none;'; ?>">
      <p style="font-size: 0.84rem; color: var(--text-secondary); text-align: center; margin-bottom: 4px;">
        Enter your 4-digit security PIN to unlock
      </p>

      <div class="lock-dots-row" id="lockDotsContainer">
        <div class="lock-dot" data-i="0"></div>
        <div class="lock-dot" data-i="1"></div>
        <div class="lock-dot" data-i="2"></div>
        <div class="lock-dot" data-i="3"></div>
      </div>

      <div class="lock-keypad-grid">
        <button type="button" class="lock-key" data-digit="1">1</button>
        <button type="button" class="lock-key" data-digit="2">2</button>
        <button type="button" class="lock-key" data-digit="3">3</button>
        <button type="button" class="lock-key" data-digit="4">4</button>
        <button type="button" class="lock-key" data-digit="5">5</button>
        <button type="button" class="lock-key" data-digit="6">6</button>
        <button type="button" class="lock-key" data-digit="7">7</button>
        <button type="button" class="lock-key" data-digit="8">8</button>
        <button type="button" class="lock-key" data-digit="9">9</button>
        <button type="button" class="lock-key action-key" id="lockClearBtn" title="Clear">C</button>
        <button type="button" class="lock-key" data-digit="0">0</button>
        <button type="button" class="lock-key action-key" id="lockBackBtn" title="Backspace">⌫</button>
      </div>
    </div>

    <!-- 2. Pattern Lock View -->
    <div id="lockPatternSection" style="<?php echo ($initialMode === 'pattern') ? 'display: block;' : 'display: none;'; ?>">
      <p style="font-size: 0.84rem; color: var(--text-secondary); text-align: center; margin-bottom: 12px;">
        Draw your 3x3 pattern to unlock
      </p>

      <div class="pattern-wrapper" id="lockPatternContainer" style="width: 240px; height: 240px; margin: 0 auto 12px auto;">
        <svg class="pattern-svg" id="lockPatternSvg">
          <polyline id="lockPatternPolyline" class="pattern-line" points="" />
        </svg>
        <div class="pattern-grid" id="lockPatternGrid">
          <div class="pattern-node" data-index="0"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="1"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="2"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="3"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="4"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="5"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="6"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="7"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="8"><div class="pattern-dot"></div></div>
        </div>
      </div>

      <div class="text-center">
        <button type="button" class="btn btn-sm btn-outline" id="lockPatternClearBtn" style="font-size: 0.76rem; padding: 4px 12px;">
          Clear Pattern
        </button>
      </div>
    </div>

    <!-- Feedback Banner -->
    <div id="lockFeedbackText" style="min-height: 24px; margin-top: 16px; text-align: center; font-size: 0.85rem; font-weight: 700;"></div>
  </div>

  <!-- Sign Out / Recovery Option -->
  <div class="text-center mt-3" style="font-size: 0.84rem; color: var(--text-muted);">
    Forgot PIN or Pattern? <a href="<?php echo url('logout'); ?>" style="color: var(--primary); font-weight: 600;">Sign Out & Login with Password</a>
  </div>
</div>

<script>
$(document).ready(function() {
  let enteredPin = '';
  let isSubmitting = false;

  // --- Tab Switcher ---
  $('#lockTabPin').on('click', function() {
    $(this).addClass('active');
    $('#lockTabPattern').removeClass('active');
    $('#lockPinSection').show();
    $('#lockPatternSection').hide();
    $('#lockFeedbackText').empty();
  });

  $('#lockTabPattern').on('click', function() {
    $(this).addClass('active');
    $('#lockTabPin').removeClass('active');
    $('#lockPatternSection').show();
    $('#lockPinSection').hide();
    $('#lockFeedbackText').empty();
  });

  // --- PIN Keypad Logic ---
  function updatePinDots() {
    $('#lockDotsContainer .lock-dot').each(function(index) {
      if (index < enteredPin.length) {
        $(this).addClass('active');
      } else {
        $(this).removeClass('active success error');
      }
    });

    if (enteredPin.length === 4 && !isSubmitting) {
      verifyPinSubmission(enteredPin);
    }
  }

  $('.lock-key[data-digit]').on('click', function() {
    if (enteredPin.length < 4 && !isSubmitting) {
      enteredPin += $(this).data('digit');
      if (window.navigator && window.navigator.vibrate) {
        window.navigator.vibrate(12);
      }
      updatePinDots();
    }
  });

  $('#lockClearBtn').on('click', function() {
    if (isSubmitting) return;
    enteredPin = '';
    updatePinDots();
    $('#lockFeedbackText').empty();
  });

  $('#lockBackBtn').on('click', function() {
    if (isSubmitting) return;
    if (enteredPin.length > 0) {
      enteredPin = enteredPin.slice(0, -1);
      updatePinDots();
      $('#lockFeedbackText').empty();
    }
  });

  // Physical keyboard support
  $(document).on('keydown', function(e) {
    if ($('#lockPinSection').is(':visible') && !isSubmitting) {
      if (e.key >= '0' && e.key <= '9' && enteredPin.length < 4) {
        enteredPin += e.key;
        updatePinDots();
      } else if (e.key === 'Backspace' && enteredPin.length > 0) {
        enteredPin = enteredPin.slice(0, -1);
        updatePinDots();
      } else if (e.key === 'Escape') {
        enteredPin = '';
        updatePinDots();
      }
    }
  });

  function verifyPinSubmission(pin) {
    isSubmitting = true;
    $('#lockFeedbackText').html('<span style="color: var(--secondary);">Verifying PIN...</span>');

    $.ajax({
      url: 'index.php?api=security',
      type: 'POST',
      data: { action: 'verify_pin', pin: pin },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#lockDotsContainer .lock-dot').addClass('success');
          $('#lockFeedbackText').html('<span style="color: var(--success);">✔ PIN Verified! Unlocking...</span>');
          if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate([20, 50, 20]);
          }
          setTimeout(function() {
            window.location.href = res.redirect || 'index.php?page=home';
          }, 450);
        } else {
          $('#lockDotsContainer').addClass('shake-animation');
          $('#lockDotsContainer .lock-dot').addClass('error');
          $('#lockFeedbackText').html('<span style="color: var(--danger);">' + (res.message || 'Incorrect PIN') + '</span>');
          if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(80);
          }
          setTimeout(function() {
            $('#lockDotsContainer').removeClass('shake-animation');
            enteredPin = '';
            updatePinDots();
            isSubmitting = false;
          }, 800);
        }
      },
      error: function() {
        $('#lockFeedbackText').html('<span style="color: var(--danger);">Network error. Please try again.</span>');
        enteredPin = '';
        updatePinDots();
        isSubmitting = false;
      }
    });
  }

  // --- Pattern Lock Engine ---
  const patternContainer = document.getElementById('lockPatternContainer');
  if (patternContainer) {
    const svg = document.getElementById('lockPatternSvg');
    const polyline = document.getElementById('lockPatternPolyline');
    const nodes = patternContainer.querySelectorAll('.pattern-node');

    let isDrawing = false;
    let selectedNodes = [];

    function getNodeCenter(node) {
      const rect = node.getBoundingClientRect();
      const containerRect = patternContainer.getBoundingClientRect();
      return {
        x: rect.left + rect.width / 2 - containerRect.left,
        y: rect.top + rect.height / 2 - containerRect.top
      };
    }

    function updatePolyline(point) {
      let pts = selectedNodes.map(n => {
        const c = getNodeCenter(n);
        return c.x + ',' + c.y;
      }).join(' ');
      if (point && selectedNodes.length > 0) {
        pts += ' ' + point.x + ',' + point.y;
      }
      polyline.setAttribute('points', pts);
    }

    function selectNode(node) {
      if (!selectedNodes.includes(node)) {
        selectedNodes.push(node);
        node.classList.add('active');
        if (window.navigator && window.navigator.vibrate) {
          window.navigator.vibrate(10);
        }
      }
    }

    function resetPattern() {
      selectedNodes.forEach(n => n.classList.remove('active', 'error', 'success'));
      selectedNodes = [];
      polyline.setAttribute('points', '');
      polyline.classList.remove('error', 'success');
    }

    $('#lockPatternClearBtn').on('click', function() {
      resetPattern();
      $('#lockFeedbackText').empty();
    });

    // Touch & Mouse Event Handlers
    function startDraw(e) {
      if (isSubmitting) return;
      isDrawing = true;
      resetPattern();
      $('#lockFeedbackText').empty();
      const node = e.target.closest('.pattern-node');
      if (node) selectNode(node);
      updatePolyline();
    }

    function moveDraw(e) {
      if (!isDrawing || isSubmitting) return;
      e.preventDefault();
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;

      const elementUnder = document.elementFromPoint(clientX, clientY);
      if (elementUnder) {
        const node = elementUnder.closest('.pattern-node');
        if (node) selectNode(node);
      }

      const rect = patternContainer.getBoundingClientRect();
      updatePolyline({
        x: clientX - rect.left,
        y: clientY - rect.top
      });
    }

    function endDraw() {
      if (!isDrawing || isSubmitting) return;
      isDrawing = false;
      updatePolyline();

      if (selectedNodes.length < 4) {
        $('#lockFeedbackText').html('<span style="color: var(--warning);">Connect at least 4 dots</span>');
        setTimeout(resetPattern, 600);
        return;
      }

      const patternStr = selectedNodes.map(n => n.dataset.index).join('-');
      verifyPatternSubmission(patternStr);
    }

    patternContainer.addEventListener('mousedown', startDraw);
    window.addEventListener('mousemove', moveDraw);
    window.addEventListener('mouseup', endDraw);

    patternContainer.addEventListener('touchstart', startDraw, { passive: false });
    window.addEventListener('touchmove', moveDraw, { passive: false });
    window.addEventListener('touchend', endDraw);

    function verifyPatternSubmission(pattern) {
      isSubmitting = true;
      $('#lockFeedbackText').html('<span style="color: var(--secondary);">Verifying Pattern...</span>');

      $.ajax({
        url: 'index.php?api=security',
        type: 'POST',
        data: { action: 'verify_pattern', pattern: pattern },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            polyline.classList.add('success');
            selectedNodes.forEach(n => n.classList.add('success'));
            $('#lockFeedbackText').html('<span style="color: var(--success);">✔ Pattern Verified! Unlocking...</span>');
            if (window.navigator && window.navigator.vibrate) {
              window.navigator.vibrate([20, 50, 20]);
            }
            setTimeout(function() {
              window.location.href = res.redirect || 'index.php?page=home';
            }, 450);
          } else {
            polyline.classList.add('error');
            selectedNodes.forEach(n => n.classList.add('error'));
            $('#lockFeedbackText').html('<span style="color: var(--danger);">' + (res.message || 'Incorrect Pattern') + '</span>');
            if (window.navigator && window.navigator.vibrate) {
              window.navigator.vibrate(80);
            }
            setTimeout(function() {
              resetPattern();
              isSubmitting = false;
            }, 800);
          }
        },
        error: function() {
          $('#lockFeedbackText').html('<span style="color: var(--danger);">Network error. Please try again.</span>');
          resetPattern();
          isSubmitting = false;
        }
      });
    }
  }
});
</script>

</body>
</html>
