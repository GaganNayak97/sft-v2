<?php
$user = $user ?? auth_user();
$totalDeposited = (float)($user['total_deposited'] ?? 0);
$totalInterest = (float)($user['total_interest_earned'] ?? 0);
$currentBalance = (float)($user['current_balance'] ?? 0);

$rateInfo = \App\Core\InterestEngine::getUserRate($totalDeposited);
$dailyIncrease = \App\Core\InterestEngine::calculateDailyInterest($totalDeposited);
?>
<div class="paytm-gradient-card mb-3">
  <!-- Top Row: Verified Badge & Live Interest Ticker -->
  <div class="d-flex justify-content-between align-items-center mb-1">
    <div class="d-flex align-items-center gap-2">
      <span style="font-size: 0.82rem; opacity: 0.9; font-weight: 600; letter-spacing: 0.3px;">SOFTPAY SAVINGS WALLET</span>
      <span class="paytm-verified">
        <?php echo svg_icon('check-circle', '', 14); ?>
        Verified
      </span>
    </div>
    <div class="paytm-ticker">
      <span class="dot"></span>
      <span><?php echo $rateInfo['daily_pct']; ?> Daily</span>
    </div>
  </div>

  <!-- Primary Balance -->
  <div class="paytm-card-balance">
    <span><?php echo format_currency($currentBalance); ?></span>
  </div>

  <!-- Daily Earnings Indicator Pill -->
  <div class="d-inline-flex align-items-center gap-2 mb-3" style="background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.22); padding: 5px 13px; border-radius: var(--border-radius-full); font-size: 0.82rem; font-weight: 700; color: #ffffff; backdrop-filter: blur(8px);">
    <?php echo svg_icon('trending', '', 15); ?>
    <span>+<?php echo format_currency($dailyIncrease); ?> daily extra interest</span>
  </div>

  <!-- Quick Action Buttons: Unified High-End Controls -->
  <div class="d-flex gap-2 mb-3 paytm-hero-actions">
    <a href="<?php echo url('deposit'); ?>" class="btn" style="background: #ffffff; color: #002970; border: none; border-radius: 9999px; padding: 11px 22px; font-weight: 800; font-size: 0.92rem; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18); display: inline-flex; align-items: center; gap: 8px;">
      <?php echo svg_icon('arrow-down-left', '', 18); ?>
      <span>Add Money (UPI)</span>
    </a>
    <a href="<?php echo url('withdraw'); ?>" class="btn" style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1.5px solid rgba(255, 255, 255, 0.35); border-radius: 9999px; padding: 11px 22px; font-weight: 700; font-size: 0.92rem; display: inline-flex; align-items: center; gap: 8px; backdrop-filter: blur(8px);">
      <?php echo svg_icon('arrow-up-right', '', 18); ?>
      <span>Withdraw</span>
    </a>
  </div>

  <!-- Bottom Compact Stats Strip -->
  <div class="d-flex align-items-center justify-content-between" style="border-top: 1px solid rgba(255, 255, 255, 0.15); padding-top: 10px;">
    <div>
      <div style="font-size: 0.7rem; opacity: 0.8; text-transform: uppercase; font-weight: 600;">Active Deposit</div>
      <div style="font-size: 1rem; font-weight: 700;"><?php echo format_currency($totalDeposited); ?></div>
    </div>

    <div class="text-right">
      <div style="font-size: 0.7rem; opacity: 0.8; text-transform: uppercase; font-weight: 600;">Total Interest Accrued</div>
      <div style="font-size: 1rem; font-weight: 700; color: #38bdf8;">+<?php echo format_currency($totalInterest); ?></div>
    </div>
  </div>
</div>
