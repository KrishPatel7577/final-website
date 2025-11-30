<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/image_config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);
$conn = getDBConnection();

// Get order
$stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email 
                        FROM orders o 
                        INNER JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: orders.php');
    exit();
}

$order = $result->fetch_assoc();
$stmt->close();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $status = trim($_POST['status']);
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
    $order['status'] = $status;
    header('Location: order_details.php?id=' . $order_id);
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.id as product_id, p.name, p.image_url, p.category 
                        FROM order_items oi 
                        INNER JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();

$pageTitle = 'Order Details #' . $order_id . ' - Admin';
include '../includes/header.php';
?>

<h2 class="mb-4">Order Details #<?php echo $order_id; ?></h2>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Order Items</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $order_items->fetch_assoc()): 
                            $subtotal = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php 
                                        $imageUrl = getProductImageUrl($item['product_id'], $item['category'] ?? '', $item['image_url']);
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                             class="me-3 rounded" 
                                             style="width: 60px; height: 60px; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th>$<?php echo number_format($order['total_price'], 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Order Information</h4>
            </div>
            <div class="card-body">
                <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
                
                <form method="POST" action="order_details.php?id=<?php echo $order_id; ?>">
                    <div class="mb-3">
                        <label for="status" class="form-label"><strong>Status:</strong></label>
                        <select name="status" id="status" class="form-select">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="orders.php" class="btn btn-outline-secondary w-100">Back to Orders</a>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>

