<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Admin - Manage Users";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// ===============================
// FLASH MESSAGE
// ===============================
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// ===============================
// CHECK ADMIN
// ===============================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ===============================
// HANDLE SUSPEND / UNSUSPEND
// ===============================
if (isset($_POST['toggle_status'])) {

    $user_id = $_POST['user_id'];
    $current_status = $_POST['current_status'];

    $new_status = ($current_status === 'active') ? 'suspended' : 'active';

    $stmt = $conn->prepare("UPDATE user_status SET status=? WHERE user_id=?");
    $stmt->execute([$new_status, $user_id]);

    if ($stmt->rowCount() === 0) {
        $stmt = $conn->prepare("INSERT INTO user_status (user_id, status) VALUES (?, ?)");
        $stmt->execute([$user_id, $new_status]);
    }

    $_SESSION['flash_message'] = "User status updated to $new_status.";
    header("Location: manage_users.php");
    exit;
}

// ===============================
// HANDLE DELETE ACCOUNT - only applies to suspended accounts
// ===============================
if(isset($_POST['delete_acct'])) {
    $user_id = $_POST['user_id'];
    $current_status = $_POST['current_status'];

    if ($current_status === 'suspended') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->execute([$user_id]);
    }

    $_SESSION['flash_message'] = "User successfully deleted.";
    header("Location: manage_users.php");
    exit;
}

// ===============================
// CREATE USER
// ===============================
if (isset($_POST['create_user'])) {

    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $password = $_POST['password'];
    $residentSIN = isset($_POST['residentSIN']) ? trim($_POST['residentSIN']) : null;

    if (empty($username) || empty($email) || empty($password)) {
        echo "<div class='alert alert-danger'>All fields are required</div>";
    } else {

        // ===============================
        // RESIDENT SIN VALIDATION
        // ===============================
        if ($role === 'resident') {

            if ($residentSIN === null || $residentSIN === '') {
                $_SESSION['flash_message'] = "SIN is required for residents";
                header("Location: manage_users.php");
                exit;
            }

            if (!preg_match('/^[0-9]{9}$/', $residentSIN)) {
                $_SESSION['flash_message'] = "SIN must be axactly 9 digits.";
                header("Location: manage_users.php");
                exit;
            }

            $checkSIN = $conn->prepare("SELECT residentSIN FROM resident WHERE residentSIN=?");
            $checkSIN->execute([$residentSIN]);

            if ($checkSIN->fetch()) {
                $_SESSION['flash_message'] = "SIN already exits.";
                header("Location: manage_users.php");
                exit;
            }
        }

        // ===============================
        // CHECK DUPLICATE USER
        // ===============================
        $check = $conn->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
        $check->execute([$username, $email]);

        if ($check->fetch()) {
            echo "<div class='alert alert-danger'>Username or Email already exists</div>";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password_hash, role, is_verified) 
                VALUES (?, ?, ?, ?, 0)
            ");
            $stmt->execute([$username, $email, $hashedPassword, $role]);

            $user_id = $conn->lastInsertId();

            // TOKEN
            $verificationToken = bin2hex(random_bytes(16));
            $createdAt = date('Y-m-d H:i:s');

            $stmtToken = $conn->prepare("
                INSERT INTO verification_tokens (user_id, token, created_at) 
                VALUES (?, ?, ?)
            ");
            $stmtToken->execute([$user_id, $verificationToken, $createdAt]);

            // EMAIL
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ronaldo.sony1898@gmail.com';
                $mail->Password   = 'jqlw fjem goyh ztam';
                $mail->Port       = 587;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

                $mail->setFrom('ronaldo.sony1898@gmail.com', 'Sonny');
                $mail->addAddress($email);

                $verificationLink = "http://localhost/elder-care-website/verify.php?token=$verificationToken";

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Account';
                $mail->Body    = "Click to verify: <a href='$verificationLink'>$verificationLink</a>";

                $mail->send();

                echo "<div class='alert alert-success'>User created! Verification email sent.</div>";

            } catch (Exception $e) {
                echo "<div class='alert alert-warning'>User created but email failed</div>";
            }

            // ROLE TABLE INSERT
            if ($role === 'resident') {

                $stmt2 = $conn->prepare("
                    INSERT INTO resident
                    (residentSIN, user_id, phone, profilePhoto, ECname, ECphone, ECemail, fname, lname)
                    VALUES (?, ?, NULL, NULL, NULL, NULL, NULL, ?, ?)
                ");
                $stmt2->execute([$residentSIN, $user_id, $fname, $lname]);

            } else if ($role === 'caregiver') {

                $stmt2 = $conn->prepare("
                    INSERT INTO caregiver
                    (user_id, phone, fname, lname)
                    VALUES (?, NULL, ?, ?)
                ");
                $stmt2->execute([$user_id, $fname, $lname]);
            }
        }
    }
}

// ===============================
// FETCH USERS
// ===============================
$stmt = $conn->query("
    SELECT u.user_id, u.username, u.email, u.role, 
           COALESCE(us.status,'active') as status
    FROM users u
    LEFT JOIN user_status us ON u.user_id = us.user_id
");
$users = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="text-center mb-4">Manage Users</h2>

    <form method="POST" class="mb-4 row g-2">

        <div class="col-md-2">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="col-md-2">
            <input type="text" name="fname" class="form-control" placeholder="First Name" required>
        </div>

        <div class="col-md-2">
            <input type="text" name="lname" class="form-control" placeholder="Last Name" required>
        </div>

        <div class="col-md-2">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>

        <div class="col-md-2">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <div class="col-md-2">
            <select name="role" class="form-select" id="roleSelect" required>
                <option value="resident">Resident</option>
                <option value="caregiver">Caregiver</option>
            </select>
        </div>

        <!-- SIN -->
        <div class="col-md-2" id="sinField">
            <input type="text" name="residentSIN" class="form-control" 
                   placeholder="SIN (9 digits)" maxlength="9" pattern="[0-9]{9}">
        </div>

        <div class="col-md-12 mt-2">
            <button type="submit" name="create_user" class="btn btn-success">
                Create User
            </button>
        </div>

    </form>

    <table class="table table-bordered text-center">
        <thead class="table-primary">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($users as $u): ?>
            <tr class="<?= $u['status']==='suspended'?'table-secondary':'' ?>">
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td>
                    <span class="badge <?= $u['status']==='active'?'bg-success':'bg-danger' ?>">
                        <?= ucfirst($u['status']) ?>
                    </span>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <input type="hidden" name="current_status" value="<?= $u['status'] ?>">
                        <button type="submit" name="toggle_status" 
                                class="btn btn-sm <?= $u['status']==='active'?'btn-danger':'btn-success' ?>">
                            <?= $u['status']==='active'?'Suspend':'Unsuspend' ?>
                        </button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <input type="hidden" name="current_status" value="<?= $u['status'] ?>">
                        <!-- Button to delete account-->
                        <button type="submit" name="delete_acct" 
                                class="btn btn-sm btn-danger" <?= $u['status']==='active'?'hidden':'' ?> >
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<!-- JS Script so SIN is required for caregiver -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const roleSelect = document.getElementById('roleSelect');
    const sinField = document.getElementById('sinField');
    const sinInput = sinField.querySelector('input');

    function toggleSIN() {
        if (roleSelect.value === 'resident') {
            sinField.style.display = 'block';
            sinInput.required = true;
        } else {
            sinField.style.display = 'none';
            sinInput.required = false;
        }
    }

    // RUN ON LOAD
    toggleSIN();

    // RUN ON CHANGE
    roleSelect.addEventListener('change', toggleSIN);
});
</script>

<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
