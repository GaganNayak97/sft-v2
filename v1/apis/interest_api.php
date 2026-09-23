<?php
use App\Core\Auth;
use App\Core\InterestEngine;

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = Auth::id();
$result = InterestEngine::checkAndAccrueDailyInterest($userId);
$user = Auth::user();

echo json_encode([
    'success' => true,
    'result' => $result,
    'total_deposited' => (float)($user['total_deposited'] ?? 0),
    'total_interest_earned' => (float)($user['total_interest_earned'] ?? 0),
    'current_balance' => (float)($user['current_balance'] ?? 0),
    'rates' => InterestEngine::getRates()
]);
?>