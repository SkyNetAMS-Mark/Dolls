<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../init.php';
}

// Get categories for navigation
$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order");

// Handle currency change
if (isset($_POST['change_currency']) && isset($_POST['currency'])) {
    setSelectedCurrency($_POST['currency']);
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
}

$currentCurrency = getSelectedCurrency();
$currencies = $db->fetchAll("SELECT * FROM currency_rates WHERE is_active = 1 ORDER BY currency_code");
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Professional reborn dolls auction'; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
</head>
<body>
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div>
                    <span>Welcome to <?php echo SITE_NAME; ?></span>
                </div>
                <div class="header-links">
                    <a href="<?php echo SITE_URL; ?>/about.php">About</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/account.php">My Account</a>
                        <a href="<?php echo SITE_URL; ?>/logout.php">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php">Login</a>
                        <a href="<?php echo SITE_URL; ?>/register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="<?php echo SITE_URL; ?>/" class="logo">
                        <?php echo SITE_NAME; ?>
                    </a>
                    
                    <div class="search-bar">
                        <form action="<?php echo SITE_URL; ?>/products.php" method="GET" class="search-form">
                            <input type="text" 
                                   name="search" 
                                   class="search-input" 
                                   placeholder="Search for reborn dolls..." 
                                   value="<?php echo isset($_GET['search']) ? escape($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                    
                    <div class="header-actions">
                        <form method="POST" style="display: inline;">
                            <select name="currency" class="currency-selector" onchange="this.form.submit()">
                                <?php foreach ($currencies as $currency): ?>
                                    <option value="<?php echo $currency['currency_code']; ?>" 
                                            <?php echo $currentCurrency === $currency['currency_code'] ? 'selected' : ''; ?>>
                                        <?php echo $currency['symbol'] . ' ' . $currency['currency_code']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="change_currency" value="1">
                        </form>
                        
                        <?php if (isLoggedIn()): ?>
                            <div class="user-menu">
                                <a href="<?php echo SITE_URL; ?>/account.php">
                                    👤 <?php echo escape($_SESSION['user_name']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <nav class="nav">
            <div class="container">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/products.php">All Dolls</a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['slug']; ?>">
                                <?php echo escape($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/about.php">About</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    
    <main>
