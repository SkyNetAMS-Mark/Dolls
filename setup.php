<?php
/**
 * SETUP & INSTALLATION CHECKER
 * 
 * This script checks your installation and creates missing directories.
 * Run this FIRST before running test.php
 * 
 * Access: https://yourdomain.com/setup.php
 * DELETE this file after successful setup!
 */

// Don't require anything - this must run standalone
session_start();

// Simple password protection
$SETUP_PASSWORD = 'setup123';

if (!isset($_POST['password']) && !isset($_SESSION['setup_authenticated'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Setup - Password Required</title>
        <style>
            body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-box { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 400px; }
            h1 { margin: 0 0 20px; color: #333; }
            input[type="password"] { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; margin: 10px 0; font-size: 16px; box-sizing: border-box; }
            button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: 600; }
            button:hover { background: #5568d3; }
            .warning { background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>🔧 Setup Installer</h1>
            <div class="warning">
                <strong>⚠️ First-Time Setup</strong><br>
                This will check and fix your installation.
            </div>
            <form method="POST">
                <label>Enter Password:</label>
                <input type="password" name="password" placeholder="Default: setup123" required autofocus>
                <button type="submit">Start Setup</button>
            </form>
            <p style="margin-top: 20px; color: #666; font-size: 14px;">
                Default password: <code>setup123</code><br>
                Change on line 13 of setup.php
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_POST['password']) && $_POST['password'] === $SETUP_PASSWORD) {
    $_SESSION['setup_authenticated'] = true;
} elseif (!isset($_SESSION['setup_authenticated'])) {
    die('Invalid password');
}

// Start setup
$rootPath = __DIR__;
$errors = [];
$warnings = [];
$success = [];
$actions = [];

// Define required structure
$requiredDirs = [
    'admin',
    'assets',
    'assets/css',
    'assets/js',
    'assets/images',
    'classes',
    'includes',
    'uploads',
    'uploads/products',
];

$requiredFiles = [
    'config.php',
    'init.php',
    'database.sql',
    'index.php',
    'products.php',
    'product.php',
    'about.php',
    'login.php',
    'register.php',
    'logout.php',
    'account.php',
    'confirm-bid.php',
    'admin/index.php',
    'admin/login.php',
    'admin/logout.php',
    'admin/header.php',
    'admin/footer.php',
    'admin/products.php',
    'admin/bids.php',
    'classes/DatabaseModel.php',
    'classes/ProductModel.php',
    'classes/BidModel.php',
    'classes/UserModel.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/functions.php',
    'assets/css/style.css',
    'assets/js/main.js',
];

// Check and create directories
foreach ($requiredDirs as $dir) {
    $fullPath = $rootPath . '/' . $dir;
    
    if (!file_exists($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            $success[] = "Created directory: $dir";
            $actions[] = "mkdir $dir";
        } else {
            $errors[] = "Failed to create directory: $dir";
        }
    } else {
        if (!is_writable($fullPath) && in_array($dir, ['uploads', 'uploads/products'])) {
            if (chmod($fullPath, 0755)) {
                $warnings[] = "Fixed permissions for: $dir";
                $actions[] = "chmod 755 $dir";
            } else {
                $warnings[] = "Cannot write to: $dir (manual fix needed)";
            }
        }
    }
}

// Check required files
$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists($rootPath . '/' . $file)) {
        $missingFiles[] = $file;
    }
}

// Check if config.php is configured
$configStatus = 'not_checked';
if (file_exists($rootPath . '/config.php')) {
    $configContent = file_get_contents($rootPath . '/config.php');
    
    if (strpos($configContent, 'your_db_username') !== false || 
        strpos($configContent, 'your_database_name') !== false) {
        $warnings[] = "config.php contains default values - needs configuration";
        $configStatus = 'needs_config';
    } else {
        $success[] = "config.php appears to be configured";
        $configStatus = 'configured';
    }
}

// Try to check database connection if config is set
$dbStatus = 'not_checked';
if ($configStatus === 'configured' && file_exists($rootPath . '/config.php')) {
    try {
        require_once $rootPath . '/config.php';
        
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $success[] = "Database connection successful";
        $dbStatus = 'connected';
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) == 0) {
            $warnings[] = "Database is empty - need to import database.sql";
        } elseif (count($tables) < 10) {
            $warnings[] = "Database has " . count($tables) . " tables - should have 10";
        } else {
            $success[] = "Database has " . count($tables) . " tables";
        }
        
    } catch (Exception $e) {
        $errors[] = "Database connection failed: " . $e->getMessage();
        $dbStatus = 'failed';
    }
}

// System info
$phpVersion = phpversion();
$phpOK = version_compare($phpVersion, '7.4.0', '>=');

$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'curl'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

// Calculate status
$criticalIssues = count($errors);
$minorIssues = count($warnings);
$totalChecks = count($requiredDirs) + count($requiredFiles) + 5; // +5 for db, config, php, etc
$passedChecks = count($success);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup & Installation Checker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .status-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            text-align: center;
        }
        .status-card .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .status-card .number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .status-card .label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .status-card.success .number { color: #27ae60; }
        .status-card.error .number { color: #e74c3c; }
        .status-card.warning .number { color: #f39c12; }
        .section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .message-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .message-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #229954;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .actions {
            text-align: center;
            margin-top: 20px;
        }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #27ae60 0%, #2ecc71 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            transition: width 0.5s ease;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-error { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Setup & Installation Checker</h1>
            <div class="subtitle">Checking your Reborn Dolls installation...</div>
        </div>

        <div class="status-grid">
            <div class="status-card <?php echo $criticalIssues == 0 ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo $criticalIssues == 0 ? '✅' : '❌'; ?></div>
                <div class="number"><?php echo $criticalIssues; ?></div>
                <div class="label">Critical Issues</div>
            </div>
            
            <div class="status-card <?php echo $minorIssues == 0 ? 'success' : 'warning'; ?>">
                <div class="icon"><?php echo $minorIssues == 0 ? '✅' : '⚠️'; ?></div>
                <div class="number"><?php echo $minorIssues; ?></div>
                <div class="label">Warnings</div>
            </div>
            
            <div class="status-card success">
                <div class="icon">✅</div>
                <div class="number"><?php echo count($success); ?></div>
                <div class="label">Passed</div>
            </div>
            
            <div class="status-card <?php echo count($missingFiles) == 0 ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo count($missingFiles) == 0 ? '✅' : '❌'; ?></div>
                <div class="number"><?php echo count($requiredFiles) - count($missingFiles); ?>/<?php echo count($requiredFiles); ?></div>
                <div class="label">Files Present</div>
            </div>
        </div>

        <?php
        $completionPercent = ($passedChecks / $totalChecks) * 100;
        ?>
        <div class="section">
            <h2>Setup Progress</h2>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $completionPercent; ?>%">
                    <?php echo round($completionPercent); ?>%
                </div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="section">
            <h2>🔴 Critical Errors (Must Fix)</h2>
            <?php foreach ($errors as $error): ?>
                <div class="message message-error">
                    <span>❌</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($warnings)): ?>
        <div class="section">
            <h2>⚠️ Warnings (Should Fix)</h2>
            <?php foreach ($warnings as $warning): ?>
                <div class="message message-warning">
                    <span>⚠️</span>
                    <span><?php echo htmlspecialchars($warning); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="section">
            <h2>✅ Success</h2>
            <?php foreach ($success as $msg): ?>
                <div class="message message-success">
                    <span>✅</span>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>📋 System Information</h2>
            <table>
                <tr>
                    <th>Check</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td>
                        <?php if ($phpOK): ?>
                            <span class="badge badge-success">OK</span>
                        <?php else: ?>
                            <span class="badge badge-error">Failed</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $phpVersion; ?> (Required: 7.4+)</td>
                </tr>
                <tr>
                    <td>PHP Extensions</td>
                    <td>
                        <?php if (empty($missingExtensions)): ?>
                            <span class="badge badge-success">OK</span>
                        <?php else: ?>
                            <span class="badge badge-error">Missing</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (empty($missingExtensions)): ?>
                            All required extensions loaded
                        <?php else: ?>
                            Missing: <?php echo implode(', ', $missingExtensions); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Config File</td>
                    <td>
                        <?php if ($configStatus === 'configured'): ?>
                            <span class="badge badge-success">OK</span>
                        <?php elseif ($configStatus === 'needs_config'): ?>
                            <span class="badge badge-warning">Needs Setup</span>
                        <?php else: ?>
                            <span class="badge badge-error">Not Found</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($configStatus === 'configured'): ?>
                            Configured
                        <?php elseif ($configStatus === 'needs_config'): ?>
                            Contains default values
                        <?php else: ?>
                            Not found
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Database</td>
                    <td>
                        <?php if ($dbStatus === 'connected'): ?>
                            <span class="badge badge-success">OK</span>
                        <?php elseif ($dbStatus === 'failed'): ?>
                            <span class="badge badge-error">Failed</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Not Checked</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($dbStatus === 'connected'): ?>
                            Connected successfully
                        <?php elseif ($dbStatus === 'failed'): ?>
                            Connection failed
                        <?php else: ?>
                            Config needed first
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Directories</td>
                    <td><span class="badge badge-success">OK</span></td>
                    <td><?php echo count($requiredDirs); ?> directories checked/created</td>
                </tr>
                <tr>
                    <td>Files</td>
                    <td>
                        <?php if (count($missingFiles) == 0): ?>
                            <span class="badge badge-success">OK</span>
                        <?php else: ?>
                            <span class="badge badge-error">Missing</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo count($requiredFiles) - count($missingFiles); ?> / <?php echo count($requiredFiles); ?> files present</td>
                </tr>
            </table>
        </div>

        <?php if (!empty($missingFiles)): ?>
        <div class="section">
            <h2>📁 Missing Files</h2>
            <p style="color: #e74c3c; margin-bottom: 15px;">
                <strong>These files are missing. Please upload them:</strong>
            </p>
            <div class="code-block">
<?php foreach ($missingFiles as $file): ?>
<?php echo htmlspecialchars($file); ?>

<?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($actions)): ?>
        <div class="section">
            <h2>🔧 Actions Performed</h2>
            <div class="code-block">
<?php foreach ($actions as $action): ?>
<?php echo htmlspecialchars($action); ?>

<?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>📝 Next Steps</h2>
            
            <?php if ($criticalIssues > 0): ?>
                <div class="message message-error">
                    <span>❌</span>
                    <span><strong>Fix critical errors first!</strong> Upload missing files and check configuration.</span>
                </div>
            <?php elseif (!empty($missingFiles)): ?>
                <div class="message message-error">
                    <span>📁</span>
                    <span><strong>Upload missing files</strong> listed above, then refresh this page.</span>
                </div>
            <?php elseif ($configStatus !== 'configured'): ?>
                <div class="message message-warning">
                    <span>⚙️</span>
                    <span><strong>Configure config.php</strong> with your database credentials.</span>
                </div>
            <?php elseif ($dbStatus !== 'connected'): ?>
                <div class="message message-warning">
                    <span>🗄️</span>
                    <span><strong>Fix database connection</strong> - check credentials in config.php.</span>
                </div>
            <?php else: ?>
                <div class="message message-success">
                    <span>✅</span>
                    <span><strong>Setup looks good!</strong> Ready to run test.php for full system check.</span>
                </div>
            <?php endif; ?>

            <ol style="margin: 20px 0; padding-left: 20px; line-height: 2;">
                <li><strong>Upload all files</strong> - Make sure you uploaded ALL files from the project</li>
                <li><strong>Configure config.php</strong> - Update database credentials</li>
                <li><strong>Import database.sql</strong> - Import via phpMyAdmin</li>
                <li><strong>Re-run this setup</strong> - Refresh to check again</li>
                <li><strong>Run test.php</strong> - Complete system test</li>
                <li><strong>Delete setup.php</strong> - Remove this file when done</li>
            </ol>
        </div>

        <div class="section actions">
            <h3 style="margin-bottom: 20px;">Quick Actions</h3>
            <a href="javascript:window.location.reload();" class="btn btn-primary">↻ Refresh / Re-check</a>
            <?php if ($criticalIssues == 0 && empty($missingFiles)): ?>
                <a href="test.php" class="btn btn-success">→ Run Full Test</a>
            <?php endif; ?>
            <a href="db-test.php" class="btn btn-primary">🗄️ Test Database</a>
        </div>

        <div style="background: white; padding: 20px; border-radius: 15px; margin-top: 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.2);">
            <p style="color: #e74c3c; font-weight: 600;">⚠️ Delete setup.php after installation is complete!</p>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">File location: <?php echo __FILE__; ?></p>
        </div>
    </div>
</body>
</html>
