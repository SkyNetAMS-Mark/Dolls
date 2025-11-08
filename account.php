<?php
require_once 'init.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'My Account';
$bidModel = new Bid();
$user = getCurrentUser();

// Get user's bids
$userBids = $bidModel->getByUser($user['id']);

require_once 'includes/header.php';
?>

<div class="container" style="margin: 40px auto;">
    <h1 style="margin-bottom: 40px; color: var(--secondary-color);">My Account</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <div>
            <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--shadow);">
                <h2 style="margin-bottom: 20px; color: var(--secondary-color); font-size: 24px;">Profile</h2>
                
                <p style="margin-bottom: 10px;">
                    <strong>Name:</strong><br>
                    <?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?>
                </p>
                
                <p style="margin-bottom: 10px;">
                    <strong>Email:</strong><br>
                    <?php echo escape($user['email']); ?>
                </p>
                
                <?php if ($user['phone']): ?>
                    <p style="margin-bottom: 10px;">
                        <strong>Phone:</strong><br>
                        <?php echo escape($user['phone']); ?>
                    </p>
                <?php endif; ?>
                
                <p style="margin-bottom: 10px;">
                    <strong>Member Since:</strong><br>
                    <?php echo formatDate($user['created_at'], 'F Y'); ?>
                </p>
            </div>
        </div>
        
        <div>
            <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--shadow);">
                <h2 style="margin-bottom: 20px; color: var(--secondary-color); font-size: 24px;">My Bids</h2>
                
                <?php if (!empty($userBids)): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--bg-light); text-align: left;">
                                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">Product</th>
                                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">Bid Amount</th>
                                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">Status</th>
                                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">Date</th>
                                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userBids as $bid): ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td style="padding: 12px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <?php if ($bid['product_image']): ?>
                                                    <img src="<?php echo UPLOAD_URL . $bid['product_image']; ?>" 
                                                         alt="<?php echo escape($bid['product_name']); ?>"
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                <?php endif; ?>
                                                <div>
                                                    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $bid['product_slug']; ?>" 
                                                       style="color: var(--primary-color); font-weight: 600;">
                                                        <?php echo escape($bid['product_name']); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 12px; font-weight: 600;">
                                            <?php echo formatPrice($bid['bid_amount'], $bid['currency']); ?>
                                        </td>
                                        <td style="padding: 12px;">
                                            <?php
                                            $statusColors = [
                                                'pending' => 'var(--warning-color)',
                                                'confirmed' => 'var(--success-color)',
                                                'accepted' => 'var(--success-color)',
                                                'rejected' => 'var(--danger-color)',
                                                'cancelled' => 'var(--text-light)'
                                            ];
                                            $statusColor = $statusColors[$bid['status']] ?? 'var(--text-color)';
                                            ?>
                                            <span style="display: inline-block; padding: 4px 12px; background: <?php echo $statusColor; ?>; color: white; border-radius: 50px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                                                <?php echo escape($bid['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px; color: var(--text-light); font-size: 14px;">
                                            <?php echo timeAgo($bid['created_at']); ?>
                                        </td>
                                        <td style="padding: 12px;">
                                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $bid['product_slug']; ?>" 
                                               class="btn btn-primary" 
                                               style="padding: 6px 15px; font-size: 14px;">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-light);">
                        <p style="margin-bottom: 20px;">You haven't placed any bids yet.</p>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Browse Dolls</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
