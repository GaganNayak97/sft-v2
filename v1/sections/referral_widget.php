<?php
$user = $user ?? auth_user();
$myCode = $user['referral_code'] ?? 'SOFTPAY88';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseDir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF'] ?? ''));
$basePath = ($baseDir === '/' || $baseDir === '.' || $baseDir === '') ? '' : rtrim($baseDir, '/');
$referralUrl = "http://{$host}{$basePath}/index.php?page=signup&ref=" . urlencode($myCode);

// Fetch live referral stats
$db = \App\Core\Database::getConnection();
$stmtRef = $db->prepare("SELECT COUNT(*) as total_invited, SUM(commission_earned) as total_commission FROM referrals WHERE referrer_user_id = ?");
$stmtRef->execute([$user['id'] ?? 0]);
$refStats = $stmtRef->fetch(\PDO::FETCH_ASSOC) ?: ['total_invited' => 0, 'total_commission' => 0];

$stmtActive = $db->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_user_id = ? AND status IN ('deposited', 'paid')");
$stmtActive->execute([$user['id'] ?? 0]);
$activeDeposits = $stmtActive->fetchColumn() ?: 0;

$shareText = urlencode("Join SoftPay using my invite code {$myCode} to start earning 0.88% to 1.00% daily interest on your savings! Sign up here: {$referralUrl}");
?>
<div class="card" style="padding: 24px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
      <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('gift', '', 20); ?></span>
      <div>
        <h3 class="card-title" style="margin: 0; font-size: 1.05rem; font-weight: 700;">Invite Friends & Earn 5%</h3>
        <p style="font-size: 0.78rem; color: var(--text-muted); margin: 2px 0 0 0;">Get 5% instant cash bonus when friends deposit.</p>
      </div>
    </div>
    <span class="badge" style="background: rgba(0, 186, 242, 0.12); color: #00BAF2; border: 1px solid rgba(0, 186, 242, 0.25); border-radius: 9999px; font-size: 0.72rem; padding: 4px 10px; font-weight: 700;">5% Direct</span>
  </div>

  <!-- Referral Code & Share Bar -->
  <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 20px; padding: 16px; margin-bottom: 16px;">
    
    <!-- Row 1: Invite Code Card -->
    <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-card, #ffffff); border: 1.5px dashed rgba(0, 132, 255, 0.35); border-radius: 14px; padding: 10px 14px; margin-bottom: 12px;">
      <div>
        <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); display: block;">Your Invite Code</span>
        <strong id="referralCodeText" style="font-size: 1.25rem; font-weight: 900; letter-spacing: 2px; color: var(--primary); display: block; font-family: monospace;"><?php echo htmlspecialchars($myCode); ?></strong>
        <input type="hidden" id="referralCodeBox" value="<?php echo htmlspecialchars($myCode); ?>">
      </div>
      <button type="button" class="btn btn-primary btn-sm" id="copyCodeBtn" style="border-radius: 9999px; padding: 8px 16px; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; gap: 6px;">
        <?php echo svg_icon('copy', '', 14); ?>
        <span>Copy Code</span>
      </button>
    </div>

    <!-- Row 2: Clean Invite Link Bar (Replaces raw cut-off URL) -->
    <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-card, #ffffff); border: 1px solid var(--border-color); border-radius: 14px; padding: 10px 14px; margin-bottom: 14px; gap: 10px;">
      <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
        <span style="color: var(--secondary, #00BAF2); flex-shrink: 0; display: flex;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
          </svg>
        </span>
        <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
          softpay.in/join/<strong style="color: var(--text-primary); font-weight: 800;"><?php echo htmlspecialchars($myCode); ?></strong>
        </span>
        <input type="hidden" id="referralUrlBox" value="<?php echo htmlspecialchars($referralUrl); ?>">
      </div>
      <button type="button" class="btn btn-secondary btn-sm" id="copyLinkBtn" style="border-radius: 9999px; padding: 8px 14px; font-weight: 700; font-size: 0.8rem; white-space: nowrap; flex-shrink: 0;">
        <span>Copy Link</span>
      </button>
    </div>

    <!-- Quick Social Share Buttons -->
    <div class="d-flex gap-2 referral-share-btns">
      <a href="https://api.whatsapp.com/send?text=<?php echo $shareText; ?>" target="_blank" class="btn btn-sm btn-success flex-1" style="background: #25D366; border: none; font-size: 0.84rem; font-weight: 700; padding: 12px 16px; border-radius: 9999px; color: #ffffff; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.32);">
        <?php echo svg_icon('whatsapp', '', 18); ?>
        <span>WhatsApp</span>
      </a>
      <a href="https://t.me/share/url?url=<?php echo urlencode($referralUrl); ?>&text=<?php echo urlencode("Earn 1.0% daily interest on SoftPay!"); ?>" target="_blank" class="btn btn-sm btn-info flex-1" style="background: #0088cc; border: none; font-size: 0.84rem; font-weight: 700; padding: 12px 16px; border-radius: 9999px; color: #ffffff; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(0, 136, 204, 0.32);">
        <?php echo svg_icon('telegram', '', 18); ?>
        <span>Telegram</span>
      </a>
    </div>
  </div>

  <!-- Referral Stats: 3 Columns with unified spacing -->
  <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
    <div class="stat-card" style="padding: 14px 12px; text-align: center; border-radius: 16px;">
      <span class="stat-card-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.4px;">Invited</span>
      <div class="stat-card-value" style="color: var(--primary); font-size: 1.25rem; font-weight: 800; margin: 3px 0;"><?php echo (int)$refStats['total_invited']; ?></div>
      <span style="font-size: 0.72rem; color: var(--text-muted);">Friends</span>
    </div>

    <div class="stat-card" style="padding: 14px 12px; text-align: center; border-radius: 16px;">
      <span class="stat-card-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.4px;">Active</span>
      <div class="stat-card-value" style="color: var(--secondary); font-size: 1.25rem; font-weight: 800; margin: 3px 0;"><?php echo (int)$activeDeposits; ?></div>
      <span style="font-size: 0.72rem; color: var(--text-muted);">Depositors</span>
    </div>

    <div class="stat-card" style="padding: 14px 12px; text-align: center; border-radius: 16px;">
      <span class="stat-card-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.4px;">Earned</span>
      <div class="stat-card-value" style="color: var(--success); font-size: 1.25rem; font-weight: 800; margin: 3px 0;"><?php echo format_currency($refStats['total_commission'] ?? 0); ?></div>
      <span style="font-size: 0.72rem; color: var(--text-muted);">Commission</span>
    </div>
  </div>
</div>
