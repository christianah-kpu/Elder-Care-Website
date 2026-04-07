<!-- Sonny -->
<?php 
$page_title="Log In";

session_start();
require_once './includes/db_connection.php';
include 'includes/header.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {

        // ===============================
        // GET USER
        // ===============================
        $stmt = $conn->prepare("
            SELECT user_id, password_hash, role, is_verified, username 
            FROM users 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // ===============================
        // VERIFY PASSWORD
        // ===============================
        if ($user && password_verify($password, $user['password_hash'])) {

            // ===============================
            // CHECK IF USER IS SUSPENDED
            // ===============================
            $statusStmt = $conn->prepare("
                SELECT status FROM user_status WHERE user_id = ?
            ");
            $statusStmt->execute([$user['user_id']]);
            $statusRow = $statusStmt->fetch();

            if ($statusRow && $statusRow['status'] === 'suspended') {
                $error_message = "Your account has been suspended. Please contact the administrator.";
            }

            // ===============================
            // CHECK EMAIL VERIFICATION (FAMILY ONLY)
            // ===============================
            elseif ($user['role'] === 'family' && !$user['is_verified']) {
                $error_message = "Your account is not verified. Please check your email.";
            }

            // ===============================
            // LOGIN SUCCESS
            // ===============================
            else {

                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['username'] = $user['username'];

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

                    default:
                        header("Location: login.php");
                        exit;
                }
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
            <p class="text-danger text-center">
                <?= htmlspecialchars($error_message); ?>
            </p>
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
            Don't have an account? <a href="register.php">Sign Up</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>