<?php
/**
 * =========================================================================
 * SoftPay Savings Management WebApp - Root Entry Router
 * =========================================================================
 * Directs incoming web requests to the active version (v1).
 * Compatible with all Shared Hosting, cPanel, VPS, and Localhost environments.
 * =========================================================================
 */

$version = $_GET['v'] ?? 'v1';
$version = preg_replace('/[^a-zA-Z0-9_-]/', '', $version);

$versionIndex = __DIR__ . '/' . $version . '/index.php';

if (file_exists($versionIndex)) {
    require_once $versionIndex;
} else {
    require_once __DIR__ . '/v1/index.php';
}
?>
