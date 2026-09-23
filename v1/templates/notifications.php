<?php
$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user['id'] ?? 0]);
$notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
?>

<div class="notifications-page-container">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin: 0;">🔔 Notification Center</h2>
      <p style="font-size: 0.88rem; color: var(--text-muted); margin: 4px 0 0 0;">
        Real-time account alerts, daily interest credit confirmations, and withdrawal updates.
      </p>
    </div>
    <div>
      <button type="button" class="btn btn-outline" id="pageMarkAllReadBtn">Mark All as Read</button>
    </div>
  </div>

  <div class="card">
    <?php if (empty($notifications)): ?>
      <div style="text-align: center; padding: 48px 14px; color: var(--text-muted);">
        <div style="font-size: 2.5rem; margin-bottom: 8px;">🔕</div>
        <p style="font-size: 0.95rem;">You're all caught up! No notifications.</p>
      </div>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($notifications as $n): ?>
          <div class="card" style="padding: 16px; <?php echo $n['is_read'] ? 'opacity: 0.85;' : 'border-left: 4px solid var(--secondary); background: rgba(0, 186, 242, 0.02);'; ?>">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <div class="d-flex align-items-center gap-2">
                <span style="font-size: 1.1rem;">
                  <?php 
                  $t = $n['type'] ?? 'info';
                  if ($t === 'success' || $t === 'deposit') echo '📥';
                  elseif ($t === 'interest') echo '📈';
                  elseif ($t === 'withdraw') echo '📤';
                  else echo '🔔';
                  ?>
                </span>
                <strong style="font-size: 0.95rem; color: var(--text-primary);"><?php echo htmlspecialchars($n['title']); ?></strong>
              </div>
              <span style="font-size: 0.78rem; color: var(--text-muted);"><?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></span>
            </div>
            <p style="font-size: 0.88rem; color: var(--text-secondary); margin: 4px 0 0 28px;">
              <?php echo htmlspecialchars($n['message']); ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
