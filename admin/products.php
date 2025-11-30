<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/image_config.php';
requireAdmin();

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    header('Location: products.php');
    exit();
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");

$pageTitle = 'Manage Products - Admin';
include '../includes/header.php';
?>

<h2 class="mb-4">Manage Products</h2>

<div class="mb-3">
    <a href="product_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Product
    </a>
</div>

<?php if ($products->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <?php 
                            $imageUrl = getProductImageUrl($product['id'], $product['category'], $product['image_url']);
                            ?>
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                 class="rounded"
                                 style="width: 60px; height: 60px; object-fit: cover;"
                                 onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($product['category']))); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="products.php?delete=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No products found.</div>
<?php endif; ?>

<?php
$conn->close();
include '../includes/footer.php';
?>

