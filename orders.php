<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

$pageTitle = 'My Orders';
include 'includes/header.php';
?>

<h2 class="mb-4">My Orders</h2>

<?php if ($orders->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></td>
                        <td><strong>$<?php echo number_format($order['total_price'], 2); ?></strong></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $order['status'] == 'pending' ? 'warning' : 
                                    ($order['status'] == 'completed' ? 'success' : 'secondary'); 
                            ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <h4>No orders found</h4>
        <p>You haven't placed any orders yet.</p>
        <a href="products.php" class="btn btn-primary">Start Shopping</a>
    </div>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

