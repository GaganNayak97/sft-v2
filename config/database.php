<?php
/**
 * =========================================================================
 * SoftPay Savings Management WebApp - Database Configuration
 * =========================================================================
 * 
 * 💡 HOSTING SETUP GUIDE FOR BEGINNERS:
 * -------------------------------------------------------------------------
 * Agar aap ise cPanel / Hostinger / Kisi bhi Web Hosting par chala rahe hain:
 * 1. Apne cPanel me jaakar ek MySQL Database aur User banayein (MySQL Databases).
 * 2. Niche 'mysql' section me apna database name, username aur password daalein.
 * 3. 'driver' ko 'auto' ya 'mysql' par set karein.
 * 
 * ⚡ ZERO-CONFIG LOCAL MODE:
 * Agar MySQL setup nahi hai, toh system automatically local SQLite database
 * (database/app.sqlite) ka use karega bina kisi configuration ke!
 * =========================================================================
 */

return [
    // Driver mode: 'auto' (tries MySQL first, falls back to SQLite), 'mysql', or 'sqlite'
    'driver' => 'auto',
    
    // MySQL Database Settings (Hosting ke liye yahan details bharein)
    'mysql' => [
        'host' => '127.0.0.1',        // Hosting server host (usually 'localhost' or '127.0.0.1')
        'port' => 3306,               // Default MySQL port
        'database' => 'savings_mgmt', // Apka MySQL Database Name (e.g. u12345_softpay)
        'username' => 'root',         // Apka MySQL Username (e.g. u12345_dbuser)
        'password' => '',             // Apka MySQL User Password
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    
    // SQLite Database Settings (Local testing & zero-setup fallback)
    'sqlite' => [
        'path' => file_exists(__DIR__ . '/../database/app.sqlite') 
            ? __DIR__ . '/../database/app.sqlite' 
            : __DIR__ . '/database/app.sqlite',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    ],

    // App Rates & Financial Settings
    'app' => [
        'name' => 'SoftPay',
        'currency' => '₹',
        'currency_code' => 'INR',
        'base_interest_rate' => 0.88,        // 0.88% daily base interest
        'reward_vip_interest_rate' => 1.00,  // 1.00% daily VIP Business reward interest
        'vip_min_deposit' => 5000.00,        // ₹5000+ unlocks VIP 1.00% daily interest
        'withdraw_min_interest_pct' => 0.55, // Accumulated interest must be >= 0.55% of deposit
        'usdt_inr_rate' => 91.50,            // 1 USDT = ₹91.50
        'referral_commission_pct' => 5.0,    // 5% bonus on referral first deposit
        'upi_vpa' => 'softpay@upi',          // Default deposit UPI VPA
        'upi_name' => 'SoftPay Savings'
    ]
];
?>
