<?php
require_once '../init.php';

$pageTitle = 'Manage Shipping Rates';

$errors = [];
$edit_rate = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->delete('shipping_rates', 'id = :id', ['id' => $id]);
    redirect('/admin/shipping.php?success=deleted');
}

// Handle Edit (Fetch)
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_rate = $db->fetchOne("SELECT * FROM shipping_rates WHERE id = ?", [$id]);
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $country_code = clean(strtoupper($_POST['country_code']));
    $country_name = clean($_POST['country_name']);
    $rate = floatval($_POST['rate']);
    $currency = clean(strtoupper($_POST['currency']));
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($country_code) || empty($country_name) || empty($currency)) {
        $errors[] = "Country Code, Name, and Currency are required.";
    }
    
    if (strlen($country_code) != 2 && $country_code !== 'OTHER') {
         $errors[] = "Country Code must be 2 letters (e.g., US) or 'OTHER'.";
    }
    
    if (empty($errors)) {
        $data = [
            'country_code' => $country_code,
            'country_name' => $country_name,
            'rate' => $rate,
            'currency' => $currency,
            'is_active' => $is_active
        ];
        
        if ($id > 0) {
            // Update
            $db->update('shipping_rates', $data, 'id = :id', ['id' => $id]);
            redirect('/admin/shipping.php?success=updated');
        } else {
            // Insert
            $db->insert('shipping_rates', $data);
            redirect('/admin/shipping.php?success=added');
        }
    }
}

// Get all rates
$shipping_rates = $db->fetchAll("SELECT * FROM shipping_rates ORDER BY country_name ASC");

require_once 'header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        switch ($_GET['success']) {
            case 'added': echo 'Shipping rate added successfully!'; break;
            case 'updated': echo 'Shipping rate updated successfully!'; break;
            case 'deleted': echo 'Shipping rate deleted successfully!'; break;
        }
        ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    
    <div class="form-card">
        <h3><?php echo $edit_rate ? 'Edit Shipping Rate' : 'Add New Rate'; ?></h3>
        <form method="POST" style="margin-top: 20px;">
            <input type="hidden" name="id" value="<?php echo $edit_rate['id'] ?? 0; ?>">
            
            <div class="form-group">
                <label>Country Name</label>
                <input type="text" name="country_name" value="<?php echo escape($edit_rate['country_name'] ?? ''); ?>" required>
                 <small style="color: var(--text-light); display: block; margin-top: 5px;">
                    Use 'Other Countries' for a default fallback.
                </small>
            </div>
            
            <div class="form-group">
                <label>Country Code</label>
                <input type="text" name="country_code" value="<?php echo escape($edit_rate['country_code'] ?? ''); ?>" required>
                <small style="color: var(--text-light); display: block; margin-top: 5px;">
                    2-letter code (e.g., NL, US, DE) or 'OTHER'.
                </small>
            </div>
            
            <div class="form-group">
                <label>Rate</label>
                <input type="number" name="rate" step="0.01" value="<?php echo $edit_rate['rate'] ?? 0.00; ?>" required>
            </div>
            
             <div class="form-group">
                <label>Currency</label>
                <input type="text" name="currency" value="<?php echo $edit_rate['currency'] ?? 'EUR'; ?>" required>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" value="1" 
                           <?php echo ($edit_rate && !$edit_rate['is_active']) ? '' : 'checked'; ?>
                           style="width: auto;">
                    Active
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <?php echo $edit_rate ? 'Update Rate' : 'Add Rate'; ?>
            </button>
            <?php if ($edit_rate): ?>
                <a href="/admin/shipping.php" class="btn btn-secondary">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($shipping_rates)): ?>
                    <?php foreach ($shipping_rates as $rate): ?>
                        <tr>
                            <td><strong><?php echo escape($rate['country_name']); ?></strong></td>
                            <td><?php echo escape($rate['country_code']); ?></td>
                            <td>
                                <strong><?php echo formatPrice($rate['rate'], $rate['currency']); ?></strong>
                            </td>
                            <td>
                                <?php if ($rate['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="?edit=<?php echo $rate['id']; ?>" 
                                       class="btn btn-primary" 
                                       style="padding: 6px 12px; font-size: 12px;">
                                        Edit
                                    </a>
                                    <a href="?delete=<?php echo $rate['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this shipping rate?')"
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
                        <td colspan="5" style="text-align: center; padding: 60px 20px; color: var(--text-light);">
                            No shipping rates found. Add one to get started.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'footer.php'; ?>