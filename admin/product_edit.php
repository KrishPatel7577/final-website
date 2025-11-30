<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = intval($_GET['id']);
$conn = getDBConnection();

// Get product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $image_url = trim($_POST['image_url'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    
    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFilename = uniqid('prod_', true) . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadFile)) {
                $image_url = 'uploads/' . $newFilename; // Store relative path
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.';
        }
    }

    // Validation
    if (empty($name) || empty($description) || $price <= 0 || empty($category)) {
        $error = 'Please fill all required fields.';
    } elseif (empty($error)) { // Only proceed if no upload error
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, category = ?, stock = ? WHERE id = ?");
        $stmt->bind_param("ssdssii", $name, $description, $price, $image_url, $category, $stock, $product_id);
        
        if ($stmt->execute()) {
            $success = 'Product updated successfully!';
            // Update local product data
            $product['name'] = $name;
            $product['description'] = $description;
            $product['price'] = $price;
            $product['image_url'] = $image_url;
            $product['category'] = $category;
            $product['stock'] = $stock;
        } else {
            $error = 'Failed to update product. Please try again.';
        }
        
        $stmt->close();
    }
}

$pageTitle = 'Edit Product - Admin';
include '../includes/header.php';
?>

<h2 class="mb-4">Edit Product</h2>

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
                
                <form method="POST" action="product_edit.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required
                                   value="<?php echo $product['price']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required
                                   value="<?php echo $product['stock']; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category *</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="laptops" <?php echo $product['category'] == 'laptops' ? 'selected' : ''; ?>>Laptops</option>
                            <option value="desktops" <?php echo $product['category'] == 'desktops' ? 'selected' : ''; ?>>Desktops</option>
                            <option value="graphic_cards" <?php echo $product['category'] == 'graphic_cards' ? 'selected' : ''; ?>>Graphic Cards</option>
                            <option value="memories" <?php echo $product['category'] == 'memories' ? 'selected' : ''; ?>>Memories</option>
                            <option value="accessories" <?php echo $product['category'] == 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <div class="input-group mb-2">
                            <input type="file" class="form-control" id="image_file" name="image_file" accept="image/*">
                        </div>
                        <div class="form-text mb-2">Or enter a URL manually:</div>
                        <input type="text" class="form-control" id="image_url" name="image_url"
                               value="<?php echo htmlspecialchars($product['image_url']); ?>"
                               placeholder="https://example.com/image.jpg or uploads/image.jpg">
                        <?php if (!empty($product['image_url'])): ?>
                            <div class="mt-2">
                                <small class="text-muted">Current Image:</small><br>
                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current" style="height: 50px; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>

