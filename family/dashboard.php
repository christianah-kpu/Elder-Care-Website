<?php 
$page_title="Family Member Dashboard";

include '../includes/header.php'; 
require_once '../includes/db_connection.php';

// Ensure logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'family') {
    header("Location: ../login.php");
    exit;
}

// Check if approved (has familymember record)
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
?>

<div class="container mt-5">

    <h2 class="mb-4">Family Dashboard</h2>

    <div class="row">

        <!--  Edit your profile -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Edit your profile information</h5>
                    <p class="card-text">Edit your profile information.</p>
                    <a href="profile.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <!-- Request to be linked to a particular resident -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Request link to Resident</h5>
                    <p class="card-text">Request to be linked to a resident to have access to their file</p>
                    <a href="request_resident.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <!-- View Residents -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">My Residents</h5>
                    <p class="card-text">View linked residents and their health summaries.</p>
                    <a href="view_residents.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Notifications</h5>
                    <p class="card-text">Receive alerts about resident health and medications.</p>
                    <a href="notifications.php" class="btn btn-warning">Go</a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>