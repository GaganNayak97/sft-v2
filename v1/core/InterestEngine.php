<?php
namespace App\Core;

use PDO;

class InterestEngine {
    /**
     * Get configured interest rates and limits
     */
    public static function getRates(): array {
        return [
            'base_rate' => (float)app_config('app.base_interest_rate', 0.88),
            'vip_rate' => (float)app_config('app.reward_vip_interest_rate', 1.00),
            'vip_threshold' => (float)app_config('app.vip_min_deposit', 5000.00),
            'withdraw_min_interest_pct' => (float)app_config('app.withdraw_min_interest_pct', 0.55),
            'usdt_rate' => (float)app_config('app.usdt_inr_rate', 91.50)
        ];
    }

    /**
     * Determine user rate based on deposit balance
     */
    public static function getUserRate(float $depositedAmount): array {
        $rates = self::getRates();
        if ($depositedAmount >= $rates['vip_threshold']) {
            return [
                'rate' => $rates['vip_rate'],
                'tier' => 'vip',
                'name' => 'Business VIP Plan',
                'daily_pct' => $rates['vip_rate'] . '%'
            ];
        }
        return [
            'rate' => $rates['base_rate'],
            'tier' => 'standard',
            'name' => 'Standard Savings Plan',
            'daily_pct' => $rates['base_rate'] . '%'
        ];
    }

    /**
     * Calculate daily interest amount
     */
    public static function calculateDailyInterest(float $depositedAmount, ?float $customRate = null): float {
        if ($depositedAmount <= 0) return 0.00;
        $rateInfo = self::getUserRate($depositedAmount);
        $rate = $customRate ?? $rateInfo['rate'];
        return round($depositedAmount * ($rate / 100), 4);
    }

    /**
     * Check if user is eligible for withdrawal under the 0.55% interest rule
     * "jab tak user jitni amount deposit kr chuka hai utni amount ka 0.55% extra na ho jaye tab tk wo withdraw request ke liye eligible nahi hona chaiye"
     */
    public static function getWithdrawalEligibility(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT total_deposited, total_interest_earned, current_balance FROM accounts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $acc = $stmt->fetch(PDO::FETCH_ASSOC);

        $rates = self::getRates();
        $minPct = $rates['withdraw_min_interest_pct']; // 0.55%

        if (!$acc || (float)$acc['total_deposited'] <= 0) {
            return [
                'eligible' => false,
                'reason' => 'You need an active deposit first before requesting withdrawals.',
                'total_deposited' => 0.00,
                'total_interest_earned' => 0.00,
                'required_interest' => 0.00,
                'remaining_deficit' => 0.00,
                'progress_pct' => 0.00,
                'min_required_pct' => $minPct
            ];
        }

        $deposited = (float)$acc['total_deposited'];
        $interestEarned = (float)$acc['total_interest_earned'];
        $balance = (float)$acc['current_balance'];

        // Required interest amount = Total Deposited * (0.55 / 100)
        $requiredInterest = round($deposited * ($minPct / 100), 4);
        $remainingDeficit = max(0, $requiredInterest - $interestEarned);
        $isEligible = ($interestEarned >= $requiredInterest) && ($balance > 0);

        $progressPct = 0;
        if ($requiredInterest > 0) {
            $progressPct = min(100, round(($interestEarned / $requiredInterest) * 100, 1));
        }

        return [
            'eligible' => $isEligible,
            'total_deposited' => $deposited,
            'total_interest_earned' => $interestEarned,
            'current_balance' => $balance,
            'required_interest' => $requiredInterest,
            'remaining_deficit' => $remainingDeficit,
            'progress_pct' => $progressPct,
            'min_required_pct' => $minPct,
            'message' => $isEligible
                ? 'Eligible! You have earned the required ' . $minPct . '%+ interest on your deposit.'
                : 'Locked. You must earn at least ' . $minPct . '% (' . format_currency($requiredInterest) . ') in interest. Deficit remaining: ' . format_currency($remainingDeficit)
        ];
    }

    /**
     * Check and accrue daily interest automatically (on login or via cron)
     */
    public static function checkAndAccrueDailyInterest(int $userId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT a.*, u.tier FROM accounts a JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
        $stmt->execute([$userId]);
        $acc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$acc || (float)$acc['total_deposited'] <= 0) {
            return null;
        }

        $today = date('Y-m-d');
        $lastDate = $acc['last_interest_date'];

        // If already credited today, skip
        if ($lastDate === $today) {
            return null;
        }

        $deposited = (float)$acc['total_deposited'];
        $rateInfo = self::getUserRate($deposited);
        $rate = $rateInfo['rate'];
        $tier = $rateInfo['tier'];

        // Calculate days to accrue (if user missed previous days, accrue for each missed day up to 30 days)
        $daysToAccrue = 1;
        if (!empty($lastDate)) {
            $diff = (strtotime($today) - strtotime($lastDate)) / 86400;
            if ($diff > 1) {
                $daysToAccrue = min(30, (int)$diff);
            }
        }

        $totalDailyInterest = 0;

        try {
            $db->beginTransaction();

            for ($i = 0; $i < $daysToAccrue; $i++) {
                $logDate = date('Y-m-d', strtotime("-".($daysToAccrue - 1 - $i)." days"));
                
                // Check if already logged for this specific date
                $checkStmt = $db->prepare("SELECT id FROM interest_logs WHERE user_id = ? AND log_date = ?");
                $checkStmt->execute([$userId, $logDate]);
                if ($checkStmt->fetch()) {
                    continue;
                }

                $dailyAmount = round($deposited * ($rate / 100), 4);
                $totalDailyInterest += $dailyAmount;

                // Insert into interest_logs
                $stmtLog = $db->prepare("
                    INSERT INTO interest_logs (user_id, principal_amount, rate_applied, interest_amount, tier_applied, log_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtLog->execute([$userId, $deposited, $rate, $dailyAmount, $tier, $logDate]);
            }

            if ($totalDailyInterest > 0) {
                // Update account balances
                $stmtUpdate = $db->prepare("
                    UPDATE accounts 
                    SET total_interest_earned = total_interest_earned + ?,
                        current_balance = current_balance + ?,
                        last_interest_date = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ?
                ");
                $stmtUpdate->execute([$totalDailyInterest, $totalDailyInterest, $today, $userId]);

                // Update user tier if eligible
                $stmtTier = $db->prepare("UPDATE users SET tier = ? WHERE id = ?");
                $stmtTier->execute([$tier, $userId]);

                // Create transaction ledger record
                $stmtTx = $db->prepare("
                    INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description)
                    VALUES (?, 'interest', ?, 'in', 'success', ?, ?)
                ");
                $refId = 'INT-' . date('Ymd-His');
                $desc = "Daily Interest ({$rate}%) Credited for {$daysToAccrue} day(s)";
                $stmtTx->execute([$userId, $totalDailyInterest, $refId, $desc]);

                // Notification
                $stmtNotif = $db->prepare("
                    INSERT INTO notifications (user_id, title, message, type)
                    VALUES (?, 'Daily Interest Credited! 🎉', ?, 'interest')
                ");
                $notifMsg = format_currency($totalDailyInterest) . " daily interest has been added to your balance at " . $rate . "% (" . $rateInfo['name'] . ").";
                $stmtNotif->execute([$userId, $notifMsg]);
            } else {
                // Update last_interest_date even if 0
                $stmtTouch = $db->prepare("UPDATE accounts SET last_interest_date = ? WHERE user_id = ?");
                $stmtTouch->execute([$today, $userId]);
            }

            $db->commit();

            return [
                'credited' => true,
                'amount' => $totalDailyInterest,
                'rate' => $rate,
                'tier' => $tier,
                'days' => $daysToAccrue
            ];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['credited' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
