<?php
require_once '../init.php';

$pageTitle = 'Manage Categories';

$errors = [];
$edit_category = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->delete('categories', 'id = :id', ['id' => $id]);
    redirect('/admin/categories.php?success=deleted');
}

// Handle Edit (Fetch)
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_category = $db->fetchOne("SELECT * FROM categories WHERE id = ?", [$id]);
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = clean($_POST['name']);
    $slug = clean($_POST['slug']);
    $description = clean($_POST['description']);
    $display_order = intval($_POST['display_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
    
    // Check for duplicate slug
    $check_slug = $db->fetchOne("SELECT id FROM categories WHERE slug = ? AND id != ?", [$slug, $id]);
    if ($check_slug) {
        $errors[] = "Slug already exists. Please choose a unique slug.";
    }
    
    if (empty($errors)) {
        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'display_order' => $display_order,
            'is_active' => $is_active
        ];
        
        if ($id > 0) {
            // Update
            $db->update('categories', $data, 'id = :id', ['id' => $id]);
            redirect('/admin/categories.php?success=updated');
        } else {
            // Insert
            $db->insert('categories', $data);
            redirect('/admin/categories.php?success=added');
        }
    }
}

// Get all categories
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY display_order ASC, name ASC");

require_once 'header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        switch ($_GET['success']) {
            case 'added': echo 'Category added successfully!'; break;
            case 'updated': echo 'Category updated successfully!'; break;
            case 'deleted': echo 'Category deleted successfully!'; break;
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
        <h3><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h3>
        <form method="POST" style="margin-top: 20px;">
            <input type="hidden" name="id" value="<?php echo $edit_category['id'] ?? 0; ?>">
            
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="name" value="<?php echo escape($edit_category['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" value="<?php echo escape($edit_category['slug'] ?? ''); ?>">
                <small style="color: var(--text-light); display: block; margin-top: 5px;">
                    Leave blank to auto-generate from name.
                </small>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?php echo escape($edit_category['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" value="<?php echo $edit_category['display_order'] ?? 0; ?>">
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" value="1" 
                           <?php echo ($edit_category && !$edit_category['is_active']) ? '' : 'checked'; ?>
                           style="width: auto;">
                    Active
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
            </button>
            <?php if ($edit_category): ?>
                <a href="/admin/categories.php" class="btn btn-secondary">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($category['name']); ?></strong><br>
                                <small style="color: var(--text-light);"><?php echo escape($category['description']); ?></small>
                            </td>
                            <td><?php echo escape($category['slug']); ?></td>
                            <td>
                                <?php if ($category['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $category['display_order']; ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="?edit=<?php echo $category['id']; ?>" 
                                       class="btn btn-primary" 
                                       style="padding: 6px 12px; font-size: 12px;">
                                        Edit
                                    </a>
                                    <a href="?delete=<?php echo $category['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this category?')"
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
                            No categories found. Add one to get started.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'footer.php'; ?>