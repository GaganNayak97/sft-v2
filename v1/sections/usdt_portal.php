<?php
$user = $user ?? auth_user();
$rates = \App\Core\InterestEngine::getRates();
$usdtRate = $rates['usdt_rate'];
?>
<div class="card mb-3" style="padding: 16px 14px;">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="d-flex align-items-center gap-2">
      <span style="color: #16a34a;"><?php echo svg_icon('usdt', '', 20); ?></span>
      <div>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">USDT Direct Sell Desk</h3>
        <p style="font-size: 0.74rem; color: var(--text-muted); margin: 0;">Convert crypto USDT to INR cash in your UPI.</p>
      </div>
    </div>
    <span class="badge badge-success" style="font-size: 0.7rem;">1 USDT = ₹<?php echo number_format($usdtRate, 2); ?></span>
  </div>

  <div class="d-flex flex-wrap gap-3">
    <!-- Sell Calculator & Order Form -->
    <div style="flex: 1.2; min-width: 260px;">
      <div id="usdtAlertBox"></div>
      <form id="usdtSellForm">
        <?php echo csrf_field(); ?>
        <div class="form-group mb-2">
          <label class="form-label" style="font-size: 0.8rem;">Amount of USDT to Sell:</label>
          <div class="d-flex align-items-center gap-2">
            <input type="number" id="usdtAmountInput" name="usdt_amount" class="form-control" placeholder="Min 10" min="10" step="1" value="50" required style="font-size: 1.15rem; font-weight: 700; padding: 8px 12px;">
            <span style="font-weight: 700; color: var(--text-secondary); font-size: 0.9rem;">USDT</span>
          </div>
        </div>

        <div class="form-group mb-2">
          <label class="form-label" style="font-size: 0.78rem; margin-bottom: 2px;">Estimated INR Payout:</label>
          <div style="font-size: 1.5rem; font-weight: 800; color: var(--success); padding: 4px 0;" id="inrPayoutDisplay">
            ₹<?php echo number_format(50 * $usdtRate, 2); ?>
          </div>
        </div>

        <div class="form-group mb-2">
          <label class="form-label" style="font-size: 0.8rem;">Blockchain Network:</label>
          <select name="network" class="form-control" id="cryptoNetworkSelect" style="padding: 8px 10px; font-size: 0.85rem;">
            <option value="TRC20">TRC20 (Tron Network - Low Gas Fee)</option>
            <option value="BEP20">BEP20 (Binance Smart Chain)</option>
            <option value="POLYGON">Polygon (MATIC)</option>
          </select>
        </div>

        <div class="form-group mb-3">
          <label class="form-label" style="font-size: 0.8rem;">Receiving UPI ID for INR:</label>
          <input type="text" name="payout_upi" class="form-control" placeholder="e.g. yourname@upi" value="<?php echo htmlspecialchars($user['upi_id'] ?? ''); ?>" required style="padding: 8px 12px; font-size: 0.88rem;">
        </div>

        <button type="submit" class="btn btn-primary btn-block" id="submitUsdtSellBtn" style="font-weight: 800;">
          <?php echo svg_icon('check-circle', '', 18); ?> Initiate USDT Sell
        </button>
      </form>
    </div>

    <!-- Instructions & Address -->
    <div style="flex: 1; min-width: 260px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 14px;">
      <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 8px; color: var(--primary);">How it Works:</h4>
      <ol style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.5; padding-left: 16px; margin-bottom: 12px;">
        <li>Enter USDT quantity & your UPI ID.</li>
        <li>Send USDT to the verified deposit address.</li>
        <li>Instant INR payout arrives upon 1 confirmation!</li>
      </ol>

      <div style="background: var(--bg-card); padding: 10px; border-radius: 8px; border: 1px dashed var(--secondary);">
        <div style="font-size: 0.68rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); margin-bottom: 2px;">System TRC20 Address:</div>
        <div style="font-family: monospace; font-size: 0.75rem; word-break: break-all; color: var(--primary); font-weight: 600;" id="usdtDepositAddress">
          TYu9qKL3Z8Nm82kL90PqX72vB1xM55R3tP
        </div>
      </div>
    </div>
  </div>
</div>
