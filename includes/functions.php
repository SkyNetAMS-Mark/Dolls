<?php
/**
 * Helper Functions
 */

// Security function to prevent XSS
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Clean input
function clean($string) {
    return trim(strip_tags($string));
}

// Redirect function
function redirect($url) {
    if (strpos($url, 'http') !== 0 && strpos($url, '/') === 0) {
        $url = SITE_URL . ltrim($url, '/');
    }
    header("Location: " . $url);
    exit;
}
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Get current user
function getCurrentUser() {
    if (isLoggedIn()) {
        $db = Database::getInstance();
        return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
    return null;
}

// Format price with currency
function formatPrice($amount, $currency = DEFAULT_CURRENCY) {
    $symbols = [
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'CHF' => 'CHF '
    ];
    
    $symbol = $symbols[$currency] ?? $currency . ' ';
    
    if ($currency === 'CHF') {
        return $symbol . number_format($amount, 2, '.', ',');
    }
    
    return $symbol . number_format($amount, 2, ',', '.');
}

// Convert currency
function convertCurrency($amount, $fromCurrency, $toCurrency) {
    if ($fromCurrency === $toCurrency) {
        return $amount;
    }
    
    $db = Database::getInstance();
    
    // Get rates
    $fromRate = $db->fetchOne("SELECT rate_to_eur FROM currency_rates WHERE currency_code = ?", [$fromCurrency]);
    $toRate = $db->fetchOne("SELECT rate_to_eur FROM currency_rates WHERE currency_code = ?", [$toCurrency]);
    
    if (!$fromRate || !$toRate) {
        return $amount;
    }
    
    // Convert to EUR first, then to target currency
    $inEur = $amount / $fromRate['rate_to_eur'];
    return $inEur * $toRate['rate_to_eur'];
}

// Get selected currency from session or cookie
function getSelectedCurrency() {
    if (isset($_SESSION['currency'])) {
        return $_SESSION['currency'];
    }
    if (isset($_COOKIE['currency'])) {
        return $_COOKIE['currency'];
    }
    return DEFAULT_CURRENCY;
}

// Set selected currency
function setSelectedCurrency($currency) {
    $_SESSION['currency'] = $currency;
    setcookie('currency', $currency, time() + (86400 * 30), '/');
}

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Send email function
function sendEmail($to, $subject, $message, $fromName = null) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . ($fromName ?? SMTP_FROM_NAME) . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Send bid confirmation email
function sendBidConfirmationEmail($bid) {
    $token = $bid['confirmation_token'];
    $confirmUrl = SITE_URL . '/confirm-bid.php?token=' . $token;
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #c41e3a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .button { display: inline-block; padding: 12px 30px; background: #c41e3a; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Confirm Your Bid</h2>
                <p>Thank you for your bid!</p>
                <p><strong>Product:</strong> " . escape($bid['product_name']) . "</p>
                <p><strong>Your Bid:</strong> " . formatPrice($bid['bid_amount'], $bid['currency']) . "</p>
                <p>Please confirm your bid by clicking the button below:</p>
                <p style='text-align: center;'>
                    <a href='{$confirmUrl}' class='button'>Confirm Bid</a>
                </p>
                <p>Or copy this link: {$confirmUrl}</p>
                <p>If you did not place this bid, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($bid['email'], "Confirm Your Bid - " . SITE_NAME, $message);
}

// Send order confirmation email
function sendOrderConfirmationEmail($order) {
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #c41e3a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .order-details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #c41e3a; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Order Confirmation</h2>
                <p>Thank you for your order!</p>
                <div class='order-details'>
                    <p><strong>Order Number:</strong> " . escape($order['order_number']) . "</p>
                    <p><strong>Product:</strong> " . escape($order['product_name']) . "</p>
                    <p><strong>Amount:</strong> " . formatPrice($order['total_amount'], $order['currency']) . "</p>
                    <p><strong>Shipping:</strong> " . formatPrice($order['shipping_cost'], $order['currency']) . "</p>
                    <p><strong>Total:</strong> " . formatPrice($order['total_amount'] + $order['shipping_cost'], $order['currency']) . "</p>
                </div>
                <p>We will send you another email when your order ships.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($order['shipping_email'], "Order Confirmation #" . $order['order_number'], $message);
}

// Upload image
function uploadImage($file, $directory = 'products') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return false;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadDir = UPLOAD_PATH . $directory . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $directory . '/' . $filename;
    }
    
    return false;
}

// Resize image
function resizeImage($source, $destination, $maxWidth, $maxHeight) {
    list($width, $height, $type) = getimagesize($source);
    
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = $width * $ratio;
    $newHeight = $height * $ratio;
    
    $image = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }
    
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $destination, IMAGE_QUALITY);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $destination, 9);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($newImage, $destination, IMAGE_QUALITY);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($newImage);
    
    return true;
}

// Calculate shipping cost
function calculateShipping($countryCode) {
    $db = Database::getInstance();
    $shipping = $db->fetchOne(
        "SELECT rate FROM shipping_rates WHERE country_code = ? AND is_active = 1",
        [$countryCode]
    );
    
    if (!$shipping) {
        $shipping = $db->fetchOne(
            "SELECT rate FROM shipping_rates WHERE country_code = 'OTHER' AND is_active = 1"
        );
    }
    
    return $shipping ? $shipping['rate'] : 0;
}

// Format date
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

// Time ago function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('d M Y', $timestamp);
    }
}

// Sanitize filename
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}

// Generate order number
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Check if auction is active
function isAuctionActive($product) {
    if ($product['status'] !== 'active') {
        return false;
    }
    
    if ($product['auction_end'] && strtotime($product['auction_end']) < time()) {
        return false;
    }
    
    return true;
}

// Get setting value
function getSetting($key, $default = null) {
    $db = Database::getInstance();
    $setting = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    return $setting ? $setting['setting_value'] : $default;
}

// Save setting
function saveSetting($key, $value) {
    $db = Database::getInstance();
    $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
    
    if ($existing) {
        return $db->update('settings', ['setting_value' => $value], 'setting_key = :key', ['key' => $key]);
    } else {
        return $db->insert('settings', ['setting_key' => $key, 'setting_value' => $value]);
    }
}
