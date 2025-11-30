<?php
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getDBConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Register';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
            <div class="card-header">
                <h3 class="text-center mb-0">
                    <i class="bi bi-person-plus me-2"></i>User Registration
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php" id="registerForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-person me-2"></i>Full Name
                        </label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name" required
                               placeholder="Enter your full name"
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required
                               placeholder="Enter your email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required minlength="6"
                               placeholder="Enter your password">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock-fill me-2"></i>Confirm Password
                        </label>
                        <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required
                               placeholder="Confirm your password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-person-plus me-2"></i>Register
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="mb-0">
                        Already have an account? 
                        <a href="login.php" class="text-decoration-none fw-bold">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>

