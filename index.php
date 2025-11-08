<?php
require_once 'init.php';

$pageTitle = 'Home';
$pageDescription = 'Discover unique handcrafted reborn dolls through our auction platform';

// Get featured products
$productModel = new Product();
$featuredProducts = $productModel->getFeatured(6);

// Get categories
$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order LIMIT 4");

require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Welcome to <?php echo SITE_NAME; ?></h1>
        <p>Discover unique, handcrafted reborn dolls through our secure bidding platform</p>
        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Browse All Dolls</a>
    </div>
</section>

<div class="container">
    <?php if (!empty($featuredProducts)): ?>
    <section style="margin: 60px 0;">
        <h2 style="text-align: center; font-size: 36px; margin-bottom: 40px; color: var(--secondary-color);">Featured Dolls</h2>
        
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
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
                            </div>
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-outline">View All Dolls</a>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if (!empty($categories)): ?>
    <section style="margin: 60px 0;">
        <h2 style="text-align: center; font-size: 36px; margin-bottom: 40px; color: var(--secondary-color);">Shop by Category</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['slug']; ?>" 
                   style="background: white; padding: 40px; text-align: center; border-radius: var(--border-radius); box-shadow: var(--shadow); transition: var(--transition);"
                   onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='var(--shadow-lg)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)';">
                    <h3 style="font-size: 24px; color: var(--secondary-color); margin-bottom: 10px;">
                        <?php echo escape($category['name']); ?>
                    </h3>
                    <?php if ($category['description']): ?>
                        <p style="color: var(--text-light);">
                            <?php echo escape($category['description']); ?>
                        </p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <section style="margin: 60px 0; background: white; padding: 50px; border-radius: var(--border-radius); box-shadow: var(--shadow);">
        <h2 style="text-align: center; font-size: 36px; margin-bottom: 40px; color: var(--secondary-color);">How It Works</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 36px; font-weight: 700;">1</div>
                <h3 style="margin-bottom: 15px; color: var(--secondary-color);">Browse Dolls</h3>
                <p style="color: var(--text-light);">Explore our collection of unique, handcrafted reborn dolls</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 36px; font-weight: 700;">2</div>
                <h3 style="margin-bottom: 15px; color: var(--secondary-color);">Place Your Bid</h3>
                <p style="color: var(--text-light);">Submit your bid and confirm via email</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 36px; font-weight: 700;">3</div>
                <h3 style="margin-bottom: 15px; color: var(--secondary-color);">Secure Payment</h3>
                <p style="color: var(--text-light);">Pay securely via PayPal or other payment methods</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 36px; font-weight: 700;">4</div>
                <h3 style="margin-bottom: 15px; color: var(--secondary-color);">Receive Your Doll</h3>
                <p style="color: var(--text-light);">Your doll will be carefully packaged and shipped to you</p>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
