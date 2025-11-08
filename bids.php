<?php
require_once '../init.php';

$pageTitle = 'Manage Bids';
$bidModel = new Bid();

// Handle bid action
if (isset($_POST['accept_bid'])) {
    $bidId = intval($_POST['bid_id']);
    if ($bidModel->acceptBid($bidId)) {
        redirect('/admin/bids.php?success=accepted');
    }
}

if (isset($_POST['reject_bid'])) {
    $bidId = intval($_POST['bid_id']);
    if ($bidModel->updateStatus($bidId, 'rejected')) {
        redirect('/admin/bids.php?success=rejected');
    }
}

// Get filters
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = clean($_GET['status']);
}
if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $filters['product_id'] = intval($_GET['product_id']);
}

// Get bids
$bids = $bidModel->getAll($filters, 50, 0);

require_once 'header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        if ($_GET['success'] === 'accepted') {
            echo "Bid accepted successfully! Product status updated to pending.";
        } elseif ($_GET['success'] === 'rejected') {
            echo "Bid rejected successfully!";
        }
        ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 10px;">
        <select name="status" class="form-control" style="padding: 10px 15px; border: 1px solid var(--border-color); border-radius: var(--border-radius);">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="confirmed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
            <option value="accepted" <?php echo (isset($_GET['status']) && $_GET['status'] === 'accepted') ? 'selected' : ''; ?>>Accepted</option>
            <option value="rejected" <?php echo (isset($_GET['status']) && $_GET['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($_GET['status'])): ?>
            <a href="<?php echo SITE_URL; ?>/admin/bids.php" class="btn btn-secondary">Clear Filter</a>
        <?php endif; ?>
    </form>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Bidder</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Bid Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bids)): ?>
                <?php foreach ($bids as $bid): ?>
                    <tr>
                        <td><strong>#<?php echo $bid['id']; ?></strong></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $bid['product_slug']; ?>" 
                               target="_blank"
                               style="color: var(--primary-color);">
                                <?php echo escape($bid['product_name']); ?>
                            </a>
                        </td>
                        <td><?php echo escape($bid['first_name'] . ' ' . $bid['last_name']); ?></td>
                        <td>
                            <a href="mailto:<?php echo $bid['email']; ?>" style="color: var(--primary-color);">
                                <?php echo escape($bid['email']); ?>
                            </a>
                        </td>
                        <td><?php echo escape($bid['phone'] ?: '-'); ?></td>
                        <td>
                            <strong style="font-size: 16px; color: var(--primary-color);">
                                <?php echo formatPrice($bid['bid_amount'], $bid['currency']); ?>
                            </strong>
                        </td>
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
                        <td>
                            <div style="font-size: 13px;">
                                <?php echo formatDate($bid['created_at']); ?>
                            </div>
                            <small style="color: var(--text-light);">
                                <?php echo timeAgo($bid['created_at']); ?>
                            </small>
                        </td>
                        <td>
                            <?php if ($bid['status'] === 'confirmed'): ?>
                                <form method="POST" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="bid_id" value="<?php echo $bid['id']; ?>">
                                    <button type="submit" 
                                            name="accept_bid" 
                                            class="btn btn-success" 
                                            style="padding: 6px 12px; font-size: 12px;"
                                            onclick="return confirm('Accept this bid? This will reject all other bids for this product.')">
                                        Accept
                                    </button>
                                    <button type="submit" 
                                            name="reject_bid" 
                                            class="btn" 
                                            style="padding: 6px 12px; font-size: 12px; background: var(--danger-color); color: white;"
                                            onclick="return confirm('Reject this bid?')">
                                        Reject
                                    </button>
                                </form>
                            <?php elseif ($bid['status'] === 'pending'): ?>
                                <span style="color: var(--text-light); font-size: 12px;">Awaiting confirmation</span>
                            <?php else: ?>
                                <span style="color: var(--text-light); font-size: 12px;">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 60px 20px; color: var(--text-light);">
                        No bids found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
