<?php
$page_title = "Resident Dashboard"; 
include '../includes/header.php'; 

// Fetch resident info
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT fname, lname, profilePhoto FROM resident WHERE user_id=?");
$stmt->execute([$user_id]);
$resident = $stmt->fetch();
?>

<div class="container mt-5">

    <h2 class="mb-4">Resident Dashboard</h2>

    <!-- Profile Image -->
    <div class="text-center mb-4">
        <?php if(!empty($resident['profilePhoto']) && file_exists("../uploads/residents/".$resident['profilePhoto'])): ?>
            <img src="../uploads/residents/<?= htmlspecialchars($resident['profilePhoto']) ?>" 
                 alt="Profile Photo" class="rounded-circle shadow" style="width:150px;height:150px;object-fit:cover;">
        <?php else: ?>
            <!-- Default Avatar Icon -->
            <div class="rounded-circle bg-light border shadow d-inline-flex justify-content-center align-items-center" 
                 style="width:150px;height:150px;">
                <i class="bi bi-person-fill-exclamation" style="font-size:80px;color:#6c757d;"></i>
            </div>
        <?php endif; ?>
        <h4 class="mt-2"><?= htmlspecialchars($resident['fname'] . ' ' . $resident['lname']) ?></h4>
    </div>

    <div class="row">

        <!-- View Profile -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">View Profile</h5>
                    <p class="card-text">View and Edit your profile.</p>
                    <a href="profile.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <!-- View Health -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">View Health Records</h5>
                    <p class="card-text">See your health history and medications.</p>
                    <a href="view_health.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <!-- Self Report -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Self Report</h5>
                    <p class="card-text">Report your mood, pain level, and sleep quality.</p>
                    <a href="self_report.php" class="btn btn-success">Go</a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>