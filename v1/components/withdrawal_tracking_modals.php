<?php
// Shared Withdrawal 12-Hour Tracking Modal & Success Modal
?>
<!-- 1. 12-Hour Withdrawal Tracking & Timeline Modal (Ultra-Luxury Fintech Redesign) -->
<div class="tx-details-backdrop" id="withdrawalTrackingModal" style="display: none;">
  <div class="tx-details-card">
    
    <!-- iOS Top Sheet Handle -->
    <div class="tx-sheet-handle"></div>

    <!-- Header -->
    <div class="tx-details-header">
      <button type="button" class="tx-header-btn close-tracking-modal" aria-label="Close" title="Back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
      </button>
      <div class="tx-header-center">
        <h3 class="tx-header-title">Transaction Details</h3>
        <span class="tx-header-sub">12-Hour Verification Queue</span>
      </div>
      <button type="button" class="tx-header-btn close-tracking-modal" aria-label="Dismiss" title="Close">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- Body -->
    <div class="tx-details-body">
      
      <!-- Luxury Hero Section -->
      <div class="tx-hero-section">
        <div class="tx-hero-label">Withdrawal Amount</div>
        <div class="tx-hero-amount" id="trackingAmountDisplay">₹0.00</div>
        <div class="tx-status-badge pending" id="trackingStatusBadge">
          <span class="tx-status-dot"></span>
          <span id="trackingStatusText">Pending (12h Waiting)</span>
        </div>
      </div>

      <!-- Fintech Banking Bridge Card -->
      <div class="tx-bridge-card">
        <div class="tx-bridge-node">
          <div class="tx-bridge-icon source">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="5" width="20" height="14" rx="3"></rect>
              <line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
          </div>
          <div>
            <div class="tx-bridge-label">Source Wallet</div>
            <div class="tx-bridge-val">SoftPay Savings</div>
          </div>
        </div>

        <div class="tx-bridge-arrow">
          <div class="tx-bridge-route-line">
            <span class="tx-bridge-travel-pulse"></span>
          </div>
          <span class="tx-bridge-chip">RBI Gateway</span>
        </div>

        <div class="tx-bridge-node right">
          <div>
            <div class="tx-bridge-label">Payout Recipient</div>
            <div class="tx-bridge-val" id="trackingRecipientDisplay" title="UPI / Bank">UPI / Bank</div>
          </div>
          <div class="tx-bridge-icon dest">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Luxury Stepper Timeline -->
      <div class="tx-stepper-timeline">
        
        <!-- Step 1: Initiated -->
        <div class="tx-step-item completed" id="stepNode1">
          <div class="tx-step-dot">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
          <div class="tx-step-content">
            <div class="tx-step-head">
              <span class="tx-step-title">Initiated</span>
              <span class="tx-step-time-badge" id="stepTime1">--:--</span>
            </div>
            <div class="tx-step-desc">Withdrawal request placed into verification queue.</div>
          </div>
        </div>

        <!-- Step 2: Interest & Balance Audit -->
        <div class="tx-step-item completed" id="stepNode2">
          <div class="tx-step-dot">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
          <div class="tx-step-content">
            <div class="tx-step-head">
              <span class="tx-step-title">Interest &amp; Balance Audit</span>
              <span class="tx-step-time-badge verified" id="stepTime2">0.55% Verified</span>
            </div>
            <div class="tx-step-desc">0.55% interest requirement &amp; balance ledger verified.</div>
          </div>
        </div>

        <!-- Step 3: Network Confirmation (12h Banking Queue) -->
        <div class="tx-step-item active" id="stepNode3">
          <div class="tx-step-dot" id="stepDot3">
            <span class="tx-step-dot-pulse"></span>
            3
          </div>
          <div class="tx-step-content">
            <div class="tx-step-head">
              <span class="tx-step-title">Network Confirmation</span>
              <span class="tx-step-time-badge progress" id="stepTime3">In Progress</span>
            </div>
            <div class="tx-step-desc" id="stepDesc3">12-Hour automated banking clearance &amp; lock queue.</div>
          </div>
        </div>

        <!-- Step 4: Completed -->
        <div class="tx-step-item" id="stepNode4">
          <div class="tx-step-dot" id="stepDot4">4</div>
          <div class="tx-step-content">
            <div class="tx-step-head">
              <span class="tx-step-title">Completed</span>
              <span class="tx-step-time-badge pending" id="stepTime4">Pending</span>
            </div>
            <div class="tx-step-desc" id="stepDesc4">Payment unlocked and ready for release.</div>
          </div>
        </div>

      </div>

      <!-- Luxury Countdown Bar Box -->
      <div class="tx-countdown-box" id="trackingCountdownBox">
        <div class="tx-countdown-meta">
          <span class="tx-countdown-label">
            <span class="tx-clock-anim-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </span>
            <span id="countdownStatusLabel">Waiting Period:</span>
          </span>
          <span class="tx-countdown-timer-pill">
            <span class="tx-countdown-timer" id="trackingCountdownTimer">12h 00m 00s</span>
          </span>
        </div>
        <div class="tx-progress-bar-bg">
          <div class="tx-progress-bar-fill" id="trackingProgressFill" style="width: 0%;">
            <span class="tx-progress-shimmer"></span>
          </div>
        </div>
      </div>

      <!-- Action Stack -->
      <div class="tx-action-stack">
        <!-- Receive Payment Button -->
        <button type="button" class="btn-receive-payout" id="claimPayoutBtn" disabled>
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          <span id="claimPayoutBtnText">Receive Payment (After 12h)</span>
        </button>

        <!-- Demo Fast Forward Shortcut (Allows instantaneous testing) -->
        <button type="button" class="btn-fastforward-demo" id="fastForwardDemoBtn" title="Fast-forward the 12 hours for instant testing">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
          </svg>
          <span>Fast-Forward 12h (Demo Mode)</span>
        </button>
      </div>

    </div>
  </div>
</div>

<!-- 2. Withdrawal Successful Modal (Pixel-Perfect media_1790161265450) -->
<div class="withdraw-success-backdrop" id="withdrawalSuccessModal" style="display: none;">
  <div class="withdraw-success-card">
    
    <!-- Top Glowing Green Circle with Checkmark -->
    <div class="success-check-aura">
      <div class="success-check-core">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>
    </div>

    <!-- Headline -->
    <h2 class="withdraw-success-title">Withdrawal Successful</h2>

    <!-- Dynamic Large Currency Amount (e.g. ₹1,000.00) -->
    <div class="withdraw-success-amount" id="successWithdrawAmount">₹0.00</div>

    <!-- Descriptive transfer statement -->
    <p class="withdraw-success-desc" id="successWithdrawDesc">
      Funds transferred out of SoftPay. Please contact the recipient platform or check your UPI app for your transaction receipt.
    </p>

    <!-- Bottom Dual Actions: Save Address + View History (Exact Match) -->
    <div class="withdraw-success-actions">
      <button type="button" class="btn-save-address" id="saveAddressBtn">
        Save Address
      </button>
      <a href="<?php echo url('history'); ?>" class="btn-view-history" id="viewHistorySuccessBtn">
        View History
      </a>
    </div>

  </div>
</div>
