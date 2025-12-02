<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/image_config.php';

$conn = getDBConnection();

// Get featured products (latest 6 products)
$stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featured_products = $stmt->get_result();

$pageTitle = 'Home - Ramsung';
include 'includes/header.php';
?>

<!-- Brand Video Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 24px;">
            <div class="position-relative">
                <video autoplay muted loop playsinline class="w-100" style="object-fit: cover; max-height: 600px;">
                    <source src="pics/final%20intro%20video.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="position-absolute bottom-0 start-0 w-100 p-4 p-md-5" style="background: linear-gradient(to top, rgba(15, 23, 42, 0.9), transparent);">
                    <h2 class="text-white fw-bold mb-2">The Future is Here</h2>
                    <p class="text-white-50 mb-0" style="max-width: 500px;">Experience the next generation of computing technology with Ramsung's cutting-edge innovations.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="jumbotron text-center text-white p-5 rounded mb-5 position-relative overflow-hidden">
    
    <div class="position-relative" style="z-index: 2;">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3 px-3 py-2 rounded-pill">
            NEW ARRIVALS 2025
        </span>
        <h1 class="display-3 fw-bold mb-4">
            Elevate Your <br>Digital Experience
        </h1>
        <p class="lead text-secondary mb-5 mx-auto" style="max-width: 600px;">
            Discover a curated collection of premium computing hardware designed for performance, aesthetics, and the future of work.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a class="btn btn-primary btn-lg px-5 py-3" href="products.php" role="button">
                Shop Collection
            </a>
            <a class="btn btn-outline-primary btn-lg px-5 py-3" href="register.php" role="button">
                Join Ramsung
            </a>
        </div>
    </div>
</div>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0 fw-bold">
        Featured Collection
    </h3>
    <a href="products.php" class="btn btn-link text-decoration-none text-primary">
        View All <i class="bi bi-arrow-right ms-2"></i>
    </a>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php while ($product = $featured_products->fetch_assoc()): ?>
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
                    <?php if ($product['stock'] < 5): ?>
                        <span class="position-absolute top-0 end-0 badge bg-danger m-2">Low Stock</span>
                    <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill" style="font-size: 0.7rem;">
                            <?php echo htmlspecialchars($product['category']); ?>
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

<!-- Contact Us Section -->
<div id="contact" class="row mt-5 mb-5 align-items-stretch">
    <div class="col-md-5 mb-4 mb-md-0">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="card-body">
                <h3 class="fw-bold mb-4">Get in Touch</h3>
                <p class="text-secondary mb-4">Have questions about our premium hardware? We're here to help you build your dream setup.</p>
                
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 text-primary">
                        <i class="bi bi-envelope-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Email Us</h6>
                        <a href="mailto:support@comp.com" class="text-decoration-none text-secondary">support@comp.com</a>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 text-primary">
                        <i class="bi bi-telephone-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Call Us</h6>
                        <p class="mb-0 text-secondary">1800-102-231</p>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-5">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 text-primary">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Visit Us</h6>
                        <p class="mb-0 text-secondary">24 Main Street, Brampton, Ontario</p>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Follow Us</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-facebook"></i>
                    </button>
                    <button class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-instagram"></i>
                    </button>
                    <button class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-twitter"></i>
                    </button>
                    <button class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-linkedin"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="card-body">
                <h3 class="fw-bold mb-4">Send a Message</h3>
                <form action="mailto:support@comp.com" method="post" enctype="text/plain">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                                <label for="name">Your Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                <label for="email">Email Address</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
                                <label for="subject">Subject</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" placeholder="Leave a comment here" id="message" name="message" style="height: 150px" required></textarea>
                                <label for="message">Message</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary btn-lg w-100 py-3" type="submit">
                                Send Message <i class="bi bi-send ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

