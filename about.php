<?php
require_once 'init.php';

$pageTitle = 'About Us';
$pageDescription = 'Learn about our reborn doll collection and commitment to quality';

require_once 'includes/header.php';
?>

<div class="container" style="margin: 60px auto; max-width: 900px;">
    <div style="background: white; padding: 50px; border-radius: var(--border-radius); box-shadow: var(--shadow);">
        <h1 style="text-align: center; margin-bottom: 40px; color: var(--secondary-color);">About Us</h1>
        
        <div style="line-height: 1.8; color: var(--text-color);">
            <h2 style="color: var(--secondary-color); margin: 30px 0 20px;">Welcome to <?php echo SITE_NAME; ?></h2>
            
            <p style="margin-bottom: 20px;">
                We are passionate about bringing the artistry and beauty of reborn dolls to collectors worldwide. Our platform 
                connects talented artists and collectors through a secure and transparent bidding system.
            </p>
            
            <p style="margin-bottom: 20px;">
                Each reborn doll featured on our site is handcrafted with meticulous attention to detail, using high-quality 
                materials and techniques. These lifelike creations are more than just dolls – they are works of art that bring 
                joy and wonder to their owners.
            </p>
            
            <h2 style="color: var(--secondary-color); margin: 30px 0 20px;">Our Mission</h2>
            
            <p style="margin-bottom: 20px;">
                Our mission is to provide a trustworthy marketplace where:
            </p>
            
            <ul style="margin: 20px 0 20px 30px; list-style: disc;">
                <li style="margin-bottom: 10px;">Artists can showcase their incredible talent and reach a global audience</li>
                <li style="margin-bottom: 10px;">Collectors can discover unique, one-of-a-kind reborn dolls</li>
                <li style="margin-bottom: 10px;">Every transaction is secure, transparent, and fair</li>
                <li style="margin-bottom: 10px;">The reborn doll community can grow and thrive</li>
            </ul>
            
            <h2 style="color: var(--secondary-color); margin: 30px 0 20px;">Why Choose Us?</h2>
            
            <ul style="margin: 20px 0 20px 30px; list-style: disc;">
                <li style="margin-bottom: 10px;"><strong>Authenticity Guaranteed:</strong> Every doll is verified for quality and craftsmanship</li>
                <li style="margin-bottom: 10px;"><strong>Secure Bidding:</strong> Your bids are protected with email confirmation</li>
                <li style="margin-bottom: 10px;"><strong>Safe Payments:</strong> Multiple secure payment options including PayPal</li>
                <li style="margin-bottom: 10px;"><strong>Worldwide Shipping:</strong> We ship carefully packaged dolls globally</li>
                <li style="margin-bottom: 10px;"><strong>Customer Support:</strong> Our team is here to help with any questions</li>
            </ul>
            
            <h2 style="color: var(--secondary-color); margin: 30px 0 20px;">Contact Us</h2>
            
            <p style="margin-bottom: 20px;">
                Have questions? We'd love to hear from you!
            </p>
            
            <p style="margin-bottom: 10px;">
                <strong>Email:</strong> <a href="mailto:<?php echo SITE_EMAIL; ?>" style="color: var(--primary-color);"><?php echo SITE_EMAIL; ?></a>
            </p>
            
            <p style="margin-bottom: 20px;">
                We typically respond within 24 hours.
            </p>
        </div>
        
        <div style="margin-top: 50px; text-align: center;">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Browse Our Collection</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
