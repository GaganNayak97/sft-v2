<?php
$currentPage = $page ?? 'home';
?>
<nav class="app-bottom-nav">
  <!-- Home Tab -->
  <a href="<?php echo url('home'); ?>" class="bottom-nav-item <?php echo ($currentPage === 'home') ? 'active' : ''; ?>" data-nav="home">
    <div class="bottom-icon">
      <?php echo svg_icon('home', '', 20); ?>
    </div>
    <span class="bottom-label">Home</span>
  </a>

  <!-- Balance Tab -->
  <a href="<?php echo url('balance'); ?>" class="bottom-nav-item <?php echo ($currentPage === 'balance') ? 'active' : ''; ?>" data-nav="balance">
    <div class="bottom-icon">
      <?php echo svg_icon('trending', '', 20); ?>
    </div>
    <span class="bottom-label">Balance</span>
  </a>

  <!-- Center Action Capsule Button (Exact match with user screenshot) -->
  <a href="<?php echo url('deposit'); ?>" class="bottom-nav-item center-capsule-item <?php echo ($currentPage === 'deposit') ? 'active' : ''; ?>" data-nav="deposit" title="Quick UPI Deposit">
    <div class="center-pill-btn">
      <div class="center-pill-icon">
        <?php echo svg_icon('plus', '', 22); ?>
      </div>
    </div>
  </a>

  <!-- Withdraw Tab -->
  <a href="<?php echo url('withdraw'); ?>" class="bottom-nav-item <?php echo ($currentPage === 'withdraw') ? 'active' : ''; ?>" data-nav="withdraw">
    <div class="bottom-icon">
      <?php echo svg_icon('withdraw', '', 20); ?>
    </div>
    <span class="bottom-label">Withdraw</span>
  </a>

  <!-- Profile Tab -->
  <a href="<?php echo url('profile'); ?>" class="bottom-nav-item <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>" data-nav="profile">
    <div class="bottom-icon">
      <?php echo svg_icon('user', '', 20); ?>
    </div>
    <span class="bottom-label">Profile</span>
  </a>
</nav>
