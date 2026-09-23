<?php
$type = $tx['type'] ?? 'deposit';
$amount = (float)($tx['amount'] ?? 0);
$direction = $tx['direction'] ?? 'in';
$title = $tx['description'] ?? 'Transaction';
$date = !empty($tx['created_at']) ? date('d M Y, h:i A', strtotime($tx['created_at'])) : date('d M Y');
$status = $tx['status'] ?? 'success';
$refId = $tx['reference_id'] ?? ('TXN-' . ($tx['id'] ?? rand(1000, 9999)));

// Gateway label & icon
$iconName = 'arrow-down-left';
$typeTag = 'Deposit';
switch ($type) {
    case 'deposit':
        $iconName = 'arrow-down-left';
        $typeTag = 'UPI 2.0 Deposit';
        break;
    case 'withdrawal':
        $iconName = 'arrow-up-right';
        $typeTag = 'Bank / UPI Payout';
        break;
    case 'interest':
        $iconName = 'trending';
        $typeTag = 'Daily Interest 1.0%';
        break;
    case 'referral_bonus':
        $iconName = 'gift';
        $typeTag = '5% Referral Reward';
        break;
    case 'usdt_sell':
        $iconName = 'usdt';
        $typeTag = 'USDT Desk Liquidation';
        break;
}

$isCredit = ($direction === 'in');
?>
<div class="transaction-row tx-interactive-row" 
     data-tx-id="<?php echo htmlspecialchars($tx['id'] ?? ''); ?>"
     data-type="<?php echo htmlspecialchars($type); ?>"
     data-type-label="<?php echo htmlspecialchars($typeTag); ?>"
     data-direction="<?php echo htmlspecialchars($direction); ?>"
     data-status="<?php echo htmlspecialchars($status); ?>"
     data-ref="<?php echo htmlspecialchars($refId); ?>"
     data-title="<?php echo htmlspecialchars($title); ?>"
     data-amount="<?php echo ($isCredit ? '+' : '-') . format_currency($amount); ?>"
     data-raw-amount="<?php echo $amount; ?>"
     data-date="<?php echo htmlspecialchars($date); ?>"
     title="Click to view official digital receipt">
  
  <div class="tx-left" style="min-width: 0; flex: 1;">
    <div class="tx-icon" style="background: <?php echo $isCredit ? 'rgba(0, 186, 242, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $isCredit ? 'var(--secondary, #00BAF2)' : '#ef4444'; ?>; width: 42px; height: 42px; border-radius: 14px; border: 1px solid <?php echo $isCredit ? 'rgba(0, 186, 242, 0.22)' : 'rgba(239, 68, 68, 0.22)'; ?>; flex-shrink: 0;">
      <?php echo svg_icon($iconName, '', 20); ?>
    </div>
    <div style="min-width: 0; flex: 1;">
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="tx-title" style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
          <?php echo htmlspecialchars($title); ?>
        </span>
      </div>
      <div class="d-flex align-items-center gap-2 text-muted tx-meta-row" style="font-size: 0.76rem;">
        <span><?php echo htmlspecialchars($date); ?></span>
        <span class="d-none d-sm-inline">&bull;</span>
        <span class="font-monospace d-none d-sm-inline" style="letter-spacing: 0.3px;">Ref: <?php echo htmlspecialchars($refId); ?></span>
      </div>
    </div>
  </div>

  <div class="text-right d-flex align-items-center gap-2" style="flex-shrink: 0;">
    <div>
      <div class="tx-amount <?php echo $direction; ?>" style="font-size: 1.05rem; font-weight: 800; color: <?php echo $isCredit ? 'var(--primary)' : '#ef4444'; ?>;">
        <?php echo ($isCredit ? '+' : '-') . format_currency($amount); ?>
      </div>
      <span class="badge" style="font-size: 0.68rem; padding: 2px 7px; border-radius: 9999px; background: <?php echo $status === 'success' ? 'rgba(0, 186, 242, 0.12)' : 'rgba(245, 158, 11, 0.12)'; ?>; color: <?php echo $status === 'success' ? '#00BAF2' : '#f59e0b'; ?>; border: 1px solid <?php echo $status === 'success' ? 'rgba(0, 186, 242, 0.25)' : 'rgba(245, 158, 11, 0.25)'; ?>;">
        <?php echo ucfirst($status); ?>
      </span>
    </div>
    <span class="tx-chevron" style="color: var(--text-muted); opacity: 0.6; margin-left: 4px;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </span>
  </div>
</div>
