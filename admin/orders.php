<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: orders.php');
    exit();
}

// Get all orders
$orders = $conn->query("SELECT o.*, u.name as user_name, u.email 
                        FROM orders o 
                        INNER JOIN users u ON o.user_id = u.id 
                        ORDER BY o.order_date DESC");

$pageTitle = 'Manage Orders - Admin';
include '../includes/header.php';
?>

<h2 class="mb-4">Manage Orders</h2>

<?php if ($orders->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
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
                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                        <td><strong>$<?php echo number_format($order['total_price'], 2); ?></strong></td>
                        <td>
                            <form method="POST" action="orders.php" class="d-inline">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No orders found.</div>
<?php endif; ?>

<?php
$conn->close();
include '../includes/footer.php';
?>

