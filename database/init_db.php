<?php
/**
 * Database Initializer & Migration Tool
 * Works seamlessly with both MySQL and SQLite.
 */

$config = require __DIR__ . '/../config/database.php';

function getDbConnection($config) {
    $driver = $config['driver'];
    
    if ($driver === 'mysql' || $driver === 'auto') {
        try {
            $m = $config['mysql'];
            // First connect without dbname to create it if it doesn't exist
            $dsnNoDb = "mysql:host={$m['host']};port={$m['port']};charset={$m['charset']}";
            $pdoRaw = new PDO($dsnNoDb, $m['username'], $m['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdoRaw->exec("CREATE DATABASE IF NOT EXISTS `{$m['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $dsn = "mysql:host={$m['host']};port={$m['port']};dbname={$m['database']};charset={$m['charset']}";
            $pdo = new PDO($dsn, $m['username'], $m['password'], $m['options']);
            return ['pdo' => $pdo, 'driver' => 'mysql'];
        } catch (PDOException $e) {
            if ($driver === 'mysql') {
                die("MySQL Connection Error: " . $e->getMessage() . "\n");
            }
            // Auto fallback to SQLite
            echo "Notice: MySQL not reachable ({$e->getMessage()}). Seamlessly falling back to local SQLite database.\n";
        }
    }
    
    // SQLite connection
    $sqlitePath = $config['sqlite']['path'];
    $dir = dirname($sqlitePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $pdo = new PDO("sqlite:" . $sqlitePath, null, null, $config['sqlite']['options']);
    $pdo->exec("PRAGMA foreign_keys = ON;");
    return ['pdo' => $pdo, 'driver' => 'sqlite'];
}

$connInfo = getDbConnection($config);
$pdo = $connInfo['pdo'];
$driver = $connInfo['driver'];

echo "Connected successfully using driver: [{$driver}]\n";

// Execute table creation
if ($driver === 'mysql') {
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
} else {
    // SQLite Schema
    $sqliteQueries = [
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
            fee REAL DEFAULT 0.00,
            net_amount REAL NOT NULL,
            payout_upi TEXT DEFAULT NULL,
            payout_bank TEXT DEFAULT NULL,
            payout_ifsc TEXT DEFAULT NULL,
            status TEXT DEFAULT 'pending',
            eligibility_rate_checked REAL NOT NULL,
            note TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            processed_at DATETIME DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS interest_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            principal_amount REAL NOT NULL,
            rate_applied REAL NOT NULL,
            interest_amount REAL NOT NULL,
            tier_applied TEXT NOT NULL,
            log_date TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            amount REAL NOT NULL,
            direction TEXT NOT NULL,
            status TEXT DEFAULT 'success',
            reference_id TEXT DEFAULT NULL,
            description TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS usdt_orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            usdt_amount REAL NOT NULL,
            exchange_rate REAL NOT NULL,
            inr_amount REAL NOT NULL,
            network TEXT DEFAULT 'TRC20',
            wallet_address TEXT DEFAULT NULL,
            payout_upi TEXT NOT NULL,
            status TEXT DEFAULT 'pending',
            tx_hash TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS referrals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            referrer_user_id INTEGER NOT NULL,
            referred_user_id INTEGER NOT NULL,
            commission_earned REAL DEFAULT 0.00,
            status TEXT DEFAULT 'registered',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            message TEXT NOT NULL,
            type TEXT DEFAULT 'info',
            is_read INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS support_faqs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category TEXT NOT NULL,
            question TEXT NOT NULL,
            answer TEXT NOT NULL,
            tags TEXT DEFAULT NULL,
            sort_order INTEGER DEFAULT 0
        )"
    ];

    foreach ($sqliteQueries as $query) {
        $pdo->exec($query);
    }
}

// Ensure avatar column exists on existing installations
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN avatar TEXT DEFAULT NULL");
} catch (\Exception $e) {
    // Column already exists
}

echo "Tables created successfully.\n";

// Seed initial support FAQs
$faqCount = $pdo->query("SELECT COUNT(*) FROM support_faqs")->fetchColumn();
if ($faqCount == 0) {
    $faqs = [
        ['Deposit', 'How do I deposit money via UPI?', 'Navigate to the Deposit page, select or enter your amount, scan the dynamic SoftPay/UPI QR code or copy the UPI ID (softpay@upi). After completing the payment in any UPI app (Paytm, GPay, PhonePe), enter the 12-digit UTR/Reference Number and click Submit. Your deposit balance will be credited instantly.', 'upi,deposit,payment,utr,softpay', 1],
        ['Interest', 'How is the 0.88% daily interest calculated?', 'Every day at 00:00 AM, our automated system calculates interest at 0.88% on your active deposited balance. For example, on a ₹10,000 deposit, you earn ₹88 every single day without any deductions. The interest is credited directly to your current balance.', 'interest,interest,daily,rate,0.88', 2],
        ['Reward', 'How do I get the Business Reward 1.0% interest rate?', 'Users with total active deposits of ₹5,000 or more are automatically upgraded to the Business VIP Tier. Once upgraded, your daily interest rate increases from 0.88% to 1.00% daily, giving you maximum returns.', 'reward,vip,business,1 percent,upgrade', 3],
        ['Withdrawal', 'Why am I not eligible to withdraw immediately (0.55% rule)?', 'To ensure system stability, our safety policy requires that your accumulated interest must reach at least 0.55% of your total deposited amount before submitting a withdrawal request. For example, if you deposit ₹1,000, you only need to earn ₹5.50 in interest (which takes less than 1 day at 0.88%) to unlock withdrawals!', 'withdraw,eligibility,0.55,rule,unlock', 4],
        ['Security', 'How do I set up my Security PIN and Pattern lock?', 'Go to Settings > Security. Here you can set a 4-digit numeric PIN as well as an interactive 3x3 pattern lock. These security layers protect your account and can be requested whenever sensitive actions like withdrawals are performed.', 'security,pin,pattern,protect', 5],
        ['USDT', 'How will USDT Selling work?', 'On the Sell USDT page, enter the USDT amount you want to convert. You will see the real-time INR payout calculation based on the current exchange rate (1 USDT = ₹91.50). Enter your UPI ID, transfer to the provided address, and receive INR directly into your account upon confirmation.', 'crypto,usdt,sell,inr,exchange', 6],
        ['Referral', 'How does the Invite & Earn program work?', 'Share your unique referral code or link with friends. When a friend signs up using your code and makes their first deposit, you receive a 5.0% instant referral bonus directly into your account balance.', 'referral,invite,earn,bonus,commission', 7]
    ];

    $stmt = $pdo->prepare("INSERT INTO support_faqs (category, question, answer, tags, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($faqs as $f) {
        $stmt->execute($f);
    }
    echo "Support FAQs seeded (" . count($faqs) . " items).\n";
}

// Seed Demo User for instant testing
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($userCount == 0) {
    $passwordHash = password_hash('demo1234', PASSWORD_BCRYPT);
    $pinHash = password_hash('1234', PASSWORD_BCRYPT);
    $patternHash = hash('sha256', '0-1-2-4-6-7-8'); // Sample Z-like pattern

    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, pin_hash, pattern_hash, referral_code, tier, upi_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtUser->execute([
        'Gagan Kumar',
        'demo@softpay.com',
        '9876543210',
        $passwordHash,
        $pinHash,
        $patternHash,
        'SOFTPAY88',
        'vip',
        'gagan@softpay'
    ]);
    $userId = $pdo->lastInsertId();

    // Initial account balance: ₹10,000 deposit, ₹176.00 interest earned (2 days at 0.88%), eligible for withdraw!
    $stmtAcc = $pdo->prepare("INSERT INTO accounts (user_id, total_deposited, total_interest_earned, current_balance, locked_balance, last_interest_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtAcc->execute([$userId, 10000.00, 176.00, 10176.00, 0.00, date('Y-m-d')]);

    // Initial Deposit record
    $stmtDep = $pdo->prepare("INSERT INTO deposits (user_id, amount, upi_vpa, utr_number, qr_ref, status, note) VALUES (?, ?, ?, ?, ?, 'approved', ?)");
    $stmtDep->execute([$userId, 10000.00, 'softpay@upi', 'UTR982341908234', 'SOFTPAY_DEP_1001', 'Initial welcome savings deposit']);

    // Interest Logs
    $stmtInt = $pdo->prepare("INSERT INTO interest_logs (user_id, principal_amount, rate_applied, interest_amount, tier_applied, log_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtInt->execute([$userId, 10000.00, 0.88, 88.00, 'standard', date('Y-m-d', strtotime('-1 day'))]);
    $stmtInt->execute([$userId, 10000.00, 0.88, 88.00, 'vip', date('Y-m-d')]);

    // Transactions
    $stmtTx = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, direction, status, reference_id, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtTx->execute([$userId, 'deposit', 10000.00, 'in', 'success', 'DEP-1001', 'UPI Deposit via SoftPay']);
    $stmtTx->execute([$userId, 'interest', 88.00, 'in', 'success', 'INT-' . date('Ymd', strtotime('-1 day')), 'Daily Interest 0.88% Credited']);
    $stmtTx->execute([$userId, 'interest', 88.00, 'in', 'success', 'INT-' . date('Ymd'), 'Daily Interest 0.88% Credited']);

    // Sample Notification
    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmtNotif->execute([$userId, 'Welcome to SoftPay!', 'Your savings journey starts now. Earn up to 1.0% daily interest on your deposits.', 'success']);
    $stmtNotif->execute([$userId, 'Daily Interest Credited', '₹88.00 has been credited to your account as daily interest (0.88%).', 'interest']);

    echo "Demo user created:\nEmail: demo@softpay.com\nPassword: demo1234\nPIN: 1234\n";
}

echo "Database initialization completed successfully!\n";
?>