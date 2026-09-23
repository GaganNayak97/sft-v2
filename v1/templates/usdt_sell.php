<?php
$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();

// Fetch recent USDT sell orders
$stmtU = $db->prepare("SELECT * FROM usdt_orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmtU->execute([$user['id'] ?? 0]);
$recentOrders = $stmtU->fetchAll(\PDO::FETCH_ASSOC);
?>

<div class="usdt-page-container">
  <!-- Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0;">Sell USDT (Crypto Desk)</h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
        Convert Tether (USDT) directly to INR cash in your UPI or Bank account.
      </p>
    </div>
    <div class="paytm-badge" style="font-size: 0.76rem;">
      <?php echo svg_icon('trending', '', 14); ?> Fast Crypto Settlement
    </div>
  </div>

  <!-- USDT Interactive Converter & Order Form -->
  <?php section('usdt_portal', ['user' => $user]); ?>

  <!-- Recent USDT Sell Orders History -->
  <div class="card mb-3" style="padding: 16px 14px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <span style="color: var(--primary);"><?php echo svg_icon('history', '', 18); ?></span>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">Your USDT Sell Orders</h3>
      </div>
      <span class="badge badge-info" style="font-size: 0.7rem;">Verified</span>
    </div>

    <?php if (empty($recentOrders)): ?>
      <div style="text-align: center; padding: 20px 10px; color: var(--text-muted); font-size: 0.82rem;">
        No USDT sell orders placed yet. Enter an amount above to create your first order!
      </div>
    <?php else: ?>
      <div class="transaction-list">
        <?php foreach ($recentOrders as $order): ?>
          <div class="transaction-row" style="padding: 10px 12px;">
            <div class="tx-left" style="gap: 10px;">
              <div class="tx-icon" style="background: rgba(38, 161, 123, 0.15); color: #16a34a; width: 36px; height: 36px; border-radius: 10px;">
                <?php echo svg_icon('usdt', '', 18); ?>
              </div>
              <div style="min-width: 0;">
                <div class="tx-title" style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">
                  Sold <?php echo number_format($order['usdt_amount'], 2); ?> USDT @ ₹<?php echo number_format($order['exchange_rate'], 2); ?>
                </div>
                <div class="tx-date" style="font-size: 0.72rem;">
                  <?php echo date('d M, h:i A', strtotime($order['created_at'])); ?> &bull; <?php echo htmlspecialchars($order['network']); ?>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="tx-amount in" style="font-size: 0.92rem;">+<?php echo format_currency($order['inr_amount']); ?></div>
              <span class="badge badge-<?php echo ($order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'info')); ?>" style="font-size: 0.68rem; padding: 2px 6px;">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
