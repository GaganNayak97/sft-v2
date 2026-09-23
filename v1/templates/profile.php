<?php
$user = $user ?? auth_user();
$tier = $user['tier'] ?? 'standard';
$tierLabel = ($tier === 'vip') ? 'VIP Business (1.0% Daily)' : 'Standard (0.88% Daily)';
?>

<div class="profile-page-container">
  <!-- Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0;">Settings & Profile</h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
        Personal details, profile avatar studio, payout accounts, and security preferences.
      </p>
    </div>
  </div>

  <!-- Hero Profile & Avatar Header Card -->
  <div class="card mb-3" style="padding: 20px; background: linear-gradient(135deg, var(--bg-card) 0%, rgba(0, 186, 242, 0.04) 100%); border: 1px solid var(--border-color);">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Interactive Avatar Box -->
        <div id="profileAvatarWrapper" style="position: relative; cursor: pointer;" title="Click to customize profile picture">
          <div class="profile-avatar-box" id="profileMainAvatarBox" style="width: 84px; height: 84px; border-radius: 50%; overflow: hidden; border: 3px solid var(--primary); box-shadow: 0 4px 16px rgba(0, 41, 112, 0.2); background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 2.2rem; font-weight: 800;">
            <?php if (!empty($user['avatar'])): ?>
              <?php if (strpos($user['avatar'], '<svg') === 0): ?>
                <?php echo $user['avatar']; ?>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
              <?php endif; ?>
            <?php else: ?>
              <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
            <?php endif; ?>
          </div>
          <!-- Camera / Edit Badge Overlay -->
          <div style="position: absolute; bottom: 0; right: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--secondary); color: #002970; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.25); border: 2px solid var(--bg-card);">
            <?php echo svg_icon('sparkles', '', 14); ?>
          </div>
        </div>

        <!-- User Info Details -->
        <div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin: 0;">
              <?php echo htmlspecialchars($user['name']); ?>
            </h3>
            <span class="badge <?php echo ($tier === 'vip') ? 'badge-vip' : 'badge-info'; ?>" style="font-size: 0.72rem;">
              <?php echo $tierLabel; ?>
            </span>
          </div>
          <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
            <?php echo htmlspecialchars($user['email']); ?> • +91 <?php echo htmlspecialchars($user['phone']); ?>
          </div>
          <div class="d-flex align-items-center gap-2 mt-2">
            <span class="trust-badge" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 12px; background: rgba(5, 150, 105, 0.1); color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
              <?php echo svg_icon('shield-check', '', 13); ?> KYC Verified
            </span>
            <span style="font-size: 0.72rem; color: var(--text-muted);">Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></span>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary btn-sm" id="openAvatarStudioBtn" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
          <?php echo svg_icon('sparkles', '', 15); ?>
          <span>Change Avatar / Photo</span>
        </button>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-3 mb-3">
    <!-- Left: Personal & Bank Details -->
    <div style="flex: 1.2; min-width: 280px;">
      <div class="card mb-3" style="padding: 18px 16px;">
        <h3 class="card-title mb-2" style="font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
          <?php echo svg_icon('user', '', 18); ?> Personal Information
        </h3>
        <form id="profileForm">
          <?php echo csrf_field(); ?>
          <div class="form-group mb-2">
            <label class="form-label" style="font-size: 0.8rem;">Full Name:</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required style="padding: 8px 10px; font-size: 0.9rem;">
          </div>

          <div class="form-group mb-2">
            <label class="form-label" style="font-size: 0.8rem;">Email Address:</label>
            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="opacity: 0.8; padding: 8px 10px; font-size: 0.9rem;">
          </div>

          <div class="form-group mb-2">
            <label class="form-label" style="font-size: 0.8rem;">Mobile Number:</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" disabled style="opacity: 0.8; padding: 8px 10px; font-size: 0.9rem;">
          </div>

          <h3 class="card-title mb-2" style="border-top: 1px solid var(--border-color); padding-top: 14px; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
            <?php echo svg_icon('credit-card', '', 18); ?> Payout UPI Destination
          </h3>

          <div class="form-group mb-2">
            <label class="form-label" style="font-size: 0.8rem;">Primary Receiving UPI ID:</label>
            <input type="text" name="upi_id" class="form-control" placeholder="e.g. username@softpay" value="<?php echo htmlspecialchars($user['upi_id'] ?? ''); ?>" style="padding: 8px 10px; font-size: 0.9rem;">
          </div>

          <div class="form-group mb-2">
            <label class="form-label" style="font-size: 0.8rem;">Bank Account Number:</label>
            <input type="text" name="bank_account" class="form-control" placeholder="e.g. 918239018234" value="<?php echo htmlspecialchars($user['bank_account'] ?? ''); ?>" style="padding: 8px 10px; font-size: 0.9rem;">
          </div>

          <div class="form-group mb-3">
            <label class="form-label" style="font-size: 0.8rem;">Bank IFSC Code:</label>
            <input type="text" name="bank_ifsc" class="form-control" placeholder="e.g. PYTM0123456" value="<?php echo htmlspecialchars($user['bank_ifsc'] ?? ''); ?>" style="padding: 8px 10px; font-size: 0.9rem;">
          </div>

          <button type="submit" class="btn btn-primary btn-sm" id="saveProfileBtn">Save Changes</button>
        </form>
      </div>
    </div>

    <!-- Right: Theme & Security Overview -->
    <div style="flex: 1; min-width: 260px;">
      <!-- Theme Selection Card -->
      <div class="card mb-3" style="padding: 16px 14px;">
        <h3 class="card-title mb-1" style="font-size: 0.95rem;">Theme & Visuals</h3>
        <p style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 10px;">
          Choose your interface appearance preference.
        </p>

        <div class="preset-chips">
          <button type="button" class="preset-chip theme-select-chip <?php echo (($user['theme_preference'] ?? 'light') === 'light') ? 'active' : ''; ?>" data-theme="light">
            Light (SoftPay)
          </button>
          <button type="button" class="preset-chip theme-select-chip <?php echo (($user['theme_preference'] ?? 'light') === 'dark') ? 'active' : ''; ?>" data-theme="dark">
            Dark Mode
          </button>
          <button type="button" class="preset-chip theme-select-chip <?php echo (($user['theme_preference'] ?? 'light') === 'system') ? 'active' : ''; ?>" data-theme="system">
            System
          </button>
        </div>
      </div>

      <!-- Security Status Card -->
      <div class="card mb-3" style="padding: 16px 14px;">
        <h3 class="card-title mb-1" style="font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
          <?php echo svg_icon('shield', '', 18); ?> Security Status
        </h3>
        <p style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 10px;">
          Protection layers for payouts and sensitive transactions.
        </p>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          <div class="d-flex justify-content-between align-items-center" style="padding: 8px 10px; background: var(--bg-main); border-radius: 8px;">
            <div>
              <strong style="font-size: 0.82rem; display: block;">4-Digit PIN</strong>
              <span style="font-size: 0.72rem; color: var(--text-muted);">
                <?php echo !empty($user['pin_hash']) ? 'Active' : 'Not Set'; ?>
              </span>
            </div>
            <a href="<?php echo url('security'); ?>" class="btn btn-sm btn-outline" style="font-size: 0.72rem; padding: 3px 8px;">Edit</a>
          </div>

          <div class="d-flex justify-content-between align-items-center" style="padding: 8px 10px; background: var(--bg-main); border-radius: 8px;">
            <div>
              <strong style="font-size: 0.82rem; display: block;">3x3 Pattern Lock</strong>
              <span style="font-size: 0.72rem; color: var(--text-muted);">
                <?php echo !empty($user['pattern_hash']) ? 'Active' : 'Not Set'; ?>
              </span>
            </div>
            <a href="<?php echo url('security'); ?>" class="btn btn-sm btn-outline" style="font-size: 0.72rem; padding: 3px 8px;">Edit</a>
          </div>
        </div>
      </div>

      <!-- Referral Code Card -->
      <div class="card" style="padding: 16px 14px;">
        <h3 class="card-title mb-1" style="font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
          <?php echo svg_icon('gift', '', 18); ?> Referral Invite Code
        </h3>
        <p style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 6px;">
          Share code with contacts to earn 5% instantly on every deposit.
        </p>
        <div style="font-size: 1.3rem; font-weight: 800; color: var(--primary); letter-spacing: 1px; margin-top: 4px;">
          <?php echo htmlspecialchars($user['referral_code'] ?? 'N/A'); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Advanced Avatar & Photo Studio Modal Dialog -->
<div class="avatar-modal-overlay" id="avatarStudioModal">
  <div class="avatar-modal-dialog">
    <!-- Modal Header -->
    <div class="avatar-modal-header">
      <div class="d-flex align-items-center gap-2">
        <div class="icon-bubble icon-bubble-primary" style="width: 36px; height: 36px;">
          <?php echo svg_icon('sparkles', '', 18); ?>
        </div>
        <div>
          <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text-primary);">Avatar & Photo Studio</h3>
          <p style="margin: 0; font-size: 0.74rem; color: var(--text-muted);">Curated financial avatars or custom cropped & filtered photo</p>
        </div>
      </div>
      <button type="button" class="icon-btn" id="closeAvatarModalBtn" style="width: 30px; height: 30px; font-size: 0.9rem;" title="Close">✕</button>
    </div>

    <!-- Modal Body -->
    <div class="avatar-modal-body">
      <!-- Tabs Navigation -->
      <div class="d-flex gap-2 mb-3" style="border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
        <button type="button" class="btn btn-sm btn-primary active" id="tabSystemAvatars" style="font-size: 0.8rem; border-radius: 20px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px;">
          <?php echo svg_icon('user', '', 14); ?> System Avatars
        </button>
        <button type="button" class="btn btn-sm btn-outline" id="tabCustomPhoto" style="font-size: 0.8rem; border-radius: 20px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px;">
          <?php echo svg_icon('sparkles', '', 14); ?> Custom Photo Studio
        </button>
      </div>

      <!-- Tab 1: System Avatars View -->
      <div id="systemAvatarsView">
        <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 12px;">
          Select a verified financial badge avatar:
        </p>

        <div class="system-avatar-grid">
          <?php foreach (system_avatars() as $sav): ?>
            <div class="system-avatar-card <?php echo !empty($sav['is_animated']) ? 'is-animated-card' : ''; ?>" data-name="<?php echo htmlspecialchars($sav['name']); ?>">
              <?php if (!empty($sav['is_animated'])): ?>
                <span class="avatar-animated-badge">✨ LIVE</span>
              <?php endif; ?>
              <div class="avatar-svg-holder">
                <?php echo $sav['svg']; ?>
              </div>
              <span class="system-avatar-name"><?php echo htmlspecialchars($sav['name']); ?></span>
              <span class="system-avatar-tag"><?php echo htmlspecialchars($sav['tag']); ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top: 1px solid var(--border-color);">
          <button type="button" id="resetDefaultAvatarBtn" class="btn btn-outline btn-sm" style="font-size: 0.76rem;">
            <?php echo svg_icon('refresh', '', 13); ?> Reset to Initial
          </button>
          <button type="button" id="applySystemAvatarBtn" class="btn btn-primary btn-sm" disabled style="font-size: 0.82rem;">
            Apply Avatar
          </button>
        </div>
      </div>

      <!-- Tab 2: Custom Photo Upload & Studio View -->
      <div id="customPhotoStudioView" style="display: none;">
        <!-- Hidden file input -->
        <input type="file" id="customPhotoInput" accept="image/png, image/jpeg, image/webp" style="display: none;">

        <!-- Dropzone / Picker -->
        <div class="photo-dropzone" id="photoDropzone" onclick="$('#customPhotoInput').click();">
          <div class="icon-bubble icon-bubble-primary" style="width: 46px; height: 46px; margin: 0 auto 8px;">
            <?php echo svg_icon('sparkles', '', 22); ?>
          </div>
          <h4 style="font-size: 0.92rem; font-weight: 700; margin-bottom: 4px; color: var(--text-primary);">Click or Drag to Upload Photo</h4>
          <p style="font-size: 0.74rem; color: var(--text-muted); margin: 0;">JPG, PNG, or WebP. Interactive pan, crop & filters on next step.</p>
        </div>

        <!-- Interactive Canvas Workspace -->
        <div id="cropWorkspace" style="display: none;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-primary);">Interactive Studio Cropper</span>
            <button type="button" class="btn btn-outline btn-sm" id="changePhotoBtn" style="font-size: 0.72rem; padding: 3px 8px;">
              Choose Different Photo
            </button>
          </div>

          <!-- Canvas with Circular Mask -->
          <div class="canvas-cropper-box">
            <canvas id="photoCropCanvas" width="300" height="300"></canvas>
            <div class="circular-crop-mask"></div>
          </div>
          <p style="text-align: center; font-size: 0.72rem; color: var(--text-muted); margin-top: 5px; margin-bottom: 12px;">
            👆 Drag with mouse or touch to pan & position within the circle
          </p>

          <!-- Studio Controls -->
          <div class="studio-controls-panel">
            <!-- Zoom Slider -->
            <div class="control-slider-group">
              <span class="control-slider-label">🔍 Zoom:</span>
              <input type="range" id="zoomSlider" class="control-slider" min="0.5" max="3.0" step="0.05" value="1.0">
            </div>

            <!-- Transform Buttons -->
            <div class="d-flex gap-2 justify-content-center">
              <button type="button" class="btn btn-outline btn-sm" id="rotateLeftBtn" style="font-size: 0.74rem; padding: 4px 10px;">
                ⟲ Rotate Left
              </button>
              <button type="button" class="btn btn-outline btn-sm" id="rotateRightBtn" style="font-size: 0.74rem; padding: 4px 10px;">
                ⟳ Rotate Right
              </button>
              <button type="button" class="btn btn-outline btn-sm" id="flipHorizontalBtn" style="font-size: 0.74rem; padding: 4px 10px;">
                ⇄ Flip Horizontal
              </button>
            </div>

            <!-- 1-Click Filters -->
            <div>
              <label class="control-slider-label mb-1" style="display: block;">Creative Color Filters:</label>
              <div class="filter-pills-row">
                <button type="button" class="filter-pill active" data-filter="normal">Normal</button>
                <button type="button" class="filter-pill" data-filter="vibrant">Vibrant</button>
                <button type="button" class="filter-pill" data-filter="warm">Warm Gold</button>
                <button type="button" class="filter-pill" data-filter="cool">Cool Blue</button>
                <button type="button" class="filter-pill" data-filter="vintage">Vintage</button>
                <button type="button" class="filter-pill" data-filter="bw">B&W</button>
              </div>
            </div>

            <!-- Fine Adjustments -->
            <div class="control-slider-group">
              <span class="control-slider-label">☀️ Brightness:</span>
              <input type="range" id="brightnessSlider" class="control-slider" min="-50" max="50" step="1" value="0">
            </div>
            <div class="control-slider-group">
              <span class="control-slider-label">🌓 Contrast:</span>
              <input type="range" id="contrastSlider" class="control-slider" min="50" max="150" step="1" value="100">
            </div>
            <div class="control-slider-group">
              <span class="control-slider-label">🎨 Saturation:</span>
              <input type="range" id="saturationSlider" class="control-slider" min="0" max="200" step="1" value="100">
            </div>

            <!-- Previews -->
            <div class="previews-bar">
              <div class="d-flex align-items-center gap-3">
                <div class="preview-circle-large">
                  <img id="largeAvatarPreview" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="preview-circle-medium">
                  <img id="navAvatarPreview" src="" alt="Mini Preview" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div>
                  <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary);">Real-Time Preview</div>
                  <div style="font-size: 0.7rem; color: var(--text-muted);">Exact circle render for navbar & profile</div>
                </div>
              </div>
            </div>

            <!-- Save Action Button -->
            <button type="button" id="saveCustomPhotoBtn" class="btn btn-primary" style="padding: 10px; font-weight: 700; font-size: 0.9rem;">
              Save & Set Profile Photo
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
