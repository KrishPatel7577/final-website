<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.stock 
                        FROM cart c 
                        INNER JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

if ($cart_items->num_rows == 0) {
    header('Location: cart.php');
    exit();
}

$total = 0;
$items = [];

// Calculate total and check stock
while ($item = $cart_items->fetch_assoc()) {
    if ($item['quantity'] > $item['stock']) {
        header('Location: cart.php');
        exit();
    }
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items[] = $item;
}

$error = '';
$success = false;

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')");
        $order_stmt->bind_param("id", $user_id, $total);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();
        
        // Create order items and update stock
        foreach ($items as $item) {
            // Insert order item
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $item_stmt->execute();
            $item_stmt->close();
            
            // Update product stock
            $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stock_stmt->execute();
            $stock_stmt->close();
        }
        
        // Clear cart
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        $clear_stmt->close();
        
        // Update session cart count
        $_SESSION['cart_count'] = 0;
        
        // Commit transaction
        $conn->commit();
        $success = true;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Order placement failed. Please try again.';
    }
}

$pageTitle = 'Checkout';
include 'includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success text-center">
        <h2><i class="bi bi-check-circle"></i> Order Placed Successfully!</h2>
        <p class="lead">Thank you for your purchase. Your order has been received.</p>
        <a href="orders.php" class="btn btn-primary">View My Orders</a>
        <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
    </div>
<?php else: ?>
    <h2 class="mb-4">Checkout</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Order Summary</h4>
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
                            <?php foreach ($items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th>$<?php echo number_format($total, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Payment Details</h4>
                </div>
                <div class="card-body">
                    <p class="mb-4"><strong>Total Amount: <span class="text-primary">$<?php echo number_format($total, 2); ?></span></strong></p>
                    
                    <form method="POST" action="checkout.php" id="checkoutForm">
                        <h6 class="mb-3">Select Payment Method</h6>
                        
                        <div class="d-grid gap-2 mb-4">
                            <div class="btn-group" role="group" aria-label="Payment Method">
                                <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="card" checked autocomplete="off">
                                <label class="btn btn-outline-primary" for="pay_card">
                                    <i class="bi bi-credit-card-2-front me-2"></i>Card
                                </label>

                                <input type="radio" class="btn-check" name="payment_method" id="pay_paypal" value="paypal" autocomplete="off">
                                <label class="btn btn-outline-primary" for="pay_paypal">
                                    <i class="bi bi-paypal me-2"></i>PayPal
                                </label>

                                <input type="radio" class="btn-check" name="payment_method" id="pay_gpay" value="gpay" autocomplete="off">
                                <label class="btn btn-outline-primary" for="pay_gpay">
                                    <i class="bi bi-google me-2"></i>GPay
                                </label>
                            </div>
                        </div>

                        <!-- Credit Card Form -->
                        <div id="card-details" class="mb-4">
                            <div class="mb-3">
                                <label for="card_name" class="form-label small text-muted">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" placeholder="John Doe">
                            </div>
                            <div class="mb-3">
                                <label for="card_number" class="form-label small text-muted">Card Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="bi bi-credit-card"></i></span>
                                    <input type="text" class="form-control border-start-0" id="card_number" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="card_expiry" class="form-label small text-muted">Expiry</label>
                                        <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="card_cvv" class="form-label small text-muted">CVV</label>
                                        <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="123" maxlength="3">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PayPal/GPay Message -->
                        <div id="wallet-message" class="alert alert-info mb-4 d-none">
                            <i class="bi bi-info-circle me-2"></i> You will be redirected to complete your payment securely.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="place_order" class="btn btn-primary btn-lg">
                                <i class="bi bi-lock-fill me-2"></i> Pay $<?php echo number_format($total, 2); ?>
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary">Back to Cart</a>
                        </div>
                        
                        <p class="text-muted small text-center mt-3 mb-0">
                            <i class="bi bi-shield-lock"></i> Secure Payment
                        </p>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cardRadio = document.getElementById('pay_card');
            const paypalRadio = document.getElementById('pay_paypal');
            const gpayRadio = document.getElementById('pay_gpay');
            const cardDetails = document.getElementById('card-details');
            const walletMessage = document.getElementById('wallet-message');
            const cardInputs = cardDetails.querySelectorAll('input');

            function togglePaymentMethod() {
                if (cardRadio.checked) {
                    cardDetails.classList.remove('d-none');
                    walletMessage.classList.add('d-none');
                    cardInputs.forEach(input => input.required = true);
                } else {
                    cardDetails.classList.add('d-none');
                    walletMessage.classList.remove('d-none');
                    cardInputs.forEach(input => input.required = false);
                }
            }

            // Listen for changes
            const radios = document.querySelectorAll('input[name="payment_method"]');
            radios.forEach(radio => {
                radio.addEventListener('change', togglePaymentMethod);
            });

            // Initial state
            togglePaymentMethod();

            // Simple Card Formatting
            document.getElementById('card_number').addEventListener('input', function (e) {
                e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
            });
            
            document.getElementById('card_expiry').addEventListener('input', function (e) {
                var input = e.target.value.replace(/\D/g, '').substring(0,4);
                var month = input.substring(0,2);
                var year = input.substring(2,4);
                if (input.length > 2) {
                    e.target.value = month + '/' + year;
                } else {
                    e.target.value = month;
                }
            });
        });
        </script>
    </div>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

