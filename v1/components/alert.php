<?php
$type = $type ?? 'info';
$message = $message ?? '';
$icon = $icon ?? 'ℹ️';

if ($type === 'success') $icon = '✅';
if ($type === 'warning') $icon = '⚠️';
if ($type === 'danger')  $icon = '❌';
?>
<div class="alert-box <?php echo htmlspecialchars($type); ?>">
  <span style="font-size: 1.1rem;"><?php echo $icon; ?></span>
  <div style="flex: 1;"><?php echo $message; ?></div>
</div>
