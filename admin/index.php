<?php
require_once '../init.php';

$pageTitle = 'Dashboard';

// Get statistics
$stats = [
    'total_products' => $db->fetchOne("SELECT COUNT(*) as count FROM products")['count'],
    'active_products' => $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'],
    'total_bids' => $db->fetchOne("SELECT COUNT(*) as count FROM bids")['count'],
    'pending_bids' => $db->fetchOne("SELECT COUNT(*) as count FROM bids WHERE status = 'confirmed'")['count'],
    'total_orders' => $db->fetchOne("SELECT COUNT(*) as count FROM orders")['count'],
    'pending_orders' => $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'pending'")['count'],
];

// Recent bids
$recentBids = $db->fetchAll(
    "SELECT b.*, p.name as product_name, p.slug as product_slug 
     FROM bids b 
     LEFT JOIN products p ON b.product_id = p.id 
     ORDER BY b.created_at DESC 
     LIMIT 10"
);

// Recent products
$recentProducts = $db->fetchAll(
    "SELECT p.*, c.name as category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     ORDER BY p.created_at DESC
     LIMIT 5"
);

require_once 'header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Products</h3>
        <div class="value"><?php echo $stats['total_products']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Active Auctions</h3>
        <div class="value"><?php echo $stats['active_products']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Total Bids</h3>
        <div class="value"><?php echo $stats['total_bids']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Pending Bids</h3>
        <div class="value"><?php echo $stats['pending_bids']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Total Orders</h3>
        <div class="value"><?php echo $stats['total_orders']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Pending Orders</h3>
        <div class="value"><?php echo $stats['pending_orders']; ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <div class="data-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h2 style="font-size: 20px; color: var(--secondary-color);">Recent Bids</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Bidder</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentBids)): ?>
                    <?php foreach ($recentBids as $bid): ?>
                        <tr>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $bid['product_slug']; ?>" 
                                   target="_blank" 
                                   style="color: var(--primary-color);">
                                    <?php echo escape($bid['product_name']); ?>
                                </a>
                            </td>
                            <td><?php echo escape($bid['first_name'] . ' ' . $bid['last_name']); ?></td>
                            <td><strong><?php echo formatPrice($bid['bid_amount'], $bid['currency']); ?></strong></td>
                            <td>
                                <?php
                                $badgeClass = [
                                    'pending' => 'badge-warning',
                                    'confirmed' => 'badge-success',
                                    'accepted' => 'badge-success',
                                    'rejected' => 'badge-danger',
                                    'cancelled' => 'badge-secondary'
                                ];
                                ?>
                                <span class="badge <?php echo $badgeClass[$bid['status']] ?? 'badge-secondary'; ?>">
                                    <?php echo $bid['status']; ?>
                                </span>
                            </td>
                            <td style="font-size: 13px; color: var(--text-light);">
                                <?php echo timeAgo($bid['created_at']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-light);">
                            No bids yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (!empty($recentBids)): ?>
            <div style="padding: 15px; text-align: center; border-top: 1px solid var(--border-color);">
                <a href="<?php echo SITE_URL; ?>/admin/bids.php" style="color: var(--primary-color); font-weight: 600;">
                    View All Bids →
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="data-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h2 style="font-size: 20px; color: var(--secondary-color);">Recent Products</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentProducts)): ?>
                    <?php foreach ($recentProducts as $product): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if ($product['primary_image']): ?>
                                        <img src="<?php echo UPLOAD_URL . $product['primary_image']; ?>" 
                                             alt="<?php echo escape($product['name']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <?php endif; ?>
                                    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" 
                                       target="_blank"
                                       style="color: var(--primary-color);">
                                        <?php echo escape($product['name']); ?>
                                    </a>
                                </div>
                            </td>
                            <td><?php echo escape($product['category_name']); ?></td>
                            <td><strong><?php echo formatPrice($product['base_price'], $product['currency']); ?></strong></td>
                            <td>
                                <?php
                                $statusBadge = [
                                    'active' => 'badge-success',
                                    'sold' => 'badge-secondary',
                                    'pending' => 'badge-warning',
                                    'draft' => 'badge-secondary'
                                ];
                                ?>
                                <span class="badge <?php echo $statusBadge[$product['status']] ?? 'badge-secondary'; ?>">
                                    <?php echo $product['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-light);">
                            No products yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (!empty($recentProducts)): ?>
            <div style="padding: 15px; text-align: center; border-top: 1px solid var(--border-color);">
                <a href="<?php echo SITE_URL; ?>/admin/products.php" style="color: var(--primary-color); font-weight: 600;">
                    View All Products →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
