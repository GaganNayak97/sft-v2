<?php
use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = Auth::id();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'set_pin':
        $pin = trim($_POST['pin'] ?? '');
        $pinConfirm = trim($_POST['pin_confirm'] ?? '');

        if ($pin !== $pinConfirm) {
            echo json_encode(['success' => false, 'message' => 'PIN confirmation does not match.']);
            exit;
        }

        $res = Security::setPin($userId, $pin);
        if ($res['success']) {
            $_SESSION['screen_unlocked'] = true;
        }
        echo json_encode($res);
        exit;

    case 'verify_pin':
        $pin = trim($_POST['pin'] ?? '');
        $valid = Security::verifyPin($userId, $pin);
        if ($valid) {
            $_SESSION['screen_unlocked'] = true;
        }
        echo json_encode([
            'success' => $valid,
            'message' => $valid ? 'PIN verified successfully!' : 'Incorrect 4-digit PIN.',
            'redirect' => $valid ? 'index.php?page=home' : null
        ]);
        exit;

    case 'set_pattern':
        $pattern = trim($_POST['pattern'] ?? '');
        $res = Security::setPattern($userId, $pattern);
        if ($res['success']) {
            $_SESSION['screen_unlocked'] = true;
        }
        echo json_encode($res);
        exit;

    case 'verify_pattern':
        $pattern = trim($_POST['pattern'] ?? '');
        $valid = Security::verifyPattern($userId, $pattern);
        if ($valid) {
            $_SESSION['screen_unlocked'] = true;
        }
        echo json_encode([
            'success' => $valid,
            'message' => $valid ? 'Pattern verified successfully!' : 'Incorrect pattern lock.',
            'redirect' => $valid ? 'index.php?page=home' : null
        ]);
        exit;

    case 'lock_screen':
        unset($_SESSION['screen_unlocked']);
        echo json_encode([
            'success' => true,
            'message' => 'Screen locked successfully.',
            'redirect' => 'index.php?page=lock'
        ]);
        exit;

    case 'remove_pin':
        $res = Security::removePin($userId);
        echo json_encode($res);
        exit;

    case 'remove_pattern':
        $res = Security::removePattern($userId);
        echo json_encode($res);
        exit;

    case 'change_password':
        $current = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');

        if (strlen($new) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            exit;
        }

        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $stmtUp = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmtUp->execute([$newHash, $userId]);

        echo json_encode(['success' => true, 'message' => 'Account password changed successfully!']);
        exit;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid security action.']);
        exit;
}
?>