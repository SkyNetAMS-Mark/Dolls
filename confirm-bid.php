<?php
require_once 'init.php';

$pageTitle = 'Confirm Your Bid';
$success = false;
$error = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $bidModel = new Bid();
    $productModel = new Product();
    
    $token = clean($_GET['token']);
    $bid = $bidModel->getByToken($token);
    
    if ($bid) {
        if ($bid['status'] === 'confirmed') {
            $success = "Your bid has already been confirmed!";
        } else {
            if ($bidModel->confirm($token)) {
                $success = "Your bid has been confirmed successfully!";
                
                // Reload bid to get updated info
                $bid = $bidModel->getByToken($token);
            } else {
                $error = "Failed to confirm your bid. Please try again or contact us.";
            }
        }
    } else {
        $error = "Invalid confirmation link. Please check your email or contact us.";
    }
} else {
    $error = "No confirmation token provided.";
}

require_once 'includes/header.php';
?>

<div class="container" style="margin: 80px auto; max-width: 600px;">
    <div style="background: white; padding: 50px; border-radius: var(--border-radius); box-shadow: var(--shadow); text-align: center;">
        <?php if ($success): ?>
            <div style="width: 80px; height: 80px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; color: white; font-size: 40px;">
                ✓
            </div>
            
            <h1 style="color: var(--secondary-color); margin-bottom: 20px;">Bid Confirmed!</h1>
            <p style="font-size: 18px; color: var(--text-color); margin-bottom: 30px;">
                <?php echo $success; ?>
            </p>
            
            <?php if (isset($bid)): ?>
                <div style="background: var(--bg-light); padding: 25px; border-radius: var(--border-radius); margin: 30px 0; text-align: left;">
                    <h3 style="margin-bottom: 15px; color: var(--secondary-color);">Bid Details</h3>
                    <p><strong>Product:</strong> <?php echo escape($bid['product_name']); ?></p>
                    <p><strong>Your Bid:</strong> <?php echo formatPrice($bid['bid_amount'], $bid['currency']); ?></p>
                    <p><strong>Status:</strong> <span style="color: var(--success-color);">Confirmed</span></p>
                    <p><strong>Date:</strong> <?php echo formatDate($bid['created_at']); ?></p>
                </div>
                
                <p style="color: var(--text-light); margin-bottom: 30px;">
                    We will notify you via email if your bid wins. If you're outbid, you can place a new bid.
                </p>
                
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $bid['product_slug']; ?>" class="btn btn-primary">
                        View Product
                    </a>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-secondary">
                        Browse More Dolls
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div style="width: 80px; height: 80px; background: var(--danger-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; color: white; font-size: 40px;">
                ✕
            </div>
            
            <h1 style="color: var(--secondary-color); margin-bottom: 20px;">Confirmation Failed</h1>
            <p style="font-size: 18px; color: var(--text-color); margin-bottom: 30px;">
                <?php echo $error; ?>
            </p>
            
            <p style="color: var(--text-light); margin-bottom: 30px;">
                If you continue to experience issues, please contact us at 
                <a href="mailto:<?php echo SITE_EMAIL; ?>" style="color: var(--primary-color);">
                    <?php echo SITE_EMAIL; ?>
                </a>
            </p>
            
            <a href="<?php echo SITE_URL; ?>/" class="btn btn-primary">
                Go to Homepage
            </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
