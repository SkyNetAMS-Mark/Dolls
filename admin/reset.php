<?php
echo "<style>body { font-family: sans-serif; padding: 20px; background: #f4f4f4; line-height: 1.6; } .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); } h1 { color: #c41e3a; } .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; } .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; } code { background: #eee; padding: 2px 5px; border-radius: 3px; }</style>";
echo "<div class='container'>";

// This is the password you want to set
$new_password = 'admin123';
$username_to_reset = 'admin';

echo "<h1>Admin Password Reset</h1>";
echo "<p>This script will reset the password for user <code>{$username_to_reset}</code> to <code>{$new_password}</code>.</p><hr>";

try {
    // --- STEP 1: Find config.php ---
    // This new code will check for config.php in the current folder,
    // and one level up, to work from /admin/ or the root folder.
    $config_path = false;
    if (file_exists(__DIR__ . '/../config.php')) {
        $config_path = __DIR__ . '/../config.php';
        echo "<p>Detected script is in a subfolder (e.g., /admin/). Found config.php one level up.</p>";
    } elseif (file_exists(__DIR__ . '/config.php')) {
        $config_path = __DIR__ . '/config.php';
        echo "<p>Detected script is in root. Found config.php in current folder.</p>";
    }
    
    if (!$config_path) {
        throw new Exception("Could not find config.php. Please place this reset script in the root 'dolls' folder or the '/admin/' folder.");
    }
    
    require_once $config_path;
    echo "<p>Successfully loaded <code>config.php</code>.</p>";

    // --- STEP 2: Find Database Class ---
    // We will use the same logic to find the database class
    $db_class_path = false;
    if (file_exists(__DIR__ . '/../classes/DatabaseModel.php')) {
        $db_class_path = __DIR__ . '/../classes/DatabaseModel.php';
    } elseif (file_exists(__DIR__ . '/classes/DatabaseModel.php')) {
        $db_class_path = __DIR__ . '/classes/DatabaseModel.php';
    } elseif (file_exists(__DIR__ . '/../Database.php')) {
        $db_class_path = __DIR__ . '/../Database.php';
    } elseif (file_exists(__DIR__ . '/Database.php')) {
        $db_class_path = __DIR__ . '/Database.php';
    }

    if (!$db_class_path) {
        throw new Exception("Could not find a valid Database class file (DatabaseModel.php or Database.php).");
    }
    
    require_once $db_class_path;
    echo "<p>Loaded Database class successfully.</p>";


    // --- STEP 3: Hash new password ---
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    if (!$new_hash) {
        throw new Exception("Failed to hash password. Check your PHP password_hash() function.");
    }
    echo "<p>New password hash generated successfully.</p>";

    // --- STEP 4: Connect to DB ---
    $db = Database::getInstance();
    if (!$db) {
        throw new Exception("Database::getInstance() failed. Check your config.php credentials.");
    }
    echo "<p>Database connection successful.</p>";

    // --- STEP 5: Update Password ---
    $data = ['password_hash' => $new_hash];
    $where = 'username = :username';
    $whereParams = ['username' => $username_to_reset];

    $result = $db->update('admin_users', $data, $where, $whereParams);

    if ($result) {
        echo "<div class'success'>";
        echo "<strong>SUCCESS!</strong><br>";
        echo "The password for user <code>{$username_to_reset}</code> has been reset to <code>{$new_password}</code>.";
        echo "</div>";
        echo "<p style='margin-top: 20px;'>You should now be able to log in at <a href='admin/login.php'>admin/login.php</a>.</p>";
    } else {
        throw new Exception("Database update query failed. The user <code>{$username_to_reset}</code> might not exist in the <code>admin_users</code> table.");
    }

} catch (Throwable $t) {
    echo "<div class='error'>";
    echo "<strong>An error occurred:</strong><br>";
    echo $t->getMessage();
    echo "</div>";
}

echo "<hr><p style='color: #c41e3a; font-weight: bold; margin-top: 20px;'>⚠️ PLEASE DELETE THIS FILE (admin_reset.php) IMMEDIATELY!</p>";
echo "</div>";
?>