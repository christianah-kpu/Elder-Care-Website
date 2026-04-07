<?php $page_title="Elder Care"; 

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include 'includes/header.php'; 
?>

<!-- Hero Section -->
<header class="bg-primary text-white text-center py-5">
    <div class="container">
        <h1>Welcome to Elder Care Home Management System</h1>
        <p class="lead">Streamline care, improve communication, and ensure resident wellbeing</p>
        <a href="login.php" class="btn btn-light btn-lg mt-3">Login</a>
        <a href="register.php" class="btn btn-outline-light btn-lg mt-3">Sign Up</a>
    </div>
</header>

<!-- Features Section -->
<section class="container my-5">
    <div class="row text-center">
        
        <div class="col-md-3">
            <h4>Residents</h4>
            <p>View your health records and track your wellbeing.</p>
        </div>

        <div class="col-md-3">
            <h4>Caregivers</h4>
            <p>Manage health data, medications, and daily care tasks.</p>
        </div>

        <div class="col-md-3">
            <h4>Administrators</h4>
            <p>Manage users, assignments, and generate reports.</p>
        </div>

        <div class="col-md-3">
            <h4>Family</h4>
            <p>Stay updated on your loved ones’ health and receive alerts.</p>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>