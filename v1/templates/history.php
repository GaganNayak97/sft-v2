<?php
$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();
$uid = (int)($user['id'] ?? 0);

// 1. Export CSV Handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmtCsv = $db->prepare("SELECT reference_id, type, amount, direction, status, description, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
    $stmtCsv->execute([$uid]);
    $allRows = $stmtCsv->fetchAll(\PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=SoftPay_Statement_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Reference ID', 'Type', 'Amount (INR)', 'Direction', 'Status', 'Description', 'Timestamp']);
    foreach ($allRows as $r) {
        fputcsv($output, [
            $r['reference_id'],
            strtoupper($r['type']),
            $r['amount'],
            strtoupper($r['direction']),
            strtoupper($r['status']),
            $r['description'],
            $r['created_at']
        ]);
    }
    fclose($output);
    exit;
}

// 2. Fetch KPI Financial Aggregations
$stmtStats = $db->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'deposit' AND status = 'success' THEN amount ELSE 0 END), 0) as total_deposit,
        COALESCE(SUM(CASE WHEN type = 'interest' AND status = 'success' THEN amount ELSE 0 END), 0) as total_interest,
        COALESCE(SUM(CASE WHEN type = 'withdrawal' AND status = 'success' THEN amount ELSE 0 END), 0) as total_withdrawn,
        COALESCE(SUM(CASE WHEN type = 'referral_bonus' AND status = 'success' THEN amount ELSE 0 END), 0) as total_referral,
        COALESCE(SUM(CASE WHEN type = 'usdt_sell' AND status = 'success' THEN amount ELSE 0 END), 0) as total_usdt,
        COUNT(*) as total_count
    FROM transactions 
    WHERE user_id = ?
");
$stmtStats->execute([$uid]);
$kpi = $stmtStats->fetch(\PDO::FETCH_ASSOC) ?: [
    'total_deposit' => 0, 'total_interest' => 0, 'total_withdrawn' => 0, 'total_referral' => 0, 'total_usdt' => 0, 'total_count' => 0
];

// Fetch Counts per Filter
$stmtCounts = $db->prepare("SELECT type, COUNT(*) as count FROM transactions WHERE user_id = ? GROUP BY type");
$stmtCounts->execute([$uid]);
$typeCounts = [];
foreach ($stmtCounts->fetchAll(\PDO::FETCH_ASSOC) as $row) {
    $typeCounts[$row['type']] = (int)$row['count'];
}

$countAll = (int)($kpi['total_count'] ?? 0);
$countDeposit = (int)($typeCounts['deposit'] ?? 0);
$countInterest = (int)($typeCounts['interest'] ?? 0);
$countWithdrawal = (int)($typeCounts['withdrawal'] ?? 0);
$countReferral = (int)($typeCounts['referral_bonus'] ?? 0);
$countUsdt = (int)($typeCounts['usdt_sell'] ?? 0);

// Active Filter
$filter = $_GET['filter'] ?? 'all';
$filter = preg_replace('/[^a-zA-Z0-9_-]/', '', $filter);

$sql = "SELECT * FROM transactions WHERE user_id = ?";
$params = [$uid];

if ($filter !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY created_at DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
?>

<div class="history-page-container">
  <!-- Top Breadcrumb & Actions Bar -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1" style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">
        <a href="<?php echo url('home'); ?>" style="color: var(--text-muted); text-decoration: none;">Dashboard</a>
        <span>/</span>
        <span style="color: var(--secondary, #00BAF2);">Transactions</span>
      </div>
      <h2 style="font-size: 1.65rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.5px;">
        Transactions
      </h2>
      <p style="font-size: 0.86rem; color: var(--text-muted); margin: 4px 0 0 0;">
        Immutable ledger of all UPI deposits, compounding daily interest, withdrawals, referral commissions, and USDT liquidations.
      </p>
    </div>

    <!-- Quick Statement & Print Actions -->
    <div class="d-flex align-items-center gap-2 no-print">
      <button type="button" onclick="window.print()" class="btn btn-sm btn-outline">
        <?php echo svg_icon('file-text', '', 15); ?>
        <span>Print Statement</span>
      </button>
      <a href="<?php echo url('history', ['export' => 'csv']); ?>" class="btn btn-sm btn-primary">
        <?php echo svg_icon('download', '', 15); ?>
        <span>Export Statement (CSV)</span>
      </a>
    </div>
  </div>

  <!-- 4-Card Financial KPI Ledger Summary (Unified Enterprise Palette) -->
  <div class="stat-card-grid mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
    <!-- 1. Total Inflow / Deposits -->
    <div class="card" style="padding: 16px 18px; border: 1px solid var(--border-color); border-radius: 20px; background: var(--bg-card);">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">Total Inflow (Deposited)</span>
        <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('arrow-down-left', '', 16); ?></span>
      </div>
      <div style="font-size: 1.45rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px;">
        <?php echo format_currency($kpi['total_deposit']); ?>
      </div>
      <span style="font-size: 0.72rem; color: var(--text-muted);">
        <?php echo $countDeposit; ?> deposits completed
      </span>
    </div>

    <!-- 2. Total Daily Interest Accrued -->
    <div class="card" style="padding: 16px 18px; border: 1px solid var(--border-color); border-radius: 20px; background: var(--bg-card);">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">Total Interest Accrued</span>
        <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('trending', '', 16); ?></span>
      </div>
      <div style="font-size: 1.45rem; font-weight: 800; color: var(--primary); letter-spacing: -0.5px;">
        +<?php echo format_currency($kpi['total_interest']); ?>
      </div>
      <span style="font-size: 0.72rem; color: var(--secondary, #00BAF2); font-weight: 600;">
        <?php echo $countInterest; ?> daily automated cycles
      </span>
    </div>

    <!-- 3. Total Outflow / Withdrawn -->
    <div class="card" style="padding: 16px 18px; border: 1px solid var(--border-color); border-radius: 20px; background: var(--bg-card);">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">Total Payouts (Withdrawn)</span>
        <span style="color: #ef4444;"><?php echo svg_icon('arrow-up-right', '', 16); ?></span>
      </div>
      <div style="font-size: 1.45rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px;">
        -<?php echo format_currency($kpi['total_withdrawn']); ?>
      </div>
      <span style="font-size: 0.72rem; color: var(--text-muted);">
        <?php echo $countWithdrawal; ?> instant settlements
      </span>
    </div>

    <!-- 4. Active Ledger Balance -->
    <div class="card" style="padding: 16px 18px; border: 1.5px solid rgba(0, 186, 242, 0.4); border-radius: 20px; background: var(--bg-card);">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--secondary, #00BAF2);">Net Liquid Wealth</span>
        <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('check-circle', '', 16); ?></span>
      </div>
      <div style="font-size: 1.45rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px;">
        <?php echo format_currency($user['current_balance'] ?? 0); ?>
      </div>
      <span style="font-size: 0.72rem; color: var(--text-muted);">
        Active wallet ledger balance
      </span>
    </div>
  </div>

  <!-- Filter Pills Bar & Instant Search Input -->
  <div class="card mb-3 no-print" style="padding: 14px 18px; border-radius: 20px;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <!-- Segment Pills -->
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <a href="<?php echo url('history', ['filter' => 'all']); ?>" class="preset-chip <?php echo ($filter === 'all') ? 'active' : ''; ?>">
          All (<?php echo $countAll; ?>)
        </a>
        <a href="<?php echo url('history', ['filter' => 'deposit']); ?>" class="preset-chip <?php echo ($filter === 'deposit') ? 'active' : ''; ?>">
          Deposits (<?php echo $countDeposit; ?>)
        </a>
        <a href="<?php echo url('history', ['filter' => 'interest']); ?>" class="preset-chip <?php echo ($filter === 'interest') ? 'active' : ''; ?>">
          Daily Interest (<?php echo $countInterest; ?>)
        </a>
        <a href="<?php echo url('history', ['filter' => 'withdrawal']); ?>" class="preset-chip <?php echo ($filter === 'withdrawal') ? 'active' : ''; ?>">
          Withdrawals (<?php echo $countWithdrawal; ?>)
        </a>
        <a href="<?php echo url('history', ['filter' => 'referral_bonus']); ?>" class="preset-chip <?php echo ($filter === 'referral_bonus') ? 'active' : ''; ?>">
          Referral (<?php echo $countReferral; ?>)
        </a>
        <a href="<?php echo url('history', ['filter' => 'usdt_sell']); ?>" class="preset-chip <?php echo ($filter === 'usdt_sell') ? 'active' : ''; ?>">
          USDT Sells (<?php echo $countUsdt; ?>)
        </a>
      </div>

      <!-- Live Search Box -->
      <div style="min-width: 240px; flex: 1; max-width: 380px;">
        <div class="d-flex align-items-center gap-2" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--border-radius-full); padding: 7px 14px;">
          <span style="color: var(--text-muted);"><?php echo svg_icon('search', '', 16); ?></span>
          <input type="text" id="txLiveSearch" placeholder="Search by UTR, Ref ID, description..." style="border: none; background: transparent; outline: none; font-size: 0.84rem; width: 100%; color: var(--text-primary);">
          <span id="clearSearchBtn" style="color: var(--text-muted); cursor: pointer; display: none; font-size: 0.8rem;">&times;</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Transactions List Card -->
  <div class="card mb-4" style="border-radius: 24px; padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <span style="color: var(--secondary, #00BAF2);"><?php echo svg_icon('history', '', 18); ?></span>
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem; font-weight: 700;">Ledger Statement Records</h3>
      </div>
      <span class="text-muted" id="txCountBadge" style="font-size: 0.76rem; font-weight: 600;">
        Showing <?php echo count($transactions); ?> of <?php echo $countAll; ?> records
      </span>
    </div>

    <?php if (empty($transactions)): ?>
      <div style="text-align: center; padding: 48px 14px; color: var(--text-muted);">
        <div style="font-size: 2.5rem; margin-bottom: 8px;">📑</div>
        <p style="font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; color: var(--text-primary);">No transactions found for this filter</p>
        <span style="font-size: 0.82rem;">Deposit funds or refer friends to see your transactions recorded here in real time.</span>
        <div class="mt-3">
          <a href="<?php echo url('deposit'); ?>" class="btn btn-sm btn-primary" style="border-radius: 12px; font-weight: 700; padding: 8px 16px;">
            Make First Deposit &rarr;
          </a>
        </div>
      </div>
    <?php else: ?>
      <div class="transaction-list" id="transactionsListContainer">
        <?php foreach ($transactions as $tx): ?>
          <?php component('transaction_item', ['tx' => $tx]); ?>
        <?php endforeach; ?>
      </div>

      <div id="noSearchMatchesMessage" style="display: none; text-align: center; padding: 36px 14px; color: var(--text-muted);">
        <p style="font-size: 0.92rem; font-weight: 700; margin-bottom: 2px;">No matching transactions found</p>
        <span style="font-size: 0.8rem;">Try searching with a different UTR, amount, or keyword.</span>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Bank-Grade Interactive Transaction Receipt Modal -->
<div class="receipt-modal-backdrop" id="receiptModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 1300; align-items: center; justify-content: center; padding: 18px;">
  <div class="receipt-modal-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 24px; max-width: 440px; width: 100%; box-shadow: 0 24px 48px rgba(0, 0, 0, 0.4); overflow: hidden; animation: searchBouncePop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);">
    
    <!-- Receipt Header -->
    <div style="background: linear-gradient(135deg, #002970 0%, #0042a5 100%); color: #ffffff; padding: 22px 22px 18px 22px; text-align: center; position: relative;">
      <button type="button" id="closeReceiptBtn" style="position: absolute; right: 14px; top: 14px; background: rgba(255, 255, 255, 0.15); border: none; color: #ffffff; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; line-height: 1;">&times;</button>
      
      <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 6px;">
        <img src="<?php echo asset('images/logo.png'); ?>" alt="SoftPay" style="width: 24px; height: 24px; border-radius: 6px;">
        <span style="font-weight: 800; font-size: 1.05rem; letter-spacing: -0.2px;">SoftPay Banking Receipt</span>
      </div>
      <span style="font-size: 0.72rem; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600;">Official Ledger Settlement</span>
      
      <div id="receiptAmountDisplay" style="font-size: 2rem; font-weight: 800; letter-spacing: -0.5px; margin-top: 10px;">
        +₹15,000.00
      </div>
      <div style="display: inline-flex; align-items: center; gap: 4px; background: rgba(255, 255, 255, 0.18); padding: 3px 10px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; margin-top: 6px;">
        <span>✓</span> <span id="receiptStatusText">Verified & Completed</span>
      </div>
    </div>

    <!-- Receipt Perforated Tear Line -->
    <div style="display: flex; align-items: center; position: relative; margin: -1px 0;">
      <div style="width: 14px; height: 24px; background: rgba(0,0,0,0.65); border-radius: 0 14px 14px 0;"></div>
      <div style="flex: 1; border-bottom: 2px dashed var(--border-color); height: 1px;"></div>
      <div style="width: 14px; height: 24px; background: rgba(0,0,0,0.65); border-radius: 14px 0 0 14px;"></div>
    </div>

    <!-- Receipt Details Table -->
    <div style="padding: 20px 24px;">
      <div style="display: flex; flex-direction: column; gap: 11px; font-size: 0.84rem;">
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Transaction Type</span>
          <span style="font-weight: 700; color: var(--text-primary);" id="receiptTypeLabel">UPI 2.0 Deposit</span>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Reference ID</span>
          <div class="d-flex align-items-center gap-1">
            <span class="font-monospace" style="font-weight: 700; color: var(--secondary, #00BAF2);" id="receiptRefId">SOFTPAY-DEP-849201</span>
            <button type="button" id="copyReceiptRefBtn" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0 4px;" title="Copy Ref ID">
              <?php echo svg_icon('copy', '', 13); ?>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Account Holder</span>
          <span style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($user['name'] ?? 'Account Holder'); ?></span>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Timestamp</span>
          <span style="font-weight: 600; color: var(--text-secondary);" id="receiptDate">19 Sep 2026, 09:15 AM</span>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Transaction Fee</span>
          <span style="font-weight: 700; color: #10b981;">₹0.00 (Zero Fee)</span>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 10px;">
          <span class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Description:</span>
          <div id="receiptDescription" style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 2px;">
            UPI Deposit via GPay
          </div>
        </div>
      </div>

      <!-- Compliance Seal -->
      <div class="d-flex align-items-center justify-content-center gap-2 mt-4 pt-3" style="border-top: 1px dashed var(--border-color); font-size: 0.72rem; color: var(--text-muted); text-align: center;">
        <?php echo svg_icon('shield', '', 14); ?>
        <span>NPCI UPI 2.0 & RBI Compliant Digital Banking Record</span>
      </div>

      <!-- Modal Action Buttons -->
      <div class="d-flex gap-2 mt-3">
        <button type="button" onclick="window.print()" class="btn btn-outline flex-1">
          Print Receipt
        </button>
        <button type="button" id="dismissReceiptBtn" class="btn btn-primary flex-1">
          Done
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Print Stylesheet -->
<style>
@media print {
  body {
    background: #ffffff !important;
    color: #000000 !important;
  }
  .app-navbar, .app-sidebar, .app-bottom-nav, .chatbot-launcher, .no-print, .tx-chevron, .receipt-modal-backdrop {
    display: none !important;
  }
  .app-main-content {
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  .card {
    border: 1px solid #ccc !important;
    box-shadow: none !important;
  }
  .history-page-container {
    padding: 20px !important;
  }
}

.tx-interactive-row:hover {
  background: var(--bg-main, #f8fafc) !important;
  transform: translateX(4px);
  border-color: rgba(0, 186, 242, 0.4) !important;
}

[data-theme="dark"] .tx-interactive-row:hover {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(0, 186, 242, 0.35) !important;
}
</style>

<!-- Live Instant Filtering & Modal Controller Script -->
<script>
$(document).ready(function() {
  // 1. Live Instant Search Filter
  $('#txLiveSearch').on('input', function() {
    const q = $(this).val().toLowerCase().trim();
    const rows = $('.tx-interactive-row');
    let matches = 0;

    if (q.length > 0) {
      $('#clearSearchBtn').show();
    } else {
      $('#clearSearchBtn').hide();
    }

    rows.each(function() {
      const title = ($(this).data('title') || '').toString().toLowerCase();
      const ref = ($(this).data('ref') || '').toString().toLowerCase();
      const amount = ($(this).data('amount') || '').toString().toLowerCase();
      const date = ($(this).data('date') || '').toString().toLowerCase();

      if (title.includes(q) || ref.includes(q) || amount.includes(q) || date.includes(q)) {
        $(this).show();
        matches++;
      } else {
        $(this).hide();
      }
    });

    $('#txCountBadge').text('Showing ' + matches + ' matching record' + (matches !== 1 ? 's' : ''));
    if (matches === 0) {
      $('#noSearchMatchesMessage').show();
    } else {
      $('#noSearchMatchesMessage').hide();
    }
  });

  $('#clearSearchBtn').on('click', function() {
    $('#txLiveSearch').val('').trigger('input');
  });

  // 2. Interactive Digital Receipt Modal Pop
  $(document).on('click', '.tx-interactive-row', function(e) {
    // If clicked directly on a link or button, skip
    if ($(e.target).closest('a, button').length) return;

    const row = $(this);
    const amount = row.data('amount');
    const typeLabel = row.data('type-label') || 'Transaction';
    const ref = row.data('ref') || 'N/A';
    const date = row.data('date') || '';
    const desc = row.data('title') || '';
    const status = (row.data('status') || 'success').toUpperCase();

    $('#receiptAmountDisplay').text(amount);
    $('#receiptTypeLabel').text(typeLabel);
    $('#receiptRefId').text(ref);
    $('#receiptDate').text(date);
    $('#receiptDescription').text(desc);
    $('#receiptStatusText').text(status === 'SUCCESS' ? 'Verified & Settled' : status);

    $('#receiptModalBackdrop').css('display', 'flex').hide().fadeIn(180);
    if ('vibrate' in navigator) {
      try { navigator.vibrate(12); } catch (e) {}
    }
  });

  $('#closeReceiptBtn, #dismissReceiptBtn, #receiptModalBackdrop').on('click', function(e) {
    if (e.target === this) {
      $('#receiptModalBackdrop').fadeOut(150);
    }
  });

  // Copy Reference ID from modal
  $('#copyReceiptRefBtn').on('click', function() {
    const text = $('#receiptRefId').text();
    const $btn = $(this);
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(function() {
        const origText = $btn.text();
        $btn.text('✓ Copied!').css('color', '#16a34a');
        setTimeout(function() {
          $btn.text(origText).css('color', '');
        }, 1500);
      });
    }
  });
});
</script>
