<?php
namespace App\Core;

use PDO;

class Security {
    public static function verifyCsrf(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function setPin(int $userId, string $pin): array {
        if (!preg_match('/^\d{4}$/', $pin)) {
            return ['success' => false, 'message' => 'PIN must be exactly 4 digits.'];
        }

        $hash = password_hash($pin, PASSWORD_BCRYPT);
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET pin_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);

        return ['success' => true, 'message' => '4-Digit Security PIN set successfully.'];
    }

    public static function verifyPin(int $userId, string $pin): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT pin_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash) {
            return false;
        }
        return password_verify($pin, $hash);
    }

    public static function setPattern(int $userId, string $pattern): array {
        // Pattern format: nodes separated by dash, e.g. "0-1-2-4-7"
        if (strlen($pattern) < 5) {
            return ['success' => false, 'message' => 'Pattern must connect at least 4 dots.'];
        }

        $hash = hash('sha256', $pattern);
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET pattern_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);

        return ['success' => true, 'message' => 'Pattern Lock set successfully.'];
    }

    public static function verifyPattern(int $userId, string $pattern): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT pattern_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash) {
            return false;
        }
        return hash_equals($hash, hash('sha256', $pattern));
    }

    public static function hasLock(int $userId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT pin_hash, pattern_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($row['pin_hash']) || !empty($row['pattern_hash']);
    }

    public static function removePin(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET pin_hash = NULL WHERE id = ?");
        $stmt->execute([$userId]);
        return ['success' => true, 'message' => 'Security PIN removed successfully.'];
    }

    public static function removePattern(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET pattern_hash = NULL WHERE id = ?");
        $stmt->execute([$userId]);
        return ['success' => true, 'message' => 'Pattern Lock removed successfully.'];
    }

    public static function sanitize(string $data): string {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
?>
