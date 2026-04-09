<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Profile";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// Flash message
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// Ensure caregiver logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

// ===============================
// FETCH CURRENT DATA
// ===============================
$stmt = $conn->prepare("
    SELECT c.*, u.email, u.username
    FROM caregiver c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$caregiver = $stmt->fetch();

if (!$caregiver) {
    echo "<div class='alert alert-danger'>Profile not found.</div>";
    include '../includes/footer.php';
    exit;
}

// ===============================
// HANDLE UPDATE
// ===============================
if (isset($_POST['update_profile'])) {

    $fname = $_POST['fname'] ?: NULL;
    $lname = $_POST['lname'] ?: NULL;
    $phone = $_POST['phone'] ?: NULL;
    $email = $_POST['email'];

    try {

        // Update users table
        $stmt1 = $conn->prepare("
            UPDATE users 
            SET email = ?
            WHERE user_id = ?
        ");
        $stmt1->execute([$email, $_SESSION['user_id']]);

        // Update caregiver table
        $stmt2 = $conn->prepare("
            UPDATE caregiver
            SET fname = ?, lname = ?, phone = ?
            WHERE user_id = ?
        ");
        $stmt2->execute([$fname, $lname, $phone, $_SESSION['user_id']]);

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
            <label>Username</label>
            <input type="text" class="form-control"
                value="<?= htmlspecialchars($caregiver['username'] ?? '') ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                value="<?= htmlspecialchars($caregiver['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>First Name</label>
            <input type="text" name="fname" class="form-control"
                value="<?= htmlspecialchars($caregiver['fname'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Last Name</label>
            <input type="text" name="lname" class="form-control"
                value="<?= htmlspecialchars($caregiver['lname'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control"
                value="<?= htmlspecialchars($caregiver['phone'] ?? '') ?>">
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