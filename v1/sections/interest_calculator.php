<div class="card mb-3 ios-calc-card" style="padding: 22px 20px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
      <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('calculator', '', 20); ?></span>
      <span class="card-title" style="margin: 0; font-size: 0.95rem; font-weight: 700;">Daily Interest Returns Calculator</span>
    </div>
    <span class="badge badge-success" style="font-size: 0.72rem; border-radius: 9999px; padding: 4px 10px;">Automated</span>
  </div>

  <!-- iOS Advanced Interactive Range Element (No direct raw inputs) -->
  <div class="ios-range-container mb-3">
    <!-- Hidden inputs for external form & JS data collection -->
    <input type="hidden" id="calcRange" value="10000" min="500" max="100000" step="500">
    <input type="hidden" id="calcAmountInput" value="10000">

    <!-- iOS Amount Hero Display Card -->
    <div class="ios-amount-hero">
      <div class="ios-amount-info">
        <span class="ios-amount-subtitle">Selected Deposit Amount</span>
        <div class="ios-amount-value-wrap">
          <span class="ios-currency-symbol">₹</span>
          <span class="ios-amount-display" id="iosAmountDisplay">10,000</span>
          <span class="ios-vip-pill active-vip" id="iosVipPill">VIP 1.0% Tier ✨</span>
        </div>
      </div>
      <!-- iOS Circular Stepper Buttons -->
      <div class="ios-stepper-group">
        <button type="button" class="ios-stepper-btn" id="iosStepMinus" aria-label="Decrease amount" title="Decrease deposit">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
        <button type="button" class="ios-stepper-btn" id="iosStepPlus" aria-label="Increase amount" title="Increase deposit">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
      </div>
    </div>

    <!-- iOS Quick Preset Segments -->
    <div class="ios-presets-scroll">
      <button type="button" class="ios-preset-chip" data-val="1000">₹1,000</button>
      <button type="button" class="ios-preset-chip" data-val="2500">₹2,500</button>
      <button type="button" class="ios-preset-chip" data-val="5000">⭐ ₹5k VIP</button>
      <button type="button" class="ios-preset-chip active" data-val="10000">₹10,000</button>
      <button type="button" class="ios-preset-chip" data-val="25000">₹25,000</button>
      <button type="button" class="ios-preset-chip" data-val="50000">₹50,000</button>
      <button type="button" class="ios-preset-chip" data-val="100000">₹1,00,000</button>
    </div>

    <!-- iOS Custom Interactive Scrubber Track -->
    <div class="ios-slider-wrapper">
      <div class="ios-slider-track" id="iosSliderTrack" role="slider" aria-valuemin="500" aria-valuemax="100000" aria-valuenow="10000" tabindex="0">
        <!-- Fluid Active Progress Fill (Default 10k = 54%) -->
        <div class="ios-slider-fill" id="iosSliderFill" style="width: 54%;"></div>
        
        <!-- VIP Milestone Marker on Track (₹5,000 mark at 36%) -->
        <div class="ios-slider-milestone" style="left: 36%;">
          <span class="ios-milestone-tag">★ VIP ₹5k</span>
        </div>

        <!-- Tactile iOS Thumb Knob with Dynamic Floating Bubble -->
        <div class="ios-slider-thumb" id="iosSliderThumb" style="left: 54%;">
          <div class="ios-floating-bubble" id="iosFloatingBubble">
            <span class="ios-bubble-val">₹10,000</span>
          </div>
          <div class="ios-thumb-inner">
            <span class="ios-thumb-center-dot"></span>
          </div>
        </div>
      </div>

      <!-- Scale Milestone Guides -->
      <div class="ios-slider-labels">
        <span>₹500</span>
        <span class="ios-label-vip" style="margin-left: 12%;">★ VIP Tier (₹5,000+)</span>
        <span>₹1,00,000</span>
      </div>
    </div>
  </div>

  <!-- Calculator Output Grid: 2x2 Clean Unified Matrix -->
  <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
    <div class="stat-card" style="padding: 13px 14px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">Plan Rate</span>
        <span id="calcTierBadge" class="badge badge-vip" style="font-size: 0.65rem; padding: 2px 7px;">VIP 1.0%</span>
      </div>
      <div class="stat-card-value" id="calcRateDisplay" style="color: var(--text-primary); font-size: 1.25rem; font-weight: 800;">1.00%</div>
      <div class="stat-card-meta" id="calcTierExplanation" style="font-size: 0.68rem; color: var(--text-muted);">Business VIP (₹5k+)</div>
    </div>

    <div class="stat-card" style="padding: 13px 14px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">Daily Interest</span>
        <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('trending', '', 14); ?></span>
      </div>
      <div class="stat-card-value" id="calcDailyReturn" style="color: var(--primary); font-size: 1.25rem; font-weight: 800;">₹100.00</div>
      <div class="stat-card-meta" style="font-size: 0.68rem; color: var(--text-muted);">Credited every 24h</div>
    </div>

    <div class="stat-card" style="padding: 13px 14px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">Monthly (30D)</span>
        <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('history', '', 14); ?></span>
      </div>
      <div class="stat-card-value" id="calcMonthlyReturn" style="color: var(--text-primary); font-size: 1.25rem; font-weight: 800;">₹3,000.00</div>
      <div class="stat-card-meta" style="font-size: 0.68rem; color: var(--text-muted);">30% monthly gain</div>
    </div>

    <div class="stat-card" style="padding: 13px 14px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px;">
      <div class="stat-card-top" style="margin-bottom: 4px;">
        <span class="stat-card-label" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">Withdraw Unlock</span>
        <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('unlock', '', 14); ?></span>
      </div>
      <div class="stat-card-value" id="calcWithdrawThreshold" style="color: var(--text-primary); font-size: 1.25rem; font-weight: 800;">₹55.00</div>
      <div class="stat-card-meta" style="font-size: 0.68rem; color: var(--text-muted);">0.55% safety rule</div>
    </div>
  </div>
</div>
