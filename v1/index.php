<?php
/**
 * =========================================================================
 * SoftPay Savings Management WebApp - Single Front Controller (v1)
 * =========================================================================
 * Enterprise modular architecture: all pages, layouts, and components
 * are dynamically resolved and rendered without spaghetti requires.
 * =========================================================================
 */

declare(strict_types=1);

// Debug mode toggle: append ?debug=1 to your URL to diagnose hosting errors
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// Load Core System Components
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ViewEngine.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/InterestEngine.php';
require_once __DIR__ . '/core/App.php';

// Dispatch Application Lifecycle
\App\Core\App::run();
?>
