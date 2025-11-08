<?php
require_once '../init.php';

if (isAdminLoggedIn()) {
    redirect('/admin/');
}

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminModel = new Admin();
    
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please provide both username and password";
    } else {
        $admin = $adminModel->login($username, $password);
        
        if ($admin) {
            redirect('/admin/');
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: var(--border-radius); box-shadow: var(--shadow-lg); width: 100%; max-width: 400px;">
        <h1 style="text-align: center; margin-bottom: 10px; color: var(--secondary-color);">Admin Login</h1>
        <p style="text-align: center; margin-bottom: 30px; color: var(--text-light); font-size: 14px;"><?php echo SITE_NAME; ?></p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" 
                       name="username" 
                       value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>" 
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 14px;">
            <a href="<?php echo SITE_URL; ?>/" style="color: var(--primary-color);">← Back to Site</a>
        </p>
    </div>
</body>
</html>
