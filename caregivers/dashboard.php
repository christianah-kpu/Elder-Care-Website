<?php
session_start();
require_once '../includes/db_connection.php';

// Ensure caregiver logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
?>

<div class="container mt-5">

<h2 class="mb-4">Caregiver Dashboard</h2>

<div class="row">

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