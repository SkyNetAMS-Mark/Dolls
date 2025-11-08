<?php
require_once '../init.php';

$pageTitle = 'Manage Products';
$productModel = new Product();

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $productId = intval($_GET['delete']);
    if ($productModel->delete($productId)) {
        redirect('/admin/products.php?success=deleted');
    }
}

// Handle status change
if (isset($_POST['change_status'])) {
    $productId = intval($_POST['product_id']);
    $newStatus = clean($_POST['status']);
    if ($productModel->update($productId, ['status' => $newStatus])) {
        redirect('/admin/products.php?success=updated');
    }
}

// Get filters
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = clean($_GET['status']);
}

// Get products
$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
            (SELECT COUNT(*) FROM bids WHERE product_id = p.id) as bid_count
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     " . (!empty($filters['status']) ? "WHERE p.status = '" . $db->escape($filters['status']) . "'" : "") . "
     ORDER BY p.created_at DESC"
);

require_once 'header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        if ($_GET['success'] === 'deleted') {
            echo "Product deleted successfully!";
        } elseif ($_GET['success'] === 'updated') {
            echo "Product updated successfully!";
        }
        ?>
    </div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 10px;">
        <select name="status" class="form-control" style="padding: 10px 15px; border: 1px solid var(--border-color); border-radius: var(--border-radius);">
            <option value="">All Statuses</option>
            <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="sold" <?php echo (isset($_GET['status']) && $_GET['status'] === 'sold') ? 'selected' : ''; ?>>Sold</option>
            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="draft" <?php echo (isset($_GET['status']) && $_GET['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>
    
    <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="btn btn-primary">➕ Add New Product</a>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Base Price</th>
                <th>Current Bid</th>
                <th>Bids</th>
                <th>Status</th>
                <th>Views</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if ($product['primary_image']): ?>
                                    <img src="<?php echo UPLOAD_URL . $product['primary_image']; ?>" 
                                         alt="<?php echo escape($product['name']); ?>"
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight: 600;"><?php echo escape($product['name']); ?></div>
                                    <small style="color: var(--text-light);"><?php echo $product['slug']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo escape($product['category_name']); ?></td>
                        <td><strong><?php echo formatPrice($product['base_price'], $product['currency']); ?></strong></td>
                        <td>
                            <?php if ($product['current_bid']): ?>
                                <strong style="color: var(--success-color);">
                                    <?php echo formatPrice($product['current_bid'], $product['currency']); ?>
                                </strong>
                            <?php else: ?>
                                <span style="color: var(--text-light);">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $product['bid_count']; ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 5px; font-size: 12px;">
                                    <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="sold" <?php echo $product['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                    <option value="pending" <?php echo $product['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="draft" <?php echo $product['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                                <input type="hidden" name="change_status" value="1">
                            </form>
                        </td>
                        <td><?php echo $product['views']; ?></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" 
                                   target="_blank"
                                   class="btn btn-secondary" 
                                   style="padding: 6px 12px; font-size: 12px;">
                                    View
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/edit-product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary" 
                                   style="padding: 6px 12px; font-size: 12px;">
                                    Edit
                                </a>
                                <a href="?delete=<?php echo $product['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this product?')"
                                   class="btn" 
                                   style="padding: 6px 12px; font-size: 12px; background: var(--danger-color); color: white;">
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 60px 20px; color: var(--text-light);">
                        <p style="margin-bottom: 20px;">No products found</p>
                        <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="btn btn-primary">Add Your First Product</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
