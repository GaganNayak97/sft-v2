<?php
$user = $user ?? auth_user();
$hasPin = !empty($user['pin_hash']);
$hasPattern = !empty($user['pattern_hash']);
?>

<div class="security-page-container">
  <!-- Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0;">Security: PIN & Pattern</h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
        Screen lock protects your entire account. Without matching your PIN/Pattern, nothing can be accessed.
      </p>
    </div>
    <?php if ($hasPin || $hasPattern): ?>
      <a href="<?php echo url('lock'); ?>&action=lock" class="btn btn-sm btn-primary">
        <?php echo svg_icon('lock', '', 14); ?> 🔒 Lock Screen Now
      </a>
    <?php endif; ?>
  </div>

  <div class="d-flex flex-wrap gap-3 mb-3">
    <!-- 1. 4-Digit Security PIN Section -->
    <div style="flex: 1; min-width: 280px;">
      <div class="card h-100" style="padding: 16px 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="d-flex align-items-center gap-2">
            <span style="color: var(--primary);"><?php echo svg_icon('lock', '', 18); ?></span>
            <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">4-Digit Security PIN</h3>
          </div>
          <span class="badge <?php echo $hasPin ? 'badge-success' : 'badge-warning'; ?>" style="font-size: 0.7rem;">
            <?php echo $hasPin ? 'Active' : 'Not Set'; ?>
          </span>
        </div>

        <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 12px;">
          Locks app on login & protects withdrawals. Without entering this PIN, no one can see your account.
        </p>

        <form id="setPinForm">
          <?php echo csrf_field(); ?>
          <div class="form-group mb-2">
            <label class="form-label" style="font-size: 0.8rem;">New 4-Digit PIN:</label>
            <input type="password" name="pin" id="newPinInput" class="form-control" placeholder="••••" maxlength="4" pattern="\d{4}" required style="font-size: 1.3rem; letter-spacing: 6px; text-align: center; padding: 8px;">
          </div>

          <div class="form-group mb-3">
            <label class="form-label" style="font-size: 0.8rem;">Confirm 4-Digit PIN:</label>
            <input type="password" name="pin_confirm" id="confirmPinInput" class="form-control" placeholder="••••" maxlength="4" pattern="\d{4}" required style="font-size: 1.3rem; letter-spacing: 6px; text-align: center; padding: 8px;">
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-sm" style="padding: 10px; font-weight: 700;">
            <?php echo $hasPin ? 'Update Security PIN' : 'Save 4-Digit PIN'; ?>
          </button>
        </form>

        <div class="d-flex gap-2 justify-content-center mt-3">
          <button type="button" class="btn btn-sm btn-outline flex-1" id="testPinModalBtn" style="font-size: 0.78rem; padding: 6px 8px;">
            <?php echo svg_icon('shield', '', 14); ?> Test PIN
          </button>
          <?php if ($hasPin): ?>
            <button type="button" class="btn btn-sm btn-outline flex-1" id="removePinBtn" style="font-size: 0.78rem; padding: 6px 8px; color: var(--danger); border-color: var(--danger);">
              Remove PIN
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- 2. 3x3 Pattern Lock Section -->
    <div style="flex: 1; min-width: 280px;">
      <div class="card h-100" style="padding: 16px 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="d-flex align-items-center gap-2">
            <span style="color: #7c3aed;"><?php echo svg_icon('sparkles', '', 18); ?></span>
            <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">3x3 Pattern Lock</h3>
          </div>
          <span class="badge <?php echo $hasPattern ? 'badge-success' : 'badge-warning'; ?>" style="font-size: 0.7rem;">
            <?php echo $hasPattern ? 'Active' : 'Not Set'; ?>
          </span>
        </div>

        <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 10px;">
          Connect at least 4 dots to draw your security pattern.
        </p>

        <!-- Interactive Setup Pattern Grid -->
        <div class="pattern-wrapper" id="setupPatternContainer" style="width: 220px; height: 220px; margin: 0 auto 10px auto;">
          <svg class="pattern-svg" id="setupPatternSvg">
            <polyline id="setupPatternPolyline" class="pattern-line" points="" />
          </svg>
          <div class="pattern-grid" id="setupPatternGrid">
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

        <div class="d-flex justify-content-between gap-2">
          <button type="button" class="btn btn-sm btn-outline flex-1" id="clearSetupPatternBtn" style="font-size: 0.78rem;">Clear</button>
          <button type="button" class="btn btn-sm btn-success flex-1" id="savePatternBtn" style="font-size: 0.78rem;">Save Pattern</button>
        </div>

        <?php if ($hasPattern): ?>
          <div class="text-center mt-2">
            <button type="button" class="btn btn-sm btn-outline" id="removePatternBtn" style="font-size: 0.76rem; color: var(--danger); border-color: var(--danger); padding: 4px 10px;">
              Remove Pattern Lock
            </button>
          </div>
        <?php endif; ?>

        <div id="setupPatternStatus" style="font-size: 0.76rem; font-weight: 600; text-align: center; margin-top: 8px;"></div>
      </div>
    </div>
  </div>

  <!-- Password Change Section -->
  <div class="card mb-3" style="padding: 16px 14px; max-width: 480px;">
    <h3 class="card-title mb-2" style="font-size: 0.95rem;">Change Account Password</h3>
    <form id="changePasswordForm">
      <?php echo csrf_field(); ?>
      <div class="form-group mb-2">
        <label class="form-label" style="font-size: 0.8rem;">Current Password:</label>
        <input type="password" name="current_password" class="form-control" required style="padding: 8px 10px; font-size: 0.88rem;">
      </div>

      <div class="form-group mb-3">
        <label class="form-label" style="font-size: 0.8rem;">New Password:</label>
        <input type="password" name="new_password" class="form-control" minlength="6" required style="padding: 8px 10px; font-size: 0.88rem;">
      </div>

      <button type="submit" class="btn btn-outline btn-sm">Update Password</button>
    </form>
  </div>
</div>
