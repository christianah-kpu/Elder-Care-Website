<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Resident Details";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// CHECK CAREGIVER
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
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
// FETCH RESIDENT
// =======================
$stmt = $conn->prepare("SELECT * FROM resident WHERE residentSIN=?");
$stmt->execute([$residentSIN]);
$resident = $stmt->fetch();

if (!$resident) {
    echo "<div class='alert alert-danger'>Resident not found</div>";
    exit;
}

// =======================
// FETCH HEALTH
// =======================
$stmt = $conn->prepare("
SELECT * FROM healthreport
WHERE residentSIN = ?
ORDER BY dateOfCreation DESC
");
$stmt->execute([$residentSIN]);
$health = $stmt->fetchAll();

// =======================
// FETCH MEDICATIONS
// =======================
$stmt = $conn->prepare("
SELECT medName, dose, timeScheduled
FROM medication
WHERE residentSIN = ?
");
$stmt->execute([$residentSIN]);
$meds = $stmt->fetchAll();

// =======================
// FETCH MED STATUS
// =======================
$stmt = $conn->prepare("
SELECT m.medName, me.status, me.timeTaken, me.date
FROM medication_entry me
JOIN medication m ON me.medID = m.medID
WHERE m.residentSIN = ?
ORDER BY me.date DESC
");
$stmt->execute([$residentSIN]);
$medStatus = $stmt->fetchAll();

// =======================
// FIXED IMAGE LOGIC (IMPORTANT FIX)
// =======================
$imgPath = null;

if (!empty($resident['profilePhoto'])) {

    // FIX: match correct upload folder
    $possiblePath = "../uploads/residents/" . $resident['profilePhoto'];

    if (file_exists($possiblePath)) {
        $imgPath = $possiblePath;
    }
}
?>

<div class="container mt-5">

<!-- HEADER WITH IMAGE -->
<div class="card shadow-sm p-3 mb-4 text-center">

<?php if ($imgPath): ?>
    <img src="<?= $imgPath ?>" 
         style="width:100px; height:100px; object-fit:cover; border-radius:50%;">
<?php else: ?>
    <div style="font-size:70px; color:#6c757d;">
        <i class="bi bi-person-circle"></i>
    </div>
<?php endif; ?>

<h3 class="mt-2">
    <?= htmlspecialchars($resident['fname'] . " " . $resident['lname']) ?>
</h3>

</div>

<!-- PROFILE INFO -->
<div class="card mb-4 p-3 shadow-sm">
<h5>Profile</h5>
<p><strong>Phone:</strong> <?= htmlspecialchars($resident['phone'] ?? 'N/A') ?></p>
<p><strong>Date of Birth:</strong> <?= htmlspecialchars($resident['DoB'] ?? 'N/A') ?></p>

<h6>Emergency Contact</h6>
<p>
<?= htmlspecialchars($resident['ECname'] ?? 'N/A') ?> |
<?= htmlspecialchars($resident['ECphone'] ?? 'N/A') ?> |
<?= htmlspecialchars($resident['ECemail'] ?? 'N/A') ?>
</p>
</div>

<!-- HEALTH HISTORY -->
<div class="card mb-4 p-3 shadow-sm">
<h5>Health History</h5>

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

<!-- MEDICATIONS -->
<div class="card mb-4 p-3 shadow-sm">
<h5>Medications</h5>

<?php if($meds): ?>
<ul>
<?php foreach($meds as $m): ?>
<li>
<?= htmlspecialchars($m['medName']) ?> 
(<?= htmlspecialchars($m['dose']) ?> at <?= htmlspecialchars($m['timeScheduled']) ?>)
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p>No medications assigned</p>
<?php endif; ?>
</div>

<!-- MEDICATION STATUS -->
<div class="card mb-4 p-3 shadow-sm">
<h5>Medication Status</h5>

<div style="max-height:250px; overflow-y:auto;">
<table class="table table-bordered text-center">
<thead class="table-light sticky-top">
<tr>
<th>Medicine</th>
<th>Status</th>
<th>Time Taken</th>
<th>Date</th>
</tr>
</thead>

<tbody>
<?php foreach($medStatus as $ms): ?>
<tr>
<td><?= htmlspecialchars($ms['medName']) ?></td>
<td>
<span class="badge 
<?= $ms['status']=='taken'?'bg-success':
($ms['status']=='missed'?'bg-danger':
($ms['status']=='delayed'?'bg-warning':'bg-secondary')) ?>">
<?= ucfirst($ms['status']) ?>
</span>
</td>
<td><?= htmlspecialchars($ms['timeTaken'] ?? '—') ?></td>
<td><?= htmlspecialchars($ms['date']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- BACK BUTTONS -->
<div class="text-center mt-4 d-flex justify-content-center gap-2">
<a href="view_residents.php" class="btn btn-outline-secondary">
← Back to Residents
</a>

<a href="dashboard.php" class="btn btn-secondary">
Dashboard
</a>
</div>

</div>

<?php include '../includes/footer.php'; ?>