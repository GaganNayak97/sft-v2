<?php
/**
 * SoftPay Spot Trading Terminal - Binance & Zerodha Kite Architecture
 * Native SoftPay System-Themed Spot Exchange with TradingView Technical Analysis at the top, Orderbook, and Live PnL
 */

$user = $user ?? auth_user();
$db = \App\Core\Database::getConnection();

// Fetch fresh user balance
$stmtAcc = $db->prepare("SELECT * FROM accounts WHERE user_id = ?");
$stmtAcc->execute([$user['id'] ?? 0]);
$account = $stmtAcc->fetch(\PDO::FETCH_ASSOC) ?: [
    'current_balance' => 0.00,
    'total_interest_earned' => 0.00
];

$userBalance = (float)($account['current_balance'] ?? 0.00);
?>

<link rel="stylesheet" href="css/trading.css?v=<?php echo time(); ?>">

<div class="spot-trading-container">

  <!-- 1. HERO TOP: Unified Advanced TradingView Chart + Market Ticker Card -->
  <div class="trading-chart-hero-card">
    
    <!-- Top Row: Market Pair Selector & Live Real-Time Price -->
    <div class="hero-chart-header">
      <div class="hero-pair-selector-wrap">
        <div class="pair-asset-icon-badge" id="pairAssetIcon" title="Active Asset">
          <?php echo svg_icon('btc', 'crypto-svg-icon', 22); ?>
        </div>
        <select id="pairSelector" class="hero-pair-select" title="Select Market Pair">
          <option value="BTC/INR" selected>BTC / INR (Bitcoin)</option>
          <option value="ETH/INR">ETH / INR (Ethereum)</option>
          <option value="GOLD/INR">GOLD / INR (Gold Spot)</option>
          <option value="SOL/INR">SOL / INR (Solana)</option>
        </select>
      </div>

      <div class="hero-price-wrap">
        <div class="hero-live-price" id="tickerHeaderPrice">₹64,250.00</div>
        <span class="ticker-change-pill up" id="tickerHeaderChange">
          <?php echo svg_icon('arrow-up', 'ticker-change-svg', 10); ?> +2.84%
        </span>
      </div>
    </div>

    <!-- Sub-Row: Sleek 24H Metrics & Available Trading Balance Strip -->
    <div class="hero-stats-ribbon">
      <div class="hero-stat-chip">
        <span class="stat-lbl">
          <?php echo svg_icon('arrow-up', 'text-success', 11); ?> 24H HIGH
        </span>
        <span class="stat-val" id="stat24High">₹65,100.00</span>
      </div>
      <div class="hero-stat-chip">
        <span class="stat-lbl">
          <?php echo svg_icon('arrow-down', 'text-danger', 11); ?> 24H LOW
        </span>
        <span class="stat-val" id="stat24Low">₹62,850.00</span>
      </div>
      <div class="hero-stat-chip">
        <span class="stat-lbl">
          <?php echo svg_icon('volume', 'text-info', 11); ?> 24H VOL
        </span>
        <span class="stat-val" id="stat24Vol">1,280.45 BTC</span>
      </div>
      <div class="hero-balance-chip">
        <span class="stat-lbl">
          <?php echo svg_icon('wallet', 'text-primary', 12); ?> TRADING BAL
        </span>
        <span class="stat-val bal" id="userAvailableTradingBal">₹<?php echo number_format($userBalance, 2); ?></span>
        <a href="<?php echo url('deposit'); ?>" class="deposit-mini-btn" title="Add Funds via UPI">
          <?php echo svg_icon('plus', '', 11); ?> Add
        </a>
      </div>
    </div>

    <!-- Interactive Technical Chart Controls Bar -->
    <div class="chart-controls-toolbar">
      <!-- Timeframe Buttons -->
      <div class="chart-tf-group">
        <button type="button" class="tf-btn" data-tf="1m">1m</button>
        <button type="button" class="tf-btn" data-tf="5m">5m</button>
        <button type="button" class="tf-btn active" data-tf="15m">15m</button>
        <button type="button" class="tf-btn" data-tf="1h">1h</button>
        <button type="button" class="tf-btn" data-tf="4h">4h</button>
        <button type="button" class="tf-btn" data-tf="1D">1D</button>
      </div>

      <!-- Indicators & View Control Tools -->
      <div class="chart-tools-group">
        <button type="button" class="tool-btn active" id="btnToggleCandle" title="Candlestick Chart">
          <?php echo svg_icon('candlestick', '', 15); ?>
          <span class="tool-btn-label">Candles</span>
        </button>
        <button type="button" class="tool-btn" id="btnToggleLine" title="Line Chart">
          <?php echo svg_icon('line-chart', '', 15); ?>
          <span class="tool-btn-label">Line</span>
        </button>
        <span class="tool-divider"></span>
        <button type="button" class="tool-btn active" id="btnToggleMA" title="7-Period Moving Average">
          <?php echo svg_icon('trending-up', 'text-warning', 13); ?> MA
        </button>
        <button type="button" class="tool-btn active" id="btnToggleEMA" title="25-Period EMA">
          <?php echo svg_icon('trending-up', 'text-info', 13); ?> EMA
        </button>
        <button type="button" class="tool-btn active" id="btnToggleVol" title="Volume Histogram">
          <?php echo svg_icon('volume', '', 13); ?> VOL
        </button>
        <span class="tool-divider"></span>
        <button type="button" class="tool-btn" id="btnZoomIn" title="Zoom In">
          <?php echo svg_icon('zoom-in', '', 15); ?>
        </button>
        <button type="button" class="tool-btn" id="btnZoomOut" title="Zoom Out">
          <?php echo svg_icon('zoom-out', '', 15); ?>
        </button>
        <button type="button" class="tool-btn" id="btnResetView" title="Reset View / Fit Screen">
          <?php echo svg_icon('reset-view', '', 15); ?>
        </button>
      </div>
    </div>

    <!-- Live Candlestick Legend / OHLC Strip -->
    <div class="chart-ohlc-legend" id="chartOhlcLegend">
      <span class="ohlc-symbol" id="legendSymbol">BTC/INR</span>
      <span class="ohlc-item">O: <strong id="legendOpen">--</strong></span>
      <span class="ohlc-item">H: <strong id="legendHigh">--</strong></span>
      <span class="ohlc-item">L: <strong id="legendLow">--</strong></span>
      <span class="ohlc-item">C: <strong id="legendClose">--</strong></span>
      <span class="ohlc-item" id="legendChangeWrap"><strong id="legendChange" class="up">--</strong></span>
      <span class="ohlc-item ohlc-vol">Vol: <strong id="legendVol">--</strong></span>
    </div>

    <!-- Native High-DPI HTML5 Canvas Chart Container -->
    <div class="tradingview-chart-box" id="tradingview_widget_box">
      <div class="chart-loading-placeholder">
        <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
        <div>Loading High-Frequency Candlestick Chart Engine...</div>
      </div>
    </div>

  </div>

  <!-- 2. Responsive Mobile Mode Switcher (Visible on Mobile / Small screens) -->
  <div class="trading-mobile-switcher" id="mobileTradingSwitcher">
    <button type="button" class="mobile-switch-btn active" id="mobileSwitchConsole" onclick="switchMobileTradingTab('console')">
      <?php echo svg_icon('zap', '', 15); ?> Trade (Buy / Sell)
    </button>
    <button type="button" class="mobile-switch-btn" id="mobileSwitchOrderbook" onclick="switchMobileTradingTab('orderbook')">
      <?php echo svg_icon('orderbook', '', 15); ?> Order Book Depth
    </button>
  </div>

  <!-- 3. Workspace Grid: Buy/Sell Execution Console & Order Book Depth -->
  <div class="trading-workspace-grid">

    <!-- Buy / Sell Execution Console Card -->
    <div class="trading-console-card" id="consoleSection">
      <!-- Buy / Sell Mode Tabs -->
      <div class="console-tab-buttons">
        <button type="button" class="console-side-tab buy active" id="consoleTabBuy">
          <?php echo svg_icon('arrow-up', '', 14); ?> BUY (Long)
        </button>
        <button type="button" class="console-side-tab sell" id="consoleTabSell">
          <?php echo svg_icon('arrow-down', '', 14); ?> SELL (Short)
        </button>
      </div>

      <!-- Order Type Selection -->
      <div class="console-order-type-row">
        <button type="button" class="console-type-pill active">
          <?php echo svg_icon('check', 'text-secondary', 12); ?> Market Order
        </button>
        <button type="button" class="console-type-pill">Limit Order</button>
      </div>

      <!-- Price Input -->
      <div class="console-input-group">
        <div class="console-input-label">
          <span>Order Price</span>
          <span>Market Rate</span>
        </div>
        <div class="console-input-wrap">
          <input type="number" id="consoleOrderPrice" class="console-field" placeholder="Price" readonly value="64250">
          <span class="console-currency-suffix">INR</span>
        </div>
      </div>

      <!-- Amount Input -->
      <div class="console-input-group">
        <div class="console-input-label">
          <span>Investment Amount</span>
          <span>Avail: <strong id="consoleAvailBal">₹<?php echo number_format($userBalance, 2); ?></strong></span>
        </div>
        <div class="console-input-wrap">
          <input type="number" id="consoleOrderAmount" class="console-field" placeholder="Min ₹10" min="10">
          <span class="console-currency-suffix">INR</span>
        </div>
      </div>

      <!-- Quick Percentage Allocation Chips -->
      <div class="console-percent-grid">
        <button type="button" class="console-pct-btn" data-pct="25">25%</button>
        <button type="button" class="console-pct-btn" data-pct="50">50%</button>
        <button type="button" class="console-pct-btn" data-pct="75">75%</button>
        <button type="button" class="console-pct-btn" data-pct="100">100% (MAX)</button>
      </div>

      <!-- Summary & Zero Brokerage Note -->
      <div class="console-summary-row">
        <span>Est. Quantity: <strong id="orderApproxQty" style="color: var(--text-primary);">0.0000 BTC</strong></span>
        <span class="zero-brokerage-badge">
          <?php echo svg_icon('shield-check', 'text-success', 12); ?> 0% Brokerage
        </span>
      </div>

      <!-- Tactile iOS Capsule Execute Order CTA -->
      <button type="button" class="console-execute-btn buy" id="executeOrderBtn">
        <?php echo svg_icon('zap', '', 18); ?>
        <span>BUY BTC</span>
      </button>
    </div>

    <!-- Live Order Book Depth Card -->
    <div class="trading-orderbook-card" id="orderbookSection">
      <div class="orderbook-top-bar">
        <strong style="font-size: 0.88rem; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
          <?php echo svg_icon('orderbook', 'text-muted', 15); ?> Order Book Depth
        </strong>
        <span class="badge badge-sm badge-success" style="font-size: 0.68rem; display: flex; align-items: center; gap: 4px;">
          <span class="live-dot-pulse"></span> Live Feed
        </span>
      </div>

      <div class="orderbook-header">
        <span>Price (INR)</span>
        <span style="text-align: right;">Size</span>
        <span style="text-align: right;" class="orderbook-col-total">Total</span>
      </div>

      <!-- Asks (Sell Orders - Red) -->
      <div class="orderbook-table" id="orderbookAsks"></div>

      <!-- Spread Price Bar -->
      <div class="orderbook-spread-row">
        <span style="color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; display: flex; align-items: center; gap: 4px;">
          <?php echo svg_icon('trending', 'text-muted', 13); ?> Market Spread
        </span>
        <span id="orderbookSpreadPrice" style="color: var(--text-primary); font-size: 0.88rem;">₹64,250.00</span>
      </div>

      <!-- Bids (Buy Orders - Green) -->
      <div class="orderbook-table" id="orderbookBids"></div>
    </div>

  </div>

  <!-- 4. Bottom Dock: Open Positions & Order History Table -->
  <div class="trading-bottom-card">
    <div class="bottom-tabs-row">
      <div class="bottom-tab-item active" id="bottomTabPositions">
        <?php echo svg_icon('trending', '', 15); ?>
        <span>Open Positions</span>
        <span class="tab-count-badge" id="positionsCountBadge">0</span>
      </div>
      <div class="bottom-tab-item" id="bottomTabHistory">
        <?php echo svg_icon('history', '', 15); ?>
        <span>Trade History</span>
      </div>
    </div>

    <!-- Active Positions Pane -->
    <div id="positionsTabPane" class="positions-table-responsive">
      <table class="spot-positions-table">
        <thead>
          <tr>
            <th>Symbol / Side</th>
            <th>Size</th>
            <th>Entry Price</th>
            <th>Mark Price</th>
            <th>Margin (INR)</th>
            <th>Unrealized P&L</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="positionsTableBody">
          <tr>
            <td colspan="7" style="text-align: center; padding: 32px 14px; color: var(--text-muted);">
              Loading positions...
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Closed Trade History Pane -->
    <div id="historyTabPane" class="positions-table-responsive" style="display: none;">
      <table class="spot-positions-table">
        <thead>
          <tr>
            <th>Symbol / Side</th>
            <th>Size</th>
            <th>Entry Price</th>
            <th>Close Price</th>
            <th>Realized P&L</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody id="historyTableBody">
          <tr>
            <td colspan="6" style="text-align: center; padding: 24px 14px; color: var(--text-muted);">
              No closed trade history yet.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- TradingView Native Canvas Engine -->
<script src="js/lightweight-charts.js?v=<?php echo time(); ?>"></script>
<!-- Spot Terminal Controller -->
<script src="js/spot_terminal.js?v=<?php echo time(); ?>"></script>
