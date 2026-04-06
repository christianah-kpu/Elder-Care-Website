<?php
$page_title = "Admin Dashboard";
session_start();
require_once '../includes/db_connection.php';

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if suspended
$stmt = $conn->prepare("SELECT status FROM user_status WHERE user_id=?");
$stmt->execute([$user_id]);
$status = $stmt->fetchColumn();
if ($status === 'suspended') {
    session_destroy();
    die("<p style='color:red;text-align:center;'>Account suspended. Contact admin.</p>");
}

include "../includes/header.php";
?>

<div class="container mt-5">
    <h2 class="mb-4">Admin Dashboard</h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Manage Users</h5>
                    <a href="manage_users.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Approve Family Account</h5>
                    <a href="approve_family.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Approve Family Request</h5>
                    <a href="approve_family_request.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

         <div class="col-md-4 mb-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Assign Caregivers</h5>
                    <a href="assign_caregivers.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Generate Reports</h5>
                    <a href="generate_report.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>