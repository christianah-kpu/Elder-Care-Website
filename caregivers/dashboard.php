<?php

$page_title="Caregiver Dashboard";

session_start();
require_once '../includes/db_connection.php';

// Ensure caregiver logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';

// Fetch caregiver info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT fname, lname, profilePhoto FROM caregiver WHERE user_id=?");
$stmt->execute([$user_id]);
$caregiver = $stmt->fetch();

?>

<div class="caregiver-dashboard container mt-5">

    <h2 class="mb-4">Caregiver Dashboard</h2>

    <!-- Profile Image -->
    <div class="text-center mb-4">
        <?php if(!empty($caregiver['profilePhoto']) && file_exists("../uploads/caregivers/".$caregiver['profilePhoto'])): ?>
            <img src="../uploads/caregivers/<?= htmlspecialchars($caregiver['profilePhoto']) ?>" 
                 alt="Profile Photo" class="rounded-circle shadow" style="width:150px;height:150px;object-fit:cover;">
        <?php else: ?>
            <!-- Default Avatar Icon -->
            <div class="rounded-circle bg-light border shadow d-inline-flex justify-content-center align-items-center" 
                 style="width:150px;height:150px;">
                <i class="bi bi-person-fill" style="font-size:80px;color:#6c757d;"></i>
            </div>
        <?php endif; ?>
        <h4 class="mt-2"><?= htmlspecialchars($caregiver['fname'] . ' ' . $caregiver['lname']) ?></h4>
    </div>

    <div class="row">

        <!-- Edit Personal profile information -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Edit My Profile</h5>
                    <p class="card-text">Edit my personal profile information</p>
                    <a href="profile.php" class="btn btn-primary">Open</a>
                </div>
            </div>
        </div>

        <!-- View Residents -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">View Residents</h5>
                    <p class="card-text">See assigned residents and their health history.</p>
                    <a href="view_residents.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <!-- Manage Health Data -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Manage Health Data</h5>
                    <p class="card-text">Add, update, or delete resident health data.</p>
                    <a href="manage_health.php" class="btn btn-success">Go</a>
                </div>
            </div>
        </div>

        <!-- Medication Tracking -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Medication Tracking</h5>
                    <p class="card-text">Mark medications as given, missed, or delayed.</p>
                    <a href="medication_status.php" class="btn btn-warning">Go</a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>