<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result->fetch_assoc()['count'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $result->fetch_assoc()['count'];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
$stats['users'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'");
$revenue = $result->fetch_assoc()['total'];
$stats['revenue'] = $revenue ? $revenue : 0;

// Recent orders
$recent_orders = $conn->query("SELECT o.*, u.name as user_name 
                                FROM orders o 
                                INNER JOIN users u ON o.user_id = u.id 
                                ORDER BY o.order_date DESC 
                                LIMIT 5");

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
?>

<h2 class="mb-4">Admin Dashboard</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Products</h5>
                <h2><?php echo $stats['products']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <h2><?php echo $stats['orders']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2><?php echo $stats['users']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h2>$<?php echo number_format($stats['revenue'], 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Quick Actions</h4>
            </div>
            <div class="card-body">
                <a href="products.php" class="btn btn-primary me-2">Manage Products</a>
                <a href="orders.php" class="btn btn-success me-2">View Orders</a>
                <a href="users.php" class="btn btn-info me-2">Manage Users</a>
                <a href="../index.php" class="btn btn-secondary">View Store</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Recent Orders</h4>
            </div>
            <div class="card-body">
                <?php if ($recent_orders->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['status'] == 'pending' ? 'warning' : 
                                                ($order['status'] == 'completed' ? 'success' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>

