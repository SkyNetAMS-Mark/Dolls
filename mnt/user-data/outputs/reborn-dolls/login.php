<?php
require_once 'init.php';

if (isLoggedIn()) {
    redirect('/account.php');
}

$pageTitle = 'Login';
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User();
    
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please provide both email and password";
    } else {
        $user = $userModel->login($email, $password);
        
        if ($user) {
            $returnUrl = isset($_GET['return']) ? $_GET['return'] : '/account.php';
            redirect($returnUrl);
        } else {
            $error = "Invalid email or password";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container" style="margin: 80px auto; max-width: 450px;">
    <div style="background: white; padding: 40px; border-radius: var(--border-radius); box-shadow: var(--shadow);">
        <h1 style="text-align: center; margin-bottom: 30px; color: var(--secondary-color);">Login</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" 
                       name="email" 
                       value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>" 
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: var(--text-light);">
            Don't have an account? 
            <a href="<?php echo SITE_URL; ?>/register.php" style="color: var(--primary-color);">Register here</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
