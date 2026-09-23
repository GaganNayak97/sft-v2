<?php
$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();

// Fetch latest 5 transactions for recent widget
$stmtTx = $db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmtTx->execute([$user['id'] ?? 0]);
$recentTxs = $stmtTx->fetchAll(\PDO::FETCH_ASSOC);

$flash = flash_get();
?>

<div class="home-page-container">
  <?php if ($flash): ?>
    <?php component('alert', ['type' => $flash['type'], 'message' => $flash['message']]); ?>
  <?php endif; ?>

  <div class="home-dashboard-layout">
    <!-- Left Column: Core Financial Operations, Simulator & Transactions -->
    <div class="home-col home-col-primary">
      <!-- 1. Core Financial Hero Balance Card -->
      <div class="home-widget-wrap">
        <?php section('hero_balance', ['user' => $user]); ?>
      </div>

      <!-- 2. Interactive Interest Calculator & Returns Simulator -->
      <div class="home-widget-wrap">
        <?php section('interest_calculator'); ?>
      </div>

      <!-- 3. Transactions Preview -->
      <div class="home-widget-wrap home-widget-transactions">
        <div class="card" style="padding: 24px;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
              <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('history', '', 20); ?></span>
              <h3 class="card-title" style="margin: 0; font-size: 1.05rem; font-weight: 700;">Transactions</h3>
            </div>
            <a href="<?php echo url('history'); ?>" class="btn btn-sm btn-outline">View All &rarr;</a>
          </div>

          <div class="transaction-list">
            <?php if (empty($recentTxs)): ?>
              <div style="text-align: center; padding: 28px 14px; color: var(--text-muted); font-size: 0.88rem;">
                No transactions yet. Deposit funds to start earning daily interest!
              </div>
            <?php else: ?>
              <?php foreach ($recentTxs as $tx): ?>
                <?php component('transaction_item', ['tx' => $tx]); ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Banking Services, Trust, VIP Growth & Community Referrals -->
    <div class="home-col home-col-secondary">
      <!-- 4. Clean 4-Column Quick Banking Services -->
      <div class="home-widget-wrap">
        <?php section('quick_actions'); ?>
      </div>

      <!-- 5. High-Trust Certified Banking & Safety Banner -->
      <div class="home-widget-wrap">
        <div class="trust-banner-card mb-0">
          <div class="d-flex align-items-center gap-3">
            <div class="trust-shield-icon">
              <?php echo svg_icon('shield-check', '', 24); ?>
            </div>
            <div>
              <strong style="font-size: 0.88rem; color: var(--text-primary); display: block; line-height: 1.2;">
                100% Secure & RBI-Compliant Banking
              </strong>
              <span style="font-size: 0.76rem; color: var(--text-muted); display: block; margin-top: 2px;">
                Zero-risk daily compounding with 256-bit automated UPI liquidity.
              </span>
            </div>
          </div>
          <div class="trust-badges-row">
            <div class="trust-chip">
              <?php echo svg_icon('check-circle', '', 14); ?>
              <span>NPCI Verified</span>
            </div>
            <div class="trust-chip">
              <?php echo svg_icon('lock', '', 14); ?>
              <span>SSL Protected</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. VIP Business Reward Tier Status Card -->
      <div class="home-widget-wrap">
        <?php section('reward_tiers', ['user' => $user]); ?>
      </div>

      <!-- 7. Fully Functional Invite Friends & Earn Program -->
      <div class="home-widget-wrap">
        <?php section('referral_widget', ['user' => $user]); ?>
      </div>
    </div>
  </div>
</div>
