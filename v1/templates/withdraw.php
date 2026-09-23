<?php
$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();

$eligibility = \App\Core\InterestEngine::getWithdrawalEligibility($user['id'] ?? 0);
$isEligible = $eligibility['eligible'];
$progress = $eligibility['progress_pct'];
$minPct = $eligibility['min_required_pct'];

// Fetch recent withdrawals
$stmtW = $db->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmtW->execute([$user['id'] ?? 0]);
$recentWithdrawals = $stmtW->fetchAll(\PDO::FETCH_ASSOC);

// Check if user has an active pending withdrawal in 12h queue
$stmtPending = $db->prepare("SELECT * FROM withdrawals WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$stmtPending->execute([$user['id'] ?? 0]);
$activeWithdrawal = $stmtPending->fetch(\PDO::FETCH_ASSOC);
?>

<div class="withdraw-page-container">
  <?php if ($activeWithdrawal): ?>
    <?php
      $isReady = !empty($activeWithdrawal['unlock_at']) && (time() >= strtotime($activeWithdrawal['unlock_at']));
    ?>
    <!-- Active Withdrawal 12h Queue Banner -->
    <div class="balance-tracing-card <?php echo $isReady ? 'ready-state' : ''; ?>" id="withdrawActiveBanner">
      <div class="balance-tracing-header">
        <div class="balance-tracing-title">
          <span class="tx-status-dot" style="color: <?php echo $isReady ? '#10b981' : '#3b82f6'; ?>;"></span>
          <span><?php echo $isReady ? 'Withdrawal Payout Ready' : 'Withdrawal in 12-Hour Queue'; ?></span>
        </div>
        <div class="balance-tracing-amount">
          <?php echo format_currency($activeWithdrawal['amount']); ?>
        </div>
      </div>

      <div class="balance-tracing-details">
        <div>
          <div class="balance-tracing-item-label">Requested At</div>
          <div class="balance-tracing-item-value"><?php echo date('d M, h:i A', strtotime($activeWithdrawal['created_at'])); ?></div>
        </div>
        <div>
          <div class="balance-tracing-item-label">Audit Status</div>
          <div class="balance-tracing-item-value" style="color: var(--success);">0.55% Interest Verified</div>
        </div>
      </div>

      <div class="balance-tracing-actions">
        <button type="button" class="btn-tracing-details open-tracking-modal-btn" data-id="<?php echo $activeWithdrawal['id']; ?>">
          <?php echo svg_icon('trending', '', 14); ?> View Live Stepper
        </button>
        <?php if ($isReady): ?>
          <button type="button" class="btn-tracing-claim claim-direct-btn" data-id="<?php echo $activeWithdrawal['id']; ?>">
            <?php echo svg_icon('check-circle', '', 14); ?> Receive Payment
          </button>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0;">Withdraw Funds</h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
        Direct UPI & Bank transfer. Minimum <?php echo $minPct; ?>% accumulated interest required.
      </p>
    </div>
    <div>
      <?php if ($isEligible): ?>
        <span class="badge badge-success" style="font-size: 0.78rem; padding: 4px 10px;">
          <?php echo svg_icon('check-circle', '', 14); ?> Eligible for Withdrawal
        </span>
      <?php else: ?>
        <span class="badge badge-danger" style="font-size: 0.78rem; padding: 4px 10px;">
          <?php echo svg_icon('lock', '', 14); ?> Locked (Under <?php echo $minPct; ?>% Rule)
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- 0.55% Interest Eligibility Status Banner -->
  <div class="card mb-3" style="border-left: 4px solid <?php echo $isEligible ? 'var(--success)' : 'var(--danger)'; ?>; padding: 14px;">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <div class="d-flex align-items-center gap-2">
        <span style="color: <?php echo $isEligible ? 'var(--success)' : 'var(--danger)'; ?>;">
          <?php echo svg_icon($isEligible ? 'unlock' : 'shield', '', 18); ?>
        </span>
        <strong style="font-size: 0.9rem; color: var(--text-primary);">
          0.55% Interest Safety Requirement
        </strong>
      </div>
      <span style="font-size: 0.82rem; font-weight: 700; color: <?php echo $isEligible ? 'var(--success)' : 'var(--primary)'; ?>;">
        <?php echo $progress; ?>%
      </span>
    </div>

    <p style="font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 8px; line-height: 1.4;">
      Deposited: <strong><?php echo format_currency($eligibility['total_deposited']); ?></strong> &bull;
      Req: <strong><?php echo format_currency($eligibility['required_interest']); ?></strong> &bull;
      Earned: <strong style="color: var(--success);"><?php echo format_currency($eligibility['total_interest_earned']); ?></strong>
    </p>

    <!-- Progress Bar -->
    <div class="progress-container" style="height: 8px; margin: 4px 0;">
      <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%;"></div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.72rem;">
      <span class="text-muted">₹0.00</span>
      <?php if (!$isEligible): ?>
        <span style="color: var(--danger); font-weight: 600;">
          Need <?php echo format_currency($eligibility['remaining_deficit']); ?> more in interest (&lt;1 day)
        </span>
      <?php else: ?>
        <span style="color: var(--success); font-weight: 600;">Unlocked & ready to payout</span>
      <?php endif; ?>
      <span class="text-muted"><?php echo $minPct; ?>%</span>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-3 mb-3">
    <!-- Withdrawal Form -->
    <div style="flex: 1.2; min-width: 280px;">
      <div class="card" style="padding: 16px 14px;">
        <h3 class="card-title mb-2" style="font-size: 0.95rem;">Request Withdrawal</h3>

        <?php if (!$isEligible): ?>
          <div class="alert-box warning mb-3" style="padding: 10px 12px; font-size: 0.8rem;">
            <span><?php echo svg_icon('lock', '', 16); ?></span>
            <div>
              <strong>Withdrawals Locked</strong><br>
              Withdrawals unlock automatically once you accumulate at least 0.55% interest on your deposit.
            </div>
          </div>
        <?php endif; ?>

        <div id="withdrawAlertBox"></div>

        <form id="withdrawalForm">
          <?php echo csrf_field(); ?>
          <div class="form-group mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label" style="font-size: 0.8rem; margin: 0;">Withdrawal Amount (₹):</label>
              <span style="font-size: 0.74rem; color: var(--text-muted);">
                Available: <strong id="withdrawAvailableBalance"><?php echo format_currency($user['current_balance'] ?? 0); ?></strong>
              </span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span style="font-size: 1.3rem; font-weight: 800; color: var(--primary);">₹</span>
              <input type="number" id="withdrawAmountInput" name="amount" class="form-control" placeholder="Min ₹100" min="100" max="<?php echo (float)($user['current_balance'] ?? 0); ?>" step="10" required <?php echo $isEligible ? '' : 'disabled'; ?> style="font-size: 1.15rem; font-weight: 700; padding: 8px 12px;">
            </div>
          </div>

          <div class="form-group mb-2">
            <label class="form-label" style="font-size: 0.8rem;">Receiving UPI ID (VPA):</label>
            <input type="text" name="payout_upi" id="withdrawUpiInput" class="form-control" placeholder="e.g. yourname@upi" value="<?php echo htmlspecialchars($user['upi_id'] ?? ''); ?>" required <?php echo $isEligible ? '' : 'disabled'; ?> style="font-size: 0.9rem; padding: 8px 12px;">
          </div>

          <div class="form-group mb-3">
            <label class="form-label" style="font-size: 0.8rem;">Optional Bank IMPS (if not UPI):</label>
            <div class="d-flex gap-2">
              <input type="text" name="bank_account" class="form-control" placeholder="Account Number" value="<?php echo htmlspecialchars($user['bank_account'] ?? ''); ?>" <?php echo $isEligible ? '' : 'disabled'; ?> style="font-size: 0.85rem; padding: 8px 10px;">
              <input type="text" name="bank_ifsc" class="form-control" placeholder="IFSC" value="<?php echo htmlspecialchars($user['bank_ifsc'] ?? ''); ?>" <?php echo $isEligible ? '' : 'disabled'; ?> style="font-size: 0.85rem; padding: 8px 10px;">
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block" id="submitWithdrawBtn" <?php echo $isEligible ? '' : 'disabled'; ?> style="font-weight: 800;">
            <?php echo $isEligible ? svg_icon('arrow-up-right', '', 18) . ' Confirm & Withdraw' : svg_icon('lock', '', 18) . ' Locked (0.55% Rule)'; ?>
          </button>
        </form>
      </div>
    </div>

    <!-- Security & Policy Info Box -->
    <div style="flex: 1; min-width: 260px;">
      <div class="card" style="background: var(--bg-main); padding: 14px;">
        <h4 style="font-size: 0.88rem; font-weight: 700; color: var(--primary); margin-bottom: 8px;">Withdrawal FAQs</h4>
        <ul style="list-style: none; padding: 0; font-size: 0.78rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 8px; line-height: 1.4;">
          <li class="d-flex align-items-center gap-2"><?php echo svg_icon('check-circle', '', 14); ?> <span><strong>0% Processing Fee:</strong> Free UPI transfers.</span></li>
          <li class="d-flex align-items-center gap-2"><?php echo svg_icon('check-circle', '', 14); ?> <span><strong>Speed:</strong> Instant to 15 mins.</span></li>
          <li class="d-flex align-items-center gap-2"><?php echo svg_icon('shield-check', '', 14); ?> <span><strong>0.55% Rule:</strong> Protects stable daily interest.</span></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Recent Withdrawals Table -->
  <div class="card mb-3" style="padding: 16px 14px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <span style="color: var(--primary);"><?php echo svg_icon('history', '', 18); ?></span>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">Withdrawal History</h3>
      </div>
      <span class="badge badge-info" style="font-size: 0.7rem;">Verified</span>
    </div>

    <?php if (empty($recentWithdrawals)): ?>
      <div style="text-align: center; padding: 20px 10px; color: var(--text-muted); font-size: 0.82rem;">
        No withdrawal requests submitted yet.
      </div>
    <?php else: ?>
      <div class="transaction-list">
        <?php foreach ($recentWithdrawals as $w): ?>
          <div class="transaction-row" style="padding: 10px 12px;">
            <div class="tx-left" style="gap: 10px;">
              <div class="tx-icon" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; width: 36px; height: 36px; border-radius: 10px;">
                <?php echo svg_icon('arrow-up-right', '', 18); ?>
              </div>
              <div style="min-width: 0;">
                <div class="tx-title" style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">
                  Withdrawal to <?php echo htmlspecialchars($w['payout_upi'] ?: 'Bank IMPS'); ?>
                </div>
                <div class="tx-date" style="font-size: 0.72rem;">
                  <?php echo date('d M, h:i A', strtotime($w['created_at'])); ?> &bull; Rate: <?php echo $w['eligibility_rate_checked']; ?>%
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="tx-amount out" style="font-size: 0.92rem;">-<?php echo format_currency($w['amount']); ?></div>
              <span class="badge badge-<?php echo ($w['status'] === 'completed' || $w['status'] === 'approved') ? 'success' : ($w['status'] === 'pending' ? 'warning' : 'danger'); ?>" style="font-size: 0.68rem; padding: 2px 6px;">
                <?php echo ucfirst($w['status']); ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/../components/withdrawal_tracking_modals.php'; ?>
</div>

