<?php $page_title="Log In";
session_start();
require_once './includes/db_connection.php';
include 'includes/header.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT user_id, password_hash, role, is_verified, username FROM users WHERE username = ?");
        $stmt->execute([$username]);    // ← no bind_param
        $user = $stmt->fetch();         // ← returns full row as array or false

        if ($user && password_verify($password, $user['password_hash'])) {
            // Only family need verification
            if ($user['role'] === 'family' && !$user['is_verified']) {
                $error_message = "Your account is not verified. Please check your email.";
            }  else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['username'] = $user['username']; // Store username in session for header display

                // Redirect based on user role
                switch($user['role']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        exit;
                    case 'family':
                        header("Location: family/dashboard.php");
                        exit;
                    case 'caregiver':
                        header("Location: caregivers/dashboard.php");
                        exit;
                    case 'resident':
                        header("Location: residents/dashboard.php");
                        exit;
                    default: //fallback if the users role is not found
                        header("Location: login.php");
                        exit;
                }
                exit;
            }
        } else {
            $error_message = "Invalid username or password.";
        }

    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    } finally {
        $stmt = null;
        $conn = null;
    }
}
?>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Login</h3>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">
            Don't have an account?  <a href="register.php">Sign Up</a>
        </p>

        </div>
    </div>


<?php include 'includes/footer.php'; ?>
