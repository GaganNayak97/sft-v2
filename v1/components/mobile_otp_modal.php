<?php
// Mobile Dedicated OTP Verification Popup Modal (media_1790163138408)
?>
<div class="mobile-otp-backdrop" id="mobileOtpModal" style="display: none;">
  <div class="mobile-otp-card">
    <button type="button" class="mobile-otp-close-btn" id="closeMobileOtpBtn" aria-label="Close">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
    
    <h3 class="mobile-otp-title">Verification Code</h3>
    <p class="mobile-otp-subtitle" id="mobileOtpSubtitle">We sent a 6-digit code to your device.</p>
    
    <!-- 6 Segmented Digit Boxes (media_1790163138408) -->
    <div class="mobile-otp-boxes-wrap" id="mobileOtpBoxesWrap">
      <input type="text" inputmode="numeric" class="mobile-otp-box" maxlength="1" data-index="0" autocomplete="one-time-code" autofocus>
      <input type="text" inputmode="numeric" class="mobile-otp-box" maxlength="1" data-index="1">
      <input type="text" inputmode="numeric" class="mobile-otp-box" maxlength="1" data-index="2">
      <input type="text" inputmode="numeric" class="mobile-otp-box" maxlength="1" data-index="3">
      <input type="text" inputmode="numeric" class="mobile-otp-box" maxlength="1" data-index="4">
      <input type="text" inputmode="numeric" class="mobile-otp-box" maxlength="1" data-index="5">
    </div>

    <div id="mobileOtpAlertBox" style="margin-bottom: 14px;"></div>

    <button type="button" class="mobile-otp-verify-btn" id="mobileVerifyAccountBtn">
      Verify Account
    </button>

    <div class="mobile-otp-resend-row">
      <span id="mobileOtpResendTimerText">Resend code in <strong id="mobileOtpTimerSec">60</strong>s</span>
      <button type="button" class="mobile-otp-resend-link" id="mobileResendCodeBtn" disabled>Resend Code</button>
    </div>
  </div>
</div>
