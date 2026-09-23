<?php
$title = $title ?? 'Stat';
$value = $value ?? '0';
$icon = $icon ?? '📊';
$bgColor = $bgColor ?? 'rgba(0, 41, 112, 0.08)';
$meta = $meta ?? '';
$trend = $trend ?? null;
?>
<div class="stat-card">
  <div class="stat-card-top">
    <span class="stat-card-label"><?php echo htmlspecialchars($title); ?></span>
    <div class="stat-card-icon" style="background: <?php echo htmlspecialchars($bgColor); ?>;">
      <?php echo $icon; ?>
    </div>
  </div>
  <div class="stat-card-value"><?php echo htmlspecialchars($value); ?></div>
  <?php if (!empty($meta)): ?>
    <div class="stat-card-meta">
      <?php if ($trend === 'up'): ?>
        <span style="color: var(--success); font-weight: 700;">&uarr;</span>
      <?php elseif ($trend === 'down'): ?>
        <span style="color: var(--danger); font-weight: 700;">&darr;</span>
      <?php endif; ?>
      <span><?php echo htmlspecialchars($meta); ?></span>
    </div>
  <?php endif; ?>
</div>
