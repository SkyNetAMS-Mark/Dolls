<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Verificatie - Reborn Dolls</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .stat.success .stat-number { color: #27ae60; }
        .stat.error .stat-number { color: #e74c3c; }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            font-size: 20px;
        }
        .file-list {
            display: grid;
            gap: 5px;
        }
        .file-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 5px;
            gap: 10px;
        }
        .file-item.found {
            background: #d4edda;
        }
        .file-item.missing {
            background: #f8d7da;
        }
        .file-icon {
            font-size: 20px;
        }
        .file-name {
            flex: 1;
            font-family: monospace;
            font-size: 14px;
        }
        .file-status {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
        }
        .file-status.found {
            background: #155724;
            color: white;
        }
        .file-status.missing {
            background: #721c24;
            color: white;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 5px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📁 File Verificatie</h1>
        <div class="subtitle">Checking alle 40 bestanden...</div>

        <?php
        $rootPath = __DIR__;
        
        // Define all required files
        $requiredFiles = [
            // Root PHP files
            'about.php',
            'account.php',
            'config.php',
            'confirm-bid.php',
            'db-test.php',
            'index.php',
            'init.php',
            'login.php',
            'logout.php',
            'product.php',
            'products.php',
            'register.php',
            'setup.php',
            'test.php',
            
            // Admin files
            'admin/bids.php',
            'admin/footer.php',
            'admin/header.php',
            'admin/index.php',
            'admin/login.php',
            'admin/logout.php',
            'admin/products.php',
            
            // Class files
            'classes/Bid.php',
            'classes/Database.php',
            'classes/Product.php',
            'classes/User.php',
            
            // Includes
            'includes/footer.php',
            'includes/functions.php',
            'includes/header.php',
            
            // Assets
            'assets/css/style.css',
            'assets/js/main.js',
            
            // Documentation
            'README.md',
            'QUICK_START.md',
            'TROUBLESHOOTING.md',
            'TESTING_GUIDE.md',
            'QUICK_FIX.md',
            'FILE_LIST.md',
            
            // Database
            'database.sql',
        ];
        
        $found = [];
        $missing = [];
        
        foreach ($requiredFiles as $file) {
            if (file_exists($rootPath . '/' . $file)) {
                $found[] = $file;
            } else {
                $missing[] = $file;
            }
        }
        
        $totalFiles = count($requiredFiles);
        $foundCount = count($found);
        $missingCount = count($missing);
        $percentage = round(($foundCount / $totalFiles) * 100);
        ?>

        <div class="stats">
            <div class="stat <?php echo $foundCount == $totalFiles ? 'success' : 'error'; ?>">
                <div class="stat-number"><?php echo $foundCount; ?>/<?php echo $totalFiles; ?></div>
                <div class="stat-label">Bestanden Gevonden</div>
            </div>
            <div class="stat <?php echo $missingCount == 0 ? 'success' : 'error'; ?>">
                <div class="stat-number"><?php echo $missingCount; ?></div>
                <div class="stat-label">Bestanden Missen</div>
            </div>
            <div class="stat <?php echo $percentage == 100 ? 'success' : 'error'; ?>">
                <div class="stat-number"><?php echo $percentage; ?>%</div>
                <div class="stat-label">Complete</div>
            </div>
        </div>

        <?php if ($missingCount == 0): ?>
            <div class="alert alert-success">
                <strong>✅ Perfect! Alle 40 bestanden zijn aanwezig!</strong><br>
                Je kunt nu verder met setup.php voor configuratie check.
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <strong>❌ <?php echo $missingCount; ?> bestand(en) ontbreken</strong><br>
                Upload de ontbrekende bestanden en refresh deze pagina.
            </div>
        <?php endif; ?>

        <?php if (!empty($missing)): ?>
        <div class="section">
            <h2>❌ Ontbrekende Bestanden (<?php echo count($missing); ?>)</h2>
            <div class="file-list">
                <?php foreach ($missing as $file): ?>
                    <div class="file-item missing">
                        <span class="file-icon">❌</span>
                        <span class="file-name"><?php echo htmlspecialchars($file); ?></span>
                        <span class="file-status missing">ONTBREEKT</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($found)): ?>
        <div class="section">
            <h2>✅ Gevonden Bestanden (<?php echo count($found); ?>)</h2>
            <div class="file-list">
                <?php foreach ($found as $file): ?>
                    <div class="file-item found">
                        <span class="file-icon">✅</span>
                        <span class="file-name"><?php echo htmlspecialchars($file); ?></span>
                        <span class="file-status found">GEVONDEN</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>📋 Wat Nu?</h2>
            <?php if ($missingCount > 0): ?>
                <ol style="line-height: 2; padding-left: 20px;">
                    <li><strong>Upload de ontbrekende bestanden</strong> via FTP</li>
                    <li><strong>Check FILE_LIST.md</strong> voor volledige lijst</li>
                    <li><strong>Refresh deze pagina</strong> om opnieuw te checken</li>
                    <li><strong>Als alles groen:</strong> Ga naar setup.php</li>
                </ol>
            <?php else: ?>
                <ol style="line-height: 2; padding-left: 20px;">
                    <li><strong>Alle files aanwezig!</strong> ✅</li>
                    <li><strong>Run setup.php</strong> voor configuratie check</li>
                    <li><strong>Run test.php</strong> voor volledige systeem test</li>
                    <li><strong>Verwijder verify.php</strong> na verificatie</li>
                </ol>
            <?php endif; ?>
        </div>

        <div class="actions">
            <a href="javascript:window.location.reload();" class="btn">↻ Refresh Check</a>
            <?php if ($missingCount == 0): ?>
                <a href="setup.php" class="btn">→ Ga naar Setup</a>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 8px; text-align: center; font-size: 14px;">
            <strong>💡 Tip:</strong> Zie FILE_LIST.md voor volledige uitleg over alle bestanden
        </div>
    </div>
</body>
</html>
