<?php
$db = \App\Core\Database::getConnection();
$faqs = $db->query("SELECT * FROM support_faqs ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
?>

<div class="help-page-container">
  <!-- Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0;">Help Center & Guides</h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
        System policies, banking guidance, and 24/7 AI chatbot assistant.
      </p>
    </div>
    <div>
      <button type="button" class="btn btn-sm btn-primary" id="openChatbotFromHelpBtn">
        <?php echo svg_icon('chat', '', 16); ?> Open Assistant
      </button>
    </div>
  </div>

  <!-- Step-by-Step Onboarding Walkthrough Cards -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; margin-bottom: 20px;">
    <div class="card" style="padding: 14px;">
      <div style="color: #059669; margin-bottom: 6px;"><?php echo svg_icon('deposit', '', 24); ?></div>
      <h4 style="font-size: 0.92rem; color: var(--text-primary); margin-bottom: 4px;">1. Deposit via UPI</h4>
      <p style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4;">
        Scan QR or copy UPI ID with any app (Paytm, GPay, PhonePe) and submit the 12-digit UTR.
      </p>
    </div>

    <div class="card" style="padding: 14px;">
      <div style="color: #0284c7; margin-bottom: 6px;"><?php echo svg_icon('trending', '', 24); ?></div>
      <h4 style="font-size: 0.92rem; color: var(--text-primary); margin-bottom: 4px;">2. Earn Daily Interest</h4>
      <p style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4;">
        Automated compounding calculates interest every midnight at 0.88% (or 1.00% VIP on ₹5,000+).
      </p>
    </div>

    <div class="card" style="padding: 14px;">
      <div style="color: #d97706; margin-bottom: 6px;"><?php echo svg_icon('shield-check', '', 24); ?></div>
      <h4 style="font-size: 0.92rem; color: var(--text-primary); margin-bottom: 4px;">3. 0.55% Safety Rule</h4>
      <p style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4;">
        Withdrawals unlock automatically once accumulated interest reaches 0.55% of your deposit principal.
      </p>
    </div>

    <div class="card" style="padding: 14px;">
      <div style="color: #7c3aed; margin-bottom: 6px;"><?php echo svg_icon('gift', '', 24); ?></div>
      <h4 style="font-size: 0.92rem; color: var(--text-primary); margin-bottom: 4px;">4. Invite & Earn 5%</h4>
      <p style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4;">
        Share your unique referral link to receive an instant 5% commission on all friend deposits.
      </p>
    </div>
  </div>

  <!-- Frequently Asked Questions Accordion -->
  <div class="card mb-3" style="padding: 16px 14px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <span style="color: var(--primary);"><?php echo svg_icon('help', '', 18); ?></span>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem;">Frequently Asked Questions</h3>
      </div>
      <span class="badge badge-info" style="font-size: 0.7rem;">Verified</span>
    </div>

    <div style="display: flex; flex-direction: column; gap: 8px;">
      <?php foreach ($faqs as $f): ?>
        <details class="card" style="padding: 10px 14px; cursor: pointer; background: var(--bg-main);">
          <summary style="font-weight: 700; font-size: 0.88rem; color: var(--text-primary); outline: none;">
            <?php echo htmlspecialchars($f['question']); ?>
          </summary>
          <div style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 8px; line-height: 1.5; border-top: 1px solid var(--border-color); padding-top: 8px;">
            <?php echo nl2br(htmlspecialchars($f['answer'])); ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</div>
