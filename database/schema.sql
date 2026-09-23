-- Savings Management WebApp Database Schema (MySQL Compatible)
-- Generated for high-yield savings management with daily compounding and withdrawal rules

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    pin_hash VARCHAR(255) DEFAULT NULL,
    pattern_hash VARCHAR(255) DEFAULT NULL,
    referral_code VARCHAR(20) NOT NULL UNIQUE,
    referred_by VARCHAR(20) DEFAULT NULL,
    tier ENUM('standard', 'vip') DEFAULT 'standard',
    upi_id VARCHAR(100) DEFAULT NULL,
    bank_account VARCHAR(50) DEFAULT NULL,
    bank_ifsc VARCHAR(20) DEFAULT NULL,
    theme_preference VARCHAR(20) DEFAULT 'light',
    avatar LONGTEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    total_deposited DECIMAL(14, 2) DEFAULT 0.00,
    total_interest_earned DECIMAL(14, 4) DEFAULT 0.00,
    current_balance DECIMAL(14, 4) DEFAULT 0.00,
    locked_balance DECIMAL(14, 4) DEFAULT 0.00,
    last_interest_date DATE DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(14, 2) NOT NULL,
    upi_vpa VARCHAR(100) NOT NULL,
    utr_number VARCHAR(100) NOT NULL,
    qr_ref VARCHAR(100) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(14, 2) NOT NULL,
    fee DECIMAL(14, 2) DEFAULT 0.00,
    net_amount DECIMAL(14, 2) NOT NULL,
    payout_upi VARCHAR(100) DEFAULT NULL,
    payout_bank VARCHAR(50) DEFAULT NULL,
    payout_ifsc VARCHAR(20) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    eligibility_rate_checked DECIMAL(6, 4) NOT NULL,
    note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    unlock_at DATETIME DEFAULT NULL,
    processed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS interest_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    principal_amount DECIMAL(14, 2) NOT NULL,
    rate_applied DECIMAL(6, 4) NOT NULL,
    interest_amount DECIMAL(14, 4) NOT NULL,
    tier_applied VARCHAR(20) NOT NULL,
    log_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('deposit', 'withdrawal', 'interest', 'referral_bonus', 'usdt_sell') NOT NULL,
    amount DECIMAL(14, 2) NOT NULL,
    direction ENUM('in', 'out') NOT NULL,
    status ENUM('success', 'pending', 'failed') DEFAULT 'success',
    reference_id VARCHAR(100) DEFAULT NULL,
    description VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usdt_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    usdt_amount DECIMAL(12, 4) NOT NULL,
    exchange_rate DECIMAL(10, 2) NOT NULL,
    inr_amount DECIMAL(14, 2) NOT NULL,
    network VARCHAR(20) DEFAULT 'TRC20',
    wallet_address VARCHAR(150) DEFAULT NULL,
    payout_upi VARCHAR(100) NOT NULL,
    status ENUM('pending', 'confirming', 'completed', 'cancelled') DEFAULT 'pending',
    tx_hash VARCHAR(150) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_user_id INT NOT NULL,
    referred_user_id INT NOT NULL,
    commission_earned DECIMAL(14, 2) DEFAULT 0.00,
    status ENUM('registered', 'deposited', 'paid') DEFAULT 'registered',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('success', 'info', 'warning', 'deposit', 'interest', 'withdraw') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS support_faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    tags VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
