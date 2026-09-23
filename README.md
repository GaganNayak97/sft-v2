# SoftPay - High-Yield Savings Management WebApp

A production-grade, modular fintech web application built with **PHP 8+, MySQL (with automatic SQLite local fallback), HTML5, CSS3, JavaScript, and jQuery**. Featuring SoftPay fintech psychology, automated daily compounding interest, tiered VIP rewards, a 0.55% withdrawal eligibility lock, UPI payments, PIN & Pattern security, an automated FAQ chatbot, and a future-ready USDT selling desk.

---

## 🌟 Key Features

1. **Daily Compounding Interest (0.88% Base Rate)**:
   - Automated daily interest calculations credited directly to active accounts.
   - Example: A ₹10,000 deposit yields ₹88.00 every day without deduction.
2. **Business VIP Reward System (1.00% Daily Rate)**:
   - Depositors with ₹5,000+ are automatically upgraded to the VIP Business Tier.
   - Earns 1.00% flat daily interest (30% per month) with priority processing.
3. **Withdrawal Eligibility Lock (0.55% Safety Rule)**:
   - Safeguards liquidity: withdrawals remain locked until the user's accumulated interest reaches at least **0.55% of their total deposited amount**.
   - Real-time progress bar shows exact deficit and unlocks automatically once satisfied.
4. **Instant UPI 2.0 Deposit Gateway**:
   - Dynamic SoftPay QR code generator, quick copy for UPI ID (`softpay@upi`), and 12-digit UTR submission.
5. **USDT Direct Sell Desk (Crypto Liquidation)**:
   - Dedicated portal to convert Tether (USDT) to INR cash via UPI/Bank at guaranteed live exchange rates (1 USDT = ₹91.50).
6. **Double-Layer Security (PIN & 3x3 Pattern Lock)**:
   - 4-Digit numeric PIN keypad with tactile feedback.
   - Interactive 3x3 touch/mouse pattern drawing canvas.
7. **Invite Friends & Earn (5% Commission)**:
   - Unique referral code and direct shareable link generator with WhatsApp and Telegram instant share.
8. **Universal Search Popup**:
   - `Ctrl + K` global shortcut with desktop modal and full-screen mobile overlay.
9. **Interactive Automated Chatbot**:
   - 24/7 AI-style assistant auto-answering questions on UPI deposits, 0.88% interest, 0.55% withdrawal rule, and security.
10. **Paytm-Inspired Visual Design**:
    - Light Mode default with deep trust navy (`#002970`), cyan (`#00BAF2`), crisp white cards, Dark Mode, and System theme sync.

---

## 🏛️ Modular Directory Architecture

```
savings-management-webapp/
├── config/
│   └── database.php                # Database credentials & financial configuration
├── database/
│   ├── schema.sql                  # Production MySQL schema
│   ├── init_db.php                 # Migration & demo seed installer
│   └── app.sqlite                  # Auto-fallback SQLite database
├── v1/
│   ├── core/
│   │   ├── Database.php            # Dual PDO database singleton
│   │   ├── ViewEngine.php          # Function rendering engine: component(), section(), layout()
│   │   ├── Auth.php                # Session, user authentication & referral tracking
│   │   ├── Security.php            # PIN, Pattern hash verification & CSRF
│   │   ├── InterestEngine.php      # 0.88% & 1.00% daily interest & 0.55% eligibility validator
│   │   └── App.php                 # Router, front-controller & asset dispatcher
│   ├── layouts/
│   │   ├── header.php              # Meta tags, assets, jQuery
│   │   ├── navbar.php              # Header branding, search trigger, theme switcher
│   │   ├── sidebar.php             # Desktop sidebar menu
│   │   ├── bottom_nav.php          # Mobile floating navigation dock
│   │   ├── notification_drawer.php # Slide-in real-time notification center
│   │   ├── search_modal.php        # Search popup / phone overlay
│   │   └── footer.php              # Chatbot launcher, scripts, modals
│   ├── sections/
│   │   ├── hero_balance.php        # Balance card with live ticker
│   │   ├── quick_actions.php       # Circular Paytm-style action buttons
│   │   ├── interest_calculator.php # Interactive returns simulator
│   │   ├── reward_tiers.php        # Plan comparison & VIP upgrade banner
│   │   ├── referral_widget.php     # Working invite link & commission stats
│   │   ├── usdt_portal.php         # USDT to INR live converter
│   │   ├── chatbot_widget.php      # Floating AI assistant
│   │   └── pattern_pin_lock.php    # PIN & 3x3 Pattern modal
│   ├── components/
│   │   ├── stat_card.php           # Metric cards
│   │   ├── transaction_item.php    # Passbook row
│   │   ├── button.php              # Standard buttons
│   │   ├── badge.php               # Status pills
│   │   ├── modal.php               # Dialog wrapper
│   │   └── alert.php               # Toast banners
│   ├── templates/
│   │   ├── home.php                # Main dashboard
│   │   ├── balance.php             # Balance & daily interest ledger
│   │   ├── deposit.php             # UPI QR & UTR deposit page
│   │   ├── withdraw.php            # Withdrawal form with 0.55% lock
│   │   ├── rewards.php             # VIP Business reward page
│   │   ├── usdt_sell.php           # USDT crypto sell portal
│   │   ├── history.php             # Passbook transaction history
│   │   ├── notifications.php       # Notification center
│   │   ├── profile.php             # Profile & settings
│   │   ├── security.php            # PIN & Pattern lock setup
│   │   ├── help.php                # Guides & FAQ directory
│   │   ├── auth_login.php          # Login page
│   │   ├── auth_signup.php         # Registration page
│   │   └── auth_forgot.php         # Password reset page
│   ├── apis/
│   │   ├── auth_api.php            # Auth & profile actions
│   │   ├── deposit_api.php         # UPI deposit handler
│   │   ├── withdraw_api.php        # 0.55% eligibility validator & payout
│   │   ├── interest_api.php        # Daily interest check
│   │   ├── usdt_api.php            # USDT sell orders
│   │   ├── security_api.php        # PIN & Pattern setup/verification
│   │   ├── chatbot_api.php         # Auto-responder
│   │   └── search_api.php          # Universal search
│   ├── crons/
│   │   └── daily_interest_cron.php # Automated daily compounding cron
│   ├── webhooks/
│   │   └── payment_webhook.php     # Payment gateway callback
│   ├── css/                        # Modular CSS stylesheets
│   ├── js/                         # Modular JavaScript controllers
│   └── index.php                   # Version controller
├── index.php                       # Root entry router
└── README.md
```

---

## 🚀 How to Run Locally

### 1. Initialize the Database
Run the migration script from terminal (creates tables & pre-seeds demo account):
```bash
php database/init_db.php
```

### 2. Start the PHP Built-in Server
```bash
php -S localhost:8080 -t v1
```
Open your browser and navigate to:
[http://localhost:8080](http://localhost:8080)

### 3. Pre-Seeded Demo Account
- **Email**: `demo@softpay.com`
- **Password**: `demo1234`
- **4-Digit PIN**: `1234`
- *Note: You can also click the "⚡ Fill Demo Credentials" button on the login screen.*
