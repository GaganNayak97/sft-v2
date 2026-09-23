/**
 * SoftPay Spot Trading Terminal Controller - Native Lightweight Charts Canvas Engine
 * High-performance 60fps Candlestick Technical Analysis, Timeframe Steppers, Indicators,
 * Live Orderbook Depth, Spot Buy/Sell Execution, and Real-Time PnL Tracker
 */

(function () {
  'use strict';

  const terminal = {
    currentSymbol: 'BTC/INR',
    currentPrice: 64250.00,
    currentTf: '15m',
    userBalance: 0.00,
    currentSide: 'buy', // 'buy' | 'sell'
    orderType: 'market',
    isCandleMode: true,
    showMA: true,
    showEMA: true,
    showVol: true,
    positions: [],
    history: [],
    pairs: {
      'BTC/INR': { basePrice: 64250.00, decimals: 2, minAmt: 100, step: 0.0001, unit: 'BTC', volUnit: 'BTC', volBase: 1280.45 },
      'ETH/INR': { basePrice: 2640.00, decimals: 2, minAmt: 50, step: 0.001, unit: 'ETH', volUnit: 'ETH', volBase: 8420.10 },
      'GOLD/INR': { basePrice: 6850.00, decimals: 2, minAmt: 50, step: 0.01, unit: 'g', volUnit: 'kg', volBase: 45.8 },
      'SOL/INR': { basePrice: 148.00, decimals: 2, minAmt: 20, step: 0.01, unit: 'SOL', volUnit: 'SOL', volBase: 14290.0 }
    }
  };

  const assetSvgs = {
    'BTC/INR': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9 7h4a2.5 2.5 0 0 1 2 4 2.5 2.5 0 0 1-2 4H9m0-8v8m2-8V5m0 14v-2m-2-4h4.5"></path></svg>`,
    'ETH/INR': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00BAF2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 4.5 12.5 12 16.5 19.5 12.5 12 2"></polygon><polygon points="12 17.5 4.5 13.5 12 22 19.5 13.5 12 17.5"></polygon></svg>`,
    'GOLD/INR': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#eab308" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14l3-7h10l3 7H4z"></path><path d="M2 18h20v-4H2v4z"></path></svg>`,
    'SOL/INR': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5h13.5l2.5 2.5H6.5L4 6.5z"></path><path d="M20 11.5H6.5L4 14h13.5l2.5-2.5z"></path><path d="M4 16.5h13.5l2.5 2.5H6.5L4 16.5z"></path></svg>`
  };

  // Chart References
  let chart = null;
  let candleSeries = null;
  let lineSeries = null;
  let maSeries = null;
  let emaSeries = null;
  let volumeSeries = null;
  let currentCandle = null;
  let candleData = [];

  const TF_SECONDS = {
    '1m': 60,
    '5m': 300,
    '15m': 900,
    '1h': 3600,
    '4h': 14400,
    '1D': 86400
  };

  // -------------------------------------------------------------
  // NATIVE LIGHTWEIGHT CHARTS CANVAS ENGINE
  // -------------------------------------------------------------
  function initNativeChart() {
    const container = document.getElementById('tradingview_widget_box');
    if (!container || !window.LightweightCharts) return;

    container.innerHTML = '';
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark' || document.body.classList.contains('dark-theme');
    const pair = terminal.pairs[terminal.currentSymbol];
    const dec = pair ? pair.decimals : 2;

    chart = LightweightCharts.createChart(container, {
      width: container.clientWidth || 360,
      height: container.clientHeight || 420,
      layout: {
        background: { color: isDark ? '#1c1c20' : '#ffffff' },
        textColor: isDark ? '#a1a1aa' : '#52525b',
        fontSize: 11,
        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
      },
      grid: {
        vertLines: { color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)' },
        horzLines: { color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)' },
      },
      crosshair: {
        mode: LightweightCharts.CrosshairMode.Normal,
      },
      rightPriceScale: {
        borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.08)',
        scaleMargins: {
          top: 0.1,
          bottom: 0.22,
        },
      },
      timeScale: {
        borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.08)',
        timeVisible: true,
        secondsVisible: false,
      },
      handleScroll: {
        mouseWheel: true,
        pressedMouseMove: true,
        horzTouchDrag: true,
        vertTouchDrag: false,
      },
      handleScale: {
        axisPressedMouseMove: true,
        mouseWheel: true,
        pinch: true,
      }
    });

    // 1. Candlestick Series
    candleSeries = chart.addSeries(LightweightCharts.CandlestickSeries, {
      upColor: '#00b074',
      downColor: '#ef4444',
      borderVisible: false,
      wickUpColor: '#00b074',
      wickDownColor: '#ef4444',
      priceFormat: {
        type: 'price',
        precision: dec,
        minMove: 1 / Math.pow(10, dec),
      },
    });

    // 2. Line / Area Series (for style toggle)
    lineSeries = chart.addSeries(LightweightCharts.AreaSeries, {
      topColor: 'rgba(0, 186, 242, 0.35)',
      bottomColor: 'rgba(0, 186, 242, 0.02)',
      lineColor: '#00BAF2',
      lineWidth: 2,
      visible: false,
      priceFormat: {
        type: 'price',
        precision: dec,
        minMove: 1 / Math.pow(10, dec),
      },
    });

    // 3. Moving Average Series (MA 7)
    maSeries = chart.addSeries(LightweightCharts.LineSeries, {
      color: '#f59e0b', // amber
      lineWidth: 2,
      title: 'MA 7',
      lastValueVisible: false,
      priceLineVisible: false,
    });

    // 4. Exponential Moving Average Series (EMA 25)
    emaSeries = chart.addSeries(LightweightCharts.LineSeries, {
      color: '#00BAF2', // cyan
      lineWidth: 2,
      title: 'EMA 25',
      lastValueVisible: false,
      priceLineVisible: false,
    });

    // 5. Volume Series
    volumeSeries = chart.addSeries(LightweightCharts.HistogramSeries, {
      priceFormat: {
        type: 'volume',
      },
      priceScaleId: '', // overlay
    });
    volumeSeries.priceScale().applyOptions({
      scaleMargins: {
        top: 0.8,
        bottom: 0,
      },
    });

    // Load initial data
    loadChartData(terminal.currentTf);

    // Crosshair inspection listener
    chart.subscribeCrosshairMove(param => {
      if (!param || !param.time || !param.seriesData) {
        updateLegend(currentCandle);
        return;
      }
      const candle = param.seriesData.get(candleSeries);
      if (candle) {
        updateLegend(candle);
      }
    });

    // Auto-resize on window change & container resize (instant full-width fill)
    if (window.ResizeObserver && container) {
      const ro = new ResizeObserver(() => {
        if (chart && container && container.clientWidth > 0) {
          chart.applyOptions({
            width: container.clientWidth,
            height: container.clientHeight,
          });
        }
      });
      ro.observe(container);
    }

    window.addEventListener('resize', () => {
      if (chart && container) {
        chart.applyOptions({
          width: container.clientWidth,
          height: container.clientHeight,
        });
      }
    });
  }

  // Generate realistic OHLC historical candles without spikes
  function generateHistoricalCandles(symbol, tf) {
    const pair = terminal.pairs[symbol];
    const dec = pair.decimals;
    const step = TF_SECONDS[tf] || 900;
    const count = 80;
    const now = Math.floor(Date.now() / 1000);

    const volatility = terminal.currentPrice * (tf === '1m' ? 0.0006 : tf === '5m' ? 0.0011 : tf === '15m' ? 0.0018 : 0.0032);

    // Pre-generate deltas
    const deltas = [];
    let sumDelta = 0;
    for (let i = 0; i < count; i++) {
      const d = (Math.random() - 0.495) * volatility;
      deltas.push(d);
      sumDelta += d;
    }

    // Anchor starting price so that the final candle's close exactly matches terminal.currentPrice
    let price = +(terminal.currentPrice - sumDelta).toFixed(dec);

    const candles = [];
    const maData = [];
    const emaData = [];
    const volData = [];

    const k = 2 / (25 + 1);
    let ema = price;

    for (let i = 0; i < count; i++) {
      const time = now - ((count - 1 - i) * step);
      const open = price;
      const close = (i === count - 1) ? terminal.currentPrice : +(open + deltas[i]).toFixed(dec);
      const wickSpread = Math.random() * (volatility * 0.55);
      const high = +(Math.max(open, close) + wickSpread).toFixed(dec);
      const low = +(Math.min(open, close) - wickSpread).toFixed(dec);
      const vol = +(Math.random() * 8.5 + 1.2).toFixed(2);
      const isUp = close >= open;

      candles.push({ time, open, high, low, close });
      volData.push({
        time,
        value: vol,
        color: isUp ? 'rgba(0, 176, 116, 0.4)' : 'rgba(239, 68, 68, 0.4)'
      });

      // Calculate MA 7
      if (i >= 6) {
        const slice = candles.slice(i - 6, i + 1);
        const sum = slice.reduce((acc, c) => acc + c.close, 0);
        maData.push({ time, value: +(sum / 7).toFixed(dec) });
      }

      // Calculate EMA 25
      ema = +(close * k + ema * (1 - k)).toFixed(dec);
      if (i >= 12) {
        emaData.push({ time, value: ema });
      }

      price = close;
    }

    return { candles, maData, emaData, volData };
  }

  function loadChartData(tf) {
    if (!chart || !candleSeries) return;

    terminal.currentTf = tf;
    const { candles, maData, emaData, volData } = generateHistoricalCandles(terminal.currentSymbol, tf);
    candleData = candles;
    currentCandle = candles[candles.length - 1];

    candleSeries.setData(candles);
    lineSeries.setData(candles.map(c => ({ time: c.time, value: c.close })));
    maSeries.setData(maData);
    emaSeries.setData(emaData);
    volumeSeries.setData(volData);

    chart.timeScale().fitContent();
    updateLegend(currentCandle);
  }

  function updateLegend(c) {
    if (!c) return;
    const symEl = document.getElementById('legendSymbol');
    const oEl = document.getElementById('legendOpen');
    const hEl = document.getElementById('legendHigh');
    const lEl = document.getElementById('legendLow');
    const cEl = document.getElementById('legendClose');
    const chgEl = document.getElementById('legendChange');
    const volEl = document.getElementById('legendVol');

    const dec = terminal.pairs[terminal.currentSymbol].decimals;
    if (symEl) symEl.textContent = terminal.currentSymbol;
    if (oEl) oEl.textContent = '₹' + Number(c.open || c.value).toLocaleString('en-IN', { minimumFractionDigits: dec });
    if (hEl) hEl.textContent = '₹' + Number(c.high || c.value).toLocaleString('en-IN', { minimumFractionDigits: dec });
    if (lEl) lEl.textContent = '₹' + Number(c.low || c.value).toLocaleString('en-IN', { minimumFractionDigits: dec });
    if (cEl) cEl.textContent = '₹' + Number(c.close || c.value).toLocaleString('en-IN', { minimumFractionDigits: dec });

    if (chgEl && c.open && c.close) {
      const diff = c.close - c.open;
      const pct = (diff / c.open) * 100;
      const isUp = diff >= 0;
      chgEl.textContent = `${isUp ? '+' : ''}${pct.toFixed(2)}%`;
      chgEl.className = isUp ? 'up' : 'down';
    }

    if (volEl) {
      const v = (Math.random() * 4.5 + 2.1).toFixed(2);
      volEl.textContent = `${v} ${terminal.pairs[terminal.currentSymbol].volUnit}`;
    }
  }

  function zoomChart(factor) {
    if (!chart) return;
    const timeScale = chart.timeScale();
    const range = timeScale.getVisibleLogicalRange();
    if (!range) return;
    const barsCount = range.to - range.from;
    const newBarsCount = barsCount * factor;
    const delta = (barsCount - newBarsCount) / 2;
    timeScale.setVisibleLogicalRange({
      from: range.from + delta,
      to: range.to - delta,
    });
  }

  // -------------------------------------------------------------
  // ORDER BOOK SIMULATOR (Binance & Kite depth)
  // -------------------------------------------------------------
  function updateOrderBook() {
    const asksContainer = document.getElementById('orderbookAsks');
    const bidsContainer = document.getElementById('orderbookBids');
    const spreadPriceEl = document.getElementById('orderbookSpreadPrice');

    if (!asksContainer || !bidsContainer) return;

    const base = terminal.currentPrice;
    const dec = terminal.pairs[terminal.currentSymbol].decimals;

    if (spreadPriceEl) {
      spreadPriceEl.textContent = '₹' + base.toLocaleString('en-IN', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }

    // Generate 5 Asks (Sell, Red, higher prices)
    let asksHtml = '';
    for (let i = 5; i >= 1; i--) {
      const askPrice = (base + (i * (base * 0.0004))).toFixed(dec);
      const askQty = (Math.random() * 1.8 + 0.15).toFixed(4);
      const total = (askPrice * askQty).toFixed(2);
      const depthPct = Math.min(100, Math.round((i / 5) * 85));

      asksHtml += `
        <div class="orderbook-row ask" onclick="fillPrice(${askPrice})">
          <div class="orderbook-depth-bar ask" style="width: ${depthPct}%;"></div>
          <span>₹${Number(askPrice).toLocaleString('en-IN')}</span>
          <span style="text-align: right; color: var(--text-secondary);">${askQty}</span>
          <span style="text-align: right; color: var(--text-muted);" class="orderbook-col-total">₹${Number(total).toLocaleString('en-IN')}</span>
        </div>
      `;
    }
    asksContainer.innerHTML = asksHtml;

    // Generate 5 Bids (Buy, Green, lower prices)
    let bidsHtml = '';
    for (let i = 1; i <= 5; i++) {
      const bidPrice = (base - (i * (base * 0.0004))).toFixed(dec);
      const bidQty = (Math.random() * 1.8 + 0.15).toFixed(4);
      const total = (bidPrice * bidQty).toFixed(2);
      const depthPct = Math.min(100, Math.round(((6 - i) / 5) * 85));

      bidsHtml += `
        <div class="orderbook-row bid" onclick="fillPrice(${bidPrice})">
          <div class="orderbook-depth-bar bid" style="width: ${depthPct}%;"></div>
          <span>₹${Number(bidPrice).toLocaleString('en-IN')}</span>
          <span style="text-align: right; color: var(--text-secondary);">${bidQty}</span>
          <span style="text-align: right; color: var(--text-muted);" class="orderbook-col-total">₹${Number(total).toLocaleString('en-IN')}</span>
        </div>
      `;
    }
    bidsContainer.innerHTML = bidsHtml;
  }

  window.fillPrice = function (p) {
    const priceInput = document.getElementById('consoleOrderPrice');
    if (priceInput) {
      priceInput.value = p;
      recalculateOrderSummary();
    }
    if (window.innerWidth < 1024) {
      window.switchMobileTradingTab('console');
    }
  };

  window.switchMobileTradingTab = function (tab) {
    const consoleSec = document.getElementById('consoleSection');
    const obSec = document.getElementById('orderbookSection');
    const btnConsole = document.getElementById('mobileSwitchConsole');
    const btnOb = document.getElementById('mobileSwitchOrderbook');

    if (tab === 'console') {
      if (consoleSec) consoleSec.style.display = 'block';
      if (obSec) obSec.style.display = 'none';
      if (btnConsole) btnConsole.classList.add('active');
      if (btnOb) btnOb.classList.remove('active');
    } else {
      if (consoleSec) consoleSec.style.display = 'none';
      if (obSec) obSec.style.display = 'block';
      if (btnConsole) btnConsole.classList.remove('active');
      if (btnOb) btnOb.classList.add('active');
    }
  };

  window.addEventListener('resize', function () {
    const consoleSec = document.getElementById('consoleSection');
    const obSec = document.getElementById('orderbookSection');
    if (window.innerWidth >= 1024) {
      if (consoleSec) consoleSec.style.display = '';
      if (obSec) obSec.style.display = '';
    } else {
      const btnOb = document.getElementById('mobileSwitchOrderbook');
      if (btnOb && btnOb.classList.contains('active')) {
        if (consoleSec) consoleSec.style.display = 'none';
        if (obSec) obSec.style.display = 'block';
      } else {
        if (consoleSec) consoleSec.style.display = 'block';
        if (obSec) obSec.style.display = 'none';
      }
    }
  });

  // -------------------------------------------------------------
  // DATA SYNC & POSITIONS ENGINE
  // -------------------------------------------------------------
  async function fetchTerminalState() {
    try {
      const res = await fetch('index.php?api=trading&action=get_terminal_state');
      const data = await res.json();
      if (data.success) {
        terminal.userBalance = data.balances.real || 0.00;
        terminal.positions = data.positions || [];
        terminal.history = data.history || [];

        updateBalanceUI();
        renderPositionsTable();
        renderHistoryTable();
      }
    } catch (e) {
      console.error('Failed to load spot terminal state', e);
    }
  }

  function updateBalanceUI() {
    const balEl = document.getElementById('userAvailableTradingBal');
    const consoleBalEl = document.getElementById('consoleAvailBal');
    const formatted = '₹' + terminal.userBalance.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    if (balEl) balEl.textContent = formatted;
    if (consoleBalEl) consoleBalEl.textContent = formatted;
  }

  function renderPositionsTable() {
    const tbody = document.getElementById('positionsTableBody');
    const countBadge = document.getElementById('positionsCountBadge');
    if (!tbody) return;

    if (countBadge) {
      countBadge.textContent = terminal.positions.length;
    }

    if (terminal.positions.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align: center; padding: 48px 14px; color: var(--text-muted);">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;">
              <div style="width: 52px; height: 52px; border-radius: 16px; background: var(--bg-main); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                  <polyline points="2 17 12 22 22 17"></polyline>
                  <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
              </div>
              <strong style="font-size: 0.95rem; color: var(--text-primary);">No Open Spot Positions</strong>
              <span style="font-size: 0.8rem; color: var(--text-muted); max-width: 300px;">Place a Market or Limit trade above to enter a live position.</span>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = terminal.positions.map(pos => {
      const isBuy = pos.side === 'buy';
      const curPrice = terminal.currentPrice;
      const entryPrice = Number(pos.entry_price);
      const qty = Number(pos.quantity);

      // Real-time Unrealized PnL
      const pnlAmt = isBuy ? (curPrice - entryPrice) * qty : (entryPrice - curPrice) * qty;
      const pnlPct = entryPrice > 0 ? (pnlAmt / Number(pos.amount_inr)) * 100 : 0;
      const isProfit = pnlAmt >= 0;

      const sideIcon = isBuy
        ? `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display:inline-block; vertical-align:middle; margin-right:3px;"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>`
        : `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display:inline-block; vertical-align:middle; margin-right:3px;"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>`;

      const closeIcon = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block; vertical-align:middle; margin-right:4px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`;

      return `
        <tr>
          <td>
            <div style="font-weight: 800; color: var(--text-primary);">${pos.symbol}</div>
            <span class="position-side-tag ${pos.side}">${sideIcon}${pos.side.toUpperCase()}</span>
          </td>
          <td>${qty.toFixed(4)}</td>
          <td>₹${entryPrice.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
          <td>₹${curPrice.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
          <td>₹${Number(pos.amount_inr).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
          <td>
            <div class="pnl-ticker ${isProfit ? 'profit' : 'loss'}">
              ${isProfit ? '+' : ''}₹${pnlAmt.toFixed(2)} (${isProfit ? '+' : ''}${pnlPct.toFixed(2)}%)
            </div>
          </td>
          <td>
            <button type="button" class="close-pos-btn" onclick="closePosition(${pos.id})">
              ${closeIcon}Liquidate
            </button>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderHistoryTable() {
    const tbody = document.getElementById('historyTableBody');
    if (!tbody) return;

    if (terminal.history.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" style="text-align: center; padding: 48px 14px; color: var(--text-muted);">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;">
              <div style="width: 52px; height: 52px; border-radius: 16px; background: var(--bg-main); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
              <strong style="font-size: 0.95rem; color: var(--text-primary);">No Closed Trade History</strong>
              <span style="font-size: 0.8rem; color: var(--text-muted); max-width: 300px;">Executed spot trades and realized profits will appear here.</span>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = terminal.history.map(pos => {
      const isBuy = pos.side === 'buy';
      const pnl = Number(pos.pnl_amount);
      const isProfit = pnl >= 0;

      const sideIcon = isBuy
        ? `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display:inline-block; vertical-align:middle; margin-right:3px;"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>`
        : `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display:inline-block; vertical-align:middle; margin-right:3px;"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>`;

      return `
        <tr>
          <td>
            <strong>${pos.symbol}</strong>
            <span class="position-side-tag ${pos.side}">${sideIcon}${pos.side.toUpperCase()}</span>
          </td>
          <td>${Number(pos.quantity).toFixed(4)}</td>
          <td>₹${Number(pos.entry_price).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
          <td>₹${Number(pos.current_price).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
          <td class="pnl-ticker ${isProfit ? 'profit' : 'loss'}">
            ${isProfit ? '+' : ''}₹${pnl.toFixed(2)}
          </td>
          <td style="color: var(--text-muted); font-size: 0.8rem;">
            ${pos.closed_at || pos.created_at}
          </td>
        </tr>
      `;
    }).join('');
  }

  // -------------------------------------------------------------
  // ORDER EXECUTION CONSOLE
  // -------------------------------------------------------------
  function recalculateOrderSummary() {
    const amtInput = document.getElementById('consoleOrderAmount');
    const priceInput = document.getElementById('consoleOrderPrice');
    const approxQtyEl = document.getElementById('orderApproxQty');
    const unit = terminal.pairs[terminal.currentSymbol].unit;

    const amount = parseFloat(amtInput?.value) || 0;
    const price = parseFloat(priceInput?.value) || terminal.currentPrice;

    if (price > 0 && amount > 0) {
      const qty = (amount / price).toFixed(4);
      if (approxQtyEl) approxQtyEl.textContent = `${qty} ${unit}`;
    } else {
      if (approxQtyEl) approxQtyEl.textContent = `0.0000 ${unit}`;
    }
  }

  async function placeSpotOrder() {
    const amtInput = document.getElementById('consoleOrderAmount');
    const priceInput = document.getElementById('consoleOrderPrice');
    const executeBtn = document.getElementById('executeOrderBtn');

    const amount = parseFloat(amtInput?.value) || 0;
    const price = parseFloat(priceInput?.value) || terminal.currentPrice;

    if (amount <= 0) {
      showToast('Please enter an investment amount greater than ₹0.');
      return;
    }

    if (amount > terminal.userBalance) {
      showToast('Insufficient trading balance. Please deposit funds first.');
      return;
    }

    if (executeBtn) {
      executeBtn.disabled = true;
      executeBtn.textContent = 'Executing...';
    }

    try {
      const formData = new FormData();
      formData.append('action', 'spot_order');
      formData.append('symbol', terminal.currentSymbol);
      formData.append('side', terminal.currentSide);
      formData.append('order_type', terminal.orderType);
      formData.append('amount_inr', amount);
      formData.append('price', price);

      const res = await fetch('index.php?api=trading', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || 'Spot order executed successfully!');
        if (amtInput) amtInput.value = '';
        recalculateOrderSummary();
        await fetchTerminalState();
      } else {
        showToast(data.message || 'Failed to place order.');
      }
    } catch (e) {
      showToast('Network error while placing order.');
    } finally {
      if (executeBtn) {
        executeBtn.disabled = false;
        executeBtn.textContent = (terminal.currentSide === 'buy' ? 'BUY ' : 'SELL ') + terminal.pairs[terminal.currentSymbol].unit;
      }
    }
  }

  window.closePosition = async function (posId) {
    if (!confirm('Are you sure you want to close this position at live market price?')) return;

    try {
      const formData = new FormData();
      formData.append('action', 'close_position');
      formData.append('position_id', posId);
      formData.append('exit_price', terminal.currentPrice);

      const res = await fetch('index.php?api=trading', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || 'Position closed successfully!');
        await fetchTerminalState();
      } else {
        showToast(data.message || 'Failed to close position.');
      }
    } catch (e) {
      showToast('Network error while closing position.');
    }
  };

  function showToast(msg) {
    const toast = document.createElement('div');
    toast.style.cssText = `
      position: fixed;
      bottom: 84px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(15, 23, 42, 0.94);
      color: #fff;
      padding: 12px 22px;
      border-radius: 9999px;
      font-size: 0.88rem;
      font-weight: 700;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
      z-index: 10000;
      pointer-events: none;
      backdrop-filter: blur(8px);
      text-align: center;
      max-width: 90%;
    `;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }

  // -------------------------------------------------------------
  // EVENT BINDINGS & LIFECYCLE
  // -------------------------------------------------------------
  function bindEvents() {
    // Pair Selector Dropdown
    const pairSelect = document.getElementById('pairSelector');
    const zapSvg = `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-right:6px;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>`;

    if (pairSelect) {
      pairSelect.addEventListener('change', (e) => {
        const sym = e.target.value;
        if (terminal.pairs[sym]) {
          terminal.currentSymbol = sym;
          terminal.currentPrice = terminal.pairs[sym].basePrice;

          const iconBadge = document.getElementById('pairAssetIcon');
          if (iconBadge && assetSvgs[sym]) {
            iconBadge.innerHTML = assetSvgs[sym];
          }

          loadChartData(terminal.currentTf);
          updateOrderBook();
          recalculateOrderSummary();
          updateTickerHeader();
          updateTickerStats();

          const executeBtn = document.getElementById('executeOrderBtn');
          if (executeBtn) {
            executeBtn.innerHTML = zapSvg + `<span>${terminal.currentSide === 'buy' ? 'BUY ' : 'SELL '}${terminal.pairs[sym].unit}</span>`;
          }
        }
      });
    }

    // Timeframe Buttons
    document.querySelectorAll('.tf-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const tf = btn.dataset.tf;
        if (!tf) return;
        document.querySelectorAll('.tf-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadChartData(tf);
      });
    });

    // Style Toggles: Candle vs Line
    const btnCandle = document.getElementById('btnToggleCandle');
    const btnLine = document.getElementById('btnToggleLine');
    if (btnCandle && btnLine) {
      btnCandle.addEventListener('click', () => {
        terminal.isCandleMode = true;
        btnCandle.classList.add('active');
        btnLine.classList.remove('active');
        candleSeries.applyOptions({ visible: true });
        lineSeries.applyOptions({ visible: false });
      });

      btnLine.addEventListener('click', () => {
        terminal.isCandleMode = false;
        btnLine.classList.add('active');
        btnCandle.classList.remove('active');
        candleSeries.applyOptions({ visible: false });
        lineSeries.applyOptions({ visible: true });
      });
    }

    // Indicator Toggles: MA, EMA, VOL
    const btnMA = document.getElementById('btnToggleMA');
    if (btnMA) {
      btnMA.addEventListener('click', () => {
        terminal.showMA = !terminal.showMA;
        btnMA.classList.toggle('active', terminal.showMA);
        maSeries.applyOptions({ visible: terminal.showMA });
      });
    }

    const btnEMA = document.getElementById('btnToggleEMA');
    if (btnEMA) {
      btnEMA.addEventListener('click', () => {
        terminal.showEMA = !terminal.showEMA;
        btnEMA.classList.toggle('active', terminal.showEMA);
        emaSeries.applyOptions({ visible: terminal.showEMA });
      });
    }

    const btnVol = document.getElementById('btnToggleVol');
    if (btnVol) {
      btnVol.addEventListener('click', () => {
        terminal.showVol = !terminal.showVol;
        btnVol.classList.toggle('active', terminal.showVol);
        volumeSeries.applyOptions({ visible: terminal.showVol });
      });
    }

    // Zoom & Reset Buttons
    const btnZoomIn = document.getElementById('btnZoomIn');
    const btnZoomOut = document.getElementById('btnZoomOut');
    const btnResetView = document.getElementById('btnResetView');

    if (btnZoomIn) {
      btnZoomIn.addEventListener('click', () => zoomChart(0.72));
    }
    if (btnZoomOut) {
      btnZoomOut.addEventListener('click', () => zoomChart(1.38));
    }
    if (btnResetView) {
      btnResetView.addEventListener('click', () => {
        if (chart) chart.timeScale().fitContent();
      });
    }

    // Buy / Sell Tabs
    const buyTab = document.getElementById('consoleTabBuy');
    const sellTab = document.getElementById('consoleTabSell');
    const executeBtn = document.getElementById('executeOrderBtn');

    if (buyTab && sellTab) {
      buyTab.addEventListener('click', () => {
        terminal.currentSide = 'buy';
        buyTab.classList.add('active');
        sellTab.classList.remove('active');
        if (executeBtn) {
          executeBtn.className = 'console-execute-btn buy';
          executeBtn.innerHTML = zapSvg + `<span>BUY ${terminal.pairs[terminal.currentSymbol].unit}</span>`;
        }
      });

      sellTab.addEventListener('click', () => {
        terminal.currentSide = 'sell';
        sellTab.classList.add('active');
        buyTab.classList.remove('active');
        if (executeBtn) {
          executeBtn.className = 'console-execute-btn sell';
          executeBtn.innerHTML = zapSvg + `<span>SELL ${terminal.pairs[terminal.currentSymbol].unit}</span>`;
        }
      });
    }

    // Percentage Buttons
    document.querySelectorAll('.console-pct-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const pct = parseInt(btn.dataset.pct, 10) || 0;
        const amount = Math.floor(terminal.userBalance * (pct / 100));
        const amountInput = document.getElementById('consoleOrderAmount');
        if (amountInput) {
          amountInput.value = amount;
          recalculateOrderSummary();
        }
      });
    });

    // Amount Input Listener
    const amtInput = document.getElementById('consoleOrderAmount');
    if (amtInput) {
      amtInput.addEventListener('input', recalculateOrderSummary);
    }

    // Execute Button
    if (executeBtn) {
      executeBtn.addEventListener('click', placeSpotOrder);
    }

    // Bottom Navigation Tabs
    const tabPos = document.getElementById('bottomTabPositions');
    const tabHist = document.getElementById('bottomTabHistory');
    const panePos = document.getElementById('positionsTabPane');
    const paneHist = document.getElementById('historyTabPane');

    if (tabPos && tabHist && panePos && paneHist) {
      tabPos.addEventListener('click', () => {
        tabPos.classList.add('active');
        tabHist.classList.remove('active');
        panePos.style.display = 'block';
        paneHist.style.display = 'none';
      });

      tabHist.addEventListener('click', () => {
        tabHist.classList.add('active');
        tabPos.classList.remove('active');
        paneHist.style.display = 'block';
        panePos.style.display = 'none';
      });
    }
  }

  function updateTickerHeader() {
    const priceEl = document.getElementById('tickerHeaderPrice');
    const dec = terminal.pairs[terminal.currentSymbol].decimals;
    if (priceEl) {
      priceEl.textContent = '₹' + terminal.currentPrice.toLocaleString('en-IN', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }
  }

  function updateTickerStats() {
    const pair = terminal.pairs[terminal.currentSymbol];
    if (!pair) return;
    const base = pair.basePrice;
    const high = (base * 1.015).toLocaleString('en-IN', { minimumFractionDigits: pair.decimals, maximumFractionDigits: pair.decimals });
    const low = (base * 0.978).toLocaleString('en-IN', { minimumFractionDigits: pair.decimals, maximumFractionDigits: pair.decimals });

    const hEl = document.getElementById('stat24High');
    const lEl = document.getElementById('stat24Low');
    const vEl = document.getElementById('stat24Vol');

    if (hEl) hEl.textContent = '₹' + high;
    if (lEl) lEl.textContent = '₹' + low;
    if (vEl) {
      if (terminal.currentSymbol === 'BTC/INR') vEl.textContent = '1,280.45 BTC';
      else if (terminal.currentSymbol === 'ETH/INR') vEl.textContent = '8,420.10 ETH';
      else if (terminal.currentSymbol === 'GOLD/INR') vEl.textContent = '45.8 kg';
      else vEl.textContent = '14,290 SOL';
    }
  }

  // Micro-tick price engine (for candlesticks, orderbook, and live positions ticker)
  function startTickStream() {
    setInterval(() => {
      const delta = (Math.random() - 0.49) * (terminal.currentPrice * 0.00035);
      terminal.currentPrice = +(terminal.currentPrice + delta).toFixed(terminal.pairs[terminal.currentSymbol].decimals);

      // Animate current active candle live
      if (currentCandle && candleSeries) {
        currentCandle.close = terminal.currentPrice;
        currentCandle.high = Math.max(currentCandle.high, terminal.currentPrice);
        currentCandle.low = Math.min(currentCandle.low, terminal.currentPrice);
        candleSeries.update(currentCandle);
        if (lineSeries) lineSeries.update({ time: currentCandle.time, value: currentCandle.close });
        updateLegend(currentCandle);
      }

      updateTickerHeader();
      updateOrderBook();
      renderPositionsTable(); // re-evaluates live PnL %
    }, 1200);
  }

  function init() {
    bindEvents();
    initNativeChart();
    updateOrderBook();
    updateTickerHeader();
    updateTickerStats();
    fetchTerminalState();
    startTickStream();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
