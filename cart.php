<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/image_config.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle remove item
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Update cart count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_data = $count_result->fetch_assoc();
    $_SESSION['cart_count'] = $count_data['count'];
    $count_stmt->close();
    
    header('Location: cart.php');
    exit();
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0) {
        // Get product stock
        $check_stmt = $conn->prepare("SELECT p.stock FROM products p 
                                      INNER JOIN cart c ON p.id = c.product_id 
                                      WHERE c.id = ? AND c.user_id = ?");
        $check_stmt->bind_param("ii", $cart_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $product = $check_result->fetch_assoc();
            if ($quantity <= $product['stock']) {
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $update_stmt->bind_param("iii", $quantity, $cart_id, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
        $check_stmt->close();
    }
    
    header('Location: cart.php');
    exit();
}

// Get cart items
$stmt = $conn->prepare("SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image_url, p.stock, p.category 
                        FROM cart c 
                        INNER JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

$total = 0;

$pageTitle = 'Shopping Cart';
include 'includes/header.php';
?>

<h2 class="mb-4">Shopping Cart</h2>

<?php if ($cart_items->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Stock</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $cart_items->fetch_assoc()): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php 
                                $imageUrl = getProductImageUrl($item['product_id'], $item['category'] ?? '', $item['image_url']);
                                ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     class="me-3 rounded" 
                                     style="width: 80px; height: 80px; object-fit: cover;"
                                     data-category="<?php echo htmlspecialchars($item['category'] ?? ''); ?>"
                                     data-product-id="<?php echo $item['product_id']; ?>"
                                     data-product-name="<?php echo htmlspecialchars($item['name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                </div>
                            </div>
                        </td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <form method="POST" action="cart.php" class="d-inline">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['stock']; ?>" 
                                       style="width: 70px;" required>
                                <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-primary">Update</button>
                            </form>
                        </td>
                        <td><?php echo $item['stock']; ?></td>
                        <td><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                        <td>
                            <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Remove this item from cart?')">
                                <i class="bi bi-trash"></i> Remove
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total:</th>
                    <th colspan="2">$<?php echo number_format($total, 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="text-end mt-4">
        <a href="products.php" class="btn btn-outline-secondary me-2">Continue Shopping</a>
        <a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout</a>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <h4>Your cart is empty</h4>
        <p>Start shopping to add items to your cart.</p>
        <a href="products.php" class="btn btn-primary">Browse Products</a>
    </div>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

