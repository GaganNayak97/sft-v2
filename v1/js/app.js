/**
 * SoftPay - Main Client-Side Controller
 */

$(document).ready(function() {
  // 1. Theme Management (Light / Dark / System) with Permanent Storage
  function applyTheme(theme) {
    const resolved = (theme === 'system') 
      ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
      : theme;
    document.documentElement.setAttribute('data-theme', resolved);
    try {
      localStorage.setItem('softpay_theme', theme);
      document.cookie = 'softpay_theme=' + encodeURIComponent(theme) + '; path=/; max-age=31536000; SameSite=Lax';
    } catch (e) {}

    // Update active chip state if on profile page
    $('.theme-select-chip').removeClass('active');
    $('.theme-select-chip[data-theme="' + theme + '"]').addClass('active');
  }

  // Restore saved theme on page ready
  try {
    const savedTheme = localStorage.getItem('softpay_theme') || (document.cookie.match(/(?:^|; )softpay_theme=([^;]*)/) || [])[1];
    if (savedTheme) {
      applyTheme(savedTheme);
    }
  } catch (e) {}

  // Menubar iOS Spring Tactile Feedback
  $('.app-navbar .icon-btn, .app-navbar .search-trigger-btn, .app-navbar .user-chip, .app-navbar .brand-logo').on('click', function() {
    const $el = $(this);
    $el.addClass('ios-btn-bounce');
    if (window.navigator && window.navigator.vibrate) {
      try { window.navigator.vibrate(14); } catch (e) {}
    }
    setTimeout(() => $el.removeClass('ios-btn-bounce'), 440);
  });

  $('#themeToggleBtn').on('click', function() {
    const $btn = $(this);
    $btn.addClass('theme-animating');
    setTimeout(() => $btn.removeClass('theme-animating'), 580);

    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
    applyTheme(newTheme);

    // Save to server database
    $.post('index.php?api=auth', { action: 'update_theme', theme: newTheme });
  });

  $('.theme-select-chip').on('click', function() {
    const selectedTheme = $(this).data('theme');
    applyTheme(selectedTheme);
    $.post('index.php?api=auth', { action: 'update_theme', theme: selectedTheme });
  });

  // 2. Notification Drawer with iOS Bell Swing & Smooth Slide
  $('#openNotificationsBtn').on('click', function() {
    const $btn = $(this);
    $btn.addClass('bell-ringing');
    setTimeout(() => $btn.removeClass('bell-ringing'), 700);

    $('#notifBackdrop').addClass('open');
    $('#notificationDrawer').addClass('open');
    $('#navNotifDot').remove();
  });

  $('#closeNotifBtn, #notifBackdrop').on('click', function() {
    $('#notifBackdrop').removeClass('open');
    $('#notificationDrawer').removeClass('open');
  });

  $('#markAllReadBtn, #pageMarkAllReadBtn').on('click', function() {
    $(this).text('Done');
    $('.notification-drawer .card, .notifications-page-container .card').css({
      'opacity': '0.85',
      'border-left': 'none'
    });
  });

  // 3. Referral Copy Buttons
  $('#copyCodeBtn').on('click', function() {
    const code = $('#referralCodeBox').val();
    navigator.clipboard.writeText(code).then(() => {
      const orig = $(this).text();
      $(this).text('Copied! ✅');
      setTimeout(() => $(this).text(orig), 2000);
    });
  });

  $('#copyLinkBtn').on('click', function() {
    const url = $('#referralUrlBox').val();
    navigator.clipboard.writeText(url).then(() => {
      const orig = $(this).text();
      $(this).text('Link Copied! ✅');
      setTimeout(() => $(this).text(orig), 2000);
    });
  });

  $('#copyVpaBtn').on('click', function() {
    const vpa = $(this).data('vpa');
    navigator.clipboard.writeText(vpa).then(() => {
      const orig = $(this).text();
      $(this).text('Copied! ✅');
      setTimeout(() => $(this).text(orig), 2000);
    });
  });

  // 4. Interactive Interest Calculator & iOS Range Element Controller
  function updateCalculator(amount) {
    amount = Math.max(100, parseFloat(amount) || 0);
    const isVip = (amount >= 5000);
    const rate = isVip ? 1.00 : 0.88;
    const dailyReturn = (amount * (rate / 100));
    const monthlyReturn = dailyReturn * 30;
    const withdrawDeficit = amount * (0.55 / 100);

    $('#calcRateDisplay').text(rate.toFixed(2) + '% / day');
    $('#calcDailyReturn').text('₹' + dailyReturn.toFixed(2));
    $('#calcMonthlyReturn').text('₹' + monthlyReturn.toFixed(2));
    $('#calcWithdrawThreshold').text('₹' + withdrawDeficit.toFixed(2));

    if (isVip) {
      $('#calcTierBadge').removeClass('badge-info').addClass('badge-vip').text('VIP 1.0%');
      $('#calcTierExplanation').text('Qualifies for Business VIP Tier (₹5,000+)');
    } else {
      $('#calcTierBadge').removeClass('badge-vip').addClass('badge-info').text('Standard 0.88%');
      $('#calcTierExplanation').text('Standard Tier. Deposit ₹5,000+ for 1.0%');
    }
  }

  function initIosRangeControl() {
    const $track = $('#iosSliderTrack');
    if (!$track.length) return;

    const $thumb = $('#iosSliderThumb');
    const $fill = $('#iosSliderFill');
    const $bubble = $('#iosFloatingBubble .ios-bubble-val');
    const $display = $('#iosAmountDisplay');
    const $vipPill = $('#iosVipPill');
    const $hiddenRange = $('#calcRange');
    const $hiddenInput = $('#calcAmountInput');

    const min = 500;
    const max = 100000;
    const defaultVal = 10000;

    let isDragging = false;
    let prevVip = null;

    // Piecewise milestone curve for natural banking slider UX
    const milestones = [
      { val: 500, pct: 0 },
      { val: 2500, pct: 18 },
      { val: 5000, pct: 36 },
      { val: 10000, pct: 54 },
      { val: 25000, pct: 72 },
      { val: 50000, pct: 86 },
      { val: 100000, pct: 100 }
    ];

    function valToPct(val) {
      val = Math.max(min, Math.min(max, val));
      for (let i = 0; i < milestones.length - 1; i++) {
        const m1 = milestones[i];
        const m2 = milestones[i + 1];
        if (val >= m1.val && val <= m2.val) {
          const ratio = (val - m1.val) / (m2.val - m1.val);
          return m1.pct + ratio * (m2.pct - m1.pct);
        }
      }
      return 0;
    }

    function pctToVal(pct) {
      pct = Math.max(0, Math.min(100, pct));
      for (let i = 0; i < milestones.length - 1; i++) {
        const m1 = milestones[i];
        const m2 = milestones[i + 1];
        if (pct >= m1.pct && pct <= m2.pct) {
          const ratio = (pct - m1.pct) / (m2.pct - m1.pct);
          const raw = m1.val + ratio * (m2.val - m1.val);
          let step = 500;
          if (raw > 50000) step = 2500;
          else if (raw > 10000) step = 1000;
          else if (raw < 2000) step = 250;
          return Math.max(min, Math.min(max, Math.round(raw / step) * step));
        }
      }
      return min;
    }

    function formatInr(num) {
      return Number(num).toLocaleString('en-IN');
    }

    function syncValue(val, triggerCalc) {
      val = Math.max(min, Math.min(max, Math.round(val)));
      const pct = Math.max(0, Math.min(100, valToPct(val)));

      $fill.css('width', pct + '%');
      $thumb.css('left', pct + '%');
      $track.attr('aria-valuenow', val);

      const formatted = formatInr(val);
      $display.text(formatted);
      $bubble.text('₹' + formatted);

      const isVip = (val >= 5000);
      if (isVip) {
        $vipPill.text('VIP 1.0% Tier ✨').addClass('active-vip');
      } else {
        $vipPill.text('Standard 0.88%').removeClass('active-vip');
      }

      if (prevVip !== null && prevVip !== isVip && 'vibrate' in navigator) {
        try { navigator.vibrate([12, 25, 12]); } catch (e) {}
      }
      prevVip = isVip;

      // Update underlying hidden inputs for data collection
      $hiddenRange.val(val);
      $hiddenInput.val(val);

      $('.ios-preset-chip').each(function() {
        const chipVal = parseInt($(this).data('val'), 10);
        $(this).toggleClass('active', chipVal === val);
      });

      if (triggerCalc !== false) {
        updateCalculator(val);
      }
    }

    function calculatePos(clientX) {
      const rect = $track[0].getBoundingClientRect();
      const offsetX = Math.max(0, Math.min(rect.width, clientX - rect.left));
      const pct = (offsetX / rect.width) * 100;
      return pctToVal(pct);
    }

    $track.on('pointerdown', function(e) {
      isDragging = true;
      $track.addClass('dragging');
      $thumb.addClass('active');
      if (this.setPointerCapture) {
        try { this.setPointerCapture(e.pointerId); } catch (err) {}
      }
      if ('vibrate' in navigator) {
        try { navigator.vibrate(8); } catch (err) {}
      }
      const val = calculatePos(e.clientX);
      syncValue(val, true);
      e.preventDefault();
    });

    $track.on('pointermove', function(e) {
      if (!isDragging) return;
      const val = calculatePos(e.clientX);
      syncValue(val, true);
    });

    $track.on('pointerup pointercancel', function(e) {
      if (!isDragging) return;
      isDragging = false;
      $track.removeClass('dragging');
      $thumb.removeClass('active');
      if (this.releasePointerCapture) {
        try { this.releasePointerCapture(e.pointerId); } catch (err) {}
      }
    });

    // Stepper Buttons (- and +)
    $('#iosStepMinus').on('click', function(e) {
      e.preventDefault();
      const current = parseInt($hiddenRange.val(), 10) || defaultVal;
      const step = current > 50000 ? 2500 : (current > 10000 ? 1000 : 500);
      syncValue(current - step, true);
      if ('vibrate' in navigator) {
        try { navigator.vibrate(8); } catch (err) {}
      }
    });

    $('#iosStepPlus').on('click', function(e) {
      e.preventDefault();
      const current = parseInt($hiddenRange.val(), 10) || defaultVal;
      const step = current >= 50000 ? 2500 : (current >= 10000 ? 1000 : 500);
      syncValue(current + step, true);
      if ('vibrate' in navigator) {
        try { navigator.vibrate(8); } catch (err) {}
      }
    });

    // Preset Segment Chips
    $(document).on('click', '.ios-preset-chip', function(e) {
      e.preventDefault();
      const targetVal = parseInt($(this).data('val'), 10);
      if (!isNaN(targetVal)) {
        syncValue(targetVal, true);
        if ('vibrate' in navigator) {
          try { navigator.vibrate(10); } catch (err) {}
        }
      }
    });

    // Accessibility Keyboard Controls
    $track.on('keydown', function(e) {
      const current = parseInt($hiddenRange.val(), 10) || defaultVal;
      if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
        e.preventDefault();
        syncValue(current - 500, true);
      } else if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
        e.preventDefault();
        syncValue(current + 500, true);
      }
    });

    // External change sync listener
    $hiddenRange.on('change input', function() {
      if (!isDragging) {
        const v = parseInt($(this).val(), 10);
        if (!isNaN(v)) syncValue(v, true);
      }
    });

    $hiddenInput.on('change input', function() {
      if (!isDragging) {
        const v = parseInt($(this).val(), 10);
        if (!isNaN(v)) syncValue(v, true);
      }
    });

    // Initial sync
    const initialVal = parseInt($hiddenRange.val(), 10) || defaultVal;
    syncValue(initialVal, true);
  }

  initIosRangeControl();

  // 5. Deposit Page: Preset Chips & QR Dynamic Regeneration
  function updateDepositQr(amt) {
    amt = Math.max(100, parseFloat(amt) || 1000);
    const isVip = (amt >= 5000);
    const rate = isVip ? 1.00 : 0.88;
    const dailyReturn = amt * (rate / 100);

    $('#rateInfoAmt').text(amt.toLocaleString());
    $('#rateInfoPct').text(rate.toFixed(2) + '%');
    $('#rateInfoDaily').text('+₹' + dailyReturn.toFixed(2));
    $('#qrAmountDisplay').text('₹' + amt.toFixed(2));

    // Update QR image source
    const vpa = 'softpay@upi';
    const upiUri = encodeURIComponent(`upi://pay?pa=${vpa}&pn=SoftPay&am=${amt}&cu=INR`);
    $('#upiQrImage').attr('src', `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${upiUri}`);
  }

  $('.preset-chips .preset-chip[data-amt]').on('click', function() {
    $('.preset-chips .preset-chip[data-amt]').removeClass('active');
    $(this).addClass('active');
    const amt = $(this).data('amt');
    $('#depositAmountInput').val(amt);
    updateDepositQr(amt);
  });

  $('#depositAmountInput').on('input', function() {
    updateDepositQr($(this).val());
  });

  // Deposit Form Submit
  $('#depositForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#submitDepositBtn');
    btn.prop('disabled', true).text('Verifying UTR & Crediting...');
    $('#depositAlertBox').empty();

    $.ajax({
      url: 'index.php?api=deposit',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#depositAlertBox').html('<div class="alert-box success mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>✅</span><div>' + res.message + ' Redirecting to balance...</div></div>');
          setTimeout(function() {
            window.location.href = 'index.php?page=balance';
          }, 800);
        } else {
          $('#depositAlertBox').html('<div class="alert-box danger mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>❌</span><div>' + (res.message || 'Deposit submission failed') + '</div></div>');
          btn.prop('disabled', false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Confirm & Credit Deposit');
        }
      },
      error: function() {
        $('#depositAlertBox').html('<div class="alert-box danger mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>❌</span><div>Server error while processing deposit. Please check your connection.</div></div>');
        btn.prop('disabled', false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Confirm & Credit Deposit');
      }
    });
  });

  // ==========================================================================
  // 6. 12-Hour Staged Withdrawal Tracking & Payout Lifecycle
  // ==========================================================================
  let trackingCountdownInterval = null;
  let activeTrackingWithdrawalId = null;

  function formatTimeRemaining(seconds) {
    if (seconds <= 0) return '00h 00m 00s';
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    const pad = (n) => (n < 10 ? '0' + n : n);
    return `${pad(hrs)}h ${pad(mins)}m ${pad(secs)}s`;
  }

  function startTrackingCountdown(remainingSecs, unlockTimestamp) {
    if (trackingCountdownInterval) {
      clearInterval(trackingCountdownInterval);
      trackingCountdownInterval = null;
    }

    let remaining = remainingSecs;
    if (unlockTimestamp) {
      remaining = Math.max(0, unlockTimestamp - Math.floor(Date.now() / 1000));
    }

    function tick() {
      if (unlockTimestamp) {
        remaining = Math.max(0, unlockTimestamp - Math.floor(Date.now() / 1000));
      } else {
        remaining = Math.max(0, remaining - 1);
      }

      const formatted = formatTimeRemaining(remaining);
      $('#trackingCountdownTimer').text(formatted);

      // Total 12h = 43200s
      const elapsed = 43200 - remaining;
      const pct = Math.min(100, Math.max(5, Math.round((elapsed / 43200) * 100)));
      $('#trackingProgressFill').css('width', pct + '%');

      // Update balance page countdown if present
      if ($('#balanceTracingCountdown').length) {
        if (remaining <= 0) {
          $('#balanceTracingCountdown').html('<span style="color: var(--success); font-weight: 800;">Ready for Payout!</span>');
        } else {
          $('#balanceTracingCountdown').text(formatted + ' remaining');
        }
      }

      if (remaining <= 0) {
        clearInterval(trackingCountdownInterval);
        trackingCountdownInterval = null;
        setTrackingReadyState();
      }
    }

    tick();
    if (remaining > 0) {
      trackingCountdownInterval = setInterval(tick, 1000);
    } else {
      setTrackingReadyState();
    }
  }

  function setTrackingReadyState() {
    $('#countdownStatusLabel').html('<span style="color: #10b981;">✓ Queue Cleared:</span>');
    $('#trackingCountdownTimer').html('<span style="color: #10b981;">Ready to Claim</span>');
    $('#trackingProgressFill').css('width', '100%');

    // Update Status Badge
    $('#trackingStatusBadge').removeClass('pending').addClass('ready');
    $('#trackingStatusText').text('Ready for Payout');

    // Update Stepper Nodes
    $('#stepNode3').removeClass('active').addClass('completed');
    $('#stepDot3').text('✓');
    $('#stepTime3').text('Cleared');

    $('#stepNode4').removeClass('waiting').addClass('completed active');
    $('#stepDot4').text('✓');
    $('#stepTime4').text('Ready');

    // Enable Receive Payment Button
    $('#claimPayoutBtn').prop('disabled', false).css({
      'box-shadow': '0 4px 20px rgba(16, 185, 129, 0.6)',
      'animation': 'pulseAura 2s infinite'
    });
    $('#claimPayoutBtnText').text('Receive Payment Now');
  }

  function populateTrackingModal(data) {
    activeTrackingWithdrawalId = data.id || data.withdrawal_id;
    $('#claimPayoutBtn').attr('data-id', activeTrackingWithdrawalId);
    $('#fastForwardDemoBtn').attr('data-id', activeTrackingWithdrawalId);

    const amountFormatted = data.amount_formatted || ('₹' + parseFloat(data.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#trackingAmountDisplay').text(amountFormatted);

    const target = data.payout_target || data.payout_upi || data.payout_bank || 'UPI / Bank';
    $('#trackingRecipientDisplay').text(target).attr('title', target);

    $('#stepTime1').text(data.created_at_formatted || 'Today');
    $('#stepTime2').text('0.55% Verified');

    if (data.is_ready || (data.remaining_seconds <= 0)) {
      setTrackingReadyState();
    } else {
      // Pending state
      $('#countdownStatusLabel').text('Waiting Period:');
      $('#trackingStatusBadge').removeClass('ready').addClass('pending');
      $('#trackingStatusText').text('Pending (12h Waiting)');

      $('#stepNode3').removeClass('completed').addClass('active');
      $('#stepDot3').text('3');
      $('#stepTime3').text('In Progress');

      $('#stepNode4').removeClass('completed active');
      $('#stepDot4').text('4');
      $('#stepTime4').text('Pending');

      $('#claimPayoutBtn').prop('disabled', true).css({
        'box-shadow': 'none',
        'animation': 'none'
      });
      $('#claimPayoutBtnText').text('Receive Payment (After 12h)');

      const unlockTs = data.unlock_at ? Math.floor(new Date(data.unlock_at.replace(' ', 'T')).getTime() / 1000) : null;
      startTrackingCountdown(data.remaining_seconds || 43200, unlockTs);
    }
  }

  // Submit Withdrawal Form -> Opens 12h Stepper Timeline Modal
  $('#withdrawalForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#submitWithdrawBtn');
    btn.prop('disabled', true).text('Checking Eligibility & Staging Request...');
    $('#withdrawAlertBox').empty();

    $.ajax({
      url: 'index.php?api=withdraw',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          // Update remaining available balance display
          if (typeof res.remaining_balance !== 'undefined') {
            const newBalFormatted = '₹' + parseFloat(res.remaining_balance).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            $('#withdrawAvailableBalance').text(newBalFormatted);
            $('#withdrawAmountInput').attr('max', res.remaining_balance);
          }

          // Reset input and button
          $('#withdrawAmountInput').val('');
          btn.prop('disabled', false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg> Confirm & Withdraw');

          // Open 12-Hour Stepper Tracking Modal (media_1790162239051)
          populateTrackingModal(res);
          $('#withdrawalTrackingModal').css('display', 'flex').hide().fadeIn(250);
        } else {
          $('#withdrawAlertBox').html('<div class="alert-box danger mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>❌</span><div>' + (res.message || 'Withdrawal rejected') + '</div></div>');
          btn.prop('disabled', false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg> Confirm & Withdraw');
        }
      },
      error: function() {
        $('#withdrawAlertBox').html('<div class="alert-box danger mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>❌</span><div>Server error while processing withdrawal. Please check your connection.</div></div>');
        btn.prop('disabled', false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg> Confirm & Withdraw');
      }
    });
  });

  // Open Tracking Modal from Balance Card or History Click
  $(document).on('click', '.open-tracking-modal-btn', function() {
    const $btn = $(this);
    const originalText = $btn.html();
    $btn.text('Loading timeline...');

    $.ajax({
      url: 'index.php?api=withdraw&action=get_active_tracking',
      type: 'GET',
      dataType: 'json',
      success: function(res) {
        $btn.html(originalText);
        if (res.success && res.data) {
          populateTrackingModal(res.data);
          $('#withdrawalTrackingModal').css('display', 'flex').hide().fadeIn(250);
        } else {
          showFloatingToast('No active pending withdrawal found.', 'info');
        }
      },
      error: function() {
        $btn.html(originalText);
        showFloatingToast('Failed to load tracking details.', 'danger');
      }
    });
  });

  // Close Tracking Modal
  $(document).on('click', '.close-tracking-modal', function() {
    $('#withdrawalTrackingModal').fadeOut(200);
  });

  $('#withdrawalTrackingModal').on('click', function(e) {
    if ($(e.target).is('#withdrawalTrackingModal')) {
      $(this).fadeOut(200);
    }
  });

  // Fast-Forward Demo Action (Instantly passes the 12h for testing)
  $(document).on('click', '#fastForwardDemoBtn, #balanceFastForwardBtn', function() {
    const $btn = $(this);
    const wId = activeTrackingWithdrawalId || $btn.attr('data-id') || '';
    $btn.prop('disabled', true).text('⚡ Fast-forwarding...');

    $.ajax({
      url: 'index.php?api=withdraw&action=fast_forward_demo',
      type: 'POST',
      data: { withdrawal_id: wId },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('⚡ Demo Fast-Forward');
        if (res.success) {
          if (res.data) {
            populateTrackingModal(res.data);
          } else {
            setTrackingReadyState();
          }
          // If clicked from balance page, update banner state
          if ($('#balanceWithdrawalTracingCard').length) {
            $('#balanceWithdrawalTracingCard').addClass('ready-state');
            $('#balanceTracingCountdown').html('<span style="color: var(--success); font-weight: 800;">Ready for Payout!</span>');
            // Add receive payment button if not present
            if (!$('.claim-direct-btn').length) {
              $('.balance-tracing-actions').html(`
                <button type="button" class="btn-tracing-details open-tracking-modal-btn" data-id="${res.data ? res.data.id : wId}">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg> View Live Stepper Details
                </button>
                <button type="button" class="btn-tracing-claim claim-direct-btn" data-id="${res.data ? res.data.id : wId}">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Receive Payment
                </button>
              `);
            }
          }
          showFloatingToast('12 Hours fast-forwarded! Payout is ready to receive.', 'success');
        } else {
          showFloatingToast(res.message || 'Could not fast-forward.', 'danger');
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('⚡ Demo Fast-Forward');
        showFloatingToast('Server error while fast-forwarding.', 'danger');
      }
    });
  });

  // Claim Payout Action -> Unlocks Payment & Opens media_1790161265450 Success Modal!
  $(document).on('click', '#claimPayoutBtn, .claim-direct-btn', function() {
    const $btn = $(this);
    const wId = $(this).attr('data-id') || activeTrackingWithdrawalId || '';
    const origHtml = $btn.html();
    $btn.prop('disabled', true).text('Disbursing Payout...');

    $.ajax({
      url: 'index.php?api=withdraw&action=claim_payout',
      type: 'POST',
      data: { withdrawal_id: wId },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html(origHtml);
        if (res.success) {
          // Close tracking modal if open
          $('#withdrawalTrackingModal').fadeOut(150);

          // Remove pending banner from page
          $('#balanceWithdrawalTracingCard, #withdrawActiveBanner').fadeOut(300);

          // Populate and show the pixel-perfect Withdrawal Successful screen (media_1790161265450)
          const amountFormatted = res.amount_formatted || ('₹' + parseFloat(res.amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          const target = res.payout_target || 'your UPI/Bank account';

          $('#successWithdrawAmount').text(amountFormatted);
          $('#successWithdrawDesc').html('Funds transferred out of SoftPay to <strong>' + target + '</strong>. Please contact the recipient platform or check your UPI app for your transaction receipt.');

          $('#withdrawalSuccessModal').css('display', 'flex').hide().fadeIn(250);

          // Update navbar balance if available
          if (typeof res.remaining_balance !== 'undefined') {
            $('#navCurrentBalance').text('₹' + parseFloat(res.remaining_balance).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          }
        } else {
          showFloatingToast(res.message || 'Failed to disburse payout.', 'danger');
        }
      },
      error: function() {
        $btn.prop('disabled', false).html(origHtml);
        showFloatingToast('Server error while claiming payout.', 'danger');
      }
    });
  });

  // Save Address Button on Success Modal
  $('#saveAddressBtn').on('click', function() {
    const $btn = $(this);
    const upi = $('#withdrawUpiInput').val() ? $('#withdrawUpiInput').val().trim() : '';
    if (upi) {
      $.post('index.php?api=auth', { action: 'update_profile', upi_id: upi });
    }
    $btn.text('✓ Saved!').css('background', '#dcfce7').css('color', '#15803d');
    setTimeout(function() {
      $('#withdrawalSuccessModal').fadeOut(200);
      $btn.text('Save Address').css('background', '').css('color', '');
    }, 600);
  });

  // Close modals when clicking outside card or pressing Escape
  $('#withdrawalSuccessModal').on('click', function(e) {
    if ($(e.target).is('#withdrawalSuccessModal')) {
      $(this).fadeOut(200);
    }
  });

  $(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
      if ($('#withdrawalSuccessModal').is(':visible')) {
        $('#withdrawalSuccessModal').fadeOut(200);
      }
      if ($('#withdrawalTrackingModal').is(':visible')) {
        $('#withdrawalTrackingModal').fadeOut(200);
      }
    }
  });

  // Initial balance page countdown ticker if element exists
  if ($('#balanceTracingCountdown').length) {
    const unlockTs = parseInt($('#balanceTracingCountdown').attr('data-unlock'), 10);
    if (unlockTs) {
      function balanceTick() {
        const nowSec = Math.floor(Date.now() / 1000);
        const rem = Math.max(0, unlockTs - nowSec);
        if (rem <= 0) {
          $('#balanceTracingCountdown').html('<span style="color: var(--success); font-weight: 800;">Ready for Payout!</span>');
        } else {
          $('#balanceTracingCountdown').text(formatTimeRemaining(rem) + ' remaining');
        }
      }
      balanceTick();
      setInterval(balanceTick, 1000);
    }
  }

  // 7. USDT Sell Calculator & Order Submit
  $('#usdtAmountInput').on('input', function() {
    const usdt = parseFloat($(this).val()) || 0;
    const rate = 91.50;
    const inr = (usdt * rate).toFixed(2);
    $('#inrPayoutDisplay').text('₹' + inr);
  });

  $('#usdtSellForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#submitUsdtSellBtn');
    btn.prop('disabled', true).text('Submitting Order...');
    $('#usdtAlertBox').empty();

    $.ajax({
      url: 'index.php?api=usdt',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#usdtAlertBox').html('<div class="alert-box success mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>✅</span><div>' + res.message + '</div></div>');
          setTimeout(function() {
            window.location.reload();
          }, 800);
        } else {
          $('#usdtAlertBox').html('<div class="alert-box danger mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>❌</span><div>' + (res.message || 'Order failed') + '</div></div>');
          btn.prop('disabled', false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Initiate USDT Sell');
        }
      },
      error: function() {
        $('#usdtAlertBox').html('<div class="alert-box danger mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>❌</span><div>Server error while initiating USDT sell order.</div></div>');
        btn.prop('disabled', false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Initiate USDT Sell');
      }
    });
  });

  // 8. Profile Update Form
  $('#profileForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#saveProfileBtn');
    btn.prop('disabled', true).text('Saving...');
    $('#profileAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: $(this).serialize() + '&action=update_profile',
      dataType: 'json',
      success: function(res) {
        if ($('#profileAlertBox').length) {
          $('#profileAlertBox').html('<div class="alert-box success mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>✅</span><div>' + res.message + '</div></div>');
        }
        btn.prop('disabled', false).text('Save Profile Settings');
      },
      error: function() {
        if ($('#profileAlertBox').length) {
          $('#profileAlertBox').html('<div class="alert-box danger mb-3" style="font-size: 0.84rem; padding: 10px 14px; border-radius: 12px;"><span>❌</span><div>Failed to update profile.</div></div>');
        }
        btn.prop('disabled', false).text('Save Profile Settings');
      }
    });
  });

  // 9. Check Interest Status Button
  $('#refreshInterestBtn').on('click', function() {
    const $btn = $(this);
    $btn.prop('disabled', true).text('Calculating...');
    $.get('index.php?api=interest', function(res) {
      $btn.html('✓ Updated!').css('color', '#16a34a');
      setTimeout(function() {
        window.location.reload();
      }, 500);
    });
  });

  // 10. Mobile Floating Capsule Dock Micro-interactions & Action Animations
  $('.app-bottom-nav .bottom-nav-item').on('click', function(e) {
    const item = $(this);

    // Subtle haptic feedback for physical mobile devices
    if (window.navigator && typeof window.navigator.vibrate === 'function') {
      try { window.navigator.vibrate(18); } catch (err) {}
    }

    // Dynamic liquid ripple at tap coordinates
    const rect = item[0].getBoundingClientRect();
    const clientX = e.clientX || (rect.left + rect.width / 2);
    const clientY = e.clientY || (rect.top + rect.height / 2);
    const x = clientX - rect.left;
    const y = clientY - rect.top;

    const ripple = $('<span class="bottom-nav-ripple"></span>');
    ripple.css({ left: x + 'px', top: y + 'px' });
    item.append(ripple);
    setTimeout(function() { ripple.remove(); }, 550);

    // Trigger action spring animation
    item.addClass('tab-action-pop');
    setTimeout(function() {
      item.removeClass('tab-action-pop');
    }, 550);
  });
});
