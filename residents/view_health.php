<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Health Records";
session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// CHECK RESIDENT
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../login.php");
    exit;
}

// GET RESIDENT SIN
$stmt = $conn->prepare("SELECT residentSIN FROM resident WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$resident = $stmt->fetch();

$residentSIN = $resident['residentSIN'] ?? null;

// FILTER VALUES
$date = $_GET['date'] ?? '';
$medicine = $_GET['medicine'] ?? '';

// FETCH MEDICINES FOR DROPDOWN
$medStmt = $conn->prepare("SELECT DISTINCT medName FROM medication WHERE residentSIN=?");
$medStmt->execute([$residentSIN]);
$medications = $medStmt->fetchAll();

// MAIN QUERY
$query = "
SELECT hr.*, m.medName
FROM healthreport hr
LEFT JOIN medication_entry me ON hr.reportID = me.reportID
LEFT JOIN medication m ON me.medID = m.medID
WHERE hr.residentSIN = ?
";

$params = [$residentSIN];

// APPLY FILTERS
if (!empty($date)) {
    $query .= " AND DATE(hr.dateOfCreation) = ?";
    $params[] = $date;
}

if (!empty($medicine)) {
    $query .= " AND m.medName = ?";
    $params[] = $medicine;
}

$query .= " ORDER BY hr.dateOfCreation DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();
?>

<div class="container mt-5">

<h2 class="mb-4">My Health Records</h2>

<!-- FILTERS -->
<form method="GET" class="row g-3 mb-3">

    <div class="col-md-3">
        <label>Date</label>
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>">
    </div>

    <div class="col-md-3">
        <label>Medicine</label>
        <select name="medicine" class="form-control">
            <option value="">All</option>
            <?php foreach ($medications as $m): ?>
                <option value="<?= $m['medName'] ?>" <?= $medicine === $m['medName'] ? 'selected' : '' ?>>
                    <?= $m['medName'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100">Search</button>
    </div>

</form>

<!-- TABLE -->
<div class="card shadow p-3">

<table class="table table-bordered text-center">
<thead class="table-light">
<tr>
    <th>Date</th>
    <th>Blood Pressure</th>
    <th>Blood Sugar</th>
    <th>Temperature</th>
    <th>Heart Rate</th>
    <th>Medicine</th>
</tr>
</thead>

<tbody>
<?php if (count($reports) > 0): ?>
    <?php foreach ($reports as $r): ?>
    <tr>
        <td><?= date("Y-m-d H:i", strtotime($r['dateOfCreation'])) ?></td>
        <td><?= $r['bloodPressure'] ?></td>
        <td><?= $r['bloodSugar'] ?></td>
        <td><?= $r['temperature'] ?></td>
        <td><?= $r['heartRate'] ?></td>
        <td><?= $r['medName'] ?? '—' ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="6">No records found</td>
</tr>
<?php endif; ?>
</tbody>

</table>

</div>

<!-- BACK -->
<div class="text-center mt-4">
<a href="dashboard.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Dashboard
</a>
</div>

</div>

<?php include '../includes/footer.php'; ?>
