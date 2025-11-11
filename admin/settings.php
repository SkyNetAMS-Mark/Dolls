<?php
require_once '../init.php';

$pageTitle = 'Site Settings';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Loop through all POST data and update settings
    foreach ($_POST as $key => $value) {
        if ($key === 'save_settings') continue;
        
        $value = clean($value);
        saveSetting($key, $value);
    }
    
    redirect('/admin/settings.php?success=updated');
}

// Get all settings
$settings = $db->fetchAll("SELECT * FROM settings ORDER BY setting_key ASC");

require_once 'header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Settings updated successfully!</div>
<?php endif; ?>

<div class="form-card" style="max-width: 800px; margin: 0 auto;">
    <form method="POST">
        
        <?php foreach ($settings as $setting): ?>
            <div class="form-group">
                <label><?php echo escape(ucfirst(str_replace('_', ' ', $setting['setting_key']))); ?></label>
                
                <?php if ($setting['setting_type'] === 'textarea' || strlen($setting['setting_value']) > 100): ?>
                    <textarea name="<?php echo escape($setting['setting_key']); ?>"><?php echo escape($setting['setting_value']); ?></textarea>
                <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                    <select name="<?php echo escape($setting['setting_key']); ?>">
                        <option value="1" <?php echo $setting['setting_value'] == '1' ? 'selected' : ''; ?>>Yes (Enabled)</option>
                        <option value="0" <?php echo $setting['setting_value'] == '0' ? 'selected' : ''; ?>>No (Disabled)</option>
                    </select>
                <?php else: ?>
                    <input type="<?php echo $setting['setting_type'] === 'email' ? 'email' : ($setting['setting_type'] === 'decimal' ? 'number' : 'text'); ?>"
                           name="<?php echo escape($setting['setting_key']); ?>" 
                           value="<?php echo escape($setting['setting_value']); ?>"
                           <?php if ($setting['setting_type'] === 'decimal') echo 'step="0.01"'; ?>
                           >
                <?php endif; ?>
                
                <?php if ($setting['description']): ?>
                    <small style="color: var(--text-light); display: block; margin-top: 5px;">
                        <?php echo escape($setting['description']); ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>