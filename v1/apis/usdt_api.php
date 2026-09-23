<?php
use App\Core\Auth;
use App\Core\Database;
use App\Core\InterestEngine;

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Please sign in to place a USDT sell request.']);
    exit;
}

$userId = Auth::id();
$usdtAmount = (float)($_POST['usdt_amount'] ?? 0);
$network = trim($_POST['network'] ?? 'TRC20');
$payoutUpi = trim($_POST['payout_upi'] ?? '');

if ($usdtAmount < 10) {
    echo json_encode(['success' => false, 'message' => 'Minimum sell quantity is 10 USDT.']);
    exit;
}

if (empty($payoutUpi)) {
    echo json_encode(['success' => false, 'message' => 'Payout UPI ID is required.']);
    exit;
}

$rates = InterestEngine::getRates();
$exchangeRate = $rates['usdt_rate']; // 91.50
$inrAmount = round($usdtAmount * $exchangeRate, 2);

$db = Database::getConnection();

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO usdt_orders (user_id, usdt_amount, exchange_rate, inr_amount, network, wallet_address, payout_upi, status)
        VALUES (?, ?, ?, ?, ?, 'TYu9qKL3Z8Nm82kL90PqX72vB1xM55R3tP', ?, 'pending')
    ");
    $stmt->execute([$userId, $usdtAmount, $exchangeRate, $inrAmount, $network, $payoutUpi]);
    $orderId = $db->lastInsertId();

    // Transaction record
    $stmtTx = $db->prepare("
        INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description)
        VALUES (?, 'usdt_sell', ?, 'in', 'pending', ?, ?)
    ");
    $stmtTx->execute([$userId, $inrAmount, 'USDT-' . $orderId, "Sell {$usdtAmount} USDT @ ₹{$exchangeRate} to {$payoutUpi}"]);

    // Notification
    $stmtNotif = $db->prepare("
        INSERT INTO notifications (user_id, title, message, type)
        VALUES (?, 'USDT Sell Order Created! 🌐', ?, 'info')
    ");
    $notifText = "Order #USDT-{$orderId} placed for {$usdtAmount} USDT (Est. " . format_currency($inrAmount) . "). Please send crypto to complete payout.";
    $stmtNotif->execute([$userId, $notifText]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'USDT sell order created successfully! Please transfer to the provided address to receive ' . format_currency($inrAmount) . '.',
        'order_id' => $orderId,
        'inr_amount' => $inrAmount
    ]);
} catch (\Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>