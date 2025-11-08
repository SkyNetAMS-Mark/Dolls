    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Your trusted source for authentic, handcrafted reborn dolls. Each doll is unique and created with love and attention to detail.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="<?php echo SITE_URL; ?>/">Home</a>
                    <a href="<?php echo SITE_URL; ?>/products.php">Browse Dolls</a>
                    <a href="<?php echo SITE_URL; ?>/about.php">About Us</a>
                    <a href="<?php echo SITE_URL; ?>/contact.php">Contact</a>
                </div>
                
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <a href="<?php echo SITE_URL; ?>/how-it-works.php">How Bidding Works</a>
                    <a href="<?php echo SITE_URL; ?>/shipping.php">Shipping Information</a>
                    <a href="<?php echo SITE_URL; ?>/faq.php">FAQ</a>
                    <a href="<?php echo SITE_URL; ?>/terms.php">Terms & Conditions</a>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: <?php echo SITE_EMAIL; ?></p>
                    <p>We respond within 24 hours</p>
                    <?php if (isAdminLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/" style="margin-top: 15px; display: inline-block; padding: 8px 15px; background: var(--primary-color); border-radius: 5px;">Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
