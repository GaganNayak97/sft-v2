<?php
$flash = flash_get();
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
  <title>Sign In - <?php echo htmlspecialchars(app_config('app.name', 'SoftPay')); ?></title>
  <link rel="icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="shortcut icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="apple-touch-icon" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/tokens.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/root.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/paytm_theme.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body style="background: var(--bg-main); justify-content: center; align-items: center; padding: 20px; min-height: 100vh;">

<div style="width: 100%; max-width: 440px; margin: auto;">
  <!-- Brand Header -->
  <div class="text-center mb-4">
    <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay" style="width: 72px; height: 72px; border-radius: 18px; margin: 0 auto 12px auto; display: block; box-shadow: 0 8px 24px rgba(0, 186, 242, 0.4); object-fit: cover;">
    <h1 style="font-size: 1.5rem; font-weight: 800; color: #002970; margin-bottom: 4px;">SoftPay</h1>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">High-Yield Savings & Daily 0.88% – 1.00% Interest</p>
  </div>

  <div class="card" style="box-shadow: 0 10px 25px rgba(0, 41, 112, 0.08); padding: 28px;">
    
    <!-- Step 1: Credentials Header -->
    <div id="step1Header">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Sign In to Account</h2>
      <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 20px;">Enter your mobile number or email to access your wallet.</p>
    </div>

    <!-- Step 2: 2FA OTP Header -->
    <div id="step2Header" style="display: none;">
      <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(0, 186, 242, 0.12); color: #00BAF2; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 9999px; margin-bottom: 8px;">
        🛡️ STEP 2 OF 2: 2-FACTOR AUTH
      </div>
      <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Security Verification</h2>
      <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 16px;" id="loginOtpSubtitle">Sent 6-digit login verification code to your registered mobile.</p>
    </div>

    <?php if ($flash): ?>
      <?php component('alert', ['type' => $flash['type'], 'message' => $flash['message']]); ?>
    <?php endif; ?>

    <div id="loginAlertBox"></div>

    <!-- Step 1: Credentials Form -->
    <form id="loginForm">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label class="form-label">Email or Mobile Number:</label>
        <input type="text" name="identifier" id="loginIdentifier" class="form-control" placeholder="e.g. demo@softpay.com" required>
      </div>

      <div class="form-group">
        <div class="d-flex justify-content-between align-items-center">
          <label class="form-label" style="margin: 0;">Password:</label>
          <a href="<?php echo url('forgot'); ?>" style="font-size: 0.8rem;">Forgot Password?</a>
        </div>
        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block mt-2" id="loginSubmitBtn">
        Sign In Securely
      </button>
    </form>

    <!-- Step 2: Two-Factor OTP Form -->
    <div id="loginOtpForm" style="display: none;">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary);">Enter 6-Digit Login Code</div>
        <span style="font-size: 0.74rem; color: #16a34a; font-weight: 700;" id="loginCarrierLatencyTag"></span>
      </div>

      <!-- 6 Segmented Digit Boxes (Direct autofill target) -->
      <div class="otp-boxes-wrap" id="loginOtpBoxesWrap">
        <input type="text" inputmode="numeric" class="otp-digit-box login-otp-box" maxlength="1" data-index="0" autocomplete="one-time-code">
        <input type="text" inputmode="numeric" class="otp-digit-box login-otp-box" maxlength="1" data-index="1">
        <input type="text" inputmode="numeric" class="otp-digit-box login-otp-box" maxlength="1" data-index="2">
        <input type="text" inputmode="numeric" class="otp-digit-box login-otp-box" maxlength="1" data-index="3">
        <input type="text" inputmode="numeric" class="otp-digit-box login-otp-box" maxlength="1" data-index="4">
        <input type="text" inputmode="numeric" class="otp-digit-box login-otp-box" maxlength="1" data-index="5">
      </div>

      <button type="button" class="btn btn-primary btn-lg btn-block mt-3" id="verifyLoginOtpBtn" style="font-weight: 800;">
        Verify & Sign In
      </button>

      <div class="otp-meta-row mt-3">
        <span id="loginResendCountdownText">Resend in <strong id="loginTimerSeconds">60</strong>s</span>
        <button type="button" class="otp-resend-btn" id="loginResendOtpBtn" disabled>Resend Code</button>
      </div>

      <div class="text-center mt-3 pt-2" style="border-top: 1px dashed var(--border-color);">
        <a href="javascript:void(0)" id="backToCredentialsBtn" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
          ← Use different email or password
        </a>
      </div>
    </div>

    <!-- Quick 1-Click Demo Login Fill -->
    <div id="demoFillSection" style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed var(--border-color); text-align: center;">
      <button type="button" class="btn btn-sm btn-outline" id="demoFillBtn" style="font-size: 0.82rem;">
        ⚡ Fill Demo Credentials & Test 2FA OTP
      </button>
    </div>
  </div>

  <div class="text-center mt-3" style="font-size: 0.88rem; color: var(--text-secondary);">
    Don't have an account? <a href="<?php echo url('signup'); ?>" style="font-weight: 700; color: #002970;">Create New Account</a>
  </div>
</div>

<script>
$(document).ready(function() {
  let currentLoginOtp = '';
  let resendCountdown = 60;
  let resendTimerInterval = null;

  // Segmented 6-digit box focus and input management for Login
  const $digitBoxes = $('.login-otp-box');
  $digitBoxes.on('input', function(e) {
    const val = $(this).val().replace(/[^0-9]/g, '');
    $(this).val(val);

    if (val) {
      $(this).addClass('filled');
      const nextIndex = parseInt($(this).data('index')) + 1;
      if (nextIndex < 6) {
        $digitBoxes.eq(nextIndex).focus();
      }
    } else {
      $(this).removeClass('filled');
    }

    if (getEnteredLoginOtp().length === 6) {
      $('#verifyLoginOtpBtn').prop('disabled', false);
    }
  });

  $digitBoxes.on('keydown', function(e) {
    const index = parseInt($(this).data('index'));
    if (e.key === 'Backspace' && !$(this).val() && index > 0) {
      $digitBoxes.eq(index - 1).focus().val('').removeClass('filled');
    }
  });

  // Support paste into login boxes
  $digitBoxes.first().on('paste', function(e) {
    const pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text').trim();
    if (/^\d{6}$/.test(pasteData)) {
      e.preventDefault();
      fillLoginOtpBoxes(pasteData);
    }
  });

  function getEnteredLoginOtp() {
    let code = '';
    $digitBoxes.each(function() {
      code += $(this).val();
    });
    return code;
  }

  function fillLoginOtpBoxes(code) {
    if (!code || code.length !== 6) return;
    $digitBoxes.each(function(i) {
      $(this).val(code[i]).addClass('filled');
    });
    $digitBoxes.last().focus();
  }

  function startLoginResendTimer() {
    clearInterval(resendTimerInterval);
    resendCountdown = 60;
    $('#loginResendOtpBtn').prop('disabled', true);
    $('#loginTimerSeconds').text(resendCountdown);
    $('#loginResendCountdownText').show();

    resendTimerInterval = setInterval(function() {
      resendCountdown--;
      $('#loginTimerSeconds').text(resendCountdown);
      if (resendCountdown <= 0) {
        clearInterval(resendTimerInterval);
        $('#loginResendCountdownText').hide();
        $('#loginResendOtpBtn').prop('disabled', false);
      }
    }, 1000);
  }

  // Step 1: Submit Credentials
  $('#loginForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#loginSubmitBtn');
    btn.prop('disabled', true).text('Verifying credentials...');
    $('#loginAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: $(this).serialize() + '&action=login',
      dataType: 'json',
      success: function(res) {
        if (res.success && res.step === 'otp_required') {
          currentLoginOtp = res.simulated_otp || '';

          if (isMobileViewport()) {
            // Mobile users get the dedicated popup modal (media_1790163138408)
            btn.prop('disabled', false).text('Sign In Securely');
            launchMobileOtpModal(currentLoginOtp, res.phone_masked);
          } else {
            // Desktop users transition inline
            $('#step1Header').slideUp(200);
            $('#loginForm').slideUp(200);
            $('#demoFillSection').slideUp(200);

            $('#loginOtpSubtitle').text('Sent 6-digit login verification code to ' + (res.phone_masked || 'your registered mobile') + '.');
            $('#step2Header').slideDown(250);
            $('#loginOtpForm').slideDown(250);
            $digitBoxes.val('').removeClass('filled');
            $digitBoxes.first().focus();

            startLoginResendTimer();

            // Immediate, smooth autofill right into the 6 boxes
            setTimeout(function() {
              fillLoginOtpBoxes(currentLoginOtp);
              $('#loginCarrierLatencyTag').html('⚡ Auto-Filled').fadeIn(150);

              // Auto-trigger verify
              setTimeout(function() {
                $('#verifyLoginOtpBtn').trigger('click');
              }, 300);
            }, 200);
          }

        } else if (res.success && !res.step) {
          // Direct fallback redirect
          window.location.href = res.redirect || 'index.php?page=home';
        } else {
          $('#loginAlertBox').html('<div class="alert-box danger"><span>❌</span><div>' + (res.message || 'Login failed') + '</div></div>');
          btn.prop('disabled', false).text('Sign In Securely');
        }
      },
      error: function() {
        $('#loginAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Server error. Please try again.</div></div>');
        btn.prop('disabled', false).text('Sign In Securely');
      }
    });
  });

  // Step 2: Verify Login OTP
  $('#verifyLoginOtpBtn').on('click', function() {
    const entered = getEnteredLoginOtp();
    if (entered.length !== 6) {
      $('#loginAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Please enter the full 6-digit code.</div></div>');
      return;
    }

    const $btn = $(this);
    $btn.prop('disabled', true).text('Verifying & Logging In...');
    $('#loginAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: {
        action: 'verify_login_otp',
        otp: entered
      },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#loginAlertBox').html('<div class="alert-box success"><span>✅</span><div>Verification successful! Signing in...</div></div>');
          setTimeout(function() {
            window.location.href = res.redirect || 'index.php?page=home';
          }, 400);
        } else {
          $btn.prop('disabled', false).text('Verify & Sign In');
          $('#loginAlertBox').html('<div class="alert-box danger"><span>❌</span><div>' + (res.message || 'Invalid OTP code') + '</div></div>');
          $digitBoxes.css('border-color', '#ef4444');
          setTimeout(() => $digitBoxes.css('border-color', ''), 2000);
        }
      },
      error: function() {
        $btn.prop('disabled', false).text('Verify & Sign In');
        $('#loginAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Server error during verification.</div></div>');
      }
    });
  });

  // Resend Login OTP
  $('#loginResendOtpBtn').on('click', function() {
    const $btn = $(this);
    $btn.prop('disabled', true);
    $('#loginAlertBox').empty();
    $('#loginCarrierLatencyTag').text('📡 Connecting to carrier network...').show();

    // Re-trigger via login credentials or resend
    const id = $('#loginIdentifier').val();
    const pw = $('#loginPassword').val();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: {
        action: 'login',
        identifier: id,
        password: pw
      },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.step === 'otp_required') {
          currentLoginOtp = res.simulated_otp || '';
          startLoginResendTimer();

          setTimeout(function() {
            fillLoginOtpBoxes(currentLoginOtp);
            $('#loginCarrierLatencyTag').html('⚡ Auto-Filled').fadeIn(150);

            setTimeout(function() {
              $('#verifyLoginOtpBtn').trigger('click');
            }, 300);
          }, 200);
        } else {
          $('#loginAlertBox').html('<div class="alert-box danger"><span>❌</span><div>' + (res.message || 'Failed to resend OTP') + '</div></div>');
        }
      },
      error: function() {
        $('#loginAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Server error while resending.</div></div>');
      }
    });
  });

  // Switch back to credentials form
  $('#backToCredentialsBtn').on('click', function() {
    $('#step2Header').slideUp(200);
    $('#loginOtpForm').slideUp(200);
    $('#step1Header').slideDown(250);
    $('#loginForm').slideDown(250);
    $('#demoFillSection').slideDown(250);
    $('#loginSubmitBtn').prop('disabled', false).text('Sign In Securely');
    $('#loginAlertBox').empty();
  });

  // Mobile OTP Verification Modal Logic (media_1790163138408)
  const isMobileViewport = () => window.innerWidth <= 768 || ('ontouchstart' in window);

  const $mBoxes = $('#mobileOtpBoxesWrap .mobile-otp-box');
  $mBoxes.on('input', function() {
    const val = $(this).val().replace(/[^0-9]/g, '');
    $(this).val(val);
    const idx = parseInt($(this).data('index'));
    if (val) {
      $(this).addClass('filled').removeClass('active-focus');
      if (idx + 1 < 6) {
        $mBoxes.eq(idx + 1).addClass('active-focus').focus();
      }
    } else {
      $(this).removeClass('filled');
    }
  });

  $mBoxes.on('keydown', function(e) {
    const idx = parseInt($(this).data('index'));
    if (e.key === 'Backspace' && !$(this).val() && idx > 0) {
      $mBoxes.eq(idx - 1).focus().val('').removeClass('filled').addClass('active-focus');
    }
  });

  function getMobileEnteredOtp() {
    let code = '';
    $mBoxes.each(function() { code += $(this).val(); });
    return code;
  }

  function launchMobileOtpModal(otpCode, phoneMasked) {
    $('#mobileOtpModal').css('display', 'flex').hide().fadeIn(220);
    if (phoneMasked) {
      $('#mobileOtpSubtitle').text('We sent a 6-digit code to ' + phoneMasked);
    }
    $mBoxes.val('').removeClass('filled active-focus');
    $mBoxes.first().addClass('active-focus').focus();

    // Sequential realistic auto-fill animation (as depicted in reference screenshot)
    if (otpCode && otpCode.length === 6) {
      let d = 0;
      const fillInterval = setInterval(function() {
        if (d < 6) {
          $mBoxes.eq(d).val(otpCode[d]).addClass('filled').removeClass('active-focus');
          if (d + 1 < 6) {
            $mBoxes.eq(d + 1).addClass('active-focus').focus();
          }
          d++;
        } else {
          clearInterval(fillInterval);
          // Auto-trigger verification button after brief pause
          setTimeout(function() {
            $('#mobileVerifyAccountBtn').trigger('click');
          }, 350);
        }
      }, 110);
    }
  }

  // Close Mobile Modal
  $('#closeMobileOtpBtn').on('click', function() {
    $('#mobileOtpModal').fadeOut(200);
  });
  $('#mobileOtpModal').on('click', function(e) {
    if ($(e.target).is('#mobileOtpModal')) {
      $(this).fadeOut(200);
    }
  });

  // Verify Account Button on Mobile Modal
  $('#mobileVerifyAccountBtn').on('click', function() {
    const code = getMobileEnteredOtp() || currentLoginOtp;
    if (code.length !== 6) {
      $('#mobileOtpAlertBox').html('<div class="alert-box danger" style="padding: 8px 12px; font-size: 0.8rem;">Please enter all 6 digits.</div>');
      return;
    }

    const $btn = $(this);
    $btn.prop('disabled', true).text('Verifying...');
    $('#mobileOtpAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: { action: 'verify_login_otp', otp: code },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $btn.text('✓ Verified!').css('background', '#10b981');
          setTimeout(function() {
            window.location.href = res.redirect || 'index.php?page=home';
          }, 350);
        } else {
          $btn.prop('disabled', false).text('Verify Account');
          $('#mobileOtpAlertBox').html('<div class="alert-box danger" style="padding: 8px 12px; font-size: 0.8rem;">' + (res.message || 'Verification failed') + '</div>');
        }
      },
      error: function() {
        $btn.prop('disabled', false).text('Verify Account');
        $('#mobileOtpAlertBox').html('<div class="alert-box danger" style="padding: 8px 12px; font-size: 0.8rem;">Server error during verification.</div>');
      }
    });
  });

  // Quick 1-Click Demo Login Button
  $('#demoFillBtn').on('click', function() {
    $('#loginIdentifier').val('demo@softpay.com');
    $('#loginPassword').val('demo1234');
    $('#loginForm').trigger('submit');
  });
});
</script>

<?php require __DIR__ . '/../components/mobile_otp_modal.php'; ?>
</body>
</html>
