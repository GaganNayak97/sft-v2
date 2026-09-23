<div class="card mb-3" style="padding: 20px 20px;">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <span class="card-title" style="font-size: 0.95rem;">Quick Banking Services</span>
    <span class="paytm-badge" style="font-size: 0.72rem; padding: 2px 8px;">SoftPay Certified</span>
  </div>

  <div class="paytm-action-grid">
    <a href="<?php echo url('deposit'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-deposit">
        <?php echo svg_icon('deposit', '', 22); ?>
      </div>
      <span class="paytm-action-label">Deposit</span>
    </a>

    <a href="<?php echo url('withdraw'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-withdraw">
        <?php echo svg_icon('withdraw', '', 22); ?>
      </div>
      <span class="paytm-action-label">Withdraw</span>
    </a>

    <a href="<?php echo url('balance'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-balance">
        <?php echo svg_icon('trending', '', 22); ?>
      </div>
      <span class="paytm-action-label">Interest</span>
    </a>

    <a href="<?php echo url('trading'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon" style="background: rgba(0, 230, 118, 0.12); color: #00e676;">
        <?php echo svg_icon('trending-up', '', 22); ?>
      </div>
      <span class="paytm-action-label">Trading</span>
    </a>

    <a href="<?php echo url('rewards'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-reward">
        <?php echo svg_icon('crown', '', 22); ?>
      </div>
      <span class="paytm-action-label">VIP 1.0%</span>
    </a>

    <a href="<?php echo url('usdt_sell'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-usdt">
        <?php echo svg_icon('usdt', '', 22); ?>
      </div>
      <span class="paytm-action-label">Sell USDT</span>
    </a>

    <a href="<?php echo url('history'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-history">
        <?php echo svg_icon('history', '', 22); ?>
      </div>
      <span class="paytm-action-label">Transactions</span>
    </a>

    <a href="<?php echo url('security'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-security">
        <?php echo svg_icon('shield', '', 22); ?>
      </div>
      <span class="paytm-action-label">Security</span>
    </a>

    <a href="<?php echo url('help'); ?>" class="paytm-action-btn">
      <div class="paytm-action-icon icon-help">
        <?php echo svg_icon('chat', '', 22); ?>
      </div>
      <span class="paytm-action-label">Help/FAQ</span>
    </a>
  </div>
</div>
