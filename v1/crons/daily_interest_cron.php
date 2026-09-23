<?php
/**
 * Automated Daily Compounding Interest Cron Job
 * Accrues 0.88% (Standard) or 1.00% (Business VIP) daily interest for all active accounts.
 * Can be run via CLI: php v1/crons/daily_interest_cron.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/ViewEngine.php';
require_once __DIR__ . '/../core/InterestEngine.php';

use App\Core\Database;
use App\Core\InterestEngine;

echo "=== Running Daily Interest Compounding Cron Job ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";

$db = Database::getConnection();
$stmt = $db->query("SELECT user_id FROM accounts WHERE total_deposited > 0");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCredited = 0;
$usersProcessed = 0;

foreach ($accounts as $acc) {
    $userId = (int)$acc['user_id'];
    $res = InterestEngine::checkAndAccrueDailyInterest($userId);
    if ($res && $res['credited']) {
        $totalCredited += $res['amount'];
        $usersProcessed++;
        echo "User ID {$userId}: Credited ₹{$res['amount']} ({$res['rate']}%, {$res['days']} day(s))\n";
    }
}

echo "=== Finished: Processed {$usersProcessed} user(s). Total Interest Credited: ₹" . number_format($totalCredited, 2) . " ===\n";
?>