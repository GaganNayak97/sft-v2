<?php
$user = $user ?? auth_user();
$tier = $user['tier'] ?? 'standard';
$deposit = (float)($user['total_deposited'] ?? 0);
$vipMin = (float)app_config('app.vip_min_deposit', 5000);
$deficit = max(0, $vipMin - $deposit);
?>
<div class="card mb-3" style="padding: 20px 20px;">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <h3 class="card-title" style="font-size: 0.95rem; margin: 0;">VIP Business Reward Tiers</h3>
      <p style="font-size: 0.78rem; color: var(--text-muted); margin: 2px 0 0 0;">
        Deposits of ₹<?php echo number_format($vipMin); ?>+ earn 1.00% daily interest.
      </p>
    </div>
    <span class="badge <?php echo ($tier === 'vip') ? 'badge-vip' : 'badge-info'; ?>" style="font-size: 0.72rem;">
      <?php echo strtoupper($tier); ?>
    </span>
  </div>

  <?php if ($tier !== 'vip'): ?>
    <div class="d-flex align-items-center gap-2 mb-3" style="background: rgba(0, 186, 242, 0.07); border: 1px solid rgba(0, 186, 242, 0.2); color: var(--text-primary); padding: 11px 14px; border-radius: 16px; font-size: 0.83rem;">
      <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('trending', '', 16); ?></span>
      <div>
        <strong>Upgrade to 1.00% Daily Interest!</strong> Deposit <strong><?php echo format_currency($deficit); ?></strong> more to unlock VIP returns.
      </div>
    </div>
  <?php else: ?>
    <div class="d-flex align-items-center gap-2 mb-3" style="background: rgba(0, 186, 242, 0.07); border: 1px solid rgba(0, 186, 242, 0.2); color: var(--text-primary); padding: 11px 14px; border-radius: 16px; font-size: 0.83rem;">
      <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('crown', '', 16); ?></span>
      <div>
        <strong>You are a Business VIP Member!</strong> Enjoying 1.00% daily interest.
      </div>
    </div>
  <?php endif; ?>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
    <!-- Standard Tier Card -->
    <div class="card" style="border: 1.5px solid var(--border-color); background: var(--bg-main); padding: 16px; border-radius: 18px; position: relative;">
      <?php if ($tier === 'standard'): ?>
        <span class="badge" style="position: absolute; top: 12px; right: 12px; font-size: 0.7rem; background: rgba(0, 186, 242, 0.12); color: #00BAF2; border: 1px solid rgba(0, 186, 242, 0.25); border-radius: 9999px; padding: 2px 8px;">Active</span>
      <?php endif; ?>
      <h4 style="font-size: 0.95rem; color: var(--text-primary); margin-bottom: 4px; font-weight: 700;">Standard Savings</h4>
      <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary); margin-bottom: 8px;">0.88% <span style="font-size: 0.82rem; font-weight: 500; color: var(--text-muted);">/ day</span></div>
      <ul style="list-style: none; padding: 0; font-size: 0.8rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 7px; margin-bottom: 14px;">
        <li class="d-flex align-items-center gap-2"><span style="color: var(--secondary);"><?php echo svg_icon('check-circle', '', 14); ?></span> <span>Deposit range: ₹100 – ₹4,999</span></li>
        <li class="d-flex align-items-center gap-2"><span style="color: var(--secondary);"><?php echo svg_icon('check-circle', '', 14); ?></span> <span>Daily interest automated credit</span></li>
        <li class="d-flex align-items-center gap-2"><span style="color: var(--secondary);"><?php echo svg_icon('check-circle', '', 14); ?></span> <span>0.55% interest withdrawal eligibility</span></li>
      </ul>
      <a href="<?php echo url('deposit'); ?>" class="btn btn-outline btn-block btn-sm" style="border-radius: 9999px; font-weight: 600;">Deposit in Standard</a>
    </div>

    <!-- VIP Business Tier Card -->
    <div class="card" style="border: 1.5px solid rgba(0, 186, 242, 0.4); background: var(--bg-main); padding: 16px; border-radius: 18px; position: relative;">
      <span class="badge badge-vip" style="position: absolute; top: 12px; right: 12px; font-size: 0.7rem; border-radius: 9999px; padding: 2px 8px;">Recommended</span>
      <h4 style="font-size: 0.95rem; color: var(--text-primary); margin-bottom: 4px; font-weight: 700;">Business VIP Tier</h4>
      <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary); margin-bottom: 8px;">1.00% <span style="font-size: 0.82rem; font-weight: 500; color: var(--text-muted);">/ day</span></div>
      <ul style="list-style: none; padding: 0; font-size: 0.8rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 7px; margin-bottom: 14px;">
        <li class="d-flex align-items-center gap-2"><span style="color: var(--secondary);"><?php echo svg_icon('check-circle', '', 14); ?></span> <span><strong>Min deposit: ₹5,000+</strong></span></li>
        <li class="d-flex align-items-center gap-2"><span style="color: var(--secondary);"><?php echo svg_icon('check-circle', '', 14); ?></span> <span><strong>1.00% Flat Daily Interest</strong> (30%/mo)</span></li>
        <li class="d-flex align-items-center gap-2"><span style="color: var(--secondary);"><?php echo svg_icon('check-circle', '', 14); ?></span> <span>Priority UPI 2.0 Payouts</span></li>
      </ul>
      <a href="<?php echo url('deposit', ['amount' => '5000']); ?>" class="btn btn-block btn-sm" style="background: linear-gradient(135deg, #0084FF 0%, #0071E3 100%); color: #ffffff; border: none; border-radius: 9999px; font-weight: 700; padding: 12px 16px; box-shadow: 0 4px 16px rgba(0, 122, 255, 0.35);">
        Upgrade to VIP (₹5,000+)
      </a>
    </div>
  </div>
</div>
