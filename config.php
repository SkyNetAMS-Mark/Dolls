<?php
// Configuration file for Reborn Dolls Auction Site

// Start session FIRST before any output
if (session_status() === PHP_SESSION_NONE) {
    // Session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // Set to 1 if using HTTPS
    session_start();
}

// Database configuration
define('DB_HOST', 'database-5018968443.webspace-host.com');
define('DB_NAME', 'dbs14942760');
define('DB_USER', 'dbu2428515');
define('DB_PASS', '!Icanfly79');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_URL', 'https://kanninga.eu/dolls/');
define('SITE_NAME', 'Reborn Dolls Auction');
define('SITE_EMAIL', 'info@kanninga.eu');

// Paths
define('ROOT_PATH', dirname(__FILE__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Timezone
date_default_timezone_set('Europe/Amsterdam');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Email configuration (for sending bid confirmations)
define('SMTP_HOST', 'smtp.strato.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', SITE_NAME);

// PayPal configuration
define('PAYPAL_EMAIL', 'your_paypal_email@domain.com');
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' or 'live'
define('PAYPAL_SANDBOX_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
define('PAYPAL_LIVE_URL', 'https://www.paypal.com/cgi-bin/webscr');

// Currency settings
define('DEFAULT_CURRENCY', 'EUR');
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '€');
}

// Bid settings
define('MIN_BID_INCREMENT', 5.00);
define('BID_CONFIRMATION_REQUIRED', true);

// Security
define('ENCRYPTION_KEY', 'your-random-32-char-key-here-change-this');

// Image settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
define('IMAGE_QUALITY', 85);
define('THUMBNAIL_WIDTH', 400);
define('THUMBNAIL_HEIGHT', 400);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('BIDS_PER_PAGE', 20);

// Helper function to load files
function require_file($file) {
    $path = ROOT_PATH . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}
