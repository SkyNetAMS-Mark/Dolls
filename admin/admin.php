<?php
require_once 'init.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Get statistics
$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stmt->execute();
$userCount = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM products");
$stmt->execute();
$productCount = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
$stmt->execute();
$activeAuctions = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM bids");
$stmt->execute();
$totalBids = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT SUM(amount) as total FROM bids");
$stmt->execute();
$totalBidAmount = $stmt->fetch()['total'] ?? 0;

// Get recent products
$stmt = $db->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recentProducts = $stmt->fetchAll();

// Get recent bids
$stmt = $db->prepare("
    SELECT b.*, p.title, u.username 
    FROM bids b 
    JOIN products p ON b.product_id = p.id 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recentBids = $stmt->fetchAll();

include 'header.php';
?>

<div class="container mt-4">
    <h1>Admin Dashboard</h1>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo $userCount; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text display-4"><?php echo $productCount; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Active Auctions</h5>
                    <p class="card-text display-4"><?php echo $activeAuctions; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Bids</h5>
                    <p class="card-text display-4"><?php echo $totalBids; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="admin-product-form.php" class="btn btn-primary btn-block mb-2">Add New Product</a>
                    <a href="admin-products.php" class="btn btn-secondary btn-block mb-2">Manage Products</a>
                    <a href="admin-users.php" class="btn btn-secondary btn-block mb-2">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Bids</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <?php if (empty($recentBids)): ?>
                        <p class="text-muted">No bids yet</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBids as $bid): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bid['username']); ?></td>
                                        <td><?php echo htmlspecialchars($bid['title']); ?></td>
                                        <td>€<?php echo number_format($bid['amount'], 2); ?></td>
                                        <td><?php echo date('d/m H:i', strtotime($bid['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Products</h5>
                    <a href="admin-products.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentProducts)): ?>
                        <p class="text-muted">No products yet</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Starting Price</th>
                                    <th>Current Price</th>
                                    <th>Status</th>
                                    <th>End Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentProducts as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                                        <td>€<?php echo number_format($product['starting_price'], 2); ?></td>
                                        <td>€<?php echo number_format($product['current_price'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($product['end_date'])); ?></td>
                                        <td>
                                            <a href="admin-product-form.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" target="_blank">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
