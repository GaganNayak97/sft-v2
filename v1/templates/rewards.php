<?php
$user = $user ?? auth_user();
$totalDeposited = (float)($user['total_deposited'] ?? 0);
$tier = $user['tier'] ?? 'standard';
$vipMin = (float)app_config('app.vip_min_deposit', 5000);
$deficit = max(0, $vipMin - $totalDeposited);
$progress = min(100, round(($totalDeposited / $vipMin) * 100, 1));
?>

<div class="rewards-page-container">
  <!-- Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin: 0;">👑 Business Reward System</h2>
      <p style="font-size: 0.88rem; color: var(--text-muted); margin: 4px 0 0 0;">
        Accelerate your daily returns. High depositors automatically unlock 1.00% daily interest instead of 0.88%.
      </p>
    </div>
    <div>
      <span class="badge <?php echo ($tier === 'vip') ? 'badge-vip' : 'badge-info'; ?>" style="font-size: 0.9rem; padding: 6px 14px;">
        <?php echo ($tier === 'vip') ? '👑 You are a Business VIP' : 'Standard Tier (0.88%)'; ?>
      </span>
    </div>
  </div>

  <!-- VIP Upgrade Progress Banner -->
  <div class="card mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, var(--bg-card) 100%); border: 2px solid rgba(245, 158, 11, 0.3);">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <span style="font-size: 1.4rem;">🚀</span>
        <strong style="font-size: 1.05rem; color: #b45309;">Business VIP Tier Qualification Progress</strong>
      </div>
      <span style="font-weight: 800; color: #b45309;"><?php echo $progress; ?>%</span>
    </div>

    <div class="progress-container" style="height: 14px;">
      <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%; background: linear-gradient(90deg, #f59e0b, #d97706);"></div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 0.82rem;">
      <span class="text-muted">Deposited: <?php echo format_currency($totalDeposited); ?></span>
      <?php if ($tier !== 'vip'): ?>
        <span style="color: #b45309; font-weight: 700;">Deposit <?php echo format_currency($deficit); ?> more to unlock 1.0% daily interest!</span>
      <?php else: ?>
        <span style="color: var(--success); font-weight: 700;">Active VIP Member! You are earning 1.0% flat daily interest.</span>
      <?php endif; ?>
      <span class="text-muted">Target: ₹<?php echo number_format($vipMin); ?></span>
    </div>
  </div>

  <!-- Side-by-Side Plan Comparison Section -->
  <?php section('reward_tiers', ['user' => $user]); ?>

  <!-- Earnings Difference Calculator Card -->
  <div class="card mb-4">
    <div class="card-header">
      <div class="d-flex align-items-center gap-2">
        <span style="font-size: 1.2rem;">💰</span>
        <h3 class="card-title">Earnings Comparison: 0.88% vs 1.00%</h3>
      </div>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
        <thead>
          <tr style="border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted);">
            <th style="padding: 10px 14px;">Deposit Amount</th>
            <th style="padding: 10px 14px;">Standard Rate (0.88%)</th>
            <th style="padding: 10px 14px;">Business VIP Rate (1.00%)</th>
            <th style="padding: 10px 14px; text-align: right;">Extra Yearly Profit</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 12px 14px; font-weight: 600;">₹5,000</td>
            <td style="padding: 12px 14px;">₹44.00 / day</td>
            <td style="padding: 12px 14px; color: #b45309; font-weight: 700;">₹50.00 / day</td>
            <td style="padding: 12px 14px; text-align: right; color: var(--success); font-weight: 700;">+₹2,190 / yr</td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 12px 14px; font-weight: 600;">₹10,000</td>
            <td style="padding: 12px 14px;">₹88.00 / day</td>
            <td style="padding: 12px 14px; color: #b45309; font-weight: 700;">₹100.00 / day</td>
            <td style="padding: 12px 14px; text-align: right; color: var(--success); font-weight: 700;">+₹4,380 / yr</td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 12px 14px; font-weight: 600;">₹25,000</td>
            <td style="padding: 12px 14px;">₹220.00 / day</td>
            <td style="padding: 12px 14px; color: #b45309; font-weight: 700;">₹250.00 / day</td>
            <td style="padding: 12px 14px; text-align: right; color: var(--success); font-weight: 700;">+₹10,950 / yr</td>
          </tr>
          <tr>
            <td style="padding: 12px 14px; font-weight: 600;">₹50,000</td>
            <td style="padding: 12px 14px;">₹440.00 / day</td>
            <td style="padding: 12px 14px; color: #b45309; font-weight: 700;">₹500.00 / day</td>
            <td style="padding: 12px 14px; text-align: right; color: var(--success); font-weight: 800;">+₹21,900 / yr</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="text-center mb-4">
    <a href="<?php echo url('deposit', ['amount' => '5000']); ?>" class="btn btn-primary btn-lg">
      Deposit ₹5,000 & Upgrade to VIP Now &rarr;
    </a>
  </div>
</div>
