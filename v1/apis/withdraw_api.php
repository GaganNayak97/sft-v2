<?php
use App\Core\Auth;
use App\Core\Database;
use App\Core\InterestEngine;

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Please sign in to access withdrawals.']);
    exit;
}

$userId = Auth::id();
$db = Database::getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? 'request';

switch ($action) {
    case 'get_active_tracking':
        $stmtActive = $db->prepare("
            SELECT * FROM withdrawals 
            WHERE user_id = ? AND status = 'pending' 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmtActive->execute([$userId]);
        $w = $stmtActive->fetch(\PDO::FETCH_ASSOC);

        if (!$w) {
            echo json_encode(['success' => true, 'has_active' => false]);
            exit;
        }

        $createdAtTs = strtotime($w['created_at']);
        $unlockAtTs = !empty($w['unlock_at']) ? strtotime($w['unlock_at']) : ($createdAtTs + 12 * 3600);
        $remainingSecs = max(0, $unlockAtTs - time());
        $isReady = ($remainingSecs === 0);
        $elapsedSecs = max(0, time() - $createdAtTs);
        $progressPct = min(100, round(($elapsedSecs / (12 * 3600)) * 100));

        $payoutTarget = $w['payout_upi'] ?: ($w['payout_bank'] ? "Bank {$w['payout_bank']}" : 'Direct Account');

        echo json_encode([
            'success' => true,
            'has_active' => true,
            'withdrawal_id' => (int)$w['id'],
            'amount' => (float)$w['amount'],
            'amount_formatted' => format_currency($w['amount']),
            'payout_target' => $payoutTarget,
            'payout_upi' => $w['payout_upi'],
            'payout_bank' => $w['payout_bank'],
            'created_at_formatted' => date('M d, h:i A', $createdAtTs),
            'created_time_only' => date('h:i A', $createdAtTs),
            'unlock_timestamp' => $unlockAtTs,
            'remaining_seconds' => $remainingSecs,
            'elapsed_seconds' => $elapsedSecs,
            'progress_pct' => $progressPct,
            'is_ready' => $isReady,
            'status' => $w['status']
        ]);
        exit;

    case 'fast_forward_demo':
        $stmtFind = $db->prepare("
            SELECT id FROM withdrawals 
            WHERE user_id = ? AND status = 'pending' 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmtFind->execute([$userId]);
        $w = $stmtFind->fetch(\PDO::FETCH_ASSOC);

        if (!$w) {
            echo json_encode(['success' => false, 'message' => 'No active pending withdrawal found to fast-forward.']);
            exit;
        }

        $pastDate = date('Y-m-d H:i:s', time() - 30);
        $stmtUp = $db->prepare("UPDATE withdrawals SET unlock_at = ? WHERE id = ?");
        $stmtUp->execute([$pastDate, $w['id']]);

        echo json_encode([
            'success' => true,
            'message' => '12-Hour audit fast-forwarded! You can now tap Receive Payment.',
            'withdrawal_id' => (int)$w['id']
        ]);
        exit;

    case 'claim_payout':
        $withdrawId = (int)($_POST['withdrawal_id'] ?? 0);
        if ($withdrawId > 0) {
            $stmtW = $db->prepare("SELECT * FROM withdrawals WHERE id = ? AND user_id = ?");
            $stmtW->execute([$withdrawId, $userId]);
            $w = $stmtW->fetch(\PDO::FETCH_ASSOC);
        } else {
            $stmtW = $db->prepare("SELECT * FROM withdrawals WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
            $stmtW->execute([$userId]);
            $w = $stmtW->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$w) {
            echo json_encode(['success' => false, 'message' => 'No pending withdrawal found.']);
            exit;
        }

        if ($w['status'] === 'completed') {
            echo json_encode([
                'success' => true,
                'already_claimed' => true,
                'amount' => (float)$w['amount'],
                'amount_formatted' => format_currency($w['amount']),
                'payout_target' => $w['payout_upi'] ?: "Bank {$w['payout_bank']}"
            ]);
            exit;
        }

        $unlockAtTs = !empty($w['unlock_at']) ? strtotime($w['unlock_at']) : (strtotime($w['created_at']) + 12 * 3600);
        if (time() < $unlockAtTs) {
            $remaining = $unlockAtTs - time();
            $hours = floor($remaining / 3600);
            $mins = floor(($remaining % 3600) / 60);
            echo json_encode([
                'success' => false,
                'message' => "Security and interest calculation audit is in progress. Please wait {$hours}h {$mins}m before receiving payment."
            ]);
            exit;
        }

        try {
            $db->beginTransaction();

            $stmtComplete = $db->prepare("
                UPDATE withdrawals 
                SET status = 'completed',
                    processed_at = CURRENT_TIMESTAMP,
                    note = '12-Hour Audit Verified & Payout Released'
                WHERE id = ?
            ");
            $stmtComplete->execute([$w['id']]);

            $payoutTarget = $w['payout_upi'] ?: ($w['payout_bank'] ? "Bank {$w['payout_bank']}" : 'UPI Account');
            $refId = 'WIT-' . $w['id'];

            // Update or insert transaction
            $stmtTxCheck = $db->prepare("SELECT id FROM transactions WHERE reference_id = ?");
            $stmtTxCheck->execute([$refId]);
            if ($stmtTxCheck->fetch()) {
                $stmtTxUp = $db->prepare("UPDATE transactions SET status = 'success', description = ? WHERE reference_id = ?");
                $stmtTxUp->execute(["Payout to {$payoutTarget} (Completed & Verified)", $refId]);
            } else {
                $stmtTx = $db->prepare("
                    INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description)
                    VALUES (?, 'withdrawal', ?, 'out', 'success', ?, ?)
                ");
                $stmtTx->execute([$userId, $w['amount'], $refId, "Payout to {$payoutTarget} (Completed & Verified)"]);
            }

            // Notification
            $stmtNotif = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type)
                VALUES (?, 'Withdrawal Successful! 💸', ?, 'withdraw')
            ");
            $notifText = format_currency($w['amount']) . " transferred successfully to " . $payoutTarget . " after 12-hour audit.";
            $stmtNotif->execute([$userId, $notifText]);

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Withdrawal of ' . format_currency($w['amount']) . ' successfully released!',
                'amount' => (float)$w['amount'],
                'amount_formatted' => format_currency($w['amount']),
                'payout_target' => $payoutTarget,
                'withdrawal_id' => (int)$w['id']
            ]);
            exit;
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error finalizing payout: ' . $e->getMessage()]);
            exit;
        }

    case 'request':
    default:
        $amount = (float)($_POST['amount'] ?? 0);
        $payoutUpi = trim($_POST['payout_upi'] ?? '');
        $bankAccount = trim($_POST['bank_account'] ?? '');
        $bankIfsc = trim($_POST['bank_ifsc'] ?? '');

        if ($amount < 100) {
            echo json_encode(['success' => false, 'message' => 'Minimum withdrawal amount is ₹100.']);
            exit;
        }

        if (empty($payoutUpi) && empty($bankAccount)) {
            echo json_encode(['success' => false, 'message' => 'Please specify a receiving UPI ID or Bank Account.']);
            exit;
        }

        // Check for existing pending withdrawal to avoid duplicate queue
        $stmtPendingCheck = $db->prepare("SELECT id FROM withdrawals WHERE user_id = ? AND status = 'pending'");
        $stmtPendingCheck->execute([$userId]);
        if ($stmtPendingCheck->fetch()) {
            echo json_encode([
                'success' => false, 
                'message' => 'You already have an active withdrawal request undergoing 12-hour verification. Please wait for it to complete.'
            ]);
            exit;
        }

        // 1. Strict 0.55% Rule Verification
        $eligibility = InterestEngine::getWithdrawalEligibility($userId);
        if (!$eligibility['eligible']) {
            echo json_encode([
                'success' => false,
                'message' => 'Withdrawal Locked: ' . $eligibility['message'],
                'eligibility' => $eligibility
            ]);
            exit;
        }

        // 2. Check Available Balance
        $stmtAcc = $db->prepare("SELECT current_balance, total_deposited FROM accounts WHERE user_id = ?");
        $stmtAcc->execute([$userId]);
        $acc = $stmtAcc->fetch(\PDO::FETCH_ASSOC);

        if (!$acc || (float)$acc['current_balance'] < $amount) {
            echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance. Available: ' . format_currency($acc['current_balance'] ?? 0)]);
            exit;
        }

        try {
            $db->beginTransaction();

            // Deduct balance from current wallet
            $stmtDeduct = $db->prepare("
                UPDATE accounts 
                SET current_balance = current_balance - ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?
            ");
            $stmtDeduct->execute([$amount, $userId]);

            // Set 12-Hour audit unlock timestamp
            $unlockAt = date('Y-m-d H:i:s', time() + 12 * 3600);
            $minPct = $eligibility['min_required_pct'];

            // Insert withdrawal record as PENDING (12-hour audit queue)
            $stmtW = $db->prepare("
                INSERT INTO withdrawals (user_id, amount, fee, net_amount, payout_upi, payout_bank, payout_ifsc, status, eligibility_rate_checked, note, created_at, unlock_at, processed_at)
                VALUES (?, ?, 0.00, ?, ?, ?, ?, 'pending', ?, '12-Hour Security & Interest Audit in progress', CURRENT_TIMESTAMP, ?, NULL)
            ");
            $stmtW->execute([$userId, $amount, $amount, $payoutUpi ?: null, $bankAccount ?: null, $bankIfsc ?: null, $minPct, $unlockAt]);
            $withdrawId = $db->lastInsertId();

            // Transaction ledger (Pending status)
            $stmtTx = $db->prepare("
                INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description)
                VALUES (?, 'withdrawal', ?, 'out', 'pending', ?, ?)
            ");
            $payoutTarget = $payoutUpi ?: "Bank {$bankAccount}";
            $stmtTx->execute([$userId, $amount, 'WIT-' . $withdrawId, "Payout to {$payoutTarget} (12h Security & Interest Audit)"]);

            // Notification
            $stmtNotif = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type)
                VALUES (?, 'Withdrawal Queued for 12h Verification', ?, 'withdraw')
            ");
            $notifText = "Withdrawal request of " . format_currency($amount) . " queued for 12-hour security and interest audit. Target: " . $payoutTarget;
            $stmtNotif->execute([$userId, $notifText]);

            $db->commit();

            $nowTs = time();
            $unlockTs = $nowTs + 12 * 3600;

            echo json_encode([
                'success' => true,
                'step' => 'pending_tracking',
                'withdrawal_id' => (int)$withdrawId,
                'amount' => $amount,
                'amount_formatted' => format_currency($amount),
                'payout_target' => $payoutTarget,
                'created_at_formatted' => date('M d, h:i A', $nowTs),
                'created_time_only' => date('h:i A', $nowTs),
                'unlock_timestamp' => $unlockTs,
                'remaining_seconds' => 12 * 3600,
                'remaining_balance' => (float)$acc['current_balance'] - $amount,
                'message' => 'Withdrawal request created successfully! 12-Hour audit initiated.'
            ]);
            exit;
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Withdrawal initiation failed: ' . $e->getMessage()]);
            exit;
        }
}
?>