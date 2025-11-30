<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Update cart count
                $cart_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
                $cart_stmt->bind_param("i", $user['id']);
                $cart_stmt->execute();
                $cart_result = $cart_stmt->get_result();
                $cart_data = $cart_result->fetch_assoc();
                $_SESSION['cart_count'] = $cart_data['count'];
                $cart_stmt->close();
                
                // Redirect
                if ($user['is_admin']) {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>



<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-lg">
            <div class="card-header">
                <h3 class="text-center mb-0">
                    <i class="bi bi-box-arrow-in-right me-2"></i>User Login
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" id="loginForm">
                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required
                               placeholder="Enter your email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="mb-0">
                        Don't have an account? 
                        <a href="register.php" class="text-decoration-none fw-bold">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

