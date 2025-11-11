<?php
require_once '../init.php';

$pageTitle = 'Manage Users';

// Handle status change
if (isset($_POST['change_status'])) {
    $userId = intval($_POST['user_id']);
    $newStatus = intval($_POST['is_active']);
    if ($db->update('users', ['is_active' => $newStatus], 'id = :id', ['id' => $userId])) {
        redirect('/admin/users.php?success=updated');
    }
}

// Get filters
$filters = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = clean($_GET['search']);
}

$where = "1=1";
$params = [];
if (!empty($filters['search'])) {
    $where .= " AND (email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
    $params['search'] = '%' . $filters['search'] . '%';
}

// Get users
$users = $db->fetchAll(
    "SELECT *, (SELECT COUNT(*) FROM bids WHERE user_id = users.id) as bid_count,
            (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as order_count
     FROM users
     WHERE $where
     ORDER BY created_at DESC",
    $params
);

require_once 'header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">User status updated successfully!</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" value="<?php echo escape($filters['search'] ?? ''); ?>" placeholder="Search by name or email..." class="form-control" style="padding: 10px 15px; border: 1px solid var(--border-color); border-radius: var(--border-radius); min-width: 300px;">
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Contact</th>
                <th>Stats (Bids/Orders)</th>
                <th>Member Since</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                        </td>
                        <td>
                            <?php echo escape($user['email']); ?><br>
                            <small style="color: var(--text-light);"><?php echo escape($user['phone'] ?? 'No phone'); ?></small>
                        </td>
                        <td>
                            <?php echo $user['bid_count']; ?> Bids<br>
                            <?php echo $user['order_count']; ?> Orders
                        </td>
                        <td>
                            <?php echo formatDate($user['created_at'], 'd M Y'); ?>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <?php if ($user['is_active']): ?>
                                    <input type="hidden" name="is_active" value="0">
                                    <button type="submit" name="change_status" class="btn" style="padding: 6px 12px; font-size: 12px; background: var(--danger-color); color: white;">
                                        Deactivate
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" name="change_status" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">
                                        Activate
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px 20px; color: var(--text-light);">
                        No users found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>