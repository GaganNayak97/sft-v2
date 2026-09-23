<?php
$user = $user ?? auth_user();
$tier = $user['tier'] ?? 'standard';
$tierLabel = ($tier === 'vip') ? 'VIP 1.0%' : '0.88%';
$unreadCount = 0;

if ($user) {
    $db = \App\Core\Database::getConnection();
    $stmtNotif = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmtNotif->execute([$user['id']]);
    $unreadCount = (int)$stmtNotif->fetchColumn();
}
?>
<header class="app-navbar">
    <div class="nav-left">
      <a href="<?php echo url('home'); ?>" class="brand-logo">
        <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay" class="brand-icon-img">
        <span>Soft<span class="brand-highlight">Pay</span></span>
      </a>
    </div>

    <div class="nav-right">
      <!-- Universal Search Button (Shifted from nav-left into theme button position) -->
      <button type="button" class="search-trigger-btn" id="openSearchBtn" title="Search features">
        <?php echo svg_icon('search', '', 18); ?>
        <span>Search features, interest, FAQs...</span>
        <span class="kbd-shortcut">Ctrl+K</span>
      </button>

      <!-- Theme Switcher Hidden (Preserved in DOM for JS / profile compatibility) -->
      <button type="button" class="icon-btn theme-toggle-btn" id="themeToggleBtn" title="Toggle Light / Dark Mode" style="display: none !important;">
        <span class="theme-icon-container">
          <svg class="theme-sun-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          <svg class="theme-moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </span>
      </button>

      <!-- Notification Bell with iOS Swing Bounce -->
      <button type="button" class="icon-btn notif-bell-btn" id="openNotificationsBtn" title="Notifications">
        <span class="bell-icon-wrapper">
          <?php echo svg_icon('bell', '', 18); ?>
        </span>
        <?php if ($unreadCount > 0): ?>
          <span class="badge-dot" id="navNotifDot"></span>
        <?php endif; ?>
      </button>

      <?php if (!empty($user['pin_hash']) || !empty($user['pattern_hash'])): ?>
        <!-- Quick Screen Lock Button -->
        <a href="<?php echo url('lock'); ?>&action=lock" class="icon-btn lock-quick-btn" id="navLockBtn" title="Lock Screen Now">
          <span class="lock-icon-wrapper">
            <?php echo svg_icon('lock', '', 17); ?>
          </span>
        </a>
      <?php endif; ?>

      <!-- User Profile Chip -->
      <?php if ($user): ?>
        <a href="<?php echo url('profile'); ?>" class="user-chip">
          <div class="user-avatar" id="navUserAvatar" style="<?php echo !empty($user['avatar']) ? 'overflow: hidden; padding: 0; background: transparent;' : ''; ?>">
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
          <span class="user-name-text"><?php echo htmlspecialchars($user['name']); ?></span>
          <span class="badge <?php echo ($tier === 'vip') ? 'badge-vip' : 'badge-info'; ?>">
            <?php echo $tierLabel; ?>
          </span>
        </a>
      <?php endif; ?>
    </div>
  </header>
  <div class="app-body">
