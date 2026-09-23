<?php
use App\Core\Auth;
use App\Core\Database;
use App\Core\InterestEngine;

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Please sign in to make a deposit.']);
    exit;
}

$userId = Auth::id();
$amount = (float)($_POST['amount'] ?? 0);
$utrNumber = trim($_POST['utr_number'] ?? '');
$upiVpa = app_config('app.upi_vpa', 'softpay@upi');

if ($amount < 100) {
    echo json_encode(['success' => false, 'message' => 'Minimum deposit amount is ₹100.']);
    exit;
}

if (empty($utrNumber) || strlen($utrNumber) < 6) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid 12-digit UPI UTR / Reference number.']);
    exit;
}

$db = Database::getConnection();

// Check for duplicate UTR
$stmtCheck = $db->prepare("SELECT id FROM deposits WHERE utr_number = ?");
$stmtCheck->execute([$utrNumber]);
if ($stmtCheck->fetch()) {
    echo json_encode(['success' => false, 'message' => 'This UTR / Reference number has already been submitted.']);
    exit;
}

try {
    $db->beginTransaction();

    // Insert deposit record
    $stmtDep = $db->prepare("
        INSERT INTO deposits (user_id, amount, upi_vpa, utr_number, qr_ref, status, note, created_at, approved_at)
        VALUES (?, ?, ?, ?, ?, 'approved', 'Instant UPI Deposit Verified', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");
    $qrRef = 'SOFTPAY-' . strtoupper(bin2hex(random_bytes(4)));
    $stmtDep->execute([$userId, $amount, $upiVpa, $utrNumber, $qrRef]);
    $depositId = $db->lastInsertId();

    // Update account balance
    $stmtAcc = $db->prepare("
        UPDATE accounts 
        SET total_deposited = total_deposited + ?,
            current_balance = current_balance + ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE user_id = ?
    ");
    $stmtAcc->execute([$amount, $amount, $userId]);

    // Check new total deposited for VIP qualification (₹5,000+)
    $stmtBal = $db->prepare("SELECT total_deposited FROM accounts WHERE user_id = ?");
    $stmtBal->execute([$userId]);
    $newTotalDeposited = (float)$stmtBal->fetchColumn();

    $rates = InterestEngine::getRates();
    $newTier = ($newTotalDeposited >= $rates['vip_threshold']) ? 'vip' : 'standard';

    $stmtUserTier = $db->prepare("UPDATE users SET tier = ? WHERE id = ?");
    $stmtUserTier->execute([$newTier, $userId]);

    // Ledger Transaction
    $stmtTx = $db->prepare("
        INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description)
        VALUES (?, 'deposit', ?, 'in', 'success', ?, ?)
    ");
    $stmtTx->execute([$userId, $amount, 'DEP-' . $depositId, "UPI Deposit via {$upiVpa} (UTR: {$utrNumber})"]);

    // Check Referral Commission (5% on deposits)
    $stmtRefCheck = $db->prepare("SELECT referrer_user_id, status FROM referrals WHERE referred_user_id = ?");
    $stmtRefCheck->execute([$userId]);
    $refInfo = $stmtRefCheck->fetch(\PDO::FETCH_ASSOC);

    if ($refInfo && $refInfo['status'] === 'registered') {
        $referrerId = (int)$refInfo['referrer_user_id'];
        $commissionRate = (float)app_config('app.referral_commission_pct', 5.0);
        $commissionAmt = round($amount * ($commissionRate / 100), 2);

        if ($commissionAmt > 0) {
            // Credit referrer
            $stmtRefCredit = $db->prepare("UPDATE accounts SET current_balance = current_balance + ? WHERE user_id = ?");
            $stmtRefCredit->execute([$commissionAmt, $referrerId]);

            // Update referral record
            $stmtRefUpdate = $db->prepare("UPDATE referrals SET commission_earned = commission_earned + ?, status = 'deposited' WHERE referred_user_id = ?");
            $stmtRefUpdate->execute([$commissionAmt, $userId]);

            // Log referrer transaction
            $stmtRefTx = $db->prepare("
                INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description)
                VALUES (?, 'referral_bonus', ?, 'in', 'success', ?, '5% Referral Commission from Invited Friend Deposit')
            ");
            $stmtRefTx->execute([$referrerId, $commissionAmt, 'REF-' . $depositId]);

            // Notify referrer
            $stmtRefNotif = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type)
                VALUES (?, 'Referral Commission Credited! 🎁', ?, 'success')
            ");
            $refNotifMsg = format_currency($commissionAmt) . " has been credited to your balance from your friend's first deposit.";
            $stmtRefNotif->execute([$referrerId, $refNotifMsg]);
        }
    }

    // User Notification
    $tierMsg = ($newTier === 'vip') ? ' You are now upgraded to Business VIP (1.00% daily interest)!' : ' Earning 0.88% daily interest.';
    $stmtNotif = $db->prepare("
        INSERT INTO notifications (user_id, title, message, type)
        VALUES (?, 'Deposit Received Successfully! 💳', ?, 'deposit')
    ");
    $notifText = format_currency($amount) . " has been credited to your account via UPI." . $tierMsg;
    $stmtNotif->execute([$userId, $notifText]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Deposit of ' . format_currency($amount) . ' approved and credited successfully!',
        'amount' => $amount,
        'new_total_deposited' => $newTotalDeposited,
        'tier' => $newTier
    ]);
} catch (\Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Transaction error: ' . $e->getMessage()]);
}
?>