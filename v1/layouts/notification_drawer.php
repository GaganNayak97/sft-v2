<?php
$user = $user ?? auth_user();
$notifications = [];

if ($user) {
    $db = \App\Core\Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 15");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
?>
<div class="drawer-backdrop" id="notifBackdrop"></div>

<div class="notification-drawer" id="notificationDrawer">
  <div class="drawer-header">
    <div class="d-flex align-items-center gap-2">
      <span style="font-size: 1.2rem;">🔔</span>
      <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">Notifications</h3>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-sm btn-outline" id="markAllReadBtn" style="padding: 4px 8px; font-size: 0.75rem;">Mark all read</button>
      <button type="button" class="icon-btn" id="closeNotifBtn" style="width: 32px; height: 32px;">✕</button>
    </div>
  </div>

  <div class="drawer-body" id="drawerNotifList">
    <?php if (empty($notifications)): ?>
      <div style="text-align: center; padding: 40px 10px; color: var(--text-muted);">
        <div style="font-size: 2.5rem; margin-bottom: 8px;">📭</div>
        <p style="font-size: 0.9rem;">No new notifications</p>
      </div>
    <?php else: ?>
      <?php foreach ($notifications as $n): ?>
        <div class="card" style="padding: 12px; <?php echo $n['is_read'] ? 'opacity: 0.8;' : 'border-left: 4px solid var(--secondary);'; ?>">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <strong style="font-size: 0.88rem; color: var(--text-primary);"><?php echo htmlspecialchars($n['title']); ?></strong>
            <span style="font-size: 0.72rem; color: var(--text-muted);"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></span>
          </div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); margin: 0;">
            <?php echo htmlspecialchars($n['message']); ?>
          </p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div style="padding: 12px; border-top: 1px solid var(--border-color); background: var(--bg-main); text-align: center;">
    <a href="<?php echo url('notifications'); ?>" style="font-size: 0.85rem; font-weight: 600;">View All Notifications &rarr;</a>
  </div>
</div>
