<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Singleton & Migration Engine
 * Automatically manages MySQL connections with instant SQLite fallback,
 * and automatically initializes database tables on first launch.
 */
class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'sqlite';

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Locate database configuration with flexible paths
        $configPath = __DIR__ . '/../../config/database.php';
        if (!file_exists($configPath)) {
            $configPath = __DIR__ . '/../config/database.php';
        }
        if (!file_exists($configPath)) {
            $configPath = dirname(__DIR__, 2) . '/config/database.php';
        }

        if (!file_exists($configPath)) {
            self::renderSetupError("Configuration File Missing", "Could not locate <code>config/database.php</code>. Please ensure the <code>config/</code> directory exists.");
            exit;
        }

        $config = require $configPath;
        $driver = $config['driver'] ?? 'auto';

        // 1. Try MySQL Connection
        if ($driver === 'mysql' || $driver === 'auto') {
            try {
                $m = $config['mysql'];
                $dsn = "mysql:host={$m['host']};port={$m['port']};dbname={$m['database']};charset={$m['charset']}";
                $pdo = new PDO($dsn, $m['username'], $m['password'], $m['options']);
                self::$instance = $pdo;
                self::$driver = 'mysql';

                // Automatically ensure tables exist on MySQL
                self::ensureTables($pdo, 'mysql');
                return self::$instance;
            } catch (PDOException $e) {
                if ($driver === 'mysql') {
                    self::renderSetupError("MySQL Connection Error", "Unable to connect to MySQL database: <strong>" . htmlspecialchars($e->getMessage()) . "</strong><br><br>Please verify your database credentials inside <code>config/database.php</code>.");
                    exit;
                }
                // If 'auto' mode, fall through to SQLite fallback
            }
        }

        // 2. Try SQLite Connection (Zero-Setup Local & Shared Hosting Fallback)
        try {
            $sqlitePath = $config['sqlite']['path'] ?? (__DIR__ . '/../../database/app.sqlite');
            $dir = dirname($sqlitePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $pdo = new PDO("sqlite:" . $sqlitePath, null, null, $config['sqlite']['options'] ?? []);
            $pdo->exec("PRAGMA foreign_keys = ON;");
            self::$instance = $pdo;
            self::$driver = 'sqlite';

            // Automatically ensure tables exist on SQLite
            self::ensureTables($pdo, 'sqlite');
            return self::$instance;
        } catch (\Throwable $e) {
            self::renderSetupError("Database Initialization Error", "Could not initialize SQLite database: <strong>" . htmlspecialchars($e->getMessage()) . "</strong><br><br>Please ensure the <code>database/</code> directory has write permissions (chmod 777 or 755), or configure your MySQL credentials in <code>config/database.php</code>.");
            exit;
        }
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    /**
     * Checks if tables exist and automatically initializes them on first run.
     */
    private static function ensureTables(PDO $pdo, string $driver): void {
        try {
            $hasUsers = false;
            if ($driver === 'mysql') {
                $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
                $hasUsers = ($stmt && $stmt->fetch());
            } else {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
                $hasUsers = ($stmt && $stmt->fetch());
            }

            if (!$hasUsers) {
                self::createTables($pdo, $driver);
                self::seedInitialData($pdo, $driver);
            }

            // Ensure binary trading table and demo balance migration
            if ($driver === 'sqlite') {
                $pdo->exec("CREATE TABLE IF NOT EXISTS binary_trades (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    account_type TEXT DEFAULT 'demo',
                    asset TEXT NOT NULL,
                    direction TEXT NOT NULL,
                    amount REAL NOT NULL,
                    entry_price REAL NOT NULL,
                    close_price REAL DEFAULT NULL,
                    payout_pct REAL NOT NULL,
                    profit_amount REAL DEFAULT 0.00,
                    status TEXT DEFAULT 'open',
                    duration_sec INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    closed_at DATETIME DEFAULT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )");

                try {
                    $pdo->exec("ALTER TABLE accounts ADD COLUMN demo_balance REAL DEFAULT 10000.00");
                } catch (\Throwable $ignored) {}

                try {
                    $pdo->exec("UPDATE accounts SET demo_balance = 10000.00 WHERE demo_balance IS NULL OR demo_balance <= 0");
                } catch (\Throwable $ignored) {}

                $pdo->exec("CREATE TABLE IF NOT EXISTS trading_positions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    symbol TEXT NOT NULL,
                    side TEXT NOT NULL,
                    order_type TEXT DEFAULT 'market',
                    amount_inr REAL NOT NULL,
                    quantity REAL NOT NULL,
                    entry_price REAL NOT NULL,
                    current_price REAL NOT NULL,
                    status TEXT DEFAULT 'open',
                    pnl_amount REAL DEFAULT 0.00,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    closed_at DATETIME DEFAULT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )");
            }
        } catch (\Throwable $e) {
            // Log or ignore if already concurrent initialized
            error_log("Database ensureTables notice: " . $e->getMessage());
        }
    }

    /**
     * Executes table schema creation
     */
    private static function createTables(PDO $pdo, string $driver): void {
        if ($driver === 'mysql') {
            $schemaFile = __DIR__ . '/../../database/schema.sql';
            if (!file_exists($schemaFile)) {
                $schemaFile = __DIR__ . '/../database/schema.sql';
            }
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $pdo->exec($sql);
            }
        } else {
            $queries = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    phone TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    pin_hash TEXT DEFAULT NULL,
                    pattern_hash TEXT DEFAULT NULL,
                    referral_code TEXT NOT NULL UNIQUE,
                    referred_by TEXT DEFAULT NULL,
                    tier TEXT DEFAULT 'standard',
                    upi_id TEXT DEFAULT NULL,
                    bank_account TEXT DEFAULT NULL,
                    bank_ifsc TEXT DEFAULT NULL,
                    theme_preference TEXT DEFAULT 'light',
                    avatar TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL UNIQUE,
                    total_deposited REAL DEFAULT 0.00,
                    total_interest_earned REAL DEFAULT 0.00,
                    current_balance REAL DEFAULT 0.00,
                    locked_balance REAL DEFAULT 0.00,
                    last_interest_date TEXT DEFAULT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS deposits (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    amount REAL NOT NULL,
                    upi_vpa TEXT NOT NULL,
                    utr_number TEXT NOT NULL,
                    qr_ref TEXT DEFAULT NULL,
                    status TEXT DEFAULT 'approved',
                    note TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    approved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS withdrawals (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    amount REAL NOT NULL,
                    payout_method TEXT NOT NULL,
                    payout_address TEXT NOT NULL,
                    fee REAL DEFAULT 0.00,
                    status TEXT DEFAULT 'pending',
                    reference_id TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    unlock_at DATETIME DEFAULT NULL,
                    processed_at DATETIME DEFAULT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS interest_accruals (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    principal_amount REAL NOT NULL,
                    rate_pct REAL NOT NULL,
                    interest_amount REAL NOT NULL,
                    tier TEXT DEFAULT 'standard',
                    accrual_date TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS transactions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    type TEXT NOT NULL,
                    amount REAL NOT NULL,
                    balance_after REAL NOT NULL,
                    reference_id TEXT DEFAULT NULL,
                    description TEXT NOT NULL,
                    metadata TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    message TEXT NOT NULL,
                    type TEXT DEFAULT 'info',
                    is_read INTEGER DEFAULT 0,
                    link TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )"
            ];

            foreach ($queries as $q) {
                $pdo->exec($q);
            }
        }
    }

    /**
     * Seeds initial demo account and welcome records for zero-friction launch
     */
    private static function seedInitialData(PDO $pdo, string $driver): void {
        try {
            $demoPass = password_hash('demo1234', PASSWORD_DEFAULT);
            $demoPin = password_hash('1234', PASSWORD_DEFAULT);
            $demoPattern = hash('sha256', '0-1-2-4-6-7-8');

            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, pin_hash, pattern_hash, referral_code, tier, upi_id, theme_preference) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'Gagan Demo',
                'demo@softpay.com',
                '9876543210',
                $demoPass,
                $demoPin,
                $demoPattern,
                'SOFTPAY88',
                'vip',
                'demo@upi',
                'light'
            ]);

            $userId = (int)$pdo->lastInsertId();

            $stmtAcc = $pdo->prepare("INSERT INTO accounts (user_id, total_deposited, total_interest_earned, current_balance, locked_balance, last_interest_date) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
            $stmtAcc->execute([$userId, 25000.00, 1850.00, 26850.00, 0.00, date('Y-m-d')]);

            $stmtTx = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, balance_after, reference_id, description) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
            $stmtTx->execute([$userId, 'deposit', 25000.00, 25000.00, 'SOFTPAY-DEP-INIT', 'Initial UPI 2.0 Deposit']);
            $stmtTx->execute([$userId, 'interest', 250.00, 25250.00, 'INTEREST-' . date('Ymd'), 'Daily Compounding Interest (1.00% VIP Tier)']);

            $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
            $stmtNotif->execute([$userId, 'Welcome to SoftPay!', 'Your daily high-yield savings account is active with guaranteed compounding returns.', 'success']);
        } catch (\Throwable $e) {
            error_log("Seed notice: " . $e->getMessage());
        }
    }

    /**
     * Renders a clean, friendly setup assistant instead of a blank screen when DB config is wrong
     */
    private static function renderSetupError(string $title, string $message): void {
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title><?php echo htmlspecialchars($title); ?> - SoftPay</title>
          <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
            .setup-card { background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 41, 112, 0.1); max-width: 600px; width: 100%; padding: 36px; border: 1px solid #e2e8f0; }
            .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
            .brand-name { font-size: 1.5rem; font-weight: 900; color: #002970; }
            .brand-name span { color: #00BAF2; }
            h2 { font-size: 1.4rem; color: #0f172a; margin-bottom: 14px; font-weight: 800; }
            .error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; color: #991b1b; font-size: 0.92rem; line-height: 1.5; margin-bottom: 24px; }
            .error-box code { background: rgba(0,0,0,0.06); padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #b91c1c; }
            .guide-steps { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 18px 22px; color: #166534; font-size: 0.9rem; line-height: 1.6; margin-bottom: 24px; }
            .guide-steps ol { padding-left: 20px; margin-top: 8px; }
            .guide-steps li { margin-bottom: 6px; }
            .btn-refresh { display: inline-block; background: #002970; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer; border: none; }
            .btn-refresh:hover { background: #00173d; }
          </style>
        </head>
        <body>
          <div class="setup-card">
            <div class="brand">
              <div class="brand-name">Soft<span>Pay</span></div>
            </div>
            <h2>⚙️ <?php echo htmlspecialchars($title); ?></h2>
            <div class="error-box">
              <?php echo $message; ?>
            </div>
            <div class="guide-steps">
              <strong>💡 Hosting Setup Steps (cPanel / Hostinger):</strong>
              <ol>
                <li>Open your Hosting File Manager or FTP.</li>
                <li>Edit <code>config/database.php</code> with your text editor.</li>
                <li>Enter your MySQL database name, username, and password.</li>
                <li>Save the file and click the button below to refresh.</li>
              </ol>
            </div>
            <button onclick="window.location.reload();" class="btn-refresh">🔄 Refresh Page</button>
          </div>
        </body>
        </html>
        <?php
    }
}
?>
