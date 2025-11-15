<?php
require_once 'init.php';

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    redirect('/products.php');
}

$productModel = new Product();
$bidModel = new Bid();

$product = $productModel->getBySlug(clean($_GET['slug']));

if (!$product) {
    redirect('/products.php');
}

// Increment view count
$productModel->incrementViews($product['id']);

$pageTitle = $product['name'];
$pageDescription = substr(strip_tags($product['description']), 0, 160);

// Get product images
$images = $productModel->getImages($product['id']);

// Get highest bid
$highestBid = $bidModel->getHighestBid($product['id']);

// Get all bids for display
$bids = $bidModel->getByProduct($product['id'], 'confirmed');

// Get related products
$relatedProducts = $productModel->getRelated($product['id'], $product['category_id'], 4);

// Check if auction is active
$auctionActive = isAuctionActive($product);

// Handle bid submission
$errors = [];
$success = false;

$currentUser = isLoggedIn() ? getCurrentUser() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_bid'])) {
    $bidAmountInDisplayCurrency = floatval($_POST['bid_amount']);
    $displayCurrency = clean($_POST['display_currency']);
    $email = clean($_POST['email']);
    $firstName = clean($_POST['first_name']);
    $lastName = clean($_POST['last_name']);
    $phone = clean($_POST['phone']);
    
    // Convert bid amount back to product's currency
    $bidAmount = convertCurrency($bidAmountInDisplayCurrency, $displayCurrency, $product['currency']);
    
    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address";
    }
    if (empty($firstName) || empty($lastName)) {
        $errors[] = "Please provide your full name";
    }
    if ($bidAmountInDisplayCurrency <= 0) {
        $errors[] = "Bid amount must be greater than 0";
    }
    
    $currentHighest = $product['current_bid'] ?: $product['base_price'];
    $minimumBid = $currentHighest + MIN_BID_INCREMENT;
    
    if ($bidAmount < $minimumBid) {
        $convertedMin = convertCurrency($minimumBid, $product['currency'], $displayCurrency);
        $errors[] = "Minimum bid is " . formatPrice($convertedMin, $displayCurrency);
    }
    
    if (!$auctionActive) {
        $errors[] = "This auction is no longer active";
    }
    
    if (empty($errors)) {
        $bidData = [
            'product_id' => $product['id'],
            'email' => $email,
            'bid_amount' => $bidAmount,
            'currency' => $product['currency'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'status' => BID_CONFIRMATION_REQUIRED ? 'pending' : 'confirmed'
        ];
        
        if (isLoggedIn() && $currentUser) {
            $bidData['user_id'] = $currentUser['id'];
        }
        
        $bidId = $bidModel->create($bidData);
        
        if ($bidId) {
            if (BID_CONFIRMATION_REQUIRED) {
                $bid = $bidModel->getById($bidId);
                $bid['product_name'] = $product['name'];
                sendBidConfirmationEmail($bid);
                $success = "Bid placed successfully! Please check your email to confirm your bid.";
            } else {
                $productModel->updateCurrentBid($product['id'], $bidAmount);
                $success = "Bid placed successfully!";
                redirect('/product.php?slug=' . $product['slug']);
            }
        } else {
            $errors[] = "Failed to place bid. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="product-detail">
        <div class="product-gallery">
            <?php if (!empty($images)): ?>
                <div class="main-image" id="mainImage" onclick="openModal(this)">
                    <img src="<?php echo UPLOAD_URL . $images[0]['image_path']; ?>" 
                         alt="<?php echo escape($product['name']); ?>"
                         id="mainImageSrc">
                </div>
                
                <?php if (count($images) > 1): ?>
                <div class="thumbnail-grid">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onclick="changeMainImage('<?php echo UPLOAD_URL . $image['image_path']; ?>', this)">
                            <img src="<?php echo UPLOAD_URL . $image['image_path']; ?>" 
                                 alt="<?php echo escape($product['name']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="main-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" 
                         alt="<?php echo escape($product['name']); ?>">
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-details">
            <h1><?php echo escape($product['name']); ?></h1>
            <span class="category"><?php echo escape($product['category_name']); ?></span>
            
            <div class="price-section">
                <div class="current-bid">
                    <?php echo $product['current_bid'] ? 'Current Bid' : 'Starting Bid'; ?>
                </div>
                <div class="bid-amount">
                    <?php 
                    $displayPrice = $product['current_bid'] ?: $product['base_price'];
                    $currency = getSelectedCurrency();
                    $convertedPrice = convertCurrency($displayPrice, $product['currency'], $currency);
                    echo formatPrice($convertedPrice, $currency);
                    ?>
                </div>
                <?php if (!empty($bids)): ?>
                    <div class="bid-info">
                        <?php echo count($bids); ?> bid<?php echo count($bids) > 1 ? 's' : ''; ?> placed
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($auctionActive): ?>
                <div class="bid-form">
                    <h3 style="margin-bottom: 20px; color: var(--secondary-color);">Place Your Bid</h3>
                    
                    <form method="POST">
                        <input type="hidden" name="display_currency" value="<?php echo $currency; ?>">
                        
                        <div class="form-group">
                            <label>Your Bid Amount (<?php echo $currency; ?>)</label>
                            <?php
                            $minimumBidInProductCurrency = $displayPrice + MIN_BID_INCREMENT;
                            $minimumBidConverted = convertCurrency($minimumBidInProductCurrency, $product['currency'], $currency);
                            ?>
                            <input type="number" 
                                   name="bid_amount" 
                                   step="0.01" 
                                   min="<?php echo number_format($minimumBidConverted, 2, '.', ''); ?>"
                                   value="<?php echo number_format($minimumBidConverted, 2, '.', ''); ?>"
                                   required>
                            <small style="color: var(--text-light); display: block; margin-top: 5px;">
                                Minimum bid: <?php echo formatPrice($minimumBidConverted, $currency); ?>
                            </small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" 
                                       name="first_name" 
                                       value="<?php echo $currentUser ? escape($currentUser['first_name']) : ''; ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" 
                                       name="last_name" 
                                       value="<?php echo $currentUser ? escape($currentUser['last_name']) : ''; ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" 
                                   name="email" 
                                   value="<?php echo $currentUser ? escape($currentUser['email']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number (Optional)</label>
                            <input type="tel" 
                                   name="phone" 
                                   value="<?php echo $currentUser ? escape($currentUser['phone']) : ''; ?>">
                        </div>
                        
                        <button type="submit" name="place_bid" class="btn btn-primary btn-block">
                            Place Bid
                        </button>
                        
                        <?php if (!isLoggedIn()): ?>
                            <p style="text-align: center; margin-top: 15px; font-size: 14px; color: var(--text-light);">
                                <a href="<?php echo SITE_URL; ?>/register.php" style="color: var(--primary-color);">Create an account</a> for faster bidding
                            </p>
                        <?php endif; ?>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    This auction has ended or the item is no longer available.
                </div>
            <?php endif; ?>
            
            <?php if ($product['description']): ?>
                <div class="description">
                    <h3 style="margin-bottom: 15px; color: var(--secondary-color);">Description</h3>
                    <?php echo nl2br(escape($product['description'])); ?>
                </div>
            <?php endif; ?>
            
            <ul class="property-list">
                <?php if ($product['artist']): ?>
                    <li>
                        <span class="property-name">Artist</span>
                        <span class="property-value"><?php echo escape($product['artist']); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['manufacturer']): ?>
                    <li>
                        <span class="property-name">Manufacturer</span>
                        <span class="property-value"><?php echo escape($product['manufacturer']); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['size']): ?>
                    <li>
                        <span class="property-name">Size</span>
                        <span class="property-value"><?php echo escape($product['size']); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['length']): ?>
                    <li>
                        <span class="property-name">Length</span>
                        <span class="property-value"><?php echo $product['length']; ?> cm</span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['weight']): ?>
                    <li>
                        <span class="property-name">Weight</span>
                        <span class="property-value"><?php echo $product['weight']; ?> kg</span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['hair_color']): ?>
                    <li>
                        <span class="property-name">Hair Color</span>
                        <span class="property-value"><?php echo escape($product['hair_color']); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['eye_color']): ?>
                    <li>
                        <span class="property-name">Eye Color</span>
                        <span class="property-value"><?php echo escape($product['eye_color']); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['skin_tone']): ?>
                    <li>
                        <span class="property-name">Skin Tone</span>
                        <span class="property-value"><?php echo escape($product['skin_tone']); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['material']): ?>
                    <li>
                        <span class="property-name">Material</span>
                        <span class="property-value"><?php echo escape($product['material']); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if ($product['age_appearance']): ?>
                    <li>
                        <span class="property-name">Age Appearance</span>
                        <span class="property-value"><?php echo escape($product['age_appearance']); ?></span>
                    </li>
                <?php endif; ?>
            </ul>
            
            <?php if ($product['includes']): ?>
                <div style="margin: 30px 0;">
                    <h3 style="margin-bottom: 15px; color: var(--secondary-color);">Includes</h3>
                    <p style="color: var(--text-color);"><?php echo nl2br(escape($product['includes'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($relatedProducts)): ?>
        <section style="margin: 60px 0;">
            <h2 style="text-align: center; font-size: 32px; margin-bottom: 40px; color: var(--secondary-color);">Related Dolls</h2>
            
            <div class="products-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="product-card">
                        <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $relatedProduct['slug']; ?>" class="product-image">
                            <?php if ($relatedProduct['primary_image']): ?>
                                <img src="<?php echo UPLOAD_URL . $relatedProduct['primary_image']; ?>" 
                                     alt="<?php echo escape($relatedProduct['name']); ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" 
                                     alt="<?php echo escape($relatedProduct['name']); ?>">
                            <?php endif; ?>
                        </a>
                        
                        <div class="product-info">
                            <div class="product-category"><?php echo escape($relatedProduct['category_name']); ?></div>
                            <h3 class="product-name">
                                <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $relatedProduct['slug']; ?>">
                                    <?php echo escape($relatedProduct['name']); ?>
                                </a>
                            </h3>
                            
                            <div class="product-meta">
                                <div class="product-price">
                                    <span class="price-label">
                                        <?php echo $relatedProduct['current_bid'] ? 'Current Bid' : 'Starting Bid'; ?>
                                    </span>
                                    <span class="price-value">
                                        <?php 
                                        $displayPrice = $relatedProduct['current_bid'] ?: $relatedProduct['base_price'];
                                        $convertedPrice = convertCurrency($displayPrice, $relatedProduct['currency'], $currency);
                                        echo formatPrice($convertedPrice, $currency);
                                        ?>
                                    </span>
                                </div>
                                <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $relatedProduct['slug']; ?>" class="btn btn-primary">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<div class="modal" id="imageModal">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
function changeMainImage(src, element) {
    document.getElementById('mainImageSrc').src = src;
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    element.classList.add('active');
}

function openModal(element) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.classList.add('active');
    modalImg.src = element.querySelector('img').src;
}

function closeModal() {
    document.getElementById('imageModal').classList.remove('active');
}

// Close modal on click outside image
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal with escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>