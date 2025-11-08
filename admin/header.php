<?php
if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}
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
        }
        .admin-sidebar h2 {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .admin-nav a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            transition: var(--transition);
        }
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .admin-content {
            background: var(--bg-light);
            padding: 30px;
        }
        .admin-header {
            background: white;
            padding: 20px 30px;
            margin: -30px -30px 30px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            overflow: hidden;
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
        }
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
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
        .badge-success {
            background: var(--success-color);
            color: white;
        }
        .badge-warning {
            background: var(--warning-color);
            color: white;
        }
        .badge-danger {
            background: var(--danger-color);
            color: white;
        }
        .badge-secondary {
            background: var(--text-light);
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <div class="admin-sidebar">
            <h2 style="color: white; font-size: 20px;">Admin Panel</h2>
            <nav class="admin-nav">
                <a href="<?php echo SITE_URL; ?>/admin/">📊 Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/admin/products.php">🎨 Products</a>
                <a href="<?php echo SITE_URL; ?>/admin/add-product.php">➕ Add Product</a>
                <a href="<?php echo SITE_URL; ?>/admin/bids.php">🔨 Bids</a>
                <a href="<?php echo SITE_URL; ?>/admin/orders.php">📦 Orders</a>
                <a href="<?php echo SITE_URL; ?>/admin/categories.php">📁 Categories</a>
                <a href="<?php echo SITE_URL; ?>/admin/shipping.php">🚚 Shipping Rates</a>
                <a href="<?php echo SITE_URL; ?>/admin/settings.php">⚙️ Settings</a>
                <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;">
                <a href="<?php echo SITE_URL; ?>/" target="_blank">🌐 View Site</a>
                <a href="<?php echo SITE_URL; ?>/admin/logout.php">🚪 Logout</a>
            </nav>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1><?php echo isset($pageTitle) ? escape($pageTitle) : 'Dashboard'; ?></h1>
                <div>
                    <span style="color: var(--text-light);">Welcome, <strong><?php echo escape($_SESSION['admin_name'] ?? 'Admin'); ?></strong></span>
                </div>
            </div>
