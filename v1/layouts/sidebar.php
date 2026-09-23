<?php
$currentPage = $page ?? 'home';
?>
<aside class="app-sidebar">
  <div class="sidebar-content">
    <span class="sidebar-section-title">Core Banking</span>
    
    <a href="<?php echo url('home'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'home') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('home', '', 18); ?></span>
      <span>Home Overview</span>
    </a>

    <a href="<?php echo url('balance'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'balance') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('trending', '', 18); ?></span>
      <span>Balance & Interest</span>
    </a>

    <a href="<?php echo url('deposit'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'deposit') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('deposit', '', 18); ?></span>
      <span>Deposit Funds (UPI)</span>
    </a>

    <a href="<?php echo url('withdraw'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'withdraw') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('withdraw', '', 18); ?></span>
      <span>Withdraw Money</span>
    </a>

    <span class="sidebar-section-title">Growth & Earnings</span>

    <a href="<?php echo url('trading'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'trading') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('trading', '', 18); ?></span>
      <span>Trading</span>
      <span class="badge badge-sm badge-success" style="margin-left: auto; font-size: 0.68rem; padding: 2px 6px;">95%</span>
    </a>

    <a href="<?php echo url('rewards'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'rewards') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('crown', '', 18); ?></span>
      <span>VIP Business Rewards</span>
    </a>

    <a href="<?php echo url('usdt_sell'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'usdt_sell') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('usdt', '', 18); ?></span>
      <span>Sell USDT (Crypto)</span>
    </a>

    <a href="<?php echo url('history'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'history') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('history', '', 18); ?></span>
      <span>Transactions</span>
    </a>

    <span class="sidebar-section-title">Account & Security</span>

    <a href="<?php echo url('notifications'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'notifications') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('bell', '', 18); ?></span>
      <span>Notifications</span>
    </a>

    <a href="<?php echo url('security'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'security') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('shield', '', 18); ?></span>
      <span>PIN & Pattern Lock</span>
    </a>

    <a href="<?php echo url('profile'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('settings', '', 18); ?></span>
      <span>Settings & Profile</span>
    </a>

    <a href="<?php echo url('help'); ?>" class="sidebar-nav-item <?php echo ($currentPage === 'help') ? 'active' : ''; ?>">
      <span class="nav-icon"><?php echo svg_icon('chat', '', 18); ?></span>
      <span>Help & Chatbot FAQs</span>
    </a>

    <div style="margin-top: auto; padding-top: 8px; display: flex; flex-direction: column; gap: 4px;">
      <?php if (!empty($user['pin_hash']) || !empty($user['pattern_hash'])): ?>
        <a href="<?php echo url('lock'); ?>&action=lock" class="sidebar-nav-item" style="color: var(--secondary);" title="Lock App Now">
          <span class="nav-icon"><?php echo svg_icon('lock', '', 18); ?></span>
          <span>Lock App</span>
        </a>
      <?php endif; ?>
      <a href="<?php echo url('logout'); ?>" class="sidebar-nav-item" style="color: var(--danger);">
        <span class="nav-icon"><?php echo svg_icon('logout', '', 18); ?></span>
        <span>Sign Out</span>
      </a>
    </div>
  </div>
</aside>
