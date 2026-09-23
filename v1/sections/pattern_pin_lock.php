<div class="search-modal-container" id="securityLockModal">
  <div class="search-modal-card" style="max-width: 420px; padding: 24px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <span style="font-size: 1.3rem;">🔒</span>
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0;" id="securityModalTitle">Security Verification</h3>
      </div>
      <button type="button" class="icon-btn" id="closeSecurityModalBtn" style="width: 32px; height: 32px;">✕</button>
    </div>

    <!-- Security Mode Tabs -->
    <div class="preset-chips justify-content-center mb-3">
      <button type="button" class="preset-chip active" id="tabPinBtn">4-Digit PIN</button>
      <button type="button" class="preset-chip" id="tabPatternBtn">3x3 Pattern Lock</button>
    </div>

    <!-- PIN Keypad View -->
    <div id="pinLockView" class="security-lock-container">
      <p style="font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-bottom: 12px;" id="pinInstructionText">
        Enter your 4-digit security PIN
      </p>

      <div class="pin-display">
        <div class="pin-digit-box" data-idx="0">&bull;</div>
        <div class="pin-digit-box" data-idx="1">&bull;</div>
        <div class="pin-digit-box" data-idx="2">&bull;</div>
        <div class="pin-digit-box" data-idx="3">&bull;</div>
      </div>

      <div class="pin-keypad">
        <button type="button" class="pin-key" data-val="1">1</button>
        <button type="button" class="pin-key" data-val="2">2</button>
        <button type="button" class="pin-key" data-val="3">3</button>
        <button type="button" class="pin-key" data-val="4">4</button>
        <button type="button" class="pin-key" data-val="5">5</button>
        <button type="button" class="pin-key" data-val="6">6</button>
        <button type="button" class="pin-key" data-val="7">7</button>
        <button type="button" class="pin-key" data-val="8">8</button>
        <button type="button" class="pin-key" data-val="9">9</button>
        <button type="button" class="pin-key" id="pinClearBtn" style="font-size: 1rem; color: var(--danger);">C</button>
        <button type="button" class="pin-key" data-val="0">0</button>
        <button type="button" class="pin-key" id="pinBackspaceBtn" style="font-size: 1.1rem;">⌫</button>
      </div>
    </div>

    <!-- 3x3 Pattern Lock View -->
    <div id="patternLockView" class="security-lock-container" style="display: none;">
      <p style="font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-bottom: 12px;" id="patternInstructionText">
        Draw your 3x3 pattern by connecting dots
      </p>

      <div class="pattern-wrapper" id="patternContainer">
        <svg class="pattern-svg" id="patternSvg">
          <polyline id="patternPolyline" class="pattern-line" points="" />
        </svg>
        <div class="pattern-grid" id="patternGrid">
          <div class="pattern-node" data-index="0"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="1"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="2"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="3"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="4"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="5"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="6"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="7"><div class="pattern-dot"></div></div>
          <div class="pattern-node" data-index="8"><div class="pattern-dot"></div></div>
        </div>
      </div>

      <div class="text-center mt-3">
        <button type="button" class="btn btn-sm btn-outline" id="patternResetBtn">Clear Pattern</button>
      </div>
    </div>

    <div id="securityFeedbackMsg" style="margin-top: 14px; text-align: center; font-size: 0.88rem; font-weight: 600;"></div>
  </div>
</div>
