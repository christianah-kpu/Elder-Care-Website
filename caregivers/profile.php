<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Profile";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// FLASH
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success text-center'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// CHECK CAREGIVER
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

// ===============================
// FETCH DATA
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
// UPDATE
// ===============================
if (isset($_POST['update_profile'])) {

    $fname = $_POST['fname'] ?: NULL;
    $lname = $_POST['lname'] ?: NULL;
    $phone = $_POST['phone'] ?: NULL;
    $email = $_POST['email'];

    $imageName = $caregiver['profilePhoto']; // keep old image

    // ===============================
    // IMAGE UPLOAD
    // ===============================
    if (!empty($_FILES['profileImage']['name'])) {

        $uploadDir = "../uploads/caregivers/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp = $_FILES['profileImage']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['profileImage']['name']);
        $targetFile = $uploadDir . $fileName;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($fileType, $allowed)) {

            if (move_uploaded_file($fileTmp, $targetFile)) {
                $imageName = $fileName;
            }

        } else {
            echo "<div class='alert alert-danger'>Only JPG, JPEG, PNG allowed.</div>";
        }
    }

    try {

        // UPDATE USERS
        $stmt1 = $conn->prepare("
            UPDATE users 
            SET email = ?
            WHERE user_id = ?
        ");
        $stmt1->execute([$email, $_SESSION['user_id']]);

        // UPDATE CAREGIVER
        $stmt2 = $conn->prepare("
            UPDATE caregiver
            SET fname=?, lname=?, phone=?, profilePhoto=?
            WHERE user_id=?
        ");
        $stmt2->execute([$fname, $lname, $phone, $imageName, $_SESSION['user_id']]);

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

    <form method="POST" enctype="multipart/form-data" class="card shadow p-4">

        <!-- PROFILE IMAGE -->
        <div class="text-center mb-4">
            <?php if(!empty($caregiver['profilePhoto']) && file_exists("../uploads/caregivers/".$caregiver['profilePhoto'])): ?>
                <img src="../uploads/caregivers/<?= htmlspecialchars($caregiver['profilePhoto']) ?>" 
                     class="rounded-circle shadow"
                     style="width:120px;height:120px;object-fit:cover;">
            <?php else: ?>
                <div class="rounded-circle bg-light border d-inline-flex justify-content-center align-items-center"
                     style="width:120px;height:120px;">
                    <i class="bi bi-person-fill" style="font-size:60px;color:#6c757d;"></i>
                </div>
            <?php endif; ?>

            <div class="mt-2">
                <label class="form-label"><strong>Profile Image</strong></label>
                <input type="file" name="profileImage" class="form-control form-control-sm">
            </div>
        </div>

        <h5>Basic Info</h5>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" class="form-control"
                value="<?= htmlspecialchars($caregiver['username'] ?? '') ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                value="<?= htmlspecialchars($caregiver['email'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label>First Name</label>
            <input type="text" name="fname" class="form-control"
                value="<?= htmlspecialchars($caregiver['fname'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label>Last Name</label>
            <input type="text" name="lname" class="form-control"
                value="<?= htmlspecialchars($caregiver['lname'] ?? '') ?>" required>
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