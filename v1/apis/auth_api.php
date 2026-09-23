<?php
use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;

header('Content-Type: application/json; charset=UTF-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $identifier = trim($_POST['identifier'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please enter both email/phone and password.']);
            exit;
        }

        // Two-Factor Dynamic OTP Verification Login Flow
        $res = Auth::initiateLogin($identifier, $password);
        echo json_encode($res);
        exit;

    case 'verify_login_otp':
        $otp = trim($_POST['otp'] ?? '');
        if (empty($otp)) {
            echo json_encode(['success' => false, 'message' => 'Please enter the 6-digit OTP code.']);
            exit;
        }

        $res = Auth::verifyLoginOtp($otp);
        echo json_encode($res);
        exit;

    case 'send_otp':
        $phone = trim($_POST['phone'] ?? '');
        $purpose = trim($_POST['purpose'] ?? 'signup');

        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Mobile number is required.']);
            exit;
        }

        // If signup purpose, verify phone is not already registered
        if ($purpose === 'signup') {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([substr(preg_replace('/[^0-9]/', '', $phone), -10)]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'This mobile number is already registered. Please sign in instead.']);
                exit;
            }
        }

        $res = Auth::sendOtp($phone, $purpose);
        echo json_encode($res);
        exit;

    case 'verify_otp':
        $phone = trim($_POST['phone'] ?? '');
        $otp = trim($_POST['otp'] ?? '');
        $purpose = trim($_POST['purpose'] ?? 'signup');

        if (empty($phone) || empty($otp)) {
            echo json_encode(['success' => false, 'message' => 'Both mobile number and OTP code are required.']);
            exit;
        }

        $res = Auth::verifyOtp($phone, $otp, $purpose);
        echo json_encode($res);
        exit;

    case 'resend_otp':
        $phone = trim($_POST['phone'] ?? '');
        $purpose = trim($_POST['purpose'] ?? 'signup');

        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Mobile number is required.']);
            exit;
        }

        $res = Auth::sendOtp($phone, $purpose);
        echo json_encode($res);
        exit;

    case 'signup':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $referralCode = trim($_POST['referral_code'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        $res = Auth::register($name, $email, $phone, $password, $referralCode ?: null);
        echo json_encode($res);
        exit;

    case 'forgot':
        $identifier = trim($_POST['identifier'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');

        if (empty($identifier) || strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Please provide valid account details and password (min 6 characters).']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'No account found matching this email or phone.']);
            exit;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmtUp = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmtUp->execute([$newHash, $user['id']]);

        echo json_encode(['success' => true, 'message' => 'Password reset successfully!']);
        exit;

    case 'update_profile':
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Auth::id();
        $name = trim($_POST['name'] ?? '');
        $upiId = trim($_POST['upi_id'] ?? '');
        $bankAccount = trim($_POST['bank_account'] ?? '');
        $bankIfsc = trim($_POST['bank_ifsc'] ?? '');

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET name = ?, upi_id = ?, bank_account = ?, bank_ifsc = ? WHERE id = ?");
        $stmt->execute([$name, $upiId, $bankAccount, $bankIfsc, $userId]);

        echo json_encode(['success' => true, 'message' => 'Profile details updated successfully!']);
        exit;

    case 'update_theme':
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $theme = trim($_POST['theme'] ?? 'light');
        if (!in_array($theme, ['light', 'dark', 'system'])) {
            $theme = 'light';
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
        $stmt->execute([$theme, Auth::id()]);

        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user'])) {
            $_SESSION['user']['theme_preference'] = $theme;
        }
        setcookie('softpay_theme', $theme, time() + 31536000, '/', '', false, false);

        echo json_encode(['success' => true, 'theme' => $theme]);
        exit;

    case 'update_avatar':
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $avatar = trim($_POST['avatar'] ?? '');
        if (empty($avatar)) {
            echo json_encode(['success' => false, 'message' => 'No avatar data provided.']);
            exit;
        }

        // Limit size to ~2MB data URI to prevent abuse
        if (strlen($avatar) > 3000000) {
            echo json_encode(['success' => false, 'message' => 'Avatar image is too large. Please choose a smaller crop.']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$avatar, Auth::id()]);

        echo json_encode([
            'success' => true, 
            'avatar' => $avatar,
            'message' => 'Profile photo & avatar updated successfully!'
        ]);
        exit;

    case 'reset_avatar':
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
        $stmt->execute([Auth::id()]);

        echo json_encode([
            'success' => true,
            'message' => 'Avatar reset to default initial.'
        ]);
        exit;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        exit;
}
?>