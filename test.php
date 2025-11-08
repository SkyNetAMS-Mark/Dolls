<?php
/**
 * System Test & Diagnostic Page
 * Check all functions and identify problems
 * 
 * SECURITY: Delete this file after testing or protect with password
 */

// Simple password protection (change this!)
$TEST_PASSWORD = 'test123';

if (!isset($_POST['password']) || $_POST['password'] !== $TEST_PASSWORD) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>System Test - Password Required</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 400px; }
            h1 { margin: 0 0 20px; color: #c41e3a; }
            input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0; font-size: 16px; }
            button { width: 100%; padding: 12px; background: #c41e3a; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
            button:hover { background: #a01829; }
            .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ffc107; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>🔒 System Test</h1>
            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                This test page should be deleted or password-protected after testing.
            </div>
            <form method="POST">
                <label>Enter Password:</label>
                <input type="password" name="password" placeholder="Default: test123" required autofocus>
                <button type="submit">Run Tests</button>
            </form>
            <p style="margin-top: 20px; color: #666; font-size: 14px;">
                Default password: <code>test123</code><br>
                Change the password in test.php line 9
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Start testing
require_once 'init.php';

$tests = [];
$errors = [];
$warnings = [];

// Test 1: PHP Version
function testPhpVersion() {
    $version = phpversion();
    $required = '7.4.0';
    $status = version_compare($version, $required, '>=');
    return [
        'name' => 'PHP Version',
        'status' => $status,
        'message' => "PHP $version" . ($status ? ' (OK)' : " (Required: $required or higher)"),
        'details' => "Installed: $version | Required: $required+"
    ];
}

// Test 2: Database Connection
function testDatabaseConnection() {
    global $db;
    try {
        $result = $db->fetchOne("SELECT 1 as test");
        return [
            'name' => 'Database Connection',
            'status' => true,
            'message' => 'Successfully connected to database',
            'details' => 'Host: ' . DB_HOST . ' | Database: ' . DB_NAME
        ];
    } catch (Exception $e) {
        return [
            'name' => 'Database Connection',
            'status' => false,
            'message' => 'Failed to connect',
            'details' => $e->getMessage()
        ];
    }
}

// Test 3: Database Tables
function testDatabaseTables() {
    global $db;
    $requiredTables = [
        'users', 'admin_users', 'products', 'product_images', 'categories',
        'bids', 'orders', 'shipping_rates', 'currency_rates', 'settings'
    ];
    
    $existingTables = [];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $result = $db->fetchOne("SHOW TABLES LIKE '$table'");
        if ($result) {
            $existingTables[] = $table;
        } else {
            $missingTables[] = $table;
        }
    }
    
    $status = empty($missingTables);
    return [
        'name' => 'Database Tables',
        'status' => $status,
        'message' => count($existingTables) . '/' . count($requiredTables) . ' tables found',
        'details' => $status ? 'All required tables exist' : 'Missing: ' . implode(', ', $missingTables)
    ];
}

// Test 4: Required PHP Extensions
function testPhpExtensions() {
    $required = ['pdo', 'pdo_mysql', 'mbstring', 'gd', 'curl', 'openssl'];
    $missing = [];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    
    $status = empty($missing);
    return [
        'name' => 'PHP Extensions',
        'status' => $status,
        'message' => (count($required) - count($missing)) . '/' . count($required) . ' extensions loaded',
        'details' => $status ? 'All required extensions available' : 'Missing: ' . implode(', ', $missing)
    ];
}

// Test 5: Upload Directory
function testUploadDirectory() {
    $uploadPath = UPLOAD_PATH;
    $productPath = UPLOAD_PATH . 'products/';
    
    $issues = [];
    
    if (!file_exists($uploadPath)) {
        $issues[] = "Upload directory doesn't exist: $uploadPath";
    } elseif (!is_writable($uploadPath)) {
        $issues[] = "Upload directory not writable: $uploadPath";
    }
    
    if (!file_exists($productPath)) {
        $issues[] = "Products directory doesn't exist: $productPath";
    } elseif (!is_writable($productPath)) {
        $issues[] = "Products directory not writable: $productPath";
    }
    
    $status = empty($issues);
    return [
        'name' => 'Upload Directories',
        'status' => $status,
        'message' => $status ? 'Upload directories OK' : count($issues) . ' issue(s) found',
        'details' => $status ? "Upload: $uploadPath\nProducts: $productPath" : implode("\n", $issues)
    ];
}

// Test 6: File Permissions
function testFilePermissions() {
    $files = ['config.php', 'init.php', 'index.php'];
    $writableFiles = [];
    
    foreach ($files as $file) {
        if (is_writable($file)) {
            $writableFiles[] = $file;
        }
    }
    
    // Config should NOT be writable in production
    $configWritable = is_writable('config.php');
    
    return [
        'name' => 'File Permissions',
        'status' => true,
        'message' => 'Permissions checked',
        'details' => $configWritable ? "⚠️ Warning: config.php is writable (should be 644 in production)" : "File permissions OK"
    ];
}

// Test 7: Session Functionality
function testSession() {
    $_SESSION['test_key'] = 'test_value';
    $works = isset($_SESSION['test_key']) && $_SESSION['test_key'] === 'test_value';
    unset($_SESSION['test_key']);
    
    return [
        'name' => 'Session Handling',
        'status' => $works,
        'message' => $works ? 'Sessions working' : 'Session error',
        'details' => $works ? 'Session read/write OK' : 'Cannot read/write session data'
    ];
}

// Test 8: Classes Load
function testClasses() {
    $classes = ['Database', 'Product', 'Bid', 'User', 'Admin'];
    $loaded = [];
    $missing = [];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            $loaded[] = $class;
        } else {
            $missing[] = $class;
        }
    }
    
    $status = empty($missing);
    return [
        'name' => 'PHP Classes',
        'status' => $status,
        'message' => count($loaded) . '/' . count($classes) . ' classes loaded',
        'details' => $status ? 'All classes available' : 'Missing: ' . implode(', ', $missing)
    ];
}

// Test 9: Database Data
function testDatabaseData() {
    global $db;
    
    $checks = [
        'categories' => $db->fetchOne("SELECT COUNT(*) as count FROM categories")['count'],
        'currency_rates' => $db->fetchOne("SELECT COUNT(*) as count FROM currency_rates")['count'],
        'shipping_rates' => $db->fetchOne("SELECT COUNT(*) as count FROM shipping_rates")['count'],
        'admin_users' => $db->fetchOne("SELECT COUNT(*) as count FROM admin_users")['count'],
        'products' => $db->fetchOne("SELECT COUNT(*) as count FROM products")['count'],
    ];
    
    $issues = [];
    if ($checks['categories'] == 0) $issues[] = "No categories found";
    if ($checks['currency_rates'] == 0) $issues[] = "No currency rates found";
    if ($checks['shipping_rates'] == 0) $issues[] = "No shipping rates found";
    if ($checks['admin_users'] == 0) $issues[] = "No admin users found";
    
    $status = empty($issues);
    $details = "Categories: {$checks['categories']}\n";
    $details .= "Currencies: {$checks['currency_rates']}\n";
    $details .= "Shipping Rates: {$checks['shipping_rates']}\n";
    $details .= "Admin Users: {$checks['admin_users']}\n";
    $details .= "Products: {$checks['products']}";
    
    if (!$status) {
        $details .= "\n\n⚠️ Issues:\n" . implode("\n", $issues);
    }
    
    return [
        'name' => 'Database Data',
        'status' => $status,
        'message' => $status ? 'Initial data OK' : count($issues) . ' issue(s) found',
        'details' => $details
    ];
}

// Test 10: Helper Functions
function testHelperFunctions() {
    $functions = ['escape', 'clean', 'formatPrice', 'formatDate', 'generateToken', 'calculateShipping'];
    $missing = [];
    
    foreach ($functions as $func) {
        if (!function_exists($func)) {
            $missing[] = $func;
        }
    }
    
    // Test a function
    $testPrice = formatPrice(1234.56, 'EUR');
    $priceWorks = !empty($testPrice);
    
    $status = empty($missing) && $priceWorks;
    return [
        'name' => 'Helper Functions',
        'status' => $status,
        'message' => count($functions) - count($missing) . '/' . count($functions) . ' functions available',
        'details' => $status ? "Test: formatPrice(1234.56) = $testPrice" : 'Missing: ' . implode(', ', $missing)
    ];
}

// Test 11: Email Configuration
function testEmailConfig() {
    $configured = defined('SMTP_HOST') && defined('SMTP_USERNAME') && !empty(SMTP_USERNAME);
    
    $details = "SMTP Host: " . (defined('SMTP_HOST') ? SMTP_HOST : 'Not set') . "\n";
    $details .= "SMTP Username: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'Not set') . "\n";
    $details .= "SMTP Port: " . (defined('SMTP_PORT') ? SMTP_PORT : 'Not set');
    
    if (!$configured) {
        $details .= "\n\n⚠️ Email not configured - bid confirmations won't work";
    }
    
    return [
        'name' => 'Email Configuration',
        'status' => $configured,
        'message' => $configured ? 'SMTP configured' : 'SMTP not configured',
        'details' => $details
    ];
}

// Test 12: PayPal Configuration
function testPayPalConfig() {
    $configured = defined('PAYPAL_EMAIL') && !empty(PAYPAL_EMAIL) && PAYPAL_EMAIL !== 'your_paypal_email@domain.com';
    
    $details = "PayPal Email: " . (defined('PAYPAL_EMAIL') ? PAYPAL_EMAIL : 'Not set') . "\n";
    $details .= "PayPal Mode: " . (defined('PAYPAL_MODE') ? PAYPAL_MODE : 'Not set');
    
    if (!$configured) {
        $details .= "\n\n⚠️ PayPal not configured - update PAYPAL_EMAIL in config.php";
    }
    
    return [
        'name' => 'PayPal Configuration',
        'status' => $configured,
        'message' => $configured ? 'PayPal configured' : 'PayPal not configured',
        'details' => $details
    ];
}

// Test 13: URL Configuration
function testUrlConfig() {
    $siteUrl = SITE_URL;
    $isHttps = strpos($siteUrl, 'https://') === 0;
    $isDefault = strpos($siteUrl, 'yourdomain.com') !== false;
    
    $details = "Site URL: $siteUrl\n";
    $details .= "Protocol: " . ($isHttps ? 'HTTPS ✓' : 'HTTP (⚠️ Use HTTPS in production)') . "\n";
    $details .= "Site Name: " . SITE_NAME . "\n";
    $details .= "Site Email: " . SITE_EMAIL;
    
    if ($isDefault) {
        $details .= "\n\n⚠️ Default URL detected - update SITE_URL in config.php";
    }
    
    return [
        'name' => 'URL Configuration',
        'status' => !$isDefault,
        'message' => $isDefault ? 'Using default URL' : 'URL configured',
        'details' => $details
    ];
}

// Test 14: Model Operations
function testModelOperations() {
    global $db;
    
    try {
        // Test Product model
        $productModel = new Product();
        $categories = $db->fetchAll("SELECT * FROM categories LIMIT 1");
        
        // Test Bid model
        $bidModel = new Bid();
        
        // Test User model
        $userModel = new User();
        
        return [
            'name' => 'Model Operations',
            'status' => true,
            'message' => 'All models initialized',
            'details' => 'Product, Bid, and User models working'
        ];
    } catch (Exception $e) {
        return [
            'name' => 'Model Operations',
            'status' => false,
            'message' => 'Model error',
            'details' => $e->getMessage()
        ];
    }
}

// Test 15: Security Settings
function testSecuritySettings() {
    $issues = [];
    $warnings = [];
    
    // Check if encryption key is default
    if (defined('ENCRYPTION_KEY') && ENCRYPTION_KEY === 'your-random-32-char-key-here-change-this') {
        $warnings[] = 'Default encryption key - change in config.php';
    }
    
    // Check error reporting
    if (ini_get('display_errors') == 1) {
        $warnings[] = 'Error display enabled - disable in production';
    }
    
    // Check admin password
    $adminCheck = $db->fetchOne("SELECT password_hash FROM admin_users LIMIT 1");
    if ($adminCheck && password_verify('admin123', $adminCheck['password_hash'])) {
        $issues[] = '🔴 CRITICAL: Default admin password detected!';
    }
    
    $status = empty($issues);
    $details = empty($issues) && empty($warnings) ? 'Security settings OK' : '';
    
    if (!empty($issues)) {
        $details .= "🔴 Critical Issues:\n" . implode("\n", $issues) . "\n\n";
    }
    if (!empty($warnings)) {
        $details .= "⚠️ Warnings:\n" . implode("\n", $warnings);
    }
    
    return [
        'name' => 'Security Settings',
        'status' => $status,
        'message' => $status ? 'Security OK' : count($issues) . ' critical issue(s)',
        'details' => $details ?: 'No security issues found'
    ];
}

// Run all tests
$tests[] = testPhpVersion();
$tests[] = testPhpExtensions();
$tests[] = testDatabaseConnection();
$tests[] = testDatabaseTables();
$tests[] = testDatabaseData();
$tests[] = testClasses();
$tests[] = testModelOperations();
$tests[] = testHelperFunctions();
$tests[] = testUploadDirectory();
$tests[] = testFilePermissions();
$tests[] = testSession();
$tests[] = testUrlConfig();
$tests[] = testEmailConfig();
$tests[] = testPayPalConfig();
$tests[] = testSecuritySettings();

// Count results
$passed = count(array_filter($tests, function($t) { return $t['status']; }));
$failed = count($tests) - $passed;
$allPassed = $failed === 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Test Results - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .summary-card .number {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .summary-card .label {
            color: #7f8c8d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .summary-card.success .number { color: #27ae60; }
        .summary-card.danger .number { color: #e74c3c; }
        .summary-card.info .number { color: #3498db; }
        .tests-grid {
            display: grid;
            gap: 20px;
        }
        .test-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            border-left: 4px solid #ddd;
        }
        .test-card.passed {
            border-left-color: #27ae60;
        }
        .test-card.failed {
            border-left-color: #e74c3c;
        }
        .test-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        .test-header:hover {
            background: #f8f9fa;
        }
        .test-name {
            font-weight: 600;
            font-size: 16px;
            color: #2c3e50;
        }
        .test-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.passed {
            background: #d4edda;
            color: #155724;
        }
        .status-badge.failed {
            background: #f8d7da;
            color: #721c24;
        }
        .test-details {
            padding: 0 20px 20px;
            display: none;
        }
        .test-details.show {
            display: block;
        }
        .test-message {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        .test-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            white-space: pre-wrap;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        .expand-icon {
            transition: transform 0.3s ease;
        }
        .expand-icon.rotated {
            transform: rotate(180deg);
        }
        .actions {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 5px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #c41e3a;
            color: white;
        }
        .btn-primary:hover {
            background: #a01829;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #229954;
        }
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-danger {
            background: #f8d7da;
            border-left-color: #e74c3c;
            color: #721c24;
        }
        .alert-warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .alert-success {
            background: #d4edda;
            border-left-color: #27ae60;
            color: #155724;
        }
        .footer {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            margin-top: 30px;
        }
        @media (max-width: 768px) {
            .summary {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 System Test Results</h1>
            <div class="subtitle"><?php echo SITE_NAME; ?> - <?php echo date('F j, Y g:i A'); ?></div>
        </div>

        <?php if ($allPassed): ?>
            <div class="alert alert-success">
                <strong>✅ All Tests Passed!</strong><br>
                Your system is configured correctly and ready to use. Remember to delete this test file before going to production.
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <strong>❌ <?php echo $failed; ?> Test<?php echo $failed > 1 ? 's' : ''; ?> Failed</strong><br>
                Please review the failed tests below and fix the issues before proceeding.
            </div>
        <?php endif; ?>

        <div class="summary">
            <div class="summary-card info">
                <div class="number"><?php echo count($tests); ?></div>
                <div class="label">Total Tests</div>
            </div>
            <div class="summary-card success">
                <div class="number"><?php echo $passed; ?></div>
                <div class="label">Passed</div>
            </div>
            <div class="summary-card danger">
                <div class="number"><?php echo $failed; ?></div>
                <div class="label">Failed</div>
            </div>
            <div class="summary-card <?php echo $allPassed ? 'success' : 'danger'; ?>">
                <div class="number"><?php echo round(($passed / count($tests)) * 100); ?>%</div>
                <div class="label">Success Rate</div>
            </div>
        </div>

        <div class="tests-grid">
            <?php foreach ($tests as $index => $test): ?>
                <div class="test-card <?php echo $test['status'] ? 'passed' : 'failed'; ?>">
                    <div class="test-header" onclick="toggleDetails(<?php echo $index; ?>)">
                        <div class="test-name"><?php echo $test['name']; ?></div>
                        <div class="test-status">
                            <span class="status-badge <?php echo $test['status'] ? 'passed' : 'failed'; ?>">
                                <?php echo $test['status'] ? '✓ Passed' : '✗ Failed'; ?>
                            </span>
                            <span class="expand-icon" id="icon-<?php echo $index; ?>">▼</span>
                        </div>
                    </div>
                    <div class="test-details" id="details-<?php echo $index; ?>">
                        <div class="test-message"><?php echo $test['message']; ?></div>
                        <div class="test-info"><?php echo htmlspecialchars($test['details']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="actions">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">Quick Actions</h3>
            <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">View Site</a>
            <a href="<?php echo SITE_URL; ?>/admin/" class="btn btn-secondary">Admin Panel</a>
            <a href="javascript:window.location.reload();" class="btn btn-success">Re-run Tests</a>
        </div>

        <div class="footer">
            <p><strong>⚠️ Security Warning:</strong> Delete this test file (test.php) before going to production!</p>
            <p style="margin-top: 10px; font-size: 12px;">File location: <?php echo __FILE__; ?></p>
        </div>
    </div>

    <script>
        function toggleDetails(index) {
            const details = document.getElementById('details-' + index);
            const icon = document.getElementById('icon-' + index);
            
            if (details.classList.contains('show')) {
                details.classList.remove('show');
                icon.classList.remove('rotated');
            } else {
                details.classList.add('show');
                icon.classList.add('rotated');
            }
        }

        // Auto-expand failed tests
        window.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($tests as $index => $test): ?>
                <?php if (!$test['status']): ?>
                    toggleDetails(<?php echo $index; ?>);
                <?php endif; ?>
            <?php endforeach; ?>
        });
    </script>
</body>
</html>
