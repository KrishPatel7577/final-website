<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $image_url = trim($_POST['image_url'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    
    // Validation
    if (empty($name) || empty($description) || $price <= 0 || empty($category)) {
        $error = 'Please fill all required fields.';
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $category, $stock);
        
        if ($stmt->execute()) {
            $success = 'Product added successfully!';
            // Clear form
            $name = $description = $image_url = $category = '';
            $price = $stock = 0;
        } else {
            $error = 'Failed to add product. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Add Product - Admin';
include '../includes/header.php';
?>

<h2 class="mb-4">Add New Product</h2>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="product_add.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required
                                   value="<?php echo isset($price) ? $price : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required
                                   value="<?php echo isset($stock) ? $stock : 0; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category *</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="laptops" <?php echo (isset($category) && $category == 'laptops') ? 'selected' : ''; ?>>Laptops</option>
                            <option value="desktops" <?php echo (isset($category) && $category == 'desktops') ? 'selected' : ''; ?>>Desktops</option>
                            <option value="graphic_cards" <?php echo (isset($category) && $category == 'graphic_cards') ? 'selected' : ''; ?>>Graphic Cards</option>
                            <option value="memories" <?php echo (isset($category) && $category == 'memories') ? 'selected' : ''; ?>>Memories</option>
                            <option value="accessories" <?php echo (isset($category) && $category == 'accessories') ? 'selected' : ''; ?>>Accessories</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="image_url" name="image_url"
                               value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>"
                               placeholder="https://example.com/image.jpg">
                        <small class="text-muted">Leave empty to use placeholder image</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Add Product</button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

