<?php
namespace App\Core;

class App {
    public static function run(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle static asset serving if requested via ?asset=...
        if (!empty($_GET['asset'])) {
            self::serveAsset($_GET['asset']);
            exit;
        }

        // Handle API routing if requested via ?api=...
        if (!empty($_GET['api'])) {
            self::routeApi($_GET['api']);
            exit;
        }

        // Determine active page
        $page = $_GET['page'] ?? 'home';
        $page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);

        // Guest vs Authenticated routing
        $publicPages = ['login', 'signup', 'forgot', 'landing'];
        $isAuth = Auth::check();

        // If guest visits root or home or landing: serve high-converting Landing Page
        if (!$isAuth) {
            if ($page === 'home' || $page === 'landing' || empty($_GET['page'])) {
                $page = 'landing';
            } elseif (!in_array($page, $publicPages)) {
                header('Location: index.php?page=landing');
                exit;
            }
        }

        if ($isAuth && in_array($page, ['login', 'signup', 'forgot'])) {
            header('Location: index.php?page=home');
            exit;
        }

        // Handle logout
        if ($page === 'logout') {
            Auth::logout();
            header('Location: index.php?page=login');
            exit;
        }

        // Handle Screen Lock enforcement for Authenticated users
        if ($isAuth) {
            $user = Auth::user();
            $hasSecurityLock = !empty($user['pin_hash']) || !empty($user['pattern_hash']);
            $isScreenUnlocked = !empty($_SESSION['screen_unlocked']);

            // Manual screen lock action (e.g. index.php?page=lock&action=lock)
            if ($page === 'lock' && isset($_GET['action']) && $_GET['action'] === 'lock') {
                unset($_SESSION['screen_unlocked']);
                header('Location: index.php?page=lock');
                exit;
            }

            // If account has PIN or Pattern set, but NOT unlocked in this session:
            if ($hasSecurityLock && !$isScreenUnlocked) {
                if ($page !== 'lock' && $page !== 'logout') {
                    header('Location: index.php?page=lock');
                    exit;
                }
            }

            // If already unlocked (or no security lock set) and navigating directly to lock page without action:
            if ((!$hasSecurityLock || $isScreenUnlocked) && $page === 'lock' && empty($_GET['action'])) {
                header('Location: index.php?page=home');
                exit;
            }
        }

        // Check and accrue daily interest for authenticated user
        if ($isAuth) {
            InterestEngine::checkAndAccrueDailyInterest(Auth::id());
        }

        // Map page to template file
        $templateMap = [
            'home' => 'home',
            'balance' => 'balance',
            'deposit' => 'deposit',
            'withdraw' => 'withdraw',
            'rewards' => 'rewards',
            'usdt_sell' => 'usdt_sell',
            'history' => 'history',
            'notifications' => 'notifications',
            'profile' => 'profile',
            'security' => 'security',
            'help' => 'help',
            'trading' => 'trading',
            'login' => 'auth_login',
            'signup' => 'auth_signup',
            'forgot' => 'auth_forgot',
            'lock' => 'auth_lock',
            'landing' => 'landing'
        ];

        $targetTemplate = $templateMap[$page] ?? 'home';

        // Load data context
        $data = [
            'page' => $page,
            'title' => ucwords(str_replace('_', ' ', $page)),
            'user' => $isAuth ? Auth::user() : null,
            'rates' => InterestEngine::getRates()
        ];

        if ($isAuth) {
            $data['eligibility'] = InterestEngine::getWithdrawalEligibility(Auth::id());
        }

        // Render layout + template
        if (in_array($page, $publicPages) || $page === 'lock') {
            template($targetTemplate, $data);
        } else {
            layout('header', $data);
            layout('navbar', $data);
            layout('sidebar', $data);
            layout('notification_drawer', $data);
            layout('search_modal', $data);

            echo '<main class="app-main-content">';
            template($targetTemplate, $data);
            echo '</main>';

            layout('bottom_nav', $data);
            layout('footer', $data);
        }
    }

    private static function serveAsset(string $path): void {
        $clean = realpath(__DIR__ . '/../' . ltrim($path, '/\\'));
        $baseDir = realpath(__DIR__ . '/..');

        // Security check against directory traversal
        if (!$clean || strpos($clean, $baseDir) !== 0 || !file_exists($clean)) {
            http_response_code(404);
            echo "Asset not found";
            return;
        }

        $ext = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
        $mimes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'xml' => 'application/xml; charset=UTF-8',
            'txt' => 'text/plain; charset=UTF-8'
        ];

        $mime = $mimes[$ext] ?? 'text/plain';
        header('Content-Type: ' . $mime);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($clean);
    }

    private static function routeApi(string $apiName): void {
        header('Content-Type: application/json; charset=UTF-8');
        $clean = preg_replace('/[^a-zA-Z0-9_-]/', '', $apiName);

        // Security Guard: If app is locked, only allow 'security' and 'auth' APIs
        if (Auth::check()) {
            $user = Auth::user();
            $hasSecurityLock = !empty($user['pin_hash']) || !empty($user['pattern_hash']);
            $isScreenUnlocked = !empty($_SESSION['screen_unlocked']);

            if ($hasSecurityLock && !$isScreenUnlocked) {
                $allowedLockApis = ['security', 'auth'];
                if (!in_array($clean, $allowedLockApis)) {
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'message' => 'SoftPay app is locked. Verify your 4-digit PIN or Pattern to proceed.',
                        'locked' => true,
                        'redirect' => 'index.php?page=lock'
                    ]);
                    return;
                }
            }
        }

        $apiFile = __DIR__ . '/../apis/' . $clean . '_api.php';

        if (!file_exists($apiFile)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'API endpoint not found: ' . $clean]);
            return;
        }

        require $apiFile;
    }
}
?>
