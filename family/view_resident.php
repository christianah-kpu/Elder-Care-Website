<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Resident Details";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// Ensure family logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'family') {
    header("Location: ../login.php");
    exit;
}

// Get fmID
$stmt = $conn->prepare("SELECT fmID FROM familymember WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$family = $stmt->fetch();

if (!$family) {
    echo "<div class='container mt-5'>
            <div class='alert alert-warning text-center'>
                Your account is awaiting admin approval.
            </div>
          </div>";
    include '../includes/footer.php';
    exit;
}

$fmID = $family['fmID'];

// Get resident ID from URL
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger text-center'>Invalid request.</div>";
    include '../includes/footer.php';
    exit;
}

$residentSIN = $_GET['id'];

// ===============================
// SECURITY CHECK + FETCH DATA
// ===============================
$stmt = $conn->prepare("
    SELECT 
        r.residentSIN,
        r.fname,
        r.lname,
        r.phone,
        r.profilePhoto,
        r.ECname,
        r.ECphone,
        r.ECemail,
        u.email
    FROM link l
    JOIN resident r ON l.residentSIN = r.residentSIN
    JOIN users u ON r.user_id = u.user_id
    WHERE l.fmID = ? 
      AND l.residentSIN = ?
      AND l.status = 'approved'
");
$stmt->execute([$fmID, $residentSIN]);
$resident = $stmt->fetch();

if (!$resident) {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center'>
                You are not authorized to view this resident.
            </div>
          </div>";
    include '../includes/footer.php';
    exit;
}
?>

<div class="container py-4">
    <h2 class="text-center mb-4">Resident Profile</h2>

    <div class="card shadow p-4">

        <div class="row">
            <!-- Profile Image -->
            <div class="col-md-4 text-center">
                <?php if (!empty($resident['profilePhoto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($resident['profilePhoto']) ?>" 
                         class="img-fluid rounded" style="max-height:200px;">
                <?php else: ?>
                    <p class="text-muted">No Image</p>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="col-md-8">
                <h4>
                    <?= htmlspecialchars(($resident['fname'] ?? '') . ' ' . ($resident['lname'] ?? '')) ?>
                </h4>

                <p><strong>Email:</strong> 
                    <?= htmlspecialchars($resident['email'] ?? '') ?>
                </p>

                <p><strong>Phone:</strong> 
                    <?= htmlspecialchars($resident['phone'] ?? '') ?>
                </p>

                <hr>

                <h5>Emergency Contact</h5>
                <p><strong>Name:</strong> 
                    <?= htmlspecialchars($resident['ECname'] ?? '') ?>
                </p>
                <p><strong>Phone:</strong> 
                    <?= htmlspecialchars($resident['ECphone'] ?? '') ?>
                </p>
                <p><strong>Email:</strong> 
                    <?= htmlspecialchars($resident['ECemail'] ?? '') ?>
                </p>
            </div>
        </div>

    </div>

    <!-- Future Section: Health Data -->
    <div class="card shadow p-4 mt-4">
        <h4>Health Summary</h4>
        <p class="text-muted">
            **Health data will be displayed here**.
        </p>
    </div>

</div>

<div class="text-center mt-4">
    <a href="view_residents.php" class="btn btn-secondary">← Back</a>
</div>

<?php include '../includes/footer.php'; ?>