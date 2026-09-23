<?php
namespace App\Core;

use PDO;

class Auth {
    public static function check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?array {
        $id = self::id();
        if (!$id) {
            return null;
        }

        static $cachedUser = null;
        if ($cachedUser !== null && $cachedUser['id'] == $id) {
            return $cachedUser;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, a.total_deposited, a.total_interest_earned, a.current_balance, a.locked_balance, a.demo_balance, a.last_interest_date
            FROM users u
            LEFT JOIN accounts a ON u.id = a.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            self::logout();
            return null;
        }

        $cachedUser = $user;
        return $user;
    }

    public static function initiateLogin(string $identifier, string $password): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email/phone or password.'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store user in pending session
        $_SESSION['pending_login_user'] = $user;

        // Generate and send OTP to registered mobile number
        $otpRes = self::sendOtp($user['phone'], 'login');
        if (!$otpRes['success']) {
            return $otpRes;
        }

        $phone = $user['phone'];
        $masked = '+91 ' . substr($phone, 0, 2) . '******' . substr($phone, -2);

        return [
            'success' => true,
            'step' => 'otp_required',
            'message' => "Security Check: 6-digit OTP sent to {$masked}",
            'phone' => $phone,
            'phone_masked' => $masked,
            'delivery_ms' => $otpRes['delivery_ms'],
            'simulated_otp' => $otpRes['simulated_otp']
        ];
    }

    public static function verifyLoginOtp(string $enteredOtp): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['pending_login_user'])) {
            return ['success' => false, 'message' => 'Session expired. Please enter your credentials again.'];
        }

        $user = $_SESSION['pending_login_user'];
        $verifyRes = self::verifyOtp($user['phone'], $enteredOtp, 'login');

        if (!$verifyRes['success']) {
            return $verifyRes;
        }

        // OTP verified - establish active authenticated session
        unset($_SESSION['pending_login_user']);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        unset($_SESSION['screen_unlocked']);

        // Trigger daily interest check on login
        InterestEngine::checkAndAccrueDailyInterest((int)$user['id']);

        $hasLock = !empty($user['pin_hash']) || !empty($user['pattern_hash']);
        return [
            'success' => true,
            'user' => $user,
            'has_lock' => $hasLock,
            'redirect' => $hasLock ? 'index.php?page=lock' : 'index.php?page=home'
        ];
    }

    public static function login(string $identifier, string $password): array {
        // Direct credential verification (fallback or direct calls)
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email/phone or password.'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        unset($_SESSION['screen_unlocked']);

        InterestEngine::checkAndAccrueDailyInterest((int)$user['id']);

        $hasLock = !empty($user['pin_hash']) || !empty($user['pattern_hash']);
        return [
            'success' => true,
            'user' => $user,
            'has_lock' => $hasLock,
            'redirect' => $hasLock ? 'index.php?page=lock' : 'index.php?page=home'
        ];
    }

    public static function sendOtp(string $phone, string $purpose = 'signup'): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) < 10) {
            return ['success' => false, 'message' => 'Please provide a valid 10-digit mobile number.'];
        }
        $phone10 = substr($cleanPhone, -10);

        // Cooldown check (60s)
        if (!empty($_SESSION['auth_otp']['cooldown_until']) && time() < $_SESSION['auth_otp']['cooldown_until']) {
            $wait = $_SESSION['auth_otp']['cooldown_until'] - time();
            return ['success' => false, 'message' => "Please wait {$wait}s before requesting a new OTP.", 'cooldown' => $wait];
        }

        // Generate dynamic cryptographically secure 6-digit OTP
        $otpCode = (string)random_int(100000, 999999);
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);

        $_SESSION['auth_otp'] = [
            'phone' => $phone10,
            'hash' => $otpHash,
            'purpose' => $purpose,
            'expires_at' => time() + 300, // 5 mins
            'cooldown_until' => time() + 60, // 60s
            'attempts' => 0
        ];

        // Realistic carrier delivery latency (1600ms - 2200ms)
        $deliveryMs = rand(1600, 2200);

        return [
            'success' => true,
            'message' => 'OTP sent successfully to +91 ' . substr($phone10, 0, 2) . '******' . substr($phone10, -2),
            'phone' => $phone10,
            'delivery_ms' => $deliveryMs,
            'simulated_otp' => $otpCode,
            'expires_in' => 300
        ];
    }

    public static function verifyOtp(string $phone, string $enteredOtp, string $purpose = 'signup'): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cleanPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);
        $enteredCode = trim($enteredOtp);

        if (empty($_SESSION['auth_otp'])) {
            return ['success' => false, 'message' => 'No active OTP request found. Please click Send OTP.'];
        }

        $otpData = &$_SESSION['auth_otp'];

        if ($otpData['phone'] !== $cleanPhone || $otpData['purpose'] !== $purpose) {
            return ['success' => false, 'message' => 'Mobile number mismatch. Please request a new OTP.'];
        }

        if (time() > $otpData['expires_at']) {
            unset($_SESSION['auth_otp']);
            return ['success' => false, 'message' => 'OTP has expired. Please request a fresh OTP.'];
        }

        if ($otpData['attempts'] >= 5) {
            unset($_SESSION['auth_otp']);
            return ['success' => false, 'message' => 'Too many failed attempts. Please request a fresh OTP.'];
        }

        $otpData['attempts']++;

        if (!password_verify($enteredCode, $otpData['hash'])) {
            $remaining = 5 - $otpData['attempts'];
            return ['success' => false, 'message' => "Invalid OTP entered. {$remaining} attempts remaining."];
        }

        // OTP Verified successfully!
        unset($_SESSION['auth_otp']);
        if ($purpose === 'signup') {
            $_SESSION['verified_signup_phone'] = $cleanPhone;
        }

        return ['success' => true, 'message' => 'Mobile number verified successfully!'];
    }

    public static function register(string $name, string $email, string $phone, string $password, ?string $referralCode = null): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cleanPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);
        if (empty($_SESSION['verified_signup_phone']) || $_SESSION['verified_signup_phone'] !== $cleanPhone) {
            return ['success' => false, 'message' => 'Mobile number OTP verification is required before opening an account.'];
        }

        $db = Database::getConnection();

        // Check if email or phone exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'An account with this email or phone already exists.'];
        }

        // Generate unique referral code
        $cleanName = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 4));
        if (strlen($cleanName) < 3) $cleanName = 'USER';
        $myReferralCode = $cleanName . rand(100, 999);

        // Check referrer
        $referrerId = null;
        if (!empty($referralCode)) {
            $stmtRef = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmtRef->execute([strtoupper(trim($referralCode))]);
            $refUser = $stmtRef->fetch(PDO::FETCH_ASSOC);
            if ($refUser) {
                $referrerId = (int)$refUser['id'];
            }
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $db->beginTransaction();

            $stmtInsert = $db->prepare("
                INSERT INTO users (name, email, phone, password_hash, referral_code, referred_by, tier)
                VALUES (?, ?, ?, ?, ?, ?, 'standard')
            ");
            $stmtInsert->execute([
                trim($name),
                trim(strtolower($email)),
                trim($phone),
                $passwordHash,
                $myReferralCode,
                $referralCode ? strtoupper(trim($referralCode)) : null
            ]);
            $newUserId = (int)$db->lastInsertId();

            // Create initial account row
            $stmtAcc = $db->prepare("
                INSERT INTO accounts (user_id, total_deposited, total_interest_earned, current_balance, locked_balance, last_interest_date)
                VALUES (?, 0.00, 0.00, 0.00, 0.00, NULL)
            ");
            $stmtAcc->execute([$newUserId]);

            // If referred, insert referral log
            if ($referrerId) {
                $stmtRefLog = $db->prepare("
                    INSERT INTO referrals (referrer_user_id, referred_user_id, commission_earned, status)
                    VALUES (?, ?, 0.00, 'registered')
                ");
                $stmtRefLog->execute([$referrerId, $newUserId]);
            }

            // Welcome notification
            $stmtNotif = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type)
                VALUES (?, 'Welcome to SoftPay!', 'Your savings account is activated. Deposit via UPI to start earning 0.88% daily interest.', 'success')
            ");
            $stmtNotif->execute([$newUserId]);

            $db->commit();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            unset($_SESSION['verified_signup_phone']);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = trim($name);
            $_SESSION['user_email'] = trim(strtolower($email));

            return ['success' => true, 'user_id' => $newUserId];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
        }
    }

    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
?>
