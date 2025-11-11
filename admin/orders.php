<?php
require_once '../init.php';

$pageTitle = 'Manage Orders';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_payment_status'])) {
        $orderId = intval($_POST['order_id']);
        $status = clean($_POST['payment_status']);
        $db->update('orders', ['payment_status' => $status], 'id = :id', ['id' => $orderId]);
    }
    
    if (isset($_POST['update_shipping_status'])) {
        $orderId = intval($_POST['order_id']);
        $status = clean($_POST['shipping_status']);
        $db->update('orders', ['shipping_status' => $status], 'id = :id', ['id' => $orderId]);
    }
    
    if (isset($_POST['update_tracking'])) {
        $orderId = intval($_POST['order_id']);
        $tracking = clean($_POST['tracking_number']);
        $db->update('orders', ['tracking_number' => $tracking], 'id = :id', ['id' => $orderId]);
    }
    
    redirect('/admin/orders.php?success=updated');
}

// Get filters
$filters = [];
if (!empty($_GET['payment_status'])) {
    $filters['payment_status'] = clean($_GET['payment_status']);
}
if (!empty($_GET['shipping_status'])) {
    $filters['shipping_status'] = clean($_GET['shipping_status']);
}

// Build query
$where = "1=1";
$params = [];
if (!empty($filters['payment_status'])) {
    $where .= " AND o.payment_status = :payment_status";
    $params['payment_status'] = $filters['payment_status'];
}
if (!empty($filters['shipping_status'])) {
    $where .= " AND o.shipping_status = :shipping_status";
    $params['shipping_status'] = $filters['shipping_status'];
}

$orders = $db->fetchAll(
    "SELECT o.*, p.name as product_name, p.slug as product_slug, b.email as bidder_email
     FROM orders o
     LEFT JOIN products p ON o.product_id = p.id
     LEFT JOIN bids b ON o.bid_id = b.id
     WHERE $where
     ORDER BY o.created_at DESC",
    $params
);

$payment_statuses = ['pending', 'paid', 'failed', 'refunded'];
$shipping_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

require_once 'header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Order updated successfully!</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 10px;">
        <select name="payment_status" class="form-control" style="padding: 10px 15px; border: 1px solid var(--border-color); border-radius: var(--border-radius);">
            <option value="">All Payment Statuses</option>
            <?php foreach ($payment_statuses as $status): ?>
                <option value="<?php echo $status; ?>" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === $status) ? 'selected' : ''; ?>>
                    <?php echo ucfirst($status); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <select name="shipping_status" class="form-control" style="padding: 10px 15px; border: 1px solid var(--border-color); border-radius: var(--border-radius);">
            <option value="">All Shipping Statuses</option>
             <?php foreach ($shipping_statuses as $status): ?>
                <option value="<?php echo $status; ?>" <?php echo (isset($_GET['shipping_status']) && $_GET['shipping_status'] === $status) ? 'selected' : ''; ?>>
                    <?php echo ucfirst($status); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Product</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Shipping</th>
                <th>Tracking</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong><?php echo escape($order['order_number']); ?></strong>
                        </td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $order['product_slug']; ?>" 
                               target="_blank"
                               style="color: var(--primary-color);">
                                <?php echo escape($order['product_name']); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo escape($order['shipping_first_name'] . ' ' . $order['shipping_last_name']); ?><br>
                            <small style="color: var(--text-light);"><?php echo escape($order['shipping_email']); ?></small>
                        </td>
                        <td>
                            <strong style="font-size: 16px; color: var(--primary-color);">
                                <?php echo formatPrice($order['total_amount'] + $order['shipping_cost'], $order['currency']); ?>
                            </strong><br>
                            <small>(<?php echo escape($order['payment_method']); ?>)</small>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="payment_status" onchange="this.form.submit()" style="padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 5px; font-size: 12px;">
                                    <?php foreach ($payment_statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo $order['payment_status'] === $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($status); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="update_payment_status" value="1">
                            </form>
                        </td>
                        <td>
                             <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="shipping_status" onchange="this.form.submit()" style="padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 5px; font-size: 12px;">
                                    <?php foreach ($shipping_statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo $order['shipping_status'] === $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($status); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="update_shipping_status" value="1">
                            </form>
                        </td>
                         <td>
                             <form method="POST" style="display: flex; gap: 5px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="text" name="tracking_number" value="<?php echo escape($order['tracking_number']); ?>" placeholder="Tracking #" style="padding: 4px 8px; font-size: 12px; border: 1px solid var(--border-color); border-radius: 5px; max-width: 120px;">
                                <button type="submit" name="update_tracking" class="btn btn-secondary" style="padding: 4px 8px; font-size: 10px;">Save</button>
                            </form>
                        </td>
                        <td>
                            <div style="font-size: 13px;">
                                <?php echo formatDate($order['created_at']); ?>
                            </div>
                            <small style="color: var(--text-light);">
                                <?php echo timeAgo($order['created_at']); ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 60px 20px; color: var(--text-light);">
                        No orders found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>