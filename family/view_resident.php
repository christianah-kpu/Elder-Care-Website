<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Resident Details";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// CHECK FAMILY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'family') {
    header("Location: ../login.php");
    exit;
}

// CHECK SIN
if (!isset($_GET['sin'])) {
    echo "<div class='alert alert-danger'>No resident selected</div>";
    exit;
}

$residentSIN = $_GET['sin'];

// =======================
// SECURITY: ONLY LINKED
// =======================
$stmt = $conn->prepare("
SELECT r.*
FROM resident r
JOIN link l ON r.residentSIN = l.residentSIN
JOIN familymember f ON l.fmID = f.fmID
WHERE r.residentSIN=? AND f.user_id=? AND l.status='approved'
");
$stmt->execute([$residentSIN, $_SESSION['user_id']]);
$resident = $stmt->fetch();

if (!$resident) {
    echo "<div class='alert alert-danger'>Access denied</div>";
    exit;
}

// =======================
// HEALTH DATA
// =======================
$stmt = $conn->prepare("
SELECT * FROM healthreport
WHERE residentSIN=?
ORDER BY dateOfCreation DESC
");
$stmt->execute([$residentSIN]);
$health = $stmt->fetchAll();

// =======================
// MEDICATION SUMMARY
// =======================
$stmt = $conn->prepare("
SELECT medName, dose, timeScheduled
FROM medication
WHERE residentSIN=?
");
$stmt->execute([$residentSIN]);
$meds = $stmt->fetchAll();

// IMAGE
$imgPath = !empty($resident['profilePhoto']) 
    ? "../uploads/".$resident['profilePhoto'] 
    : null;
?>

<div class="container mt-5">

<!-- PROFILE HEADER -->
<div class="card shadow-sm p-3 mb-4 text-center">

<?php if ($imgPath): ?>
<img src="<?= $imgPath ?>" 
     style="width:100px; height:100px; object-fit:cover; border-radius:50%;">
<?php else: ?>
<div style="font-size:70px; color:#6c757d;">
<i class="bi bi-person-circle"></i>
</div>
<?php endif; ?>

<h3 class="mt-2"><?= $resident['fname']." ".$resident['lname'] ?></h3>

</div>

<!-- PROFILE -->
<div class="card mb-4 p-3 shadow-sm">
<h5>Profile</h5>
<p><strong>Phone:</strong> <?= $resident['phone'] ?? 'N/A' ?></p>
<p><strong>Date of Birth:</strong> <?= $resident['DoB'] ?? 'N/A' ?></p>

<h6>Emergency Contact</h6>
<p>
<?= $resident['ECname'] ?? 'N/A' ?> |
<?= $resident['ECphone'] ?? 'N/A' ?> |
<?= $resident['ECemail'] ?? 'N/A' ?>
</p>
</div>

<!-- HEALTH SUMMARY -->
<div class="card mb-4 p-3 shadow-sm">
<h5>Health Records</h5>

<div style="max-height:300px; overflow-y:auto;">
<table class="table table-bordered text-center">
<thead class="table-light sticky-top">
<tr>
<th>Date</th>
<th>BP</th>
<th>Sugar</th>
<th>Temp</th>
<th>Heart</th>
</tr>
</thead>

<tbody>
<?php foreach($health as $h): ?>
<tr>
<td><?= date("Y-m-d H:i", strtotime($h['dateOfCreation'])) ?></td>
<td><?= $h['bloodPressure'] ?></td>
<td><?= $h['bloodSugar'] ?></td>
<td><?= $h['temperature'] ?></td>
<td><?= $h['heartRate'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- MEDICATION -->
<div class="card mb-4 p-3 shadow-sm">
<h5>Medications</h5>

<?php if($meds): ?>
<ul>
<?php foreach($meds as $m): ?>
<li><?= $m['medName'] ?> (<?= $m['dose'] ?> at <?= $m['timeScheduled'] ?>)</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p>No medications</p>
<?php endif; ?>
</div>

<!-- BACK -->
<div class="text-center mt-4 d-flex justify-content-center gap-2">

<a href="view_residents.php" class="btn btn-outline-secondary">
<i class="bi bi-arrow-left"></i> Residents
</a>

<a href="dashboard.php" class="btn btn-secondary">
<i class="bi bi-speedometer2"></i> Dashboard
</a>

</div>

</div>

<?php include '../includes/footer.php'; ?>