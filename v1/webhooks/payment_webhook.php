<?php
/**
 * Payment & Transaction Webhook Handler
 * Receives automated notifications from payment gateways (Paytm / UPI / Crypto)
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/ViewEngine.php';
require_once __DIR__ . '/../core/InterestEngine.php';

header('Content-Type: application/json; charset=UTF-8');

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (empty($data)) {
    $data = $_POST;
}

$event = $data['event'] ?? 'payment.success';
$utr = $data['utr'] ?? $data['reference_id'] ?? '';
$amount = (float)($data['amount'] ?? 0);
$userId = (int)($data['user_id'] ?? 0);

if (empty($utr) || $amount <= 0 || $userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid webhook payload']);
    exit;
}

$db = \App\Core\Database::getConnection();

// Check deposit
$stmt = $db->prepare("SELECT id, status FROM deposits WHERE utr_number = ? AND user_id = ?");
$stmt->execute([$utr, $userId]);
$dep = $stmt->fetch(\PDO::FETCH_ASSOC);

if ($dep && $dep['status'] === 'pending') {
    $stmtUp = $db->prepare("UPDATE deposits SET status = 'approved', approved_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmtUp->execute([$dep['id']]);

    $stmtAcc = $db->prepare("UPDATE accounts SET total_deposited = total_deposited + ?, current_balance = current_balance + ? WHERE user_id = ?");
    $stmtAcc->execute([$amount, $amount, $userId]);

    echo json_encode(['success' => true, 'message' => 'Deposit approved via webhook']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Webhook received and processed']);
?>