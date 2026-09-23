<?php
$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();

$vpa = app_config('app.upi_vpa', 'softpay@upi');
$presetAmount = isset($_GET['amount']) ? (float)$_GET['amount'] : 1000;

// Fetch recent deposits
$stmtDep = $db->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmtDep->execute([$user['id'] ?? 0]);
$recentDeposits = $stmtDep->fetchAll(\PDO::FETCH_ASSOC);
?>

<div class="deposit-page-container">
  <!-- Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0;">Add Money via UPI</h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
        Zero fee deposit. Starts earning 0.88% daily interest (1.0% VIP on ₹5,000+).
      </p>
    </div>
    <div class="upi-badge-container" style="padding: 4px 10px; font-size: 0.76rem;">
      <span class="upi-logo-text">UPI 2.0</span>
      <span>Instant Auto-Credit</span>
    </div>
  </div>

  <!-- Trust Security Strip -->
  <div class="trust-banner-card mb-3" style="padding: 10px 14px;">
    <div class="d-flex align-items-center gap-2">
      <div style="color: #059669;"><?php echo svg_icon('shield-check', '', 22); ?></div>
      <div>
        <strong style="font-size: 0.82rem; color: var(--text-primary); display: block;">Official UPI Certified Payment</strong>
        <span style="font-size: 0.72rem; color: var(--text-muted);">Transfer from Paytm, PhonePe, Google Pay or BHIM UPI safely.</span>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-3 mb-3">
    <!-- Left: Amount & UTR Form -->
    <div style="flex: 1.2; min-width: 280px;">
      <div class="card" style="padding: 16px 14px;">
        <h3 class="card-title mb-2" style="font-size: 0.95rem;">1. Enter Deposit Amount</h3>

        <div id="depositAlertBox"></div>

        <form id="depositForm">
          <?php echo csrf_field(); ?>
          <div class="form-group mb-2">
            <div class="d-flex align-items-center gap-2">
              <span style="font-size: 1.3rem; font-weight: 800; color: var(--primary);">₹</span>
              <input type="number" id="depositAmountInput" name="amount" class="form-control" value="<?php echo $presetAmount; ?>" min="100" max="500000" step="100" required style="font-size: 1.2rem; font-weight: 700; padding: 8px 12px;">
            </div>
            
            <div class="preset-chips mt-2">
              <button type="button" class="preset-chip" data-amt="500">₹500</button>
              <button type="button" class="preset-chip active" data-amt="1000">₹1,000</button>
              <button type="button" class="preset-chip" data-amt="2500">₹2,500</button>
              <button type="button" class="preset-chip" data-amt="5000">₹5,000 (VIP 1%)</button>
              <button type="button" class="preset-chip" data-amt="10000">₹10,000</button>
            </div>
          </div>

          <!-- Dynamic Rate Preview Badge -->
          <div class="alert-box info mb-3" id="depositRateInfoBox" style="padding: 10px 12px; font-size: 0.8rem;">
            <span><?php echo svg_icon('trending', '', 16); ?></span>
            <div>
              At ₹<span id="rateInfoAmt"><?php echo number_format($presetAmount); ?></span>, you earn 
              <strong id="rateInfoPct"><?php echo ($presetAmount >= 5000) ? '1.00%' : '0.88%'; ?></strong> daily 
              (<strong id="rateInfoDaily">+₹<?php echo number_format($presetAmount * (($presetAmount >= 5000) ? 0.01 : 0.0088), 2); ?></strong>/day)!
            </div>
          </div>

          <h3 class="card-title mb-2" style="border-top: 1px solid var(--border-color); padding-top: 14px; font-size: 0.95rem;">
            2. Enter 12-Digit UPI UTR
          </h3>

          <div class="form-group mb-3">
            <label class="form-label" style="font-size: 0.8rem;">12-Digit UPI UTR / Transaction Reference:</label>
            <input type="text" name="utr_number" id="utrNumberInput" class="form-control" placeholder="e.g. 423982938102" pattern="[A-Za-z0-9]{8,25}" required style="font-size: 0.92rem; padding: 9px 12px;">
            <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; display: block;">
              Copy the 12-digit UTR from your UPI app receipt after payment.
            </span>
          </div>

          <button type="submit" class="btn btn-primary btn-block" id="submitDepositBtn" style="font-weight: 800;">
            <?php echo svg_icon('check-circle', '', 18); ?> Confirm &amp; Credit Deposit
          </button>
        </form>
      </div>
    </div>

    <!-- Right: Dynamic SoftPay / UPI QR & VPA Box -->
    <div style="flex: 1; min-width: 260px;">
      <div class="card text-center" style="background: linear-gradient(180deg, rgba(0, 41, 112, 0.02) 0%, var(--bg-card) 100%); padding: 16px 14px;">
        <span class="paytm-badge mb-2" style="font-size: 0.74rem;">Scan & Pay Any UPI App</span>
        <h4 style="font-size: 0.95rem; color: var(--text-primary); margin-bottom: 10px;">Official SoftPay Gateway</h4>

        <!-- QR Code Display Box -->
        <div style="width: 170px; height: 170px; margin: 0 auto 10px auto; background: #fff; padding: 8px; border-radius: 12px; border: 2px solid #002970; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-card);">
          <img id="upiQrImage" src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=upi%3A%2F%2Fpay%3Fpa%3D<?php echo urlencode($vpa); ?>%26pn%3DSoftPay%26am%3D<?php echo $presetAmount; ?>%26cu%3DINR" alt="UPI QR Code" style="width: 100%; height: 100%; object-fit: contain;">
        </div>

        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 10px;">
          Amount to Pay: <strong style="font-size: 1.15rem; color: var(--primary);" id="qrAmountDisplay">₹<?php echo number_format($presetAmount, 2); ?></strong>
        </div>

        <!-- Copy UPI ID Row -->
        <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 10px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
          <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($vpa); ?></span>
          <button type="button" class="btn btn-sm btn-outline" id="copyVpaBtn" data-vpa="<?php echo htmlspecialchars($vpa); ?>" style="padding: 4px 8px; font-size: 0.72rem;">
            <?php echo svg_icon('copy', '', 13); ?> Copy
          </button>
        </div>

        <!-- Quick UPI Intent App Buttons (Mobile) -->
        <div class="d-flex gap-2 justify-content-center">
          <a href="upi://pay?pa=<?php echo urlencode($vpa); ?>&pn=SoftPay&am=<?php echo $presetAmount; ?>&cu=INR" class="btn btn-sm btn-outline flex-1" style="font-size: 0.74rem; padding: 6px 4px; border-color: #002970; color: #002970;">
            Paytm
          </a>
          <a href="upi://pay?pa=<?php echo urlencode($vpa); ?>&pn=SoftPay&am=<?php echo $presetAmount; ?>&cu=INR" class="btn btn-sm btn-outline flex-1" style="font-size: 0.74rem; padding: 6px 4px; border-color: #5f6368; color: #5f6368;">
            GPay
          </a>
          <a href="upi://pay?pa=<?php echo urlencode($vpa); ?>&pn=SoftPay&am=<?php echo $presetAmount; ?>&cu=INR" class="btn btn-sm btn-outline flex-1" style="font-size: 0.74rem; padding: 6px 4px; border-color: #5f259f; color: #5f259f;">
            PhonePe
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Deposit History List -->
  <div class="card mb-3" style="padding: 16px 14px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <span style="color: var(--primary);"><?php echo svg_icon('history', '', 18); ?></span>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">Recent UPI Deposits</h3>
      </div>
      <span class="badge badge-info" style="font-size: 0.7rem;">Verified</span>
    </div>

    <?php if (empty($recentDeposits)): ?>
      <div style="text-align: center; padding: 20px 10px; color: var(--text-muted); font-size: 0.82rem;">
        No deposits submitted yet. Scan the QR code above to add money!
      </div>
    <?php else: ?>
      <div class="transaction-list">
        <?php foreach ($recentDeposits as $dep): ?>
          <div class="transaction-row" style="padding: 10px 12px;">
            <div class="tx-left" style="gap: 10px;">
              <div class="tx-icon" style="background: rgba(0, 176, 116, 0.12); color: #059669; width: 36px; height: 36px; border-radius: 10px;">
                <?php echo svg_icon('arrow-down-left', '', 18); ?>
              </div>
              <div style="min-width: 0;">
                <div class="tx-title" style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">
                  UPI via <?php echo htmlspecialchars($dep['upi_vpa']); ?>
                </div>
                <div class="tx-date" style="font-size: 0.72rem;">
                  <?php echo date('d M, h:i A', strtotime($dep['created_at'])); ?> &bull; UTR: <?php echo htmlspecialchars($dep['utr_number']); ?>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="tx-amount in" style="font-size: 0.92rem;">+<?php echo format_currency($dep['amount']); ?></div>
              <span class="badge badge-success" style="font-size: 0.68rem; padding: 2px 6px;"><?php echo ucfirst($dep['status']); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
