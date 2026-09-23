/**
 * SoftPay Binary Trading Terminal - High-Frequency Canvas & Settlement Engine
 * Real-time Candlesticks, Tick Physics, Dual-Wallet, and On-Chart Execution Markers
 */

(function () {
  'use strict';

  // State Configuration
  const state = {
    accountType: 'demo', // 'demo' | 'real'
    balances: {
      demo: 10000.00,
      real: 0.00,
      interest: 0.00
    },
    asset: 'gold', // 'gold' | 'btc' | 'eth' | 'eurusd'
    assetsConfig: {
      gold: { name: 'Gold (OTC)', symbol: 'XAU/USD', payout: 93, basePrice: 4052.00, decimals: 2, vol: 0.85 },
      btc: { name: 'Bitcoin', symbol: 'BTC/USD', payout: 95, basePrice: 64280.00, decimals: 2, vol: 15.0 },
      eth: { name: 'Ethereum', symbol: 'ETH/USD', payout: 92, basePrice: 2640.00, decimals: 2, vol: 2.2 },
      eurusd: { name: 'EUR / USD', symbol: 'EUR/USD', payout: 91, basePrice: 1.08450, decimals: 5, vol: 0.00015 }
    },
    timeframeSec: 60, // 1m candles
    durationSec: 60,  // 1 min trade expiry
    amount: 100,      // default investment
    soundEnabled: true,
    currentPrice: 4052.00,
    candleTimeLeft: 60,
    candles: [],
    activeTrades: [],
    closedTrades: [],
    sentimentBullPct: 52
  };

  // DOM Elements cache
  let canvas, ctx;
  let animFrameId = null;
  let tickIntervalId = null;
  let timerIntervalId = null;

  // Web Audio Context for tactile feedback
  let audioCtx = null;
  function getAudioContext() {
    if (!audioCtx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (AudioCtx) audioCtx = new AudioCtx();
    }
    if (audioCtx && audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
    return audioCtx;
  }

  function playSound(type) {
    if (!state.soundEnabled) return;
    try {
      const actx = getAudioContext();
      if (!actx) return;

      const now = actx.currentTime;
      const osc = actx.createOscillator();
      const gain = actx.createGain();
      osc.connect(gain);
      gain.connect(actx.destination);

      if (type === 'click') {
        osc.frequency.setValueAtTime(480, now);
        osc.frequency.exponentialRampToValueAtTime(320, now + 0.05);
        gain.gain.setValueAtTime(0.12, now);
        gain.gain.linearRampToValueAtTime(0.01, now + 0.05);
        osc.start(now);
        osc.stop(now + 0.05);
      } else if (type === 'place') {
        osc.frequency.setValueAtTime(350, now);
        osc.frequency.exponentialRampToValueAtTime(700, now + 0.12);
        gain.gain.setValueAtTime(0.18, now);
        gain.gain.linearRampToValueAtTime(0.01, now + 0.12);
        osc.start(now);
        osc.stop(now + 0.12);
      } else if (type === 'win') {
        // High triumphant arpeggio
        [523.25, 659.25, 783.99, 1046.50].forEach((freq, i) => {
          const o = actx.createOscillator();
          const g = actx.createGain();
          o.connect(g);
          g.connect(actx.destination);
          o.frequency.value = freq;
          const t = now + (i * 0.08);
          g.gain.setValueAtTime(0.2, t);
          g.gain.exponentialRampToValueAtTime(0.01, t + 0.3);
          o.start(t);
          o.stop(t + 0.35);
        });
      } else if (type === 'lost') {
        osc.frequency.setValueAtTime(320, now);
        osc.frequency.exponentialRampToValueAtTime(180, now + 0.25);
        gain.gain.setValueAtTime(0.18, now);
        gain.gain.linearRampToValueAtTime(0.01, now + 0.25);
        osc.start(now);
        osc.stop(now + 0.25);
      }
    } catch (e) {
      // Audio not supported or blocked
    }
  }

  // -------------------------------------------------------------
  // CANDLESTICK DATA GENERATION & TICK ENGINE
  // -------------------------------------------------------------
  function initCandles(assetKey) {
    const config = state.assetsConfig[assetKey];
    state.currentPrice = config.basePrice;
    state.candles = [];

    const totalCandles = 42;
    const now = Math.floor(Date.now() / 1000);
    let lastClose = config.basePrice;

    for (let i = totalCandles; i >= 1; i--) {
      const time = (now - (i * state.timeframeSec));
      const delta = (Math.random() - 0.49) * config.vol * 2.2;
      const open = lastClose;
      const close = +(open + delta).toFixed(config.decimals);
      const high = +(Math.max(open, close) + Math.random() * config.vol).toFixed(config.decimals);
      const low = +(Math.min(open, close) - Math.random() * config.vol).toFixed(config.decimals);

      state.candles.push({ time, open, high, low, close });
      lastClose = close;
    }

    // Current open candle
    const openTime = Math.floor(now / state.timeframeSec) * state.timeframeSec;
    state.candles.push({
      time: openTime,
      open: lastClose,
      high: lastClose,
      low: lastClose,
      close: lastClose
    });

    state.currentPrice = lastClose;
    state.candleTimeLeft = state.timeframeSec - (now % state.timeframeSec);
  }

  function simulateTick() {
    const config = state.assetsConfig[state.asset];
    const currentCandle = state.candles[state.candles.length - 1];
    if (!currentCandle) return;

    // Small momentum biased micro-tick
    const bias = (Math.random() - 0.485);
    const tick = bias * (config.vol * 0.45);
    const newPrice = +(state.currentPrice + tick).toFixed(config.decimals);

    state.currentPrice = newPrice;
    currentCandle.close = newPrice;
    if (newPrice > currentCandle.high) currentCandle.high = newPrice;
    if (newPrice < currentCandle.low) currentCandle.low = newPrice;

    // Micro adjust sentiment
    if (Math.random() < 0.25) {
      const shift = (Math.random() > 0.5 ? 1 : -1);
      state.sentimentBullPct = Math.max(30, Math.min(78, state.sentimentBullPct + shift));
      updateSentimentUI();
    }
  }

  function advanceTimeEngine() {
    const now = Math.floor(Date.now() / 1000);
    state.candleTimeLeft = state.timeframeSec - (now % state.timeframeSec);

    // If candle expires, push new candle
    if (state.candleTimeLeft <= 1) {
      const config = state.assetsConfig[state.asset];
      const openPrice = state.currentPrice;
      const nextTime = Math.floor(now / state.timeframeSec) * state.timeframeSec;

      state.candles.push({
        time: nextTime,
        open: openPrice,
        high: openPrice,
        low: openPrice,
        close: openPrice
      });

      // Keep candle array memory bounded
      if (state.candles.length > 55) {
        state.candles.shift();
      }
    }

    // Check active trades expiration
    checkActiveTradesSettlement();
  }

  // -------------------------------------------------------------
  // CANVAS RENDERING ENGINE (60 FPS)
  // -------------------------------------------------------------
  function setupCanvas() {
    canvas = document.getElementById('tradingCanvas');
    if (!canvas) return;
    ctx = canvas.getContext('2d');
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
  }

  function resizeCanvas() {
    if (!canvas || !canvas.parentElement) return;
    const rect = canvas.parentElement.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
  }

  function renderChart() {
    if (!ctx || !canvas) return;
    const width = canvas.width / (window.devicePixelRatio || 1);
    const height = canvas.height / (window.devicePixelRatio || 1);

    ctx.clearRect(0, 0, width, height);

    if (state.candles.length === 0) return;

    // 1. Calculate price bounds with safe padding
    let minPrice = Infinity;
    let maxPrice = -Infinity;

    state.candles.forEach(c => {
      if (c.low < minPrice) minPrice = c.low;
      if (c.high > maxPrice) maxPrice = c.high;
    });

    // Also include active trade entry prices in visible scale
    state.activeTrades.forEach(tr => {
      if (tr.entry_price < minPrice) minPrice = tr.entry_price;
      if (tr.entry_price > maxPrice) maxPrice = tr.entry_price;
    });

    const priceSpan = (maxPrice - minPrice) || 1;
    const pad = priceSpan * 0.16;
    const topPrice = maxPrice + pad;
    const bottomPrice = minPrice - pad;
    const totalSpan = topPrice - bottomPrice;

    function priceToY(p) {
      return height - ((p - bottomPrice) / totalSpan) * height;
    }

    // 2. Draw Price Grid Lines
    const gridLines = 6;
    ctx.lineWidth = 1;
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.04)';
    ctx.fillStyle = 'rgba(132, 142, 156, 0.6)';
    ctx.font = '10px -apple-system, sans-serif';
    ctx.textAlign = 'right';

    for (let i = 0; i <= gridLines; i++) {
      const y = (height / gridLines) * i;
      const priceAtY = topPrice - (i / gridLines) * totalSpan;

      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(width - 55, y);
      ctx.stroke();

      const config = state.assetsConfig[state.asset];
      ctx.fillText(priceAtY.toFixed(config.decimals), width - 8, y + 3);
    }

    // 3. Draw Candlesticks
    const rightMargin = 65;
    const availableWidth = width - rightMargin;
    const visibleCount = state.candles.length;
    const candleWidth = Math.max(5, (availableWidth / visibleCount) * 0.68);
    const spacing = availableWidth / visibleCount;

    state.candles.forEach((c, index) => {
      const x = index * spacing + spacing / 2;
      const openY = priceToY(c.open);
      const closeY = priceToY(c.close);
      const highY = priceToY(c.high);
      const lowY = priceToY(c.low);

      const isBull = c.close >= c.open;
      const color = isBull ? '#00e676' : '#ff3b69';

      // Draw Wick
      ctx.strokeStyle = color;
      ctx.lineWidth = 1.6;
      ctx.beginPath();
      ctx.moveTo(x, highY);
      ctx.lineTo(x, lowY);
      ctx.stroke();

      // Draw Body
      const bodyTop = Math.min(openY, closeY);
      const bodyHeight = Math.max(2.5, Math.abs(closeY - openY));
      ctx.fillStyle = color;

      // Rounded candle rect
      ctx.beginPath();
      roundRect(ctx, x - candleWidth / 2, bodyTop, candleWidth, bodyHeight, 2);
      ctx.fill();
    });

    // 4. Draw Current Price Tracking Line & Countdown Badge
    const curY = priceToY(state.currentPrice);
    const curColor = state.currentPrice >= state.candles[state.candles.length - 1].open ? '#00e676' : '#ff3b69';

    ctx.save();
    ctx.strokeStyle = curColor;
    ctx.lineWidth = 1.2;
    ctx.setLineDash([4, 4]);
    ctx.beginPath();
    ctx.moveTo(0, curY);
    ctx.lineTo(width - 65, curY);
    ctx.stroke();
    ctx.restore();

    // Price Pill at right edge
    const priceText = state.currentPrice.toFixed(state.assetsConfig[state.asset].decimals);
    const timerText = '00:' + String(state.candleTimeLeft).padStart(2, '0');

    // Live countdown tag
    ctx.fillStyle = 'rgba(19, 23, 34, 0.9)';
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
    ctx.lineWidth = 1;
    ctx.beginPath();
    roundRect(ctx, width - 110, curY - 11, 42, 22, 6);
    ctx.fill();
    ctx.stroke();

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 9px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(timerText, width - 89, curY + 3.5);

    // Current Price Tag (White pill)
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    roundRect(ctx, width - 64, curY - 12, 60, 24, 8);
    ctx.fill();

    ctx.fillStyle = '#090c10';
    ctx.font = 'bold 10px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(priceText, width - 34, curY + 4);

    // 5. Render Active Trade Markers on Chart
    state.activeTrades.forEach(tr => {
      const entryY = priceToY(tr.entry_price);
      const isUp = tr.direction === 'up';
      const trColor = isUp ? '#00e676' : '#ff3b69';

      // Entry Price Horizontal Line
      ctx.save();
      ctx.strokeStyle = trColor;
      ctx.lineWidth = 1.5;
      ctx.setLineDash([2, 3]);
      ctx.beginPath();
      ctx.moveTo(0, entryY);
      ctx.lineTo(width - 65, entryY);
      ctx.stroke();
      ctx.restore();

      // Pin Marker at entry price
      const pinX = width - 130;
      ctx.fillStyle = trColor;
      ctx.beginPath();
      ctx.arc(pinX, entryY, 11, 0, Math.PI * 2);
      ctx.fill();

      // Direction Arrow
      ctx.fillStyle = isUp ? '#000000' : '#ffffff';
      ctx.font = 'bold 11px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(isUp ? '↑' : '↓', pinX, entryY + 4);

      // Amount Badge next to pin
      ctx.fillStyle = 'rgba(19, 23, 34, 0.92)';
      ctx.beginPath();
      roundRect(ctx, pinX - 38, entryY - 9, 24, 18, 5);
      ctx.fill();

      ctx.fillStyle = '#ffffff';
      ctx.font = 'bold 9px sans-serif';
      ctx.textAlign = 'center';
      const prefix = tr.account_type === 'demo' ? 'D' : '₹';
      ctx.fillText(prefix + Math.round(tr.amount), pinX - 26, entryY + 3.5);
    });
  }

  function roundRect(ctx, x, y, width, height, radius) {
    if (width < 2 * radius) radius = width / 2;
    if (height < 2 * radius) radius = height / 2;
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.arcTo(x + width, y, x + width, y + height, radius);
    ctx.arcTo(x + width, y + height, x, y + height, radius);
    ctx.arcTo(x, y + height, x, y, radius);
    ctx.arcTo(x, y, x + width, y, radius);
    ctx.closePath();
  }

  // -------------------------------------------------------------
  // UI SYNC & CONTROLS
  // -------------------------------------------------------------
  function updateHeaderBalanceUI() {
    const balEl = document.getElementById('tradingBalanceDisplay');
    const badgeEl = document.getElementById('tradingAccountBadge');
    if (!balEl || !badgeEl) return;

    if (state.accountType === 'demo') {
      balEl.textContent = 'D ' + state.balances.demo.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      badgeEl.innerHTML = 'Demo account <svg width="10" height="6" viewBox="0 0 10 6" fill="currentColor"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
      badgeEl.className = 'trading-account-badge demo';
    } else {
      balEl.textContent = '₹ ' + state.balances.real.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      badgeEl.innerHTML = 'Real account (Interest) <svg width="10" height="6" viewBox="0 0 10 6" fill="currentColor"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
      badgeEl.className = 'trading-account-badge real';
    }

    // Update modal radio options
    document.querySelectorAll('.acc-switch-option').forEach(el => {
      if (el.dataset.type === state.accountType) {
        el.classList.add('active');
      } else {
        el.classList.remove('active');
      }
    });

    const demoVal = document.getElementById('accModalDemoBal');
    const realVal = document.getElementById('accModalRealBal');
    if (demoVal) demoVal.textContent = 'D ' + state.balances.demo.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    if (realVal) realVal.textContent = '₹ ' + state.balances.real.toLocaleString('en-IN', { minimumFractionDigits: 2 });
  }

  function updateAssetUI() {
    const config = state.assetsConfig[state.asset];
    const nameEl = document.getElementById('tradingAssetName');
    const payoutEl = document.getElementById('tradingAssetPayout');
    if (nameEl) nameEl.textContent = config.name;
    if (payoutEl) payoutEl.textContent = config.payout + '%';

    updateProfitPreview();

    // Update asset items inside modal
    document.querySelectorAll('.asset-item-card').forEach(card => {
      if (card.dataset.asset === state.asset) {
        card.classList.add('active');
      } else {
        card.classList.remove('active');
      }
    });
  }

  function updateProfitPreview() {
    const profitEl = document.getElementById('tradingProfitAmount');
    if (!profitEl) return;
    const config = state.assetsConfig[state.asset];
    const profit = (state.amount * (config.payout / 100)).toFixed(2);
    const prefix = state.accountType === 'demo' ? 'D' : '₹';
    profitEl.textContent = '+' + prefix + profit;
  }

  function updateSentimentUI() {
    const bearFill = document.getElementById('sentimentBearFill');
    const bullFill = document.getElementById('sentimentBullFill');
    const topTag = document.getElementById('sentimentTopTag');
    const botTag = document.getElementById('sentimentBotTag');

    const bull = state.sentimentBullPct;
    const bear = 100 - bull;

    if (bearFill) bearFill.style.height = bear + '%';
    if (bullFill) bullFill.style.height = bull + '%';
    if (topTag) topTag.textContent = bear + '%';
    if (botTag) botTag.textContent = bull + '%';
  }

  function updateOrdersBadge() {
    const badge = document.getElementById('tradingOrdersCount');
    const navBadge = document.getElementById('tradesNavBadge');
    const count = state.activeTrades.length;

    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    }
    if (navBadge) {
      navBadge.textContent = count;
      navBadge.style.display = count > 0 ? 'flex' : 'none';
    }
  }

  // -------------------------------------------------------------
  // TRADE EXECUTION & BACKEND SYNC
  // -------------------------------------------------------------
  async function placeTrade(direction) {
    playSound('click');

    const config = state.assetsConfig[state.asset];
    const amount = state.amount;
    const currentBal = state.accountType === 'demo' ? state.balances.demo : state.balances.real;

    if (currentBal < amount) {
      if (state.accountType === 'demo') {
        showToast('Insufficient Demo Balance. Click to Reset.', 'lost');
      } else {
        showToast('Insufficient Real Balance. Accrue daily interest or deposit.', 'lost');
      }
      return;
    }

    // Button tactile feedback & optimistic lock
    const btnId = direction === 'up' ? 'tradeBtnUp' : 'tradeBtnDown';
    const btn = document.getElementById(btnId);
    if (btn) btn.disabled = true;

    try {
      const formData = new FormData();
      formData.append('action', 'place_trade');
      formData.append('account_type', state.accountType);
      formData.append('asset', state.asset);
      formData.append('direction', direction);
      formData.append('amount', amount);
      formData.append('entry_price', state.currentPrice);
      formData.append('duration_sec', state.durationSec);
      formData.append('payout_pct', config.payout);

      const res = await fetch('index.php?api=trading', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        playSound('place');

        // Deduct balance locally
        if (state.accountType === 'demo') {
          state.balances.demo = data.balance_after;
        } else {
          state.balances.real = data.balance_after;
        }
        updateHeaderBalanceUI();

        // Register active trade
        const newTrade = {
          ...data.trade,
          created_time: Date.now(),
          end_time: Date.now() + (state.durationSec * 1000)
        };
        state.activeTrades.push(newTrade);
        updateOrdersBadge();

        showToast('Trade placed: ' + direction.toUpperCase() + ' ' + (state.accountType === 'demo' ? 'D' : '₹') + amount, 'won');
      } else {
        showToast(data.message || 'Error placing trade', 'lost');
      }
    } catch (err) {
      showToast('Network error placing trade', 'lost');
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  async function checkActiveTradesSettlement() {
    const now = Date.now();
    const remainingTrades = [];

    for (const trade of state.activeTrades) {
      if (now >= trade.end_time) {
        // Trade expired -> resolve with backend
        await settleTrade(trade);
      } else {
        remainingTrades.push(trade);
      }
    }

    state.activeTrades = remainingTrades;
    updateOrdersBadge();
  }

  async function settleTrade(trade) {
    try {
      const formData = new FormData();
      formData.append('action', 'close_trade');
      formData.append('trade_id', trade.id);
      formData.append('close_price', state.currentPrice);

      const res = await fetch('index.php?api=trading', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        if (data.account_type === 'demo') {
          state.balances.demo = data.balance_after;
        } else {
          state.balances.real = data.balance_after;
        }
        updateHeaderBalanceUI();

        const prefix = data.account_type === 'demo' ? 'D' : '₹';
        if (data.status === 'won') {
          playSound('win');
          showToast('🎉 TRADE WON! +' + prefix + data.profit_amount.toFixed(2), 'won');
        } else if (data.status === 'draw') {
          showToast('Trade Draw: Refunded ' + prefix + data.amount, 'won');
        } else {
          playSound('lost');
          showToast('Trade Closed: Lost ' + prefix + data.amount, 'lost');
        }

        // Add to closed trades
        state.closedTrades.unshift({
          ...trade,
          status: data.status,
          close_price: data.close_price,
          profit_amount: data.profit_amount
        });
      }
    } catch (err) {
      console.error('Error settling trade:', err);
    }
  }

  // Toast Notification System
  function showToast(msg, type) {
    const toast = document.getElementById('tradingToastBanner');
    const textEl = document.getElementById('tradingToastText');
    if (!toast || !textEl) return;

    textEl.textContent = msg;
    toast.className = 'trading-toast-banner active ' + (type === 'lost' ? 'lost' : '');

    setTimeout(() => {
      toast.classList.remove('active');
    }, 3200);
  }

  // -------------------------------------------------------------
  // INITIALIZATION & EVENT LISTENERS
  // -------------------------------------------------------------
  async function loadInitialState() {
    try {
      const res = await fetch('index.php?api=trading&action=get_state');
      const data = await res.json();
      if (data.success) {
        state.balances.demo = data.balances.demo;
        state.balances.real = data.balances.real;
        state.balances.interest = data.balances.interest;
        state.closedTrades = data.closed_trades || [];

        // Reconnect running trades if within duration
        if (data.active_trades && data.active_trades.length > 0) {
          const now = Date.now();
          data.active_trades.forEach(tr => {
            const createdAtMs = new Date(tr.created_at).getTime();
            const durMs = (tr.duration_sec || 60) * 1000;
            const endMs = createdAtMs + durMs;
            if (endMs > now) {
              state.activeTrades.push({
                ...tr,
                created_time: createdAtMs,
                end_time: endMs
              });
            }
          });
        }

        updateHeaderBalanceUI();
        updateOrdersBadge();
      }
    } catch (e) {
      console.error('Could not load trading initial state', e);
    }
  }

  function bindEvents() {
    // Steppers: Time
    const timeDec = document.getElementById('stepperTimeDec');
    const timeInc = document.getElementById('stepperTimeInc');
    const timeDisplay = document.getElementById('stepperTimeVal');
    const durations = [30, 60, 120, 180, 300];

    if (timeDec && timeInc && timeDisplay) {
      timeDec.addEventListener('click', () => {
        playSound('click');
        let idx = durations.indexOf(state.durationSec);
        if (idx > 0) {
          state.durationSec = durations[idx - 1];
          timeDisplay.textContent = state.durationSec >= 60 ? (state.durationSec / 60) + ' min' : state.durationSec + 's';
        }
      });
      timeInc.addEventListener('click', () => {
        playSound('click');
        let idx = durations.indexOf(state.durationSec);
        if (idx < durations.length - 1) {
          state.durationSec = durations[idx + 1];
          timeDisplay.textContent = state.durationSec >= 60 ? (state.durationSec / 60) + ' min' : state.durationSec + 's';
        }
      });
    }

    // Steppers: Amount
    const amtDec = document.getElementById('stepperAmtDec');
    const amtInc = document.getElementById('stepperAmtInc');
    const amtDisplay = document.getElementById('stepperAmtVal');
    const amtWrap = document.getElementById('stepperAmtWrap');

    function syncAmountUI() {
      if (amtDisplay) {
        const prefix = state.accountType === 'demo' ? 'D' : '₹';
        amtDisplay.textContent = prefix + state.amount;
      }
      updateProfitPreview();
    }

    if (amtDec && amtInc) {
      amtDec.addEventListener('click', () => {
        playSound('click');
        if (state.amount > 10) {
          state.amount = Math.max(10, state.amount - 50);
          syncAmountUI();
        }
      });
      amtInc.addEventListener('click', () => {
        playSound('click');
        state.amount += 50;
        syncAmountUI();
      });
    }

    // Quick Amount Chips in Modal
    if (amtWrap) {
      amtWrap.addEventListener('click', () => {
        openModal('amountModal');
      });
    }

    document.querySelectorAll('.preset-amt-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        playSound('click');
        state.amount = parseInt(chip.dataset.amount, 10) || 100;
        syncAmountUI();
        closeModal('amountModal');
      });
    });

    // Trade Execution Buttons
    const btnDown = document.getElementById('tradeBtnDown');
    const btnUp = document.getElementById('tradeBtnUp');

    if (btnDown) {
      btnDown.addEventListener('click', () => placeTrade('down'));
    }
    if (btnUp) {
      btnUp.addEventListener('click', () => placeTrade('up'));
    }

    // Modals: Account Switcher
    const accToggle = document.getElementById('tradingAccountToggle');
    if (accToggle) {
      accToggle.addEventListener('click', () => openModal('accountModal'));
    }

    document.querySelectorAll('.acc-switch-option').forEach(opt => {
      opt.addEventListener('click', () => {
        playSound('click');
        state.accountType = opt.dataset.type;
        updateHeaderBalanceUI();
        syncAmountUI();
        closeModal('accountModal');
      });
    });

    const resetDemoBtn = document.getElementById('resetDemoBalBtn');
    if (resetDemoBtn) {
      resetDemoBtn.addEventListener('click', async () => {
        playSound('click');
        try {
          const res = await fetch('index.php?api=trading&action=reset_demo', { method: 'POST' });
          const d = await res.json();
          if (d.success) {
            state.balances.demo = d.demo_balance;
            updateHeaderBalanceUI();
            showToast('Demo Balance replenished to ₹10,000!', 'won');
            closeModal('accountModal');
          }
        } catch (e) {}
      });
    }

    // Modals: Asset Selector
    const assetChip = document.getElementById('tradingAssetChip');
    if (assetChip) {
      assetChip.addEventListener('click', () => openModal('assetModal'));
    }

    document.querySelectorAll('.asset-item-card').forEach(card => {
      card.addEventListener('click', () => {
        playSound('click');
        const asset = card.dataset.asset;
        if (state.assetsConfig[asset]) {
          state.asset = asset;
          initCandles(asset);
          updateAssetUI();
          closeModal('assetModal');
        }
      });
    });

    // Modals: Orders / Trades Drawer
    const ordersBtn = document.getElementById('tradingOrdersBtn');
    const tradesNavItem = document.getElementById('tradingNavTrades');
    if (ordersBtn) {
      ordersBtn.addEventListener('click', () => openTradesDrawer());
    }
    if (tradesNavItem) {
      tradesNavItem.addEventListener('click', (e) => {
        e.preventDefault();
        openTradesDrawer();
      });
    }

    // Sound toggle
    const soundToggle = document.getElementById('tradingSoundToggle');
    if (soundToggle) {
      soundToggle.addEventListener('click', () => {
        state.soundEnabled = !state.soundEnabled;
        soundToggle.classList.toggle('active', state.soundEnabled);
        if (state.soundEnabled) playSound('click');
      });
    }

    // Modal Close Buttons
    document.querySelectorAll('.trading-modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
          overlay.classList.remove('active');
        }
      });
    });

    document.querySelectorAll('.modal-close-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const modalId = btn.dataset.modal;
        if (modalId) closeModal(modalId);
      });
    });
  }

  function openModal(id) {
    playSound('click');
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
  }

  function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
  }

  function openTradesDrawer() {
    renderTradesDrawerContent();
    openModal('tradesModal');
  }

  function renderTradesDrawerContent() {
    const activeContainer = document.getElementById('activeTradesList');
    const closedContainer = document.getElementById('closedTradesList');
    if (!activeContainer || !closedContainer) return;

    // Active Trades
    if (state.activeTrades.length === 0) {
      activeContainer.innerHTML = '<div style="text-align: center; color: var(--trade-text-dim); padding: 20px; font-size: 0.88rem;">No active trades running. Place an Up or Down trade on the chart!</div>';
    } else {
      activeContainer.innerHTML = state.activeTrades.map(tr => {
        const remainingSec = Math.max(0, Math.ceil((tr.end_time - Date.now()) / 1000));
        const isUp = tr.direction === 'up';
        const prefix = tr.account_type === 'demo' ? 'D' : '₹';
        const color = isUp ? 'var(--trade-bull)' : 'var(--trade-bear)';

        return `
          <div style="background: var(--trade-surface); border: 1px solid var(--trade-border); border-radius: 12px; padding: 12px 14px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
            <div>
              <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 0.92rem;">
                <span style="color: ${color};">${isUp ? '↑ UP' : '↓ DOWN'}</span>
                <span>${tr.asset.toUpperCase()}</span>
                <span style="font-size: 0.72rem; color: var(--trade-text-dim);">(${tr.account_type})</span>
              </div>
              <div style="font-size: 0.76rem; color: var(--trade-text-dim); margin-top: 2px;">
                Entry: ${tr.entry_price} • Inv: ${prefix}${tr.amount}
              </div>
            </div>
            <div style="text-align: right;">
              <div style="font-size: 1rem; font-weight: 800; color: var(--trade-accent);">
                ${remainingSec}s
              </div>
              <div style="font-size: 0.72rem; color: var(--trade-text-dim);">expiring</div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Closed Trades
    if (state.closedTrades.length === 0) {
      closedContainer.innerHTML = '<div style="text-align: center; color: var(--trade-text-dim); padding: 20px; font-size: 0.88rem;">No closed trades history yet.</div>';
    } else {
      closedContainer.innerHTML = state.closedTrades.map(tr => {
        const isWon = tr.status === 'won';
        const isDraw = tr.status === 'draw';
        const prefix = tr.account_type === 'demo' ? 'D' : '₹';
        const resultColor = isWon ? 'var(--trade-bull)' : (isDraw ? 'var(--trade-gold)' : 'var(--trade-bear)');
        const resultText = isWon ? '+' + prefix + Number(tr.profit_amount).toFixed(2) : (isDraw ? 'Draw' : '-' + prefix + tr.amount);

        return `
          <div style="background: var(--trade-surface); border: 1px solid var(--trade-border); border-radius: 12px; padding: 12px 14px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
            <div>
              <div style="font-weight: 700; font-size: 0.9rem;">
                ${tr.asset ? tr.asset.toUpperCase() : 'TRADE'} • ${tr.direction ? tr.direction.toUpperCase() : ''}
              </div>
              <div style="font-size: 0.74rem; color: var(--trade-text-dim); margin-top: 2px;">
                Entry: ${tr.entry_price} → Close: ${tr.close_price || '-'}
              </div>
            </div>
            <div style="text-align: right;">
              <div style="font-weight: 800; font-size: 0.95rem; color: ${resultColor};">
                ${resultText}
              </div>
              <span style="font-size: 0.7rem; text-transform: uppercase; color: ${resultColor}; font-weight: 700;">
                ${tr.status}
              </span>
            </div>
          </div>
        `;
      }).join('');
    }
  }

  // -------------------------------------------------------------
  // MAIN LAUNCH LIFECYCLE
  // -------------------------------------------------------------
  function start() {
    setupCanvas();
    initCandles(state.asset);
    updateHeaderBalanceUI();
    updateAssetUI();
    updateSentimentUI();
    bindEvents();
    loadInitialState();

    // Start 60 FPS Canvas Render Loop
    function loop() {
      renderChart();
      animFrameId = requestAnimationFrame(loop);
    }
    animFrameId = requestAnimationFrame(loop);

    // Live Tick generator (every 380ms)
    tickIntervalId = setInterval(simulateTick, 380);

    // 1-Second Time Engine
    timerIntervalId = setInterval(advanceTimeEngine, 1000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }

})();
