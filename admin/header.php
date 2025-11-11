<?php
if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

// Get the current script name to set the active link
$current_page = basename($_SERVER['SCRIPT_NAME']);

$nav_links = [
    'index.php' => '📊 Dashboard',
    'products.php' => '🎨 Products',
    'add-product.php' => '➕ Add Product',
    'bids.php' => '🔨 Bids',
    'orders.php' => '📦 Orders',
    'categories.php' => '📁 Categories',
    'users.php' => '👥 Users',
    'shipping.php' => '🚚 Shipping Rates',
    'settings.php' => '⚙️ Settings',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' : ''; ?>Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .admin-wrapper {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        .admin-sidebar {
            background: var(--secondary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            width: 250px;
            height: 100%;
            overflow-y: auto;
        }
        .admin-sidebar h2 {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            font-size: 20px;
            color: white;
        }
        .admin-nav a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            transition: var(--transition);
            border-left: 3px solid transparent;
        }
        .admin-nav a:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            font-weight: 600;
            border-left-color: var(--primary-color);
        }
        .admin-content-wrapper {
            grid-column: 2 / -1;
        }
        .admin-content {
            background: var(--bg-light);
            padding: 30px;
            min-height: 100vh;
        }
        .admin-header {
            background: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: var(--border-radius);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        .stat-card h3 {
            font-size: 14px;
            color: var(--text-light);
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
        }
        .data-table {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow-x: auto;
        }
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            background: var(--bg-light);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: var(--success-color); color: white; }
        .badge-warning { background: var(--warning-color); color: white; }
        .badge-danger { background: var(--danger-color); color: white; }
        .badge-secondary { background: var(--text-light); color: white; }
        .badge-info { background: #17a2b8; color: white; }
        
        /* Form Styles */
        .form-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary-color);
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 15px;
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(196, 30, 58, 0.1);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <div class="admin-sidebar">
            <h2><?php echo SITE_NAME; ?></h2>
            <nav class="admin-nav">
                <?php foreach ($nav_links as $file => $name): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/<?php echo $file; ?>"
                       class="<?php echo ($current_page === $file || ($current_page === 'edit-product.php' && $file === 'products.php')) ? 'active' : ''; ?>">
                       <?php echo $name; ?>
                    </a>
                <?php endforeach; ?>
                
                <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;">
                <a href="<?php echo SITE_URL; ?>/" target="_blank">🌐 View Site</a>
                <a href="<?php echo SITE_URL; ?>/admin/logout.php">🚪 Logout</a>
            </nav>
        </div>
        
        <div class="admin-content-wrapper">
            <div class="admin-content">
                <div class="admin-header">
                    <h1><?php echo isset($pageTitle) ? escape($pageTitle) : 'Dashboard'; ?></h1>
                    <div>
                        <span style="color: var(--text-light);">Welcome, <strong><?php echo escape($_SESSION['admin_name'] ?? 'Admin'); ?></strong></span>
                    </div>
                </div>