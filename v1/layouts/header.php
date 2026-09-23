<?php
$savedCookieTheme = $_COOKIE['softpay_theme'] ?? null;
$activeTheme = $savedCookieTheme ?: ($user['theme_preference'] ?? 'light');
if (!in_array($activeTheme, ['light', 'dark', 'system'])) {
    $activeTheme = 'light';
}

$appName = app_config('app.name', 'SoftPay');
$pageTitle = htmlspecialchars($title ?? 'High-Yield Savings & Daily Compounding');
$fullTitle = $pageTitle . ' - ' . $appName . ' | Earn 0.88% – 1.00% Daily Interest';
$metaDescription = "SoftPay is India's leading digital savings platform offering 0.88% to 1.00% daily interest, instant UPI deposits, zero-risk compounding, and 24/7 bank-grade security.";
$metaKeywords = "SoftPay, digital savings, daily interest, high yield interest, upi deposit, compounding savings, business vip rewards, instant withdrawal, fintech india";

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentUrl = $protocol . $host . ($_SERVER['REQUEST_URI'] ?? '');
$logoUrl = $protocol . $host . asset('images/logo.png');
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($activeTheme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <!-- 0ms Immediate Theme Blocker Script (Guarantees Zero Flashing on Unlimited Reloads) -->
  <script>
    (function() {
      try {
        const saved = localStorage.getItem('softpay_theme') || (document.cookie.match(/(?:^|; )softpay_theme=([^;]*)/) || [])[1];
        if (saved === 'dark' || saved === 'light') {
          document.documentElement.setAttribute('data-theme', saved);
        } else if (saved === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
          document.documentElement.setAttribute('data-theme', 'dark');
        }
      } catch (e) {}
    })();
  </script>

  <!-- SEO Primary Meta Tags for Google Ranking -->
  <title><?php echo $fullTitle; ?></title>
  <meta name="title" content="<?php echo $fullTitle; ?>">
  <meta name="description" content="<?php echo $metaDescription; ?>">
  <meta name="keywords" content="<?php echo $metaKeywords; ?>">
  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
  <meta name="author" content="SoftPay Technologies">
  <meta name="theme-color" content="#002970">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="canonical" href="<?php echo htmlspecialchars($currentUrl); ?>">

  <!-- Open Graph / Facebook / WhatsApp SEO Tags -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
  <meta property="og:title" content="<?php echo $fullTitle; ?>">
  <meta property="og:description" content="<?php echo $metaDescription; ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($logoUrl); ?>">
  <meta property="og:site_name" content="SoftPay">
  <meta property="og:locale" content="en_IN">

  <!-- Twitter Card SEO Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
  <meta name="twitter:title" content="<?php echo $fullTitle; ?>">
  <meta name="twitter:description" content="<?php echo $metaDescription; ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($logoUrl); ?>">

  <!-- Google Structured Data (JSON-LD Schema Markup) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "<?php echo htmlspecialchars($protocol . $host); ?>/#organization",
        "name": "SoftPay",
        "url": "<?php echo htmlspecialchars($protocol . $host); ?>",
        "logo": "<?php echo htmlspecialchars($logoUrl); ?>"
      },
      {
        "@type": "FinancialService",
        "@id": "<?php echo htmlspecialchars($protocol . $host); ?>/#service",
        "name": "SoftPay High-Yield Digital Savings",
        "url": "<?php echo htmlspecialchars($protocol . $host); ?>",
        "description": "<?php echo $metaDescription; ?>",
        "currenciesAccepted": "INR",
        "paymentAccepted": "UPI, NetBanking, IMPS, Crypto (USDT)",
        "priceRange": "₹100 - ₹10,00,000"
      },
      {
        "@type": "WebSite",
        "@id": "<?php echo htmlspecialchars($protocol . $host); ?>/#website",
        "url": "<?php echo htmlspecialchars($protocol . $host); ?>",
        "name": "SoftPay",
        "potentialAction": {
          "@type": "SearchAction",
          "target": "<?php echo htmlspecialchars($protocol . $host); ?>/index.php?page=search&q={search_term_string}",
          "query-input": "required name=search_term_string"
        }
      }
    ]
  }
  </script>

  <!-- Favicon & App Brand Icon -->
  <link rel="icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="shortcut icon" type="image/png" href="<?php echo asset('images/favicon.png'); ?>">
  <link rel="apple-touch-icon" href="<?php echo asset('images/favicon.png'); ?>">
  
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="<?php echo asset('css/tokens.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/root.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/paytm_theme.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/layouts.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/pattern_lock.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/avatar_studio.css'); ?>">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body>
<div class="app-wrapper">
