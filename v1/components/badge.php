<?php
$text = $text ?? '';
$type = $type ?? 'info';
$icon = $icon ?? '';
?>
<span class="badge badge-<?php echo htmlspecialchars($type); ?>">
  <?php if ($icon): ?><span><?php echo $icon; ?></span><?php endif; ?>
  <span><?php echo htmlspecialchars($text); ?></span>
</span>
