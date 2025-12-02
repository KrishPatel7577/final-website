<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/image_config.php';

$conn = getDBConnection();

// Get filter parameters
$category = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$price_range = trim($_GET['price_range'] ?? '');
$in_stock = isset($_GET['in_stock']) ? 1 : 0;

// Build query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($price_range)) {
    switch ($price_range) {
        case 'under_100':
            $query .= " AND price < 100";
            break;
        case '100_500':
            $query .= " AND price BETWEEN 100 AND 500";
            break;
        case '500_1000':
            $query .= " AND price BETWEEN 500 AND 1000";
            break;
        case 'over_1000':
            $query .= " AND price > 1000";
            break;
    }
}

if ($in_stock) {
    $query .= " AND stock > 0";
}

$sort = $_GET['sort'] ?? 'newest';

switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY name ASC";
        break;
    default: // newest
        $query .= " ORDER BY created_at DESC";
        break;
}

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if (!empty($params)) {
    // bind_param requires references, so we need to construct an array of references
    $bind_params = [];
    $bind_params[] = &$types;
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
}

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$products = $stmt->get_result();

// Store count before iterating
$product_count = $products->num_rows;

// Get all categories for filter
$categories_stmt = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = $categories_stmt->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Products - Ramsung';
include 'includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-5">
    <h2 class="mb-0 fw-bold">
        <i class="bi bi-grid-3x3-gap me-2"></i>
        <?php 
        if (!empty($category)) {
            echo ucfirst(str_replace('_', ' ', htmlspecialchars($category)));
        } elseif (!empty($search)) {
            echo 'Search: "' . htmlspecialchars($search) . '"';
        } else {
            echo 'All Products';
        }
        ?>
    </h2>
</div>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-md-12">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-flex gap-2" id="filterForm">
            <div style="width: 300px;">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0" name="search" placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle px-4" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-sliders me-2"></i>Filters
                </button>
                <div class="dropdown-menu p-4" style="min-width: 300px;">
                    <h6 class="dropdown-header px-0 mb-3 text-uppercase small fw-bold text-muted">Filter Options</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                        <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($cat['category']))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Price Range</label>
                        <select class="form-select" name="price_range">
                            <option value="">Any Price</option>
                            <option value="under_100" <?php echo $price_range === 'under_100' ? 'selected' : ''; ?>>Under $100</option>
                            <option value="100_500" <?php echo $price_range === '100_500' ? 'selected' : ''; ?>>$100 - $500</option>
                            <option value="500_1000" <?php echo $price_range === '500_1000' ? 'selected' : ''; ?>>$500 - $1000</option>
                            <option value="over_1000" <?php echo $price_range === 'over_1000' ? 'selected' : ''; ?>>Over $1000</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="stockFilter" <?php echo $in_stock ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="stockFilter">
                                In Stock Only
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline-secondary">Clear All</a>
                    </div>
                </div>
            </div>

            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="text-muted small fw-bold text-nowrap">Sort by:</span>
                <select class="form-select" name="sort" style="width: 180px;" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Products Grid -->
<?php if ($product_count > 0): ?>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php while ($product = $products->fetch_assoc()): ?>
        <div class="col">
            <div class="card product-card h-100 border-0 shadow-sm">
                <?php 
                $imageUrl = getProductImageUrl($product['id'], $product['category'], $product['image_url']);
                ?>
                <div class="position-relative">
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                         class="card-img-top product-image" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         data-category="<?php echo htmlspecialchars($product['category']); ?>"
                         data-product-id="<?php echo $product['id']; ?>"
                         data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                         data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
                         loading="lazy">
                    <?php if ($product['stock'] == 0): ?>
                        <span class="position-absolute top-0 end-0 badge bg-danger m-2">Out of Stock</span>
                    <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill" style="font-size: 0.7rem;">
                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($product['category']))); ?>
                        </span>
                    </div>
                    <h5 class="card-title text-truncate"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text small flex-grow-1"><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></p>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <span class="fw-bold text-primary h5 mb-0">$<?php echo number_format($product['price'], 2); ?></span>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            View
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
    <div class="alert alert-info">
        <h4>No products found</h4>
        <p>Try adjusting your search or filter criteria.</p>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const filterForm = document.getElementById('filterForm');
    
    // Allow Enter key to submit search
    if (searchInput && filterForm) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterForm.submit();
            }
        });
    }
});
</script>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

