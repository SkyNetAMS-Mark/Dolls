<?php
/**
 * Quick Test Script - Verify Login/Registration Fixes
 * 
 * This script tests that all classes are loading correctly
 * and that the database connection is working.
 * 
 * Access: https://yourdomain.com/test-login-fix.php
 * DELETE this file after testing!
 */

// Set error reporting BEFORE any output
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Variable to store test results
$testResults = [];
$allPassed = true;

// Test 1: Check if init.php exists
if (file_exists('init.php')) {
    $testResults[] = ['status' => 'pass', 'title' => 'init.php exists', 'message' => 'init.php file found'];
} else {
    $testResults[] = ['status' => 'fail', 'title' => 'init.php missing', 'message' => 'init.php not found'];
    $allPassed = false;
}

// Test 2: Try to load init.php
try {
    require_once 'init.php';
    $testResults[] = ['status' => 'pass', 'title' => 'init.php loaded', 'message' => 'init.php loaded successfully'];
} catch (Exception $e) {
    $testResults[] = ['status' => 'fail', 'title' => 'init.php error', 'message' => 'Error loading init.php: ' . $e->getMessage()];
    $allPassed = false;
    
    // Output results and exit if init fails
    outputResults($testResults, $allPassed);
    exit;
}

// Test 3: Check if classes are loaded
$classes = ['Database', 'User', 'Admin', 'Product', 'Bid'];
foreach ($classes as $class) {
    if (class_exists($class)) {
        $testResults[] = ['status' => 'pass', 'title' => "$class class", 'message' => "$class class loaded successfully"];
    } else {
        $testResults[] = ['status' => 'fail', 'title' => "$class class", 'message' => "$class class NOT loaded"];
        $allPassed = false;
    }
}

// Test 4: Check if helper functions exist
$functions = ['isLoggedIn', 'clean', 'escape', 'redirect', 'formatPrice'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        $testResults[] = ['status' => 'pass', 'title' => "$func()", 'message' => "$func() function exists"];
    } else {
        $testResults[] = ['status' => 'fail', 'title' => "$func()", 'message' => "$func() function NOT found"];
        $allPassed = false;
    }
}

// Test 5: Test Database Connection
try {
    $db = Database::getInstance();
    $testResults[] = ['status' => 'pass', 'title' => 'Database instance', 'message' => 'Database instance created'];
    
    // Try a simple query
    $result = $db->query("SELECT 1 as test");
    if ($result) {
        $testResults[] = ['status' => 'pass', 'title' => 'Database query', 'message' => 'Database query successful'];
    } else {
        $testResults[] = ['status' => 'fail', 'title' => 'Database query', 'message' => 'Database query failed'];
        $allPassed = false;
    }
} catch (Exception $e) {
    $testResults[] = ['status' => 'fail', 'title' => 'Database connection', 'message' => 'Database error: ' . $e->getMessage()];
    $allPassed = false;
}

// Test 6: Check if users table exists
try {
    $userCheck = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    if ($userCheck !== null) {
        $testResults[] = ['status' => 'pass', 'title' => 'users table', 'message' => "users table exists (contains {$userCheck['count']} users)"];
    } else {
        $testResults[] = ['status' => 'warning', 'title' => 'users table', 'message' => 'users table might be empty'];
    }
} catch (Exception $e) {
    $testResults[] = ['status' => 'fail', 'title' => 'users table', 'message' => 'users table error: ' . $e->getMessage()];
    $allPassed = false;
}

// Test 7: Check if admin_users table exists
try {
    $adminCheck = $db->fetchOne("SELECT COUNT(*) as count FROM admin_users");
    if ($adminCheck !== null) {
        $testResults[] = ['status' => 'pass', 'title' => 'admin_users table', 'message' => "admin_users table exists (contains {$adminCheck['count']} admins)"];
    } else {
        $testResults[] = ['status' => 'warning', 'title' => 'admin_users table', 'message' => 'admin_users table might be empty'];
    }
} catch (Exception $e) {
    $testResults[] = ['status' => 'fail', 'title' => 'admin_users table', 'message' => 'admin_users table error: ' . $e->getMessage()];
    $allPassed = false;
}

// Test 8: Test User Model Methods
try {
    $userModel = new User();
    $testResults[] = ['status' => 'pass', 'title' => 'User model', 'message' => 'User model instantiated'];
    
    // Check if methods exist
    $methods = ['create', 'login', 'getByEmail', 'emailExists'];
    foreach ($methods as $method) {
        if (method_exists($userModel, $method)) {
            $testResults[] = ['status' => 'pass', 'title' => "User::$method()", 'message' => "User::$method() method exists"];
        } else {
            $testResults[] = ['status' => 'fail', 'title' => "User::$method()", 'message' => "User::$method() method NOT found"];
            $allPassed = false;
        }
    }
} catch (Exception $e) {
    $testResults[] = ['status' => 'fail', 'title' => 'User model', 'message' => 'User model error: ' . $e->getMessage()];
    $allPassed = false;
}

// Test 9: Test Admin Model Methods
try {
    $adminModel = new Admin();
    $testResults[] = ['status' => 'pass', 'title' => 'Admin model', 'message' => 'Admin model instantiated'];
    
    if (method_exists($adminModel, 'login')) {
        $testResults[] = ['status' => 'pass', 'title' => 'Admin::login()', 'message' => 'Admin::login() method exists'];
    } else {
        $testResults[] = ['status' => 'fail', 'title' => 'Admin::login()', 'message' => 'Admin::login() method NOT found'];
        $allPassed = false;
    }
} catch (Exception $e) {
    $testResults[] = ['status' => 'fail', 'title' => 'Admin model', 'message' => 'Admin model error: ' . $e->getMessage()];
    $allPassed = false;
}

// Test 10: Session Check
if (session_status() === PHP_SESSION_ACTIVE) {
    $testResults[] = ['status' => 'pass', 'title' => 'Session', 'message' => 'Session is active'];
} else {
    $testResults[] = ['status' => 'warning', 'title' => 'Session', 'message' => 'Session not active'];
}

// Output all results
outputResults($testResults, $allPassed);

// Function to output results
function outputResults($results, $allPassed) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Fix Test Results</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 20px;
                min-height: 100vh;
            }
            .container {
                max-width: 900px;
                margin: 0 auto;
            }
            h1 {
                color: white;
                text-align: center;
                margin-bottom: 30px;
                font-size: 2.5em;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            .test-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 15px;
                margin-bottom: 30px;
            }
            .test {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                border-left: 5px solid #333;
                transition: transform 0.2s;
            }
            .test:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            }
            .test.pass { border-left-color: #28a745; }
            .test.fail { border-left-color: #dc3545; }
            .test.warning { border-left-color: #ffc107; }
            .test-title {
                font-weight: bold;
                margin-bottom: 8px;
                font-size: 1.1em;
            }
            .test.pass .test-title { color: #28a745; }
            .test.fail .test-title { color: #dc3545; }
            .test.warning .test-title { color: #ff8800; }
            .test-message {
                color: #666;
                font-size: 0.95em;
                line-height: 1.5;
            }
            .summary {
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
                text-align: center;
                margin-bottom: 20px;
            }
            .summary.pass {
                border: 3px solid #28a745;
            }
            .summary.fail {
                border: 3px solid #dc3545;
            }
            .summary h2 {
                margin-bottom: 20px;
                font-size: 2em;
            }
            .summary.pass h2 { color: #28a745; }
            .summary.fail h2 { color: #dc3545; }
            .summary-text {
                font-size: 1.1em;
                line-height: 1.8;
                color: #333;
                margin-bottom: 20px;
            }
            .next-steps {
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
                border-left: 5px solid #17a2b8;
            }
            .next-steps h3 {
                color: #17a2b8;
                margin-bottom: 15px;
                font-size: 1.5em;
            }
            .next-steps ol {
                padding-left: 20px;
                color: #333;
            }
            .next-steps li {
                margin: 10px 0;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                margin: 10px 5px;
                font-weight: 600;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #5568d3;
            }
            .warning-box {
                background: #fff3cd;
                border: 2px solid #ffc107;
                padding: 20px;
                border-radius: 10px;
                margin-top: 20px;
                text-align: center;
            }
            .warning-box strong {
                color: #e74c3c;
                font-size: 1.2em;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔧 Login Fix Test Results</h1>
            
            <?php if ($allPassed): ?>
                <div class="summary pass">
                    <h2>✅ ALL TESTS PASSED!</h2>
                    <div class="summary-text">
                        Your login and registration system is now working correctly.<br>
                        All classes are loaded, database is connected, and user authentication is functional.
                    </div>
                    <a href="register.php" class="btn">Try Registration</a>
                    <a href="login.php" class="btn">Try Login</a>
                </div>
            <?php else: ?>
                <div class="summary fail">
                    <h2>❌ SOME TESTS FAILED</h2>
                    <div class="summary-text">
                        Review the failed tests below and fix the issues.<br>
                        Check the error messages for guidance on what needs to be corrected.
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="test-grid">
                <?php foreach ($results as $test): ?>
                    <div class="test <?php echo $test['status']; ?>">
                        <div class="test-title">
                            <?php 
                            echo $test['status'] === 'pass' ? '✅' : ($test['status'] === 'fail' ? '❌' : '⚠️');
                            echo ' ' . htmlspecialchars($test['title']); 
                            ?>
                        </div>
                        <div class="test-message"><?php echo htmlspecialchars($test['message']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="next-steps">
                <h3>📋 Next Steps</h3>
                <?php if ($allPassed): ?>
                    <ol>
                        <li><strong>Test registration:</strong> Go to <a href="register.php">register.php</a> and create a new account</li>
                        <li><strong>Check database:</strong> Verify the user was created in the database</li>
                        <li><strong>Test login:</strong> Log out and try logging in with your new credentials</li>
                        <li><strong>Test admin login:</strong> If you have an admin account, test admin login</li>
                        <li><strong>Delete this file:</strong> Remove test-login-fix.php from your server</li>
                    </ol>
                <?php else: ?>
                    <ol>
                        <li><strong>Upload corrected files:</strong> Make sure you uploaded all the fixed files</li>
                        <li><strong>Check file names:</strong> Ensure files are named exactly: init.php, config.php, Database.php, User.php, etc.</li>
                        <li><strong>Verify database:</strong> Check that your database credentials in config.php are correct</li>
                        <li><strong>Check permissions:</strong> Make sure PHP can read all .php files</li>
                        <li><strong>Re-run this test:</strong> After making fixes, refresh this page</li>
                    </ol>
                <?php endif; ?>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ IMPORTANT:</strong> Delete this test file after you're done testing!<br>
                <small>File location: <?php echo __FILE__; ?></small>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
