<?php
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
  <title>Reset Password - <?php echo htmlspecialchars(app_config('app.name', 'SoftPay')); ?></title>
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
  <div class="text-center mb-4">
    <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay" style="width: 72px; height: 72px; border-radius: 18px; margin: 0 auto 12px auto; display: block; box-shadow: 0 8px 24px rgba(0, 186, 242, 0.4); object-fit: cover;">
    <h1 style="font-size: 1.5rem; font-weight: 800; color: #002970; margin-bottom: 4px;">Reset Account Password</h1>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">Enter your registered details to recover access.</p>
  </div>

  <div class="card" style="box-shadow: 0 10px 25px rgba(0, 41, 112, 0.08); padding: 28px;">
    <div id="forgotAlertBox"></div>

    <form id="forgotForm">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label class="form-label">Registered Email or Phone:</label>
        <input type="text" name="identifier" class="form-control" placeholder="e.g. demo@softpay.com" required>
      </div>

      <div class="form-group">
        <label class="form-label">Enter New Password:</label>
        <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" minlength="6" required>
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block mt-2" id="forgotSubmitBtn">
        Reset Password
      </button>
    </form>
  </div>

  <div class="text-center mt-3" style="font-size: 0.88rem; color: var(--text-secondary);">
    Remembered your credentials? <a href="<?php echo url('login'); ?>" style="font-weight: 700; color: #002970;">Return to Sign In</a>
  </div>
</div>

<script>
$(document).ready(function() {
  $('#forgotForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#forgotSubmitBtn');
    btn.prop('disabled', true).text('Updating password...');
    $('#forgotAlertBox').empty();

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: $(this).serialize() + '&action=forgot',
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#forgotAlertBox').html('<div class="alert-box success"><span>✅</span><div>Password reset successfully! Redirecting to login...</div></div>');
          setTimeout(function() {
            window.location.href = 'index.php?page=login';
          }, 1500);
        } else {
          $('#forgotAlertBox').html('<div class="alert-box danger"><span>❌</span><div>' + (res.message || 'Account not found') + '</div></div>');
          btn.prop('disabled', false).text('Reset Password');
        }
      },
      error: function() {
        $('#forgotAlertBox').html('<div class="alert-box danger"><span>❌</span><div>Server error. Please try again.</div></div>');
        btn.prop('disabled', false).text('Reset Password');
      }
    });
  });
});
</script>
</body>
</html>
