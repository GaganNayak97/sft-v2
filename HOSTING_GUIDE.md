# 🚀 SoftPay WebApp - Hosting & Deployment Guide (For Beginners)

Yeh guide aapko SoftPay Savings Management WebApp ko kisi bhi Shared Hosting, cPanel, Hostinger, VPS ya Localhost par deploy karne ka aasan step-by-step tareeqa batati hai.

---

## 📁 1. Project Directory Structure

Jab aap files upload karte hain, aapka hosting root (`public_html/`) is tarah dikhna chahiye:

```text
public_html/
├── .htaccess                 <-- Apache routing aur security file
├── index.php                 <-- Root router (PHP properly closed ?>)
├── config/
│   ├── .htaccess             <-- Direct browser download block
│   └── database.php          <-- MySQL & SQLite connection settings
├── database/
│   ├── .htaccess             <-- Direct app.sqlite download block
│   ├── app.sqlite            <-- Zero-config local SQLite database
│   ├── schema.sql            <-- Production MySQL database schema
│   └── init_db.php           <-- Database installer tool
├── v1/
│   ├── index.php             <-- Single front controller
│   ├── core/                 <-- Database, Auth, Security, ViewEngine
│   ├── apis/                 <-- All AJAX API endpoints
│   ├── layouts/              <-- Header, Navbar, Sidebar, BottomNav, Footer
│   ├── sections/             <-- Dashboard components
│   ├── templates/            <-- All page templates (landing, home, history, etc.)
│   ├── css/                  <-- Modular CSS stylesheets
│   └── js/                   <-- Frontend scripts
└── HOSTING_GUIDE.md          <-- Yeh guide
```

---

## ⚡ 2. Quick 3-Step Deployment (cPanel / Hostinger)

### Step 1: Files Upload Karein
1. Saari files aur folders ko zip karein.
2. Apne cPanel me **File Manager** open karein.
3. `public_html/` folder ke andar zip upload karein aur **Extract** kar dein.

### Step 2: Database Setup (Option A ya Option B)

#### Option A: MySQL Database (Recommended for Production)
1. cPanel me **MySQL Databases** par jayein.
2. Ek naya database banayein (e.g. `u12345_softpay`).
3. Ek naya database user banayein (e.g. `u12345_dbuser`) aur strong password set karein.
4. User ko Database se jodkar **ALL PRIVILEGES** grant karein.
5. Ab `config/database.php` file ko edit karein:
   ```php
   'driver' => 'mysql', // ya 'auto'
   
   'mysql' => [
       'host' => 'localhost',         // Most hostings par 'localhost' hota hai
       'port' => 3306,
       'database' => 'u12345_softpay', // Apka Database Name
       'username' => 'u12345_dbuser',  // Apka Database Username
       'password' => 'YourPassword123',// Apka Password
       // ...
   ],
   ```
6. **Auto-Installer Feature**: Aapko koi tables manually create karne ki zaroorat nahi hai! Jaise hi aap pehli baar website open karenge, SoftPay automatically saari tables aur demo seed data initialize kar dega!

#### Option B: Zero-Config SQLite (Testing ke liye)
1. Agar aap MySQL setup nahi karna chahte, toh `config/database.php` me:
   ```php
   'driver' => 'sqlite',
   ```
2. Bas confirm karein ki `database/` folder ki permissions **755** ya **777** (writeable) hain.

---

## 🛡️ 3. Hosting Server Requirements

- **PHP Version**: PHP 8.1, 8.2, 8.3 ya 8.4
- **Required PHP Extensions**:
  - `pdo`
  - `pdo_mysql` (Agar MySQL use kar rahe hain)
  - `pdo_sqlite` (Agar SQLite use kar rahe hain)
  - `mbstring`
  - `json`
  - `session`
- **Web Server**: Apache / LiteSpeed (mod_rewrite enabled) ya Nginx

---

## 🔑 4. Pre-Configured Demo Account

Pehli baar login karne ke liye pre-seeded credentials:
- **Email**: `demo@softpay.com`
- **Password**: `demo1234`
- **4-Digit PIN**: `1234`
- **3x3 Pattern**: `0-1-2-4-6-7-8` (Z shape)
- **Referral Code**: `SOFTPAY88`

---

## 🐞 5. Troubleshooting & Debugging

Agar hosting par page open hone me koi issue aaye:
1. **Diagnostic Debug Mode**:
   Apne browser URL ke aage `?debug=1` lagakar open karein:
   `https://yourdomain.com/index.php?debug=1`
   Yeh PHP ke actual warning ya missing extension error ko screen par clearly display karega.
2. **Database Connection Screen**:
   Agar database credentials galat honge, toh blank screen ke bajaye SoftPay ka **Setup Assistant** screen open hoga jo aapko batayega ki kaunsa credential galat hai aur use kaise theek karein.
3. **Closing PHP Tags**:
   Sabhi 57 PHP files me clean `?>` closing tags aur zero whitespace add kiya gaya hai, jisse header-sent ya syntax parse issues nahi aate.
