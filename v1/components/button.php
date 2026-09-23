<?php
$text = $text ?? 'Submit';
$type = $type ?? 'button';
$variant = $variant ?? 'primary';
$class = $class ?? '';
$id = $id ?? '';
$href = $href ?? null;
$icon = $icon ?? '';
$disabled = !empty($disabled) ? 'disabled' : '';

$classes = "btn btn-{$variant} {$class}";

if ($href): ?>
  <a href="<?php echo htmlspecialchars($href); ?>" class="<?php echo $classes; ?>" <?php echo $id ? 'id="'.$id.'"' : ''; ?>>
    <?php if ($icon): ?><span><?php echo $icon; ?></span><?php endif; ?>
    <span><?php echo htmlspecialchars($text); ?></span>
  </a>
<?php else: ?>
  <button type="<?php echo htmlspecialchars($type); ?>" class="<?php echo $classes; ?>" <?php echo $id ? 'id="'.$id.'"' : ''; ?> <?php echo $disabled; ?>>
    <?php if ($icon): ?><span><?php echo $icon; ?></span><?php endif; ?>
    <span><?php echo htmlspecialchars($text); ?></span>
  </button>
<?php endif; ?>
