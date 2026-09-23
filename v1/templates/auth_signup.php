<?php
$ref = $_GET['ref'] ?? '';
$ref = preg_replace('/[^A-Za-z0-9]/', '', $ref);
$initialAmount = !empty($_GET['amount']) ? (float)$_GET['amount'] : null;
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
  <title>Create Account - <?php echo htmlspecialchars(app_config('app.name', 'SoftPay')); ?></title>
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

<div style="width: 100%; max-width: 460px; margin: auto;">
  <!-- Brand Header -->
  <div class="text-center mb-4">
    <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay" style="width: 72px; height: 72px; border-radius: 18px; margin: 0 auto 12px auto; display: block; box-shadow: 0 8px 24px rgba(0, 186, 242, 0.4); object-fit: cover;">
    <h1 style="font-size: 1.5rem; font-weight: 800; color: #002970; margin-bottom: 4px;">SoftPay</h1>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">Start Earning 0.88% – 1.00% Daily Compounding Returns</p>
  </div>

  <div class="card" style="box-shadow: 0 10px 25px rgba(0, 41, 112, 0.08); padding: 28px;">
    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Create Free Account</h2>
    <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 20px;">Open your high-yield digital savings account in seconds.</p>

    <?php if ($initialAmount): ?>
      <div class="alert-box success mb-3" style="font-size: 0.82rem; padding: 10px 12px; border-radius: 10px;">
        <span>⚡</span>
        <div>
          Ready to deposit <strong>₹<?php echo number_format($initialAmount); ?></strong>! Sign up now to start earning <strong><?php echo ($initialAmount >= 5000) ? '1.00%' : '0.88%'; ?> daily interest</strong> immediately.
        </div>
      </div>
    <?php endif; ?>

    <div id="signupAlertBox"></div>

    <form id="signupForm">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label class="form-label">Full Name:</label>
        <input type="text" name="name" id="signupName" class="form-control" placeholder="e.g. Rahul Sharma" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email Address:</label>
        <input type="email" name="email" id="signupEmail" class="form-control" placeholder="e.g. rahul@gmail.com" required>
      </div>

      <div class="form-group">
        <label class="form-label d-flex justify-content-between align-items-center">
          <span>Mobile Number (10 Digits):</span>
          <span id="phoneVerifiedBadge" class="phone-verified-pill" style="display: none;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Phone Verified
          </span>
        </label>
        <div style="display: flex; gap: 8px;">
          <div style="position: relative; flex: 1;">
            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-muted); font-size: 0.88rem;">+91</span>
            <input type="tel" name="phone" id="signupPhone" class="form-control" placeholder="9812345678" pattern="[0-9]{10}" maxlength="10" required style="padding-left: 48px;">
          </div>
          <button type="button" class="btn btn-outline" id="sendOtpBtn" style="white-space: nowrap; font-weight: 700; min-width: 100px; padding: 0 14px;">
            Send OTP
          </button>
        </div>
      </div>

      <!-- Segmented Dynamic SMS OTP Verification Section (Direct input targets) -->
      <div id="otpSection" style="display: none; background: var(--bg-card); border: 1.5px solid rgba(0, 186, 242, 0.4); border-radius: 16px; padding: 18px; margin: 16px 0 20px 0; box-shadow: 0 6px 20px rgba(0, 186, 242, 0.08);">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-primary);">Verify Mobile Number</div>
          <span style="font-size: 0.74rem; color: #16a34a; font-weight: 700;" id="carrierLatencyTag"></span>
        </div>
        <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 12px;" id="otpRecipientText">Sent 6-digit OTP to your number</p>

        <!-- 6 Segmented Digit Boxes -->
        <div class="otp-boxes-wrap" id="otpBoxesWrap">
          <input type="text" inputmode="numeric" class="otp-digit-box" maxlength="1" data-index="0" autocomplete="one-time-code">
          <input type="text" inputmode="numeric" class="otp-digit-box" maxlength="1" data-index="1">
          <input type="text" inputmode="numeric" class="otp-digit-box" maxlength="1" data-index="2">
          <input type="text" inputmode="numeric" class="otp-digit-box" maxlength="1" data-index="3">
          <input type="text" inputmode="numeric" class="otp-digit-box" maxlength="1" data-index="4">
          <input type="text" inputmode="numeric" class="otp-digit-box" maxlength="1" data-index="5">
        </div>

        <button type="button" class="btn btn-primary btn-block" id="verifyOtpBtn" style="font-weight: 800;">
          Verify & Confirm Phone
        </button>

        <div class="otp-meta-row">
          <span id="otpCountdownText">Resend OTP in <strong id="otpTimerSeconds">60</strong>s</span>
          <button type="button" class="otp-resend-btn" id="resendOtpBtn" disabled>Resend OTP</button>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Create Password:</label>
        <input type="password" name="password" id="signupPassword" class="form-control" placeholder="Min 6 characters" minlength="6" required>
      </div>

      <div class="form-group">
        <label class="form-label">Referral Code (Optional):</label>
        <input type="text" name="referral_code" class="form-control" placeholder="e.g. SOFTPAY88" value="<?php echo htmlspecialchars($ref); ?>" style="text-transform: uppercase;">
        <span style="font-size: 0.75rem; color: var(--text-muted);">Enter an invite code to qualify for referral bonus promotions.</span>
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block mt-2" id="signupSubmitBtn">
        Open Savings Account
      </button>
    </form>
  </div>

  <div class="text-center mt-3" style="font-size: 0.88rem; color: var(--text-secondary);">
    Already have an account? <a href="<?php echo url('login'); ?>" style="font-weight: 700; color: #002970;">Sign In Here</a>
  </div>
</div>

<script>
$(document).ready(function() {
  let isPhoneVerified = false;
  let currentOtp = '';
  let resendCountdown = 60;
  let resendTimerInterval = null;

  // 6-digit box focus and input management
  const $digitBoxes = $('.otp-digit-box');
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

    checkAllBoxesFilled();
  });

  $digitBoxes.on('keydown', function(e) {
    const index = parseInt($(this).data('index'));
    if (e.key === 'Backspace' && !$(this).val() && index > 0) {
      $digitBoxes.eq(index - 1).focus().val('').removeClass('filled');
    }
  });

  // Support paste into boxes
  $digitBoxes.first().on('paste', function(e) {
    const pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text').trim();
    if (/^\d{6}$/.test(pasteData)) {
      e.preventDefault();
      fillOtpBoxes(pasteData);
    }
  });

  function getEnteredOtp() {
    let code = '';
    $digitBoxes.each(function() {
      code += $(this).val();
    });
    return code;
  }

  function fillOtpBoxes(code) {
    if (!code || code.length !== 6) return;
    $digitBoxes.each(function(i) {
      $(this).val(code[i]).addClass('filled');
    });
    $digitBoxes.last().focus();
    checkAllBoxesFilled();
  }

  function checkAllBoxesFilled() {
    const entered = getEnteredOtp();
    if (entered.length === 6) {
      $('#verifyOtpBtn').prop('disabled', false);
    }
  }

  function startResendTimer() {
    clearInterval(resendTimerInterval);
    resendCountdown = 60;
    $('#resendOtpBtn').prop('disabled', true);
    $('#otpTimerSeconds').text(resendCountdown);
    $('#otpCountdownText').show();

    resendTimerInterval = setInterval(function() {
      resendCountdown--;
      $('#otpTimerSeconds').text(resendCountdown);
      if (resendCountdown <= 0) {
        clearInterval(resendTimerInterval);
        $('#otpCountdownText').hide();
        $('#resendOtpBtn').prop('disabled', false);
      }
    }, 1000);
  }

  // Request OTP function
  function triggerSendOtp() {
    const phone = $('#signupPhone').val().trim();
    if (!phone || phone.length !== 10) {
      $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Please enter a valid 10-digit mobile number.</div></div>');
      $('#signupPhone').focus();
      return;
    }

    const $btn = $('#sendOtpBtn');
    $btn.prop('disabled', true).text('Sending...');
    $('#carrierLatencyTag').text('📡 Connecting to carrier network...').show();
    $('#signupAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: {
        action: 'send_otp',
        phone: phone,
        purpose: 'signup'
      },
      dataType: 'json',
      success: function(res) {
        $btn.text('Resend');
        if (res.success) {
          currentOtp = res.simulated_otp || '';
          startResendTimer();

          if (isMobileViewport()) {
            // Mobile users get the dedicated popup modal (media_1790163138408)
            launchMobileSignupOtpModal(currentOtp, '+91 ' + phone);
          } else {
            // Desktop users use inline flow
            $('#otpSection').slideDown(300);
            $('#otpRecipientText').text('Sent 6-digit OTP to +91 ' + phone);

            // Immediate, smooth autofill right into the 6 boxes
            setTimeout(function() {
              fillOtpBoxes(currentOtp);
              $('#carrierLatencyTag').html('⚡ Auto-Filled').fadeIn(150);

              // Auto-trigger verification
              setTimeout(function() {
                $('#verifyOtpBtn').trigger('click');
              }, 300);
            }, 200);
          }

        } else {
          $btn.prop('disabled', false).text('Send OTP');
          $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>' + (res.message || 'Failed to send OTP') + '</div></div>');
        }
      },
      error: function() {
        $btn.prop('disabled', false).text('Send OTP');
        $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Server error while requesting OTP.</div></div>');
      }
    });
  }

  $('#sendOtpBtn').on('click', triggerSendOtp);
  $('#resendOtpBtn').on('click', triggerSendOtp);

  // Verify OTP button
  $('#verifyOtpBtn').on('click', function() {
    const phone = $('#signupPhone').val().trim();
    const entered = getEnteredOtp();

    if (entered.length !== 6) {
      $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Please enter all 6 digits of the OTP.</div></div>');
      return;
    }

    const $btn = $(this);
    $btn.prop('disabled', true).text('Verifying...');
    $('#signupAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: {
        action: 'verify_otp',
        phone: phone,
        otp: entered,
        purpose: 'signup'
      },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          isPhoneVerified = true;
          $('#phoneVerifiedBadge').fadeIn(200);
          $('#sendOtpBtn').hide();
          $('#signupPhone').prop('readonly', true).css('background', 'var(--bg-main)');
          $('#otpSection').slideUp(300);
          $('#signupAlertBox').html('<div class="alert-box success"><span>✅</span><div>Mobile number verified successfully! You can now complete your registration.</div></div>');
          $btn.prop('disabled', false).text('Verify & Confirm Phone');
        } else {
          $btn.prop('disabled', false).text('Verify & Confirm Phone');
          $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>' + (res.message || 'OTP verification failed') + '</div></div>');
          $digitBoxes.css('border-color', '#ef4444');
          setTimeout(() => $digitBoxes.css('border-color', ''), 2000);
        }
      },
      error: function() {
        $btn.prop('disabled', false).text('Verify & Confirm Phone');
        $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Verification server error.</div></div>');
      }
    });
  });

  // Signup form submit
  $('#signupForm').on('submit', function(e) {
    e.preventDefault();

    // If phone not verified yet, automatically prompt OTP
    if (!isPhoneVerified) {
      $('#signupAlertBox').html('<div class="alert-box warning"><span>⚠️</span><div>Please verify your mobile number with OTP first.</div></div>');
      if ($('#otpSection').is(':hidden')) {
        triggerSendOtp();
      }
      return;
    }

    const btn = $('#signupSubmitBtn');
    btn.prop('disabled', true).text('Creating account...');
    $('#signupAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: $(this).serialize() + '&action=signup',
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          const targetAmt = '<?php echo $initialAmount ? (int)$initialAmount : ""; ?>';
          const dest = targetAmt ? ('index.php?page=deposit&amount=' + targetAmt) : 'index.php?page=deposit';
          window.location.href = dest;
        } else {
          $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>' + (res.message || 'Signup failed') + '</div></div>');
          btn.prop('disabled', false).text('Open Savings Account');
        }
      },
      error: function() {
        $('#signupAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Server error. Please try again.</div></div>');
        btn.prop('disabled', false).text('Open Savings Account');
      }
    });
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

  function launchMobileSignupOtpModal(otpCode, phoneMasked) {
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

  // Verify Account Button on Mobile Modal (Registration)
  $('#mobileVerifyAccountBtn').on('click', function() {
    const phone = $('#signupPhone').val().trim();
    const code = getMobileEnteredOtp() || currentOtp;
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
      data: {
        action: 'verify_otp',
        phone: phone,
        otp: code,
        purpose: 'signup'
      },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          isPhoneVerified = true;
          $btn.text('✓ Verified!').css('background', '#10b981');
          $('#phoneVerifiedBadge').fadeIn(200);
          $('#sendOtpBtn').hide();
          $('#signupPhone').prop('readonly', true).css('background', 'var(--bg-main)');
          setTimeout(function() {
            $('#mobileOtpModal').fadeOut(200);
            $('#signupAlertBox').html('<div class="alert-box success"><span>✅</span><div>Mobile number verified successfully! You can now complete your registration.</div></div>');
          }, 400);
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
});
</script>

<?php require __DIR__ . '/../components/mobile_otp_modal.php'; ?>
</body>
</html>
