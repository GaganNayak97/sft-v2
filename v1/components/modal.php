<?php
$id = $id ?? 'customModal';
$title = $title ?? 'Notification';
$body = $body ?? '';
?>
<div class="search-modal-container" id="<?php echo htmlspecialchars($id); ?>">
  <div class="search-modal-card" style="max-width: 480px; padding: 24px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0;"><?php echo htmlspecialchars($title); ?></h3>
      <button type="button" class="icon-btn close-modal-btn" style="width: 32px; height: 32px;">✕</button>
    </div>
    <div class="modal-content-slot">
      <?php echo $body; ?>
    </div>
  </div>
</div>
