<?php
$page_title = "Resident Dashboard"; 
include '../includes/header.php'; 
?>

<div class="container mt-5">

    <h2 class="mb-4">Resident Dashboard</h2>

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