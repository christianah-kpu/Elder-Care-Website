<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Profile";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// flash success message to prevent reroute
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// Ensure resident logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../login.php");
    exit;
}

// ===============================
// FETCH CURRENT DATA
// ===============================
$stmt = $conn->prepare("
    SELECT r.*, u.email
    FROM resident r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$resident = $stmt->fetch();

if (!$resident) {
    echo "<div class='alert alert-danger'>Profile not found.</div>";
    include '../includes/footer.php';
    exit;
}

// ===============================
// HANDLE UPDATE
// ===============================
if (isset($_POST['update_profile'])) {

    $phone   = $_POST['phone'] ?: NULL;
    // $email   = $_POST['email'];
    $ECname  = $_POST['ECname'] ?: NULL;
    $ECphone = $_POST['ECphone'] ?: NULL;
    $ECemail = $_POST['ECemail'] ?: NULL;

    try {

        // Update resident table
        $stmt = $conn->prepare("
            UPDATE resident 
            SET phone=?, ECname=?, ECphone=?, ECemail=?
            WHERE user_id=?
        ");
        $stmt->execute([$phone, $ECname, $ECphone, $ECemail, $_SESSION['user_id']]);

        $_SESSION['flash_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit;

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
    }
}
?>

<div class="container py-4">
    <h2 class="text-center mb-4">My Profile</h2>

    <form method="POST" class="card shadow p-4">

        <h5>Basic Info</h5>

        <div class="mb-3">
            <label>Email</label>
            <input type="text" class="form-control"
                value="<?= htmlspecialchars($resident['email'] ?? '') ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control"
                value="<?= htmlspecialchars($resident['phone'] ?? '') ?>">
        </div>

        <hr>

        <h5>Emergency Contact</h5>

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="ECname" class="form-control"
                value="<?= htmlspecialchars($resident['ECname'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="ECphone" class="form-control"
                value="<?= htmlspecialchars($resident['ECphone'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="ECemail" class="form-control"
                value="<?= htmlspecialchars($resident['ECemail'] ?? '') ?>">
        </div>

        <button type="submit" name="update_profile" class="btn btn-primary w-100">
            Update Profile
        </button>

    </form>
</div>

<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">← Back</a>
</div>

<?php include '../includes/footer.php'; ?>