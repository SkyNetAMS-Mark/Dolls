<?php
require_once 'init.php';

if (isLoggedIn()) {
    redirect('/account.php');
}

$pageTitle = 'Create Account';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User();
    
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $firstName = clean($_POST['first_name']);
    $lastName = clean($_POST['last_name']);
    $phone = clean($_POST['phone']);
    
    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address";
    }
    if (empty($firstName) || empty($lastName)) {
        $errors[] = "Please provide your full name";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    if ($userModel->emailExists($email)) {
        $errors[] = "An account with this email already exists";
    }
    
    if (empty($errors)) {
        $userData = [
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ];
        
        $userId = $userModel->create($userData);
        
        if ($userId) {
            // Auto login
            $userModel->login($email, $password);
            redirect('/account.php');
        } else {
            $errors[] = "Failed to create account. Please try again.";
        }
    }
}

require_once 'header.php';
?>

<div class="container" style="margin: 60px auto; max-width: 500px;">
    <div style="background: white; padding: 40px; border-radius: var(--border-radius); box-shadow: var(--shadow);">
        <h1 style="text-align: center; margin-bottom: 30px; color: var(--secondary-color);">Create Account</h1>
        
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? escape($_POST['first_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? escape($_POST['last_name']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number (Optional)</label>
                <input type="tel" name="phone" value="<?php echo isset($_POST['phone']) ? escape($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
                <small style="color: var(--text-light); display: block; margin-top: 5px;">
                    At least 6 characters
                </small>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: var(--text-light);">
            Already have an account? 
            <a href="<?php echo SITE_URL; ?>/login.php" style="color: var(--primary-color);">Login here</a>
        </p>
    </div>
</div>

<?php require_once 'footer.php'; ?>
