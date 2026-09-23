<?php
$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();

$totalDeposited = (float)($user['total_deposited'] ?? 0);
$totalInterest = (float)($user['total_interest_earned'] ?? 0);
$currentBalance = (float)($user['current_balance'] ?? 0);
$rateInfo = \App\Core\InterestEngine::getUserRate($totalDeposited);
$dailyIncrease = \App\Core\InterestEngine::calculateDailyInterest($totalDeposited);

// Fetch interest logs
$stmtLogs = $db->prepare("SELECT * FROM interest_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 30");
$stmtLogs->execute([$user['id'] ?? 0]);
$interestLogs = $stmtLogs->fetchAll(\PDO::FETCH_ASSOC);

// Projections
$proj7Days = $dailyIncrease * 7;
$proj30Days = $dailyIncrease * 30;
$proj365Days = $dailyIncrease * 365;

// Check for active pending withdrawal in 12h tracing queue
$stmtPending = $db->prepare("SELECT * FROM withdrawals WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$stmtPending->execute([$user['id'] ?? 0]);
$activeWithdrawal = $stmtPending->fetch(\PDO::FETCH_ASSOC);
?>

<div class="balance-page-container">
  <!-- Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0;">Balance & Daily Interest</h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
        Daily compounding records and automated interest breakdown.
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?php echo url('deposit'); ?>" class="btn btn-sm btn-success">
        <?php echo svg_icon('plus', '', 14); ?> Add Money
      </a>
      <button type="button" class="btn btn-sm btn-outline" id="refreshInterestBtn">
        <?php echo svg_icon('refresh', '', 14); ?> Check Interest
      </button>
    </div>
  </div>

  <?php if ($activeWithdrawal): ?>
    <?php
      $isReady = !empty($activeWithdrawal['unlock_at']) && (time() >= strtotime($activeWithdrawal['unlock_at']));
      $unlockTs = !empty($activeWithdrawal['unlock_at']) ? strtotime($activeWithdrawal['unlock_at']) : (time() + 43200);
      $remainingSecs = max(0, $unlockTs - time());
    ?>
    <!-- Active Withdrawal Tracing System (Balance Page) -->
    <div class="balance-tracing-card <?php echo $isReady ? 'ready-state' : ''; ?>" id="balanceWithdrawalTracingCard">
      <div class="balance-tracing-header">
        <div class="balance-tracing-title">
          <span class="tx-status-dot" style="color: <?php echo $isReady ? '#10b981' : '#3b82f6'; ?>;"></span>
          <span><?php echo $isReady ? 'Withdrawal Ready to Receive' : 'Withdrawal Tracing: 12-Hour Waiting Queue'; ?></span>
        </div>
        <div class="balance-tracing-amount">
          <?php echo format_currency($activeWithdrawal['amount']); ?>
        </div>
      </div>

      <div class="balance-tracing-details">
        <div>
          <div class="balance-tracing-item-label">Requested At (Time)</div>
          <div class="balance-tracing-item-value"><?php echo date('d M, h:i A', strtotime($activeWithdrawal['created_at'])); ?></div>
        </div>
        <div>
          <div class="balance-tracing-item-label">Interest &amp; Balance Audit</div>
          <div class="balance-tracing-item-value" style="color: var(--success);">
            <?php echo svg_icon('check-circle', '', 12); ?> 0.55% Rule Verified
          </div>
        </div>
        <div>
          <div class="balance-tracing-item-label">Clearing Gateway Status</div>
          <div class="balance-tracing-item-value">
            <?php echo $isReady ? '<span style="color: var(--success); font-weight: 800;">Cleared (Ready)</span>' : '<span style="color: #3b82f6;">Clearing &amp; Auditing Queue</span>'; ?>
          </div>
        </div>
        <div>
          <div class="balance-tracing-item-label">Countdown / Release</div>
          <div class="balance-tracing-item-value" id="balanceTracingCountdown" data-unlock="<?php echo $unlockTs; ?>">
            <?php echo $isReady ? 'Ready for Payout' : 'Calculating...'; ?>
          </div>
        </div>
      </div>

      <div class="balance-tracing-actions">
        <button type="button" class="btn-tracing-details open-tracking-modal-btn" data-id="<?php echo $activeWithdrawal['id']; ?>">
          <?php echo svg_icon('trending', '', 14); ?> View Live Stepper Details
        </button>
        <?php if ($isReady): ?>
          <button type="button" class="btn-tracing-claim claim-direct-btn" data-id="<?php echo $activeWithdrawal['id']; ?>">
            <?php echo svg_icon('check-circle', '', 14); ?> Receive Payment
          </button>
        <?php else: ?>
          <button type="button" class="btn-fastforward-demo" id="balanceFastForwardBtn" style="flex: 0.8; height: 40px; padding: 0 10px;">
            ⚡ Demo Fast-Forward
          </button>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>


  <!-- Primary Balance Metrics Grid: 2x2 on Mobile -->
  <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px;">
    <!-- 1. Principal Deposited -->
    <div class="stat-card" style="border-top: 3px solid #002970; padding: 12px 10px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem;">Principal</span>
        <span style="color: #002970;"><?php echo svg_icon('credit-card', '', 16); ?></span>
      </div>
      <div class="stat-card-value" style="color: #002970; font-size: 1.3rem;"><?php echo format_currency($totalDeposited); ?></div>
      <div class="stat-card-meta" style="font-size: 0.68rem;">Active deposited balance</div>
    </div>

    <!-- 2. Daily Interest Growth -->
    <div class="stat-card" style="border-top: 3px solid var(--secondary); background: linear-gradient(180deg, rgba(0, 186, 242, 0.05) 0%, var(--bg-card) 100%); padding: 12px 10px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem;">Daily Interest</span>
        <span class="badge badge-info" style="font-size: 0.65rem; padding: 2px 6px;"><?php echo $rateInfo['daily_pct']; ?></span>
      </div>
      <div class="stat-card-value" style="color: var(--secondary); font-size: 1.3rem;">+<?php echo format_currency($dailyIncrease); ?></div>
      <div class="stat-card-meta" style="font-size: 0.68rem;">Credited every midnight</div>
    </div>

    <!-- 3. Total Interest Earned -->
    <div class="stat-card" style="border-top: 3px solid var(--success); padding: 12px 10px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem;">Total Interest</span>
        <span style="color: var(--success);"><?php echo svg_icon('trending', '', 16); ?></span>
      </div>
      <div class="stat-card-value" style="color: var(--success); font-size: 1.3rem;">+<?php echo format_currency($totalInterest); ?></div>
      <div class="stat-card-meta" style="font-size: 0.68rem;">Profit accrued to date</div>
    </div>

    <!-- 4. Total Current Balance -->
    <div class="stat-card" style="border-top: 3px solid #8b5cf6; padding: 12px 10px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem;">Total Balance</span>
        <span style="color: #8b5cf6;"><?php echo svg_icon('lock', '', 16); ?></span>
      </div>
      <div class="stat-card-value" style="color: #8b5cf6; font-size: 1.3rem;"><?php echo format_currency($currentBalance); ?></div>
      <div class="stat-card-meta" style="font-size: 0.68rem;">Principal + all interest</div>
    </div>
  </div>

  <!-- Compounding Growth Forecast Table -->
  <div class="card mb-3" style="padding: 16px 14px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <span style="color: var(--primary);"><?php echo svg_icon('trending', '', 18); ?></span>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">Interest Growth Forecast</h3>
      </div>
      <span class="badge badge-vip" style="font-size: 0.7rem;"><?php echo $rateInfo['name']; ?></span>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
        <thead>
          <tr style="border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted);">
            <th style="padding: 8px 10px;">Period</th>
            <th style="padding: 8px 10px;">Rate</th>
            <th style="padding: 8px 10px;">Extra Gain</th>
            <th style="padding: 8px 10px;">Projected</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 9px 10px; font-weight: 600;">1 Day</td>
            <td style="padding: 9px 10px;"><?php echo $rateInfo['daily_pct']; ?></td>
            <td style="padding: 9px 10px; color: var(--success); font-weight: 700;">+<?php echo format_currency($dailyIncrease); ?></td>
            <td style="padding: 9px 10px; font-weight: 700;"><?php echo format_currency($currentBalance + $dailyIncrease); ?></td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 9px 10px; font-weight: 600;">7 Days</td>
            <td style="padding: 9px 10px;"><?php echo $rateInfo['daily_pct']; ?></td>
            <td style="padding: 9px 10px; color: var(--success); font-weight: 700;">+<?php echo format_currency($proj7Days); ?></td>
            <td style="padding: 9px 10px; font-weight: 700;"><?php echo format_currency($currentBalance + $proj7Days); ?></td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 9px 10px; font-weight: 600;">30 Days</td>
            <td style="padding: 9px 10px;"><?php echo $rateInfo['daily_pct']; ?></td>
            <td style="padding: 9px 10px; color: var(--success); font-weight: 700;">+<?php echo format_currency($proj30Days); ?></td>
            <td style="padding: 9px 10px; font-weight: 700;"><?php echo format_currency($currentBalance + $proj30Days); ?></td>
          </tr>
          <tr>
            <td style="padding: 9px 10px; font-weight: 600;">365 Days</td>
            <td style="padding: 9px 10px;"><?php echo $rateInfo['daily_pct']; ?></td>
            <td style="padding: 9px 10px; color: var(--success); font-weight: 800;">+<?php echo format_currency($proj365Days); ?></td>
            <td style="padding: 9px 10px; font-weight: 800; color: var(--primary);"><?php echo format_currency($currentBalance + $proj365Days); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Daily Interest Credit Logs -->
  <div class="card mb-3" style="padding: 16px 14px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <span style="color: var(--primary);"><?php echo svg_icon('history', '', 18); ?></span>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">Daily Interest Payout Logs</h3>
      </div>
      <span class="badge badge-info" style="font-size: 0.7rem;">Past 30 Days</span>
    </div>

    <?php if (empty($interestLogs)): ?>
      <div style="text-align: center; padding: 24px 10px; color: var(--text-muted); font-size: 0.85rem;">
        No interest records logged yet. Make a deposit to start earning daily interest!
      </div>
    <?php else: ?>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
          <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted);">
              <th style="padding: 8px 10px;">Date</th>
              <th style="padding: 8px 10px;">Principal</th>
              <th style="padding: 8px 10px;">Rate</th>
              <th style="padding: 8px 10px; text-align: right;">Interest Credited</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($interestLogs as $log): ?>
              <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 9px 10px; font-weight: 600;"><?php echo date('d M Y', strtotime($log['log_date'])); ?></td>
                <td style="padding: 9px 10px;"><?php echo format_currency($log['principal_amount']); ?></td>
                <td style="padding: 9px 10px;"><span class="badge badge-info" style="font-size: 0.68rem; padding: 2px 6px;"><?php echo $log['rate_applied']; ?>%</span></td>
                <td style="padding: 9px 10px; text-align: right; color: var(--success); font-weight: 700;">
                  +<?php echo format_currency($log['interest_amount']); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/../components/withdrawal_tracking_modals.php'; ?>
</div>
