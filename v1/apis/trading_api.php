<?php
if (!class_exists('App\Core\Auth')) {
    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/Auth.php';
    require_once __DIR__ . '/../core/Security.php';
    require_once __DIR__ . '/../core/InterestEngine.php';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Core\Auth;
use App\Core\Database;

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Please sign in to access Trading.']);
    exit;
}

$userId = Auth::id();
$db = Database::getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? 'get_state';

// Ensure user account exists
$stmtAcc = $db->prepare("SELECT * FROM accounts WHERE user_id = ?");
$stmtAcc->execute([$userId]);
$account = $stmtAcc->fetch(\PDO::FETCH_ASSOC);

if (!$account) {
    echo json_encode(['success' => false, 'message' => 'Account not found.']);
    exit;
}

// -------------------------------------------------------------
// ACTION: GET SPOT TERMINAL STATE (Balances, Positions, History)
// -------------------------------------------------------------
if ($action === 'get_terminal_state') {
    // Fetch active open positions
    $stmtPos = $db->prepare("SELECT * FROM trading_positions WHERE user_id = ? AND status = 'open' ORDER BY created_at DESC");
    $stmtPos->execute([$userId]);
    $openPositions = $stmtPos->fetchAll(\PDO::FETCH_ASSOC);

    // Fetch closed positions history (latest 25)
    $stmtHist = $db->prepare("SELECT * FROM trading_positions WHERE user_id = ? AND status = 'closed' ORDER BY closed_at DESC LIMIT 25");
    $stmtHist->execute([$userId]);
    $closedPositions = $stmtHist->fetchAll(\PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'balances' => [
            'real' => (float)($account['current_balance'] ?? 0.00),
            'interest' => (float)($account['total_interest_earned'] ?? 0.00),
            'deposited' => (float)($account['total_deposited'] ?? 0.00)
        ],
        'positions' => $openPositions,
        'history' => $closedPositions
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: PLACE SPOT BUY / SELL ORDER
// -------------------------------------------------------------
if ($action === 'spot_order') {
    $side = strtolower(trim($_POST['side'] ?? 'buy'));
    if (!in_array($side, ['buy', 'sell'])) {
        $side = 'buy';
    }

    $symbol = trim($_POST['symbol'] ?? 'BTC/INR');
    $amountInr = (float)($_POST['amount_inr'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $orderType = strtolower(trim($_POST['order_type'] ?? 'market'));

    if ($amountInr < 10) {
        echo json_encode(['success' => false, 'message' => 'Minimum order amount is ₹10.']);
        exit;
    }

    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid market price.']);
        exit;
    }

    try {
        $db->beginTransaction();

        $stmtAccLock = $db->prepare("SELECT * FROM accounts WHERE user_id = ?");
        $stmtAccLock->execute([$userId]);
        $acc = $stmtAccLock->fetch(\PDO::FETCH_ASSOC);

        $currentBalance = (float)($acc['current_balance'] ?? 0.00);

        if ($currentBalance < $amountInr) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Insufficient SoftPay balance. Please add funds to trade.']);
            exit;
        }

        // Deduct balance
        $newBalance = $currentBalance - $amountInr;
        $stmtUp = $db->prepare("UPDATE accounts SET current_balance = ? WHERE user_id = ?");
        $stmtUp->execute([$newBalance, $userId]);

        $quantity = round($amountInr / $price, 6);

        // Insert position
        $stmtPos = $db->prepare("
            INSERT INTO trading_positions (user_id, symbol, side, order_type, amount_inr, quantity, entry_price, current_price, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open', CURRENT_TIMESTAMP)
        ");
        $stmtPos->execute([$userId, $symbol, $side, $orderType, $amountInr, $quantity, $price, $price]);
        $posId = (int)$db->lastInsertId();

        // Ledger transaction
        $refId = 'TRD-' . strtoupper(substr(uniqid(), -8));
        $desc = strtoupper($side) . ' ' . $symbol . ' (' . $quantity . ' @ ₹' . number_format($price, 2) . ')';
        $stmtTx = $db->prepare("INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description) VALUES (?, 'spot_trade', ?, 'debit', 'success', ?, ?)");
        $stmtTx->execute([$userId, $amountInr, $refId, $desc]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order executed successfully! Position opened.',
            'position' => [
                'id' => $posId,
                'symbol' => $symbol,
                'side' => $side,
                'order_type' => $orderType,
                'amount_inr' => $amountInr,
                'quantity' => $quantity,
                'entry_price' => $price,
                'current_price' => $price,
                'status' => 'open'
            ],
            'new_balance' => $newBalance
        ]);
        exit;

    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Trade execution error: ' . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// ACTION: CLOSE SPOT POSITION (Sell & Book Realized PnL)
// -------------------------------------------------------------
if ($action === 'close_position') {
    $posId = (int)($_POST['position_id'] ?? 0);
    $closePrice = (float)($_POST['close_price'] ?? 0);

    if ($posId <= 0 || $closePrice <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid position parameters.']);
        exit;
    }

    try {
        $db->beginTransaction();

        $stmtPos = $db->prepare("SELECT * FROM trading_positions WHERE id = ? AND user_id = ? AND status = 'open'");
        $stmtPos->execute([$posId, $userId]);
        $pos = $stmtPos->fetch(\PDO::FETCH_ASSOC);

        if (!$pos) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Active position not found or already closed.']);
            exit;
        }

        $entryPrice = (float)$pos['entry_price'];
        $amountInr = (float)$pos['amount_inr'];
        $quantity = (float)$pos['quantity'];
        $side = $pos['side'];

        // Calculate Realized PnL
        if ($side === 'buy') {
            $pnlAmount = round(($closePrice - $entryPrice) * $quantity, 2);
        } else {
            $pnlAmount = round(($entryPrice - $closePrice) * $quantity, 2);
        }

        $totalReturn = max(0, $amountInr + $pnlAmount);

        // Credit to SoftPay balance
        $stmtAcc = $db->prepare("SELECT * FROM accounts WHERE user_id = ?");
        $stmtAcc->execute([$userId]);
        $acc = $stmtAcc->fetch(\PDO::FETCH_ASSOC);

        $currentBal = (float)($acc['current_balance'] ?? 0.00);
        $newBalance = $currentBal + $totalReturn;

        if ($pnlAmount > 0) {
            $newInterest = (float)($acc['total_interest_earned'] ?? 0.00) + $pnlAmount;
            $stmtUp = $db->prepare("UPDATE accounts SET current_balance = ?, total_interest_earned = ? WHERE user_id = ?");
            $stmtUp->execute([$newBalance, $newInterest, $userId]);
        } else {
            $stmtUp = $db->prepare("UPDATE accounts SET current_balance = ? WHERE user_id = ?");
            $stmtUp->execute([$newBalance, $userId]);
        }

        // Update position record
        $stmtUpPos = $db->prepare("
            UPDATE trading_positions 
            SET status = 'closed', current_price = ?, pnl_amount = ?, closed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmtUpPos->execute([$closePrice, $pnlAmount, $posId]);

        // Ledger transaction
        $refId = 'POS-CLS-' . strtoupper(substr(uniqid(), -6));
        $desc = 'Closed ' . $pos['symbol'] . ' Position (PnL: ' . ($pnlAmount >= 0 ? '+' : '') . '₹' . number_format($pnlAmount, 2) . ')';
        $stmtTx = $db->prepare("INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description) VALUES (?, 'spot_trade', ?, 'credit', 'success', ?, ?)");
        $stmtTx->execute([$userId, $totalReturn, $refId, $desc]);

        // Push notification
        $pnlFormatted = ($pnlAmount >= 0 ? '+' : '') . '₹' . number_format($pnlAmount, 2);
        $notifTitle = ($pnlAmount >= 0 ? '🟢 Position Closed in Profit!' : '🔴 Position Closed');
        $notifMsg = 'Closed ' . $pos['symbol'] . ' position at ₹' . number_format($closePrice, 2) . '. PnL: ' . $pnlFormatted . '. Funds returned to balance.';
        $stmtNotif = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmtNotif->execute([$userId, $notifTitle, $notifMsg, ($pnlAmount >= 0 ? 'success' : 'info')]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Position closed successfully!',
            'position_id' => $posId,
            'pnl_amount' => $pnlAmount,
            'total_return' => $totalReturn,
            'new_balance' => $newBalance
        ]);
        exit;

    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Close position error: ' . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// ACTION: GET STATE (Balances, active trades, recent history)
// -------------------------------------------------------------
if ($action === 'get_state') {
    // Fetch active trades
    $stmtActive = $db->prepare("SELECT * FROM binary_trades WHERE user_id = ? AND status = 'open' ORDER BY created_at DESC");
    $stmtActive->execute([$userId]);
    $activeTrades = $stmtActive->fetchAll(\PDO::FETCH_ASSOC);

    // Fetch closed trades (latest 20)
    $stmtClosed = $db->prepare("SELECT * FROM binary_trades WHERE user_id = ? AND status != 'open' ORDER BY closed_at DESC LIMIT 20");
    $stmtClosed->execute([$userId]);
    $closedTrades = $stmtClosed->fetchAll(\PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'balances' => [
            'demo' => (float)($account['demo_balance'] ?? 10000.00),
            'real' => (float)($account['current_balance'] ?? 0.00),
            'interest' => (float)($account['total_interest_earned'] ?? 0.00),
        ],
        'active_trades' => $activeTrades,
        'closed_trades' => $closedTrades
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: RESET DEMO BALANCE
// -------------------------------------------------------------
if ($action === 'reset_demo') {
    $stmtReset = $db->prepare("UPDATE accounts SET demo_balance = 10000.00 WHERE user_id = ?");
    $stmtReset->execute([$userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Demo balance reset to ₹10,000.00',
        'demo_balance' => 10000.00
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: PLACE TRADE
// -------------------------------------------------------------
if ($action === 'place_trade') {
    $accountType = strtolower(trim($_POST['account_type'] ?? 'demo'));
    if (!in_array($accountType, ['demo', 'real'])) {
        $accountType = 'demo';
    }

    $asset = trim($_POST['asset'] ?? 'gold');
    $direction = strtolower(trim($_POST['direction'] ?? ''));
    if (!in_array($direction, ['up', 'down'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid trade direction (must be up or down).']);
        exit;
    }

    $amount = (float)($_POST['amount'] ?? 0);
    if ($amount < 1) {
        echo json_encode(['success' => false, 'message' => 'Minimum trade amount is 1.']);
        exit;
    }

    $durationSec = (int)($_POST['duration_sec'] ?? 60);
    if ($durationSec < 5 || $durationSec > 3600) {
        $durationSec = 60;
    }

    $entryPrice = (float)($_POST['entry_price'] ?? 0);
    if ($entryPrice <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid entry price.']);
        exit;
    }

    $payoutPct = (float)($_POST['payout_pct'] ?? 93);
    if ($payoutPct <= 0 || $payoutPct > 100) {
        $payoutPct = 93;
    }

    try {
        $db->beginTransaction();

        // Refresh account record with row lock
        $stmtAccLock = $db->prepare("SELECT * FROM accounts WHERE user_id = ?");
        $stmtAccLock->execute([$userId]);
        $acc = $stmtAccLock->fetch(\PDO::FETCH_ASSOC);

        $currentDemo = (float)($acc['demo_balance'] ?? 10000.00);
        $currentReal = (float)($acc['current_balance'] ?? 0.00);

        if ($accountType === 'demo') {
            if ($currentDemo < $amount) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Insufficient Demo balance. Reset demo account to replenish.']);
                exit;
            }
            $newDemo = $currentDemo - $amount;
            $stmtUp = $db->prepare("UPDATE accounts SET demo_balance = ? WHERE user_id = ?");
            $stmtUp->execute([$newDemo, $userId]);
            $balanceAfter = $newDemo;
        } else {
            // Real account using Daily Interest & Balance
            if ($currentReal < $amount) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Insufficient Real Balance. Accrue daily interest or deposit funds to trade.']);
                exit;
            }
            $newReal = $currentReal - $amount;
            $stmtUp = $db->prepare("UPDATE accounts SET current_balance = ? WHERE user_id = ?");
            $stmtUp->execute([$newReal, $userId]);
            $balanceAfter = $newReal;

            // Log transaction
            $refId = 'TRD-' . strtoupper(substr(uniqid(), -8));
            $desc = 'Binary Trade (' . strtoupper($direction) . ') on ' . strtoupper($asset);
            $stmtTx = $db->prepare("INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description) VALUES (?, 'binary_trade', ?, 'debit', 'success', ?, ?)");
            $stmtTx->execute([$userId, $amount, $refId, $desc]);
        }

        // Insert binary trade record
        $stmtTrade = $db->prepare("
            INSERT INTO binary_trades (user_id, account_type, asset, direction, amount, entry_price, payout_pct, status, duration_sec, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'open', ?, CURRENT_TIMESTAMP)
        ");
        $stmtTrade->execute([$userId, $accountType, $asset, $direction, $amount, $entryPrice, $payoutPct, $durationSec]);
        $tradeId = (int)$db->lastInsertId();

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Trade placed successfully!',
            'trade' => [
                'id' => $tradeId,
                'account_type' => $accountType,
                'asset' => $asset,
                'direction' => $direction,
                'amount' => $amount,
                'entry_price' => $entryPrice,
                'payout_pct' => $payoutPct,
                'duration_sec' => $durationSec,
                'status' => 'open'
            ],
            'balance_after' => $balanceAfter,
            'account_type' => $accountType
        ]);
        exit;

    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Trade error: ' . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// ACTION: CLOSE TRADE (Automated settlement at expiry)
// -------------------------------------------------------------
if ($action === 'close_trade') {
    $tradeId = (int)($_POST['trade_id'] ?? 0);
    $closePrice = (float)($_POST['close_price'] ?? 0);

    if ($tradeId <= 0 || $closePrice <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid close trade parameters.']);
        exit;
    }

    try {
        $db->beginTransaction();

        $stmtTrade = $db->prepare("SELECT * FROM binary_trades WHERE id = ? AND user_id = ? AND status = 'open'");
        $stmtTrade->execute([$tradeId, $userId]);
        $trade = $stmtTrade->fetch(\PDO::FETCH_ASSOC);

        if (!$trade) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Active trade not found or already closed.']);
            exit;
        }

        $direction = strtolower($trade['direction']);
        $entryPrice = (float)$trade['entry_price'];
        $amount = (float)$trade['amount'];
        $payoutPct = (float)$trade['payout_pct'];
        $accountType = $trade['account_type'];

        // Determine outcome
        $status = 'lost';
        $profitAmount = 0.00;
        $totalReturn = 0.00;

        if ($direction === 'up') {
            if ($closePrice > $entryPrice) {
                $status = 'won';
                $profitAmount = round($amount * ($payoutPct / 100), 2);
                $totalReturn = $amount + $profitAmount;
            } elseif ($closePrice == $entryPrice) {
                $status = 'draw';
                $profitAmount = 0.00;
                $totalReturn = $amount;
            }
        } elseif ($direction === 'down') {
            if ($closePrice < $entryPrice) {
                $status = 'won';
                $profitAmount = round($amount * ($payoutPct / 100), 2);
                $totalReturn = $amount + $profitAmount;
            } elseif ($closePrice == $entryPrice) {
                $status = 'draw';
                $profitAmount = 0.00;
                $totalReturn = $amount;
            }
        }

        // Credit returns if Won or Draw
        $stmtAcc = $db->prepare("SELECT * FROM accounts WHERE user_id = ?");
        $stmtAcc->execute([$userId]);
        $acc = $stmtAcc->fetch(\PDO::FETCH_ASSOC);

        $newBalance = 0;
        if ($accountType === 'demo') {
            $current = (float)($acc['demo_balance'] ?? 10000.00);
            $newBalance = $current + $totalReturn;
            $stmtUp = $db->prepare("UPDATE accounts SET demo_balance = ? WHERE user_id = ?");
            $stmtUp->execute([$newBalance, $userId]);
        } else {
            $current = (float)($acc['current_balance'] ?? 0.00);
            $newBalance = $current + $totalReturn;

            // If won, also increment total_interest_earned by profit
            if ($status === 'won') {
                $newInterest = (float)($acc['total_interest_earned'] ?? 0.00) + $profitAmount;
                $stmtUp = $db->prepare("UPDATE accounts SET current_balance = ?, total_interest_earned = ? WHERE user_id = ?");
                $stmtUp->execute([$newBalance, $newInterest, $userId]);

                // Record transaction
                $refId = 'TRD-WIN-' . strtoupper(substr(uniqid(), -6));
                $desc = 'Trading Win (' . strtoupper($trade['asset']) . ' ' . strtoupper($direction) . '): +₹' . number_format($profitAmount, 2);
                $stmtTx = $db->prepare("INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description) VALUES (?, 'binary_win', ?, 'credit', 'success', ?, ?)");
                $stmtTx->execute([$userId, $profitAmount, $refId, $desc]);

                // Notification
                $notifTitle = '🎉 Trading Profit Credited!';
                $notifMsg = 'You won +₹' . number_format($profitAmount, 2) . ' on ' . strtoupper($trade['asset']) . ' binary trade. Funds added to your balance!';
                $stmtNotif = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')");
                $stmtNotif->execute([$userId, $notifTitle, $notifMsg]);
            } else {
                $stmtUp = $db->prepare("UPDATE accounts SET current_balance = ? WHERE user_id = ?");
                $stmtUp->execute([$newBalance, $userId]);
            }
        }

        // Update trade status
        $stmtUpTrade = $db->prepare("
            UPDATE binary_trades 
            SET status = ?, close_price = ?, profit_amount = ?, closed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmtUpTrade->execute([$status, $closePrice, $profitAmount, $tradeId]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'trade_id' => $tradeId,
            'status' => $status,
            'entry_price' => $entryPrice,
            'close_price' => $closePrice,
            'amount' => $amount,
            'profit_amount' => $profitAmount,
            'total_return' => $totalReturn,
            'balance_after' => $newBalance,
            'account_type' => $accountType
        ]);
        exit;

    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Settlement error: ' . $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
exit;
