<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Handle delete user
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    // Don't allow deleting admin users
    $check_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['is_admin'] == 0) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $check_stmt->close();
    header('Location: users.php');
    exit();
}

// Get all users (excluding current admin)
$current_user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, name, email, is_admin, created_at FROM users WHERE id != ? ORDER BY created_at DESC");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$users = $stmt->get_result();

$pageTitle = 'Manage Users - Admin';
include '../includes/header.php';
?>

<h2 class="mb-4">Manage Users</h2>

<?php if ($users->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($user['is_admin']): ?>
                                <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">User</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if (!$user['is_admin']): ?>
                                <a href="users.php?delete=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete</a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No users found.</div>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>

