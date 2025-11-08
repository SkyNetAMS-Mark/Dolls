<?php
require_once 'init.php';

$pageTitle = 'Browse Reborn Dolls';
$pageDescription = 'Browse our collection of authentic handcrafted reborn dolls';

$productModel = new Product();

// Get filters from URL
$filters = [];
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filters['category'] = clean($_GET['category']);
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = clean($_GET['search']);
}
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $filters['min_price'] = floatval($_GET['min_price']);
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $filters['max_price'] = floatval($_GET['max_price']);
}
if (isset($_GET['hair_color']) && !empty($_GET['hair_color'])) {
    $filters['hair_color'] = clean($_GET['hair_color']);
}
if (isset($_GET['eye_color']) && !empty($_GET['eye_color'])) {
    $filters['eye_color'] = clean($_GET['eye_color']);
}
if (isset($_GET['size']) && !empty($_GET['size'])) {
    $filters['size'] = clean($_GET['size']);
}
if (isset($_GET['sort']) && !empty($_GET['sort'])) {
    $filters['sort'] = clean($_GET['sort']);
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get products
$products = $productModel->getAll($filters, $limit, $offset);
$totalProducts = $productModel->getTotalCount($filters);
$totalPages = ceil($totalProducts / $limit);

// Get filter options
$filterOptions = $productModel->getFilterOptions();
$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order");

require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 40px;">
    <h1 style="font-size: 36px; margin-bottom: 30px; color: var(--secondary-color);">
        <?php 
        if (isset($filters['category'])) {
            $cat = $db->fetchOne("SELECT name FROM categories WHERE slug = ?", [$filters['category']]);
            echo escape($cat['name'] ?? 'Browse Dolls');
        } elseif (isset($filters['search'])) {
            echo 'Search Results for "' . escape($filters['search']) . '"';
        } else {
            echo 'Browse All Dolls';
        }
        ?>
    </h1>
    
    <div class="filter-section">
        <form method="GET" action="products.php" id="filterForm">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Category</label>
                    <select name="category" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['slug']; ?>" 
                                    <?php echo (isset($filters['category']) && $filters['category'] === $category['slug']) ? 'selected' : ''; ?>>
                                <?php echo escape($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Featured</option>
                        <option value="newest" <?php echo (isset($filters['sort']) && $filters['sort'] === 'newest') ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_low" <?php echo (isset($filters['sort']) && $filters['sort'] === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo (isset($filters['sort']) && $filters['sort'] === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popular" <?php echo (isset($filters['sort']) && $filters['sort'] === 'popular') ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Hair Color</label>
                    <select name="hair_color" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Hair Colors</option>
                        <?php foreach ($filterOptions['hair_colors'] as $option): ?>
                            <?php if (!empty($option['hair_color'])): ?>
                                <option value="<?php echo escape($option['hair_color']); ?>" 
                                        <?php echo (isset($filters['hair_color']) && $filters['hair_color'] === $option['hair_color']) ? 'selected' : ''; ?>>
                                    <?php echo escape($option['hair_color']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Eye Color</label>
                    <select name="eye_color" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Eye Colors</option>
                        <?php foreach ($filterOptions['eye_colors'] as $option): ?>
                            <?php if (!empty($option['eye_color'])): ?>
                                <option value="<?php echo escape($option['eye_color']); ?>" 
                                        <?php echo (isset($filters['eye_color']) && $filters['eye_color'] === $option['eye_color']) ? 'selected' : ''; ?>>
                                    <?php echo escape($option['eye_color']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Size</label>
                    <select name="size" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Sizes</option>
                        <?php foreach ($filterOptions['sizes'] as $option): ?>
                            <?php if (!empty($option['size'])): ?>
                                <option value="<?php echo escape($option['size']); ?>" 
                                        <?php echo (isset($filters['size']) && $filters['size'] === $option['size']) ? 'selected' : ''; ?>>
                                    <?php echo escape($option['size']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Min Price (€)</label>
                    <input type="number" 
                           name="min_price" 
                           min="0" 
                           step="10"
                           value="<?php echo isset($filters['min_price']) ? $filters['min_price'] : ''; ?>"
                           placeholder="Min">
                </div>
                
                <div class="filter-group">
                    <label>Max Price (€)</label>
                    <input type="number" 
                           name="max_price" 
                           min="0" 
                           step="10"
                           value="<?php echo isset($filters['max_price']) ? $filters['max_price'] : ''; ?>"
                           placeholder="Max">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="products.php" class="btn btn-secondary">Clear Filters</a>
            </div>
            
            <?php if (isset($_GET['search'])): ?>
                <input type="hidden" name="search" value="<?php echo escape($_GET['search']); ?>">
            <?php endif; ?>
        </form>
    </div>
    
    <div style="margin: 20px 0; color: var(--text-light);">
        Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> dolls
    </div>
    
    <?php if (!empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" class="product-image">
                        <?php if ($product['primary_image']): ?>
                            <img src="<?php echo UPLOAD_URL . $product['primary_image']; ?>" 
                                 alt="<?php echo escape($product['name']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" 
                                 alt="<?php echo escape($product['name']); ?>"
                                 loading="lazy">
                        <?php endif; ?>
                        <?php if ($product['featured']): ?>
                            <span class="product-badge">Featured</span>
                        <?php endif; ?>
                    </a>
                    
                    <div class="product-info">
                        <div class="product-category"><?php echo escape($product['category_name']); ?></div>
                        <h3 class="product-name">
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>">
                                <?php echo escape($product['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-meta">
                            <div class="product-price">
                                <span class="price-label">
                                    <?php echo $product['current_bid'] ? 'Current Bid' : 'Starting Bid'; ?>
                                </span>
                                <span class="price-value">
                                    <?php 
                                    $displayPrice = $product['current_bid'] ?: $product['base_price'];
                                    $currency = getSelectedCurrency();
                                    $convertedPrice = convertCurrency($displayPrice, $product['currency'], $currency);
                                    echo formatPrice($convertedPrice, $currency);
                                    ?>
                                </span>
                                <?php if ($product['bid_count'] > 0): ?>
                                    <span class="bid-count"><?php echo $product['bid_count']; ?> bids</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">« Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow);">
            <p style="font-size: 18px; color: var(--text-light); margin-bottom: 20px;">No dolls found matching your criteria.</p>
            <a href="products.php" class="btn btn-primary">View All Dolls</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
