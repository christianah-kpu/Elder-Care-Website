<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Profile";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// FLASH MESSAGE
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success text-center'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// CHECK RESIDENT LOGIN
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

    // KEEP OLD VALUES IF EMPTY
    $phone   = !empty($_POST['phone']) ? $_POST['phone'] : $resident['phone'];
    $ECname  = !empty($_POST['ECname']) ? $_POST['ECname'] : $resident['ECname'];
    $ECphone = !empty($_POST['ECphone']) ? $_POST['ECphone'] : $resident['ECphone'];
    $ECemail = !empty($_POST['ECemail']) ? $_POST['ECemail'] : $resident['ECemail'];

    // ===============================
    // IMAGE UPLOAD
    // ===============================
    $profilePhoto = $resident['profilePhoto'];

    if (!empty($_FILES['profileImage']['name'])) {

        $targetDir = "../uploads/residents/";

        // Create folder if not exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["profileImage"]["name"]);
        $targetFile = $targetDir . $fileName;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($fileType, $allowed)) {

            if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFile)) {

                // OPTIONAL: delete old image
                if (!empty($resident['profilePhoto']) && file_exists($targetDir.$resident['profilePhoto'])) {
                    unlink($targetDir.$resident['profilePhoto']);
                }

                $profilePhoto = $fileName;
            }
        }
    }

    try {

        $stmt = $conn->prepare("
            UPDATE resident 
            SET phone=?, ECname=?, ECphone=?, ECemail=?, profilePhoto=?
            WHERE user_id=?
        ");
        $stmt->execute([
            $phone, 
            $ECname, 
            $ECphone, 
            $ECemail, 
            $profilePhoto, 
            $_SESSION['user_id']
        ]);

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

<!-- PROFILE IMAGE -->
<div class="text-center mb-4">
<?php if(!empty($resident['profilePhoto']) && file_exists("../uploads/residents/".$resident['profilePhoto'])): ?>
    <img src="../uploads/residents/<?= htmlspecialchars($resident['profilePhoto']) ?>" 
         class="rounded-circle shadow"
         style="width:140px;height:140px;object-fit:cover;">
<?php else: ?>
    <div class="rounded-circle bg-light border shadow d-inline-flex justify-content-center align-items-center"
         style="width:140px;height:140px;">
        <i class="bi bi-person-fill-exclamation" style="font-size:70px;color:#6c757d;"></i>
    </div>
<?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" class="card shadow p-4">

<h5 class="mb-3">Basic Info</h5>

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

<div class="mb-3">
<label>Update Profile Image</label>
<input type="file" name="profileImage" class="form-control">
</div>

<hr>

<h5 class="mb-3">Emergency Contact</h5>

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

<div class="text-center mt-4">
<a href="dashboard.php" class="btn btn-secondary">← Back</a>
</div>

</div>

<?php include '../includes/footer.php'; ?>