<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/image_config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = intval($_GET['id']);
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();
$added_to_cart = false;
$error = '';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($quantity < 1) {
        $error = 'Quantity must be at least 1.';
    } elseif ($quantity > $product['stock']) {
        $error = 'Insufficient stock. Available: ' . $product['stock'];
    } else {
        $user_id = $_SESSION['user_id'];
        
        // Check if item already in cart
        $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update quantity
            $cart_item = $check_result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            if ($new_quantity > $product['stock']) {
                $error = 'Cannot add more items. Available stock: ' . $product['stock'];
            } else {
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                $update_stmt->execute();
                $update_stmt->close();
                $added_to_cart = true;
            }
        } else {
            // Add new item
            $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $insert_stmt->execute();
            $insert_stmt->close();
            $added_to_cart = true;
        }
        
        $check_stmt->close();
        
        if ($added_to_cart) {
            // Update cart count in session
            $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_data = $count_result->fetch_assoc();
            $_SESSION['cart_count'] = $count_data['count'];
            $count_stmt->close();
        }
    }
}

$pageTitle = $product['name'] . ' - Ramsung';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <?php 
        $imageUrl = getProductImageUrl($product['id'], $product['category'], $product['image_url']);
        ?>
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
             class="img-fluid rounded shadow-lg product-detail-image" 
             alt="<?php echo htmlspecialchars($product['name']); ?>"
             data-category="<?php echo htmlspecialchars($product['category']); ?>"
             data-product-id="<?php echo $product['id']; ?>"
             data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
             data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
             style="border-radius: 20px;"
             loading="lazy"
             style="border-radius: 20px;">
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-lg p-4">
            <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="mb-4">
                <span class="badge bg-secondary me-2"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($product['category']))); ?></span>
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>In Stock (<?php echo $product['stock']; ?> available)
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger">
                        <i class="bi bi-x-circle me-1"></i>Out of Stock
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <h2 class="text-primary fw-bold mb-0">$<?php echo number_format($product['price'], 2); ?></h2>
                <small class="text-muted">Price includes all taxes</small>
            </div>
            
            <div class="mb-4">
                <h4 class="fw-bold mb-3">
                    <i class="bi bi-info-circle me-2"></i>Description
                </h4>
                <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        
            <?php if ($added_to_cart): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>Product added to cart successfully!
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isLoggedIn()): ?>
                <?php if ($product['stock'] > 0): ?>
                    <form method="POST" action="product.php?id=<?php echo $product_id; ?>" class="mt-4">
                        <div class="mb-4">
                            <label for="quantity" class="form-label fw-bold">
                                <i class="bi bi-123 me-2"></i>Quantity
                            </label>
                            <input type="number" class="form-control form-control-lg" id="quantity" name="quantity" 
                                   value="1" min="1" max="<?php echo $product['stock']; ?>" required
                                   style="max-width: 150px;">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100" disabled>
                        <i class="bi bi-x-circle me-2"></i>Out of Stock
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>Please <a href="login.php" class="alert-link fw-bold">login</a> to add items to cart.
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="products.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-2"></i>Back to Products
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reviews Section -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 shadow-lg p-4">
            <h3 class="fw-bold mb-4"><i class="bi bi-star-fill text-warning me-2"></i>Customer Reviews</h3>
            
            <?php
            // Generate reviews
            $fake_reviews = [
                ['name' => 'Alex M.', 'rating' => 5, 'date' => '2 days ago', 'text' => 'Absolutely amazing product! Exceeded my expectations in every way. Highly recommended!'],
                ['name' => 'Sarah J.', 'rating' => 4, 'date' => '1 week ago', 'text' => 'Great quality for the price. Shipping was fast too. Just wish it came in more colors.'],
                ['name' => 'Michael B.', 'rating' => 5, 'date' => '2 weeks ago', 'text' => 'Best purchase I\'ve made this year. The build quality is top-notch and it works perfectly.'],
                ['name' => 'Emily R.', 'rating' => 4, 'date' => '1 month ago', 'text' => 'Solid performance. I use it daily and haven\'t had any issues. Good value.']
            ];
            
            // Randomize slightly based on product ID to make it look dynamic
            srand($product_id);
            shuffle($fake_reviews);
            $avg_rating = 4.0 + (rand(0, 9) / 10); // Random rating between 4.0 and 4.9
            $review_count = rand(15, 120);
            ?>
            
            <div class="row align-items-center mb-5">
                <div class="col-md-4 text-center border-end">
                    <h1 class="display-1 fw-bold text-primary mb-0"><?php echo $avg_rating; ?></h1>
                    <div class="mb-2">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="bi bi-star-fill <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?> fs-4"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-muted">Based on <?php echo $review_count; ?> reviews</p>
                </div>
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted me-3">5 Stars</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo rand(60, 80); ?>%"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted me-3">4 Stars</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo rand(15, 30); ?>%"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted me-3">3 Stars</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo rand(5, 10); ?>%"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted me-3">2 Stars</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo rand(0, 5); ?>%"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">1 Star&nbsp;</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo rand(0, 2); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="reviews-list">
                <?php foreach($fake_reviews as $review): ?>
                    <div class="review-item mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0"><?php echo $review['name']; ?></h5>
                            <small class="text-muted"><?php echo $review['date']; ?></small>
                        </div>
                        <div class="mb-2">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="bi bi-star-fill <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-secondary"><?php echo $review['text']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary">Load More Reviews</button>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

