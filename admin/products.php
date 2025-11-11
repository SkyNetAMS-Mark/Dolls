<?php
require_once '../init.php';

$pageTitle = 'Edit Product';
$productModel = new Product();
$errors = [];

// Get Product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id === 0) {
    redirect('/admin/products.php');
}

// Fetch product
$product = $productModel->getById($product_id);
if (!$product) {
    redirect('/admin/products.php');
}

// Fetch images
$product_images = $productModel->getImages($product_id);

// Get categories for dropdown
$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$currencies = $db->fetchAll("SELECT * FROM currency_rates WHERE is_active = 1 ORDER BY currency_code");

// Handle Image Delete
if (isset($_GET['delete_image'])) {
    $image_id = intval($_GET['delete_image']);
    // Extra check to ensure image belongs to this product
    $image = $db->fetchOne("SELECT * FROM product_images WHERE id = ? AND product_id = ?", [$image_id, $product_id]);
    if ($image) {
        // Delete file from server
        if (file_exists(UPLOAD_PATH . $image['image_path'])) {
            unlink(UPLOAD_PATH . $image['image_path']);
        }
        // Delete from DB
        $productModel->deleteImage($image_id);
    }
    redirect('/admin/edit-product.php?id=' . $product_id . '&success=image_deleted');
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id' => intval($_POST['category_id']),
        'name' => clean($_POST['name']),
        'slug' => clean($_POST['slug']),
        'description' => clean($_POST['description']),
        'base_price' => floatval($_POST['base_price']),
        'currency' => clean($_POST['currency']),
        'weight' => floatval($_POST['weight']) ?: null,
        'length' => floatval($_POST['length']) ?: null,
        'artist' => clean($_POST['artist']),
        'manufacturer' => clean($_POST['manufacturer']),
        'hair_color' => clean($_POST['hair_color']),
        'eye_color' => clean($_POST['eye_color']),
        'skin_tone' => clean($_POST['skin_tone']),
        'size' => clean($_POST['size']),
        'material' => clean($_POST['material']),
        'age_appearance' => clean($_POST['age_appearance']),
        'includes' => clean($_POST['includes']),
        'condition_note' => clean($_POST['condition_note']),
        'status' => clean($_POST['status']),
        'featured' => isset($_POST['featured']) ? 1 : 0,
    ];
    
    if (empty($data['name'])) {
        $errors[] = "Product name is required.";
    }
    
    if (empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name']), '-'));
    }
    
    // Check for duplicate slug
    $check_slug = $db->fetchOne("SELECT id FROM products WHERE slug = ? AND id != ?", [$data['slug'], $product_id]);
    if ($check_slug) {
        $errors[] = "Slug already exists. Please choose a unique slug.";
    }
    
    if ($data['base_price'] <= 0) {
        $errors[] = "Base price must be greater than 0.";
    }
    
    if (empty($errors)) {
        // 1. Update product
        if ($productModel->update($product_id, $data)) {
            
            // 2. Upload new primary image (if provided)
            $primary_image = $_FILES['primary_image'] ?? null;
            if ($primary_image && $primary_image['error'] === UPLOAD_ERR_OK) {
                $imagePath = uploadImage($primary_image, 'products');
                if ($imagePath) {
                    $productModel->addImage($product_id, $imagePath, true);
                }
            }
            
            // 3. Upload new gallery images
            if (isset($_FILES['gallery_images'])) {
                $gallery_files = $_FILES['gallery_images'];
                foreach ($gallery_files['tmp_name'] as $index => $tmp_name) {
                    if ($gallery_files['error'][$index] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $gallery_files['name'][$index],
                            'type' => $gallery_files['type'][$index],
                            'tmp_name' => $tmp_name,
                            'error' => $gallery_files['error'][$index],
                            'size' => $gallery_files['size'][$index]
                        ];
                        $galleryImagePath = uploadImage($file, 'products');
                        if ($galleryImagePath) {
                            $productModel->addImage($product_id, $galleryImagePath, false);
                        }
                    }
                }
            }
            
            redirect('/admin/edit-product.php?id=' . $product_id . '&success=updated');
        } else {
            $errors[] = "Failed to update product.";
        }
    }
    // Re-fetch product data on error to show new (unsaved) values
    $product = array_merge($product, $data);
}

require_once 'header.php';
?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        if ($_GET['success'] === 'updated') echo 'Product updated successfully!';
        if ($_GET['success'] === 'image_deleted') echo 'Image deleted successfully!';
        ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="form-card">
        <div class="form-grid">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo escape($product['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" value="<?php echo escape($product['slug']); ?>">
            </div>
            
            <div class="form-group full-width">
                <label>Description</label>
                <textarea name="description"><?php echo escape($product['description']); ?></textarea>
            </div>
            
             <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo escape($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active (Auction Open)</option>
                    <option value="draft" <?php echo $product['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="pending" <?php echo $product['status'] == 'pending' ? 'selected' : ''; ?>>Pending (Bid Accepted)</option>
                    <option value="sold" <?php echo $product['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Base Price</label>
                <input type="number" name="base_price" step="0.01" value="<?php echo escape($product['base_price']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Currency</label>
                <select name="currency" required>
                    <?php foreach ($currencies as $currency): ?>
                    <option value="<?php echo $currency['currency_code']; ?>" <?php echo $product['currency'] == $currency['currency_code'] ? 'selected' : ''; ?>>
                        <?php echo escape($currency['currency_code']); ?> (<?php echo escape($currency['symbol']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <hr style="grid-column: 1 / -1; border: none; border-top: 1px solid var(--border-color); margin: 10px 0;">
            
            <h3 style="grid-column: 1 / -1; color: var(--secondary-color);">Product Details</h3>
            
            <div class="form-group">
                <label>Artist</label>
                <input type="text" name="artist" value="<?php echo escape($product['artist']); ?>">
            </div>
            
            <div class="form-group">
                <label>Manufacturer</label>
                <input type="text" name="manufacturer" value="<?php echo escape($product['manufacturer']); ?>">
            </div>
            
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" name="weight" step="0.01" value="<?php echo escape($product['weight']); ?>">
            </div>
            
            <div class="form-group">
                <label>Length (cm)</label>
                <input type="number" name="length" step="0.1" value="<?php echo escape($product['length']); ?>">
            </div>
            
            <div class="form-group">
                <label>Hair Color</label>
                <input type="text" name="hair_color" value="<?php echo escape($product['hair_color']); ?>">
            </div>
            
            <div class="form-group">
                <label>Eye Color</label>
                <input type="text" name="eye_color" value="<?php echo escape($product['eye_color']); ?>">
            </div>
            
             <div class="form-group">
                <label>Skin Tone</label>
                <input type="text" name="skin_tone" value="<?php echo escape($product['skin_tone']); ?>">
            </div>
            
             <div class="form-group">
                <label>Size (e.g., Newborn, 20")</label>
                <input type="text" name="size" value="<?php echo escape($product['size']); ?>">
            </div>
            
            <div class="form-group">
                <label>Material (e.g., Vinyl, Silicone)</label>
                <input type="text" name="material" value="<?php echo escape($product['material']); ?>">
            </div>
            
            <div class="form-group">
                <label>Age Appearance (e.g., 0-3 Months)</label>
                <input type="text" name="age_appearance" value="<?php echo escape($product['age_appearance']); ?>">
            </div>
            
            <div class="form-group full-width">
                <label>What's Included</label>
                <textarea name="includes" placeholder="e.g., Blanket, Pacifier, Certificate of Authenticity..."><?php echo escape($product['includes']); ?></textarea>
            </div>
            
            <div class="form-group full-width">
                <label>Condition Note (Optional)</label>
                <textarea name="condition_note"><?php echo escape($product['condition_note']); ?></textarea>
            </div>
            
            <hr style="grid-column: 1 / -1; border: none; border-top: 1px solid var(--border-color); margin: 10px 0;">
            
            <h3 style="grid-column: 1 / -1; color: var(--secondary-color);">Manage Images</h3>
            
            <div class="form-group full-width">
                <label>Current Images</label>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; padding: 10px; background: var(--bg-light); border-radius: var(--border-radius);">
                    <?php if (empty($product_images)): ?>
                        <p style="color: var(--text-light);">No images uploaded yet.</p>
                    <?php else: ?>
                        <?php foreach ($product_images as $image): ?>
                            <div style="position: relative; border: 1px solid var(--border-color); border-radius: 5px; overflow: hidden;">
                                <img src="<?php echo UPLOAD_URL . $image['image_path']; ?>" style="width: 100px; height: 100px; object-fit: cover;">
                                <?php if ($image['is_primary']): ?>
                                    <span class="badge badge-success" style="position: absolute; top: 5px; left: 5px; font-size: 10px;">Primary</span>
                                <?php endif; ?>
                                <a href="?id=<?php echo $product_id; ?>&delete_image=<?php echo $image['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this image?')"
                                   style="position: absolute; bottom: 5px; right: 5px; background: var(--danger-color); color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-weight: bold; font-size: 12px;">
                                   X
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>Upload New Primary Image</label>
                <input type="file" name="primary_image" accept="image/*">
                <small style="color: var(--text-light); display: block; margin-top: 5px;">
                    Upload to replace the current primary image.
                </small>
            </div>
            
            <div class="form-group">
                <label>Upload New Gallery Images</label>
                <input type="file" name="gallery_images[]" accept="image/*" multiple>
                 <small style="color: var(--text-light); display: block; margin-top: 5px;">
                    Upload one or more new images to add to the gallery.
                </small>
            </div>
            
            <hr style="grid-column: 1 / -1; border: none; border-top: 1px solid var(--border-color); margin: 10px 0;">
            
            <div class="form-group full-width">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?> style="width: auto;">
                    Feature this product on the homepage
                </label>
            </div>

            <div class="form-group full-width">
                <button type="submit" class="btn btn-primary">Update Product</button>
                 <a href="/admin/products.php" class="btn btn-secondary">Back to Products</a>
            </div>
        </div>
    </div>
</form>

<?php require_once 'footer.php'; ?>