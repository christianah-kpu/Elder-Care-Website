<?php
$page_title = "Family Dashboard"; 
include '../includes/header.php'; 

session_start();
require_once '../includes/db_connection.php';

// Ensure logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'family') {
    header("Location: ../login.php");
    exit;
}

// Check approval
$stmt = $conn->prepare("
    SELECT fm.*, u.email 
    FROM familymember fm
    JOIN users u ON fm.user_id = u.user_id
    WHERE fm.user_id = ?
");
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
?>

<div class="family-dashboard container mt-5">

    <h2 class="mb-4">Family Dashboard</h2>

    <!-- PROFILE SECTION -->
    <div class="text-center mb-4">

        <!-- No image column in DB → use icon -->
        <div class="rounded-circle bg-light border shadow d-inline-flex justify-content-center align-items-center" 
             style="width:150px;height:150px;">
            <i class="bi bi-person-fill-exclamation" style="font-size:80px;color:#6c757d;"></i>
        </div>

        <h4 class="mt-2">
            <?= htmlspecialchars(($family['fname'] ?? '') . ' ' . ($family['lname'] ?? '')) ?>
        </h4>

        <p class="text-muted"><?= htmlspecialchars($family['email']) ?></p>
    </div>

    <div class="row">

        <!-- PROFILE -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">View Profile</h5>
                    <p class="card-text">View and update your personal information.</p>
                    <a href="profile.php" class="btn btn-primary">Open</a>
                </div>
            </div>
        </div>

        <!-- REQUEST LINK -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Request Resident Access</h5>
                    <p class="card-text">Request to connect with a resident.</p>
                    <a href="request_resident.php" class="btn btn-success">Open</a>
                </div>
            </div>
        </div>

        <!-- VIEW RESIDENTS -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">My Residents</h5>
                    <p class="card-text">View linked residents and their health summary.</p>
                    <a href="view_residents.php" class="btn btn-dark">Open</a>
                </div>
            </div>
        </div>

        <!-- NOTIFICATIONS -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Notifications</h5>
                    <p class="card-text">Check alerts for health trends and medications.</p>
                    <a href="notifications.php" class="btn btn-warning">Open</a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>