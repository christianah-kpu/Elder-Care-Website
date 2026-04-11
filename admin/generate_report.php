<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Generate Reports";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// CHECK ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// FILTERS
$report_type = $_GET['report_type'] ?? 'monthly';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('n');

$dateFilter = "";
$params = [];

if ($year) {
    $dateFilter .= " YEAR(hr.dateOfCreation) = ? ";
    $params[] = $year;
}
if ($report_type === 'monthly' && $month) {
    $dateFilter .= ($dateFilter ? " AND " : "") . " MONTH(hr.dateOfCreation) = ? ";
    $params[] = $month;
}

// COUNTS
$resCount = $conn->query("SELECT COUNT(*) FROM resident")->fetchColumn();
$careCount = $conn->query("SELECT COUNT(*) FROM caregiver")->fetchColumn();

// MED SUMMARY
$medSQL = "
SELECT r.fname, r.lname, COUNT(me.entryID) as missed_count
FROM resident r
LEFT JOIN medication m ON r.residentSIN = m.residentSIN
LEFT JOIN medication_entry me ON m.medID = me.medID AND me.status='missed'
LEFT JOIN healthreport hr ON me.reportID = hr.reportID
";

if ($dateFilter) $medSQL .= " WHERE $dateFilter ";
$medSQL .= " GROUP BY r.residentSIN ORDER BY missed_count DESC";

$stmt = $conn->prepare($medSQL);
$stmt->execute($params);
$medSummary = $stmt->fetchAll();

// HEALTH SUMMARY
$healthSQL = "
SELECT r.fname, r.lname,
SUM(CASE WHEN hr.bloodPressure > 140 THEN 1 ELSE 0 END) AS abnormal_bp,
SUM(CASE WHEN hr.bloodSugar > 180 THEN 1 ELSE 0 END) AS abnormal_sugar,
SUM(CASE WHEN hr.temperature > 38 THEN 1 ELSE 0 END) AS high_temp,
SUM(CASE WHEN hr.heartRate < 60 OR hr.heartRate > 100 THEN 1 ELSE 0 END) AS irregular_hr
FROM resident r
LEFT JOIN healthreport hr ON r.residentSIN = hr.residentSIN
";

if ($dateFilter) $healthSQL .= " WHERE $dateFilter ";
$healthSQL .= " GROUP BY r.residentSIN ORDER BY abnormal_bp DESC";

$stmt = $conn->prepare($healthSQL);
$stmt->execute($params);
$healthSummary = $stmt->fetchAll();
?>

<div class="container mt-5">

<h2 class="mb-4 text-center">Reports Dashboard</h2>

<!-- FILTER -->
<div class="card shadow-sm mb-4">
<div class="card-body">

<form method="GET" class="row g-3 align-items-end">

<div class="col-md-3">
<label>Type</label>
<select name="report_type" class="form-select">
<option value="monthly" <?= $report_type=='monthly'?'selected':'' ?>>Monthly</option>
<option value="yearly" <?= $report_type=='yearly'?'selected':'' ?>>Yearly</option>
</select>
</div>

<div class="col-md-3">
<label>Year</label>
<input type="number" name="year" class="form-control" value="<?= $year ?>">
</div>

<div class="col-md-3">
<label>Month</label>
<select name="month" class="form-select">
<?php for($i=1;$i<=12;$i++): ?>
<option value="<?= $i ?>" <?= $i==$month?'selected':'' ?>>
<?= date('F', mktime(0,0,0,$i,1)) ?>
</option>
<?php endfor; ?>
</select>
</div>

<div class="col-md-3">
<button class="btn btn-primary w-100">Generate</button>
</div>

</form>

</div>
</div>

<!-- SUMMARY CARDS -->
<div class="row text-center mb-4">

<div class="col-md-6">
<div class="card shadow-sm p-3">
<h5>Total Residents</h5>
<h3><?= $resCount ?></h3>
</div>
</div>

<div class="col-md-6">
<div class="card shadow-sm p-3">
<h5>Total Caregivers</h5>
<h3><?= $careCount ?></h3>
</div>
</div>

</div>

<!-- MEDICATION -->
<div class="card shadow-sm mb-4">
<div class="card-header bg-dark text-white">
Missed Medications (Top → Least)
</div>

<div class="table-responsive">
<table class="table table-hover text-center mb-0">

<thead class="table-light">
<tr>
<th>Resident</th>
<th>Missed</th>
</tr>
</thead>

<tbody>
<?php foreach($medSummary as $index => $m): ?>
<tr class="<?= $index==0?'table-danger':'' ?>">
<td><?= $m['fname'].' '.$m['lname'] ?></td>
<td><span class="badge bg-danger"><?= $m['missed_count'] ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>

</table>
</div>
</div>

<!-- HEALTH -->
<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
Health Irregularities
</div>

<div class="table-responsive">
<table class="table table-hover text-center mb-0">

<thead class="table-light">
<tr>
<th>Resident</th>
<th>BP</th>
<th>Sugar</th>
<th>Temp</th>
<th>HR</th>
</tr>
</thead>

<tbody>
<?php foreach($healthSummary as $index => $h): ?>
<tr class="<?= $index==0?'table-warning':'' ?>">
<td><?= $h['fname'].' '.$h['lname'] ?></td>
<td><?= $h['abnormal_bp'] ?></td>
<td><?= $h['abnormal_sugar'] ?></td>
<td><?= $h['high_temp'] ?></td>
<td><?= $h['irregular_hr'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>

</table>
</div>
</div>

<div class="text-center mt-4">
<a href="dashboard.php" class="btn btn-secondary">← Back</a>
</div>

</div>