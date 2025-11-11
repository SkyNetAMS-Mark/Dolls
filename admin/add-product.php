<?php
require_once '../init.php';

$pageTitle = 'Add New Product';
$productModel = new Product();
$errors = [];

// Get categories for dropdown
$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$currencies = $db->fetchAll("SELECT * FROM currency_rates WHERE is_active = 1 ORDER BY currency_code");

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
    $check_slug = $db->fetchOne("SELECT id FROM products WHERE slug = ?", [$data['slug']]);
    if ($check_slug) {
        $errors[] = "Slug already exists. Please choose a unique slug.";
    }
    
    if ($data['base_price'] <= 0) {
        $errors[] = "Base price must be greater than 0.";
    }

    // Image upload validation
    $primary_image = $_FILES['primary_image'] ?? null;
    if (empty($primary_image) || $primary_image['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "A primary image is required.";
    }
    
    if (empty($errors)) {
        // 1. Create product
        $productId = $productModel->create($data);
        
        if ($productId) {
            // 2. Upload primary image
            $imagePath = uploadImage($primary_image, 'products');
            if ($imagePath) {
                $productModel->addImage($productId, $imagePath, true);
            }
            
            // 3. Upload gallery images
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
                            $productModel->addImage($productId, $galleryImagePath, false);
                        }
                    }
                }
            }
            
            redirect('/admin/products.php?success=added');
        } else {
            $errors[] = "Failed to create product.";
        }
    }
}

require_once 'header.php';
?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="form-card">
        <div class="form-grid">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo escape($_POST['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" value="<?php echo escape($_POST['slug'] ?? ''); ?>">
            </div>
            
            <div class="form-group full-width">
                <label>Description</label>
                <textarea name="description"><?php echo escape($_POST['description'] ?? ''); ?></textarea>
            </div>
            
             <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo escape($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="active">Active (Auction Open)</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending (Bid Accepted)</option>
                    <option value="sold">Sold</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Base Price</label>
                <input type="number" name="base_price" step="0.01" value="<?php echo escape($_POST['base_price'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Currency</label>
                <select name="currency" required>
                    <?php foreach ($currencies as $currency): ?>
                    <option value="<?php echo $currency['currency_code']; ?>" <?php echo $currency['currency_code'] === 'EUR' ? 'selected' : ''; ?>>
                        <?php echo escape($currency['currency_code']); ?> (<?php echo escape($currency['symbol']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <hr style="grid-column: 1 / -1; border: none; border-top: 1px solid var(--border-color); margin: 10px 0;">
            
            <h3 style="grid-column: 1 / -1; color: var(--secondary-color);">Product Details</h3>
            
            <div class="form-group">
                <label>Artist</label>
                <input type="text" name="artist" value="<?php echo escape($_POST['artist'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Manufacturer</label>
                <input type="text" name="manufacturer" value="<?php echo escape($_POST['manufacturer'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" name="weight" step="0.01" value="<?php echo escape($_POST['weight'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Length (cm)</label>
                <input type="number" name="length" step="0.1" value="<?php echo escape($_POST['length'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Hair Color</label>
                <input type="text" name="hair_color" value="<?php echo escape($_POST['hair_color'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Eye Color</label>
                <input type="text" name="eye_color" value="<?php echo escape($_POST['eye_color'] ?? ''); ?>">
            </div>
            
             <div class="form-group">
                <label>Skin Tone</label>
                <input type="text" name="skin_tone" value="<?php echo escape($_POST['skin_tone'] ?? ''); ?>">
            </div>
            
             <div class="form-group">
                <label>Size (e.g., Newborn, 20")</label>
                <input type="text" name="size" value="<?php echo escape($_POST['size'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Material (e.g., Vinyl, Silicone)</label>
                <input type="text" name="material" value="<?php echo escape($_POST['material'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Age Appearance (e.g., 0-3 Months)</label>
                <input type="text" name="age_appearance" value="<?php echo escape($_POST['age_appearance'] ?? ''); ?>">
            </div>
            
            <div class="form-group full-width">
                <label>What's Included</label>
                <textarea name="includes" placeholder="e.g., Blanket, Pacifier, Certificate of Authenticity..."><?php echo escape($_POST['includes'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group full-width">
                <label>Condition Note (Optional)</label>
                <textarea name="condition_note"><?php echo escape($_POST['condition_note'] ?? ''); ?></textarea>
            </div>
            
            <hr style="grid-column: 1 / -1; border: none; border-top: 1px solid var(--border-color); margin: 10px 0;">
            
            <h3 style="grid-column: 1 / -1; color: var(--secondary-color);">Images</h3>
            
            <div class="form-group">
                <label>Primary Image (Required)</label>
                <input type="file" name="primary_image" accept="image/*" required>
            </div>
            
            <div class="form-group">
                <label>Gallery Images (Optional)</label>
                <input type="file" name="gallery_images[]" accept="image/*" multiple>
            </div>
            
            <hr style="grid-column: 1 / -1; border: none; border-top: 1px solid var(--border-color); margin: 10px 0;">
            
            <div class="form-group full-width">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="featured" value="1" style="width: auto;">
                    Feature this product on the homepage
                </label>
            </div>

            <div class="form-group full-width">
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </div>
    </div>
</form>

<?php require_once 'footer.php'; ?>