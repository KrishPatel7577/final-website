<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/image_config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: orders.php');
    exit();
}

$order = $result->fetch_assoc();
$stmt->close();

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.id as product_id, p.name, p.image_url, p.category 
                        FROM order_items oi 
                        INNER JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();

$pageTitle = 'Order Details #' . $order_id;
include 'includes/header.php';
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
                <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-<?php 
                        echo $order['status'] == 'pending' ? 'warning' : 
                            ($order['status'] == 'completed' ? 'success' : 'secondary'); 
                    ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
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
include 'includes/footer.php';
?>

