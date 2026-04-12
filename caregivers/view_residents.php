<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Residents";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// CHECK CAREGIVER
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

// GET caregiver empID
$stmt = $conn->prepare("SELECT empID FROM caregiver WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$caregiver = $stmt->fetch();

if (!$caregiver) {
    echo "<div class='alert alert-danger'>Caregiver not found.</div>";
    exit;
}

$empID = $caregiver['empID'];

// FILTERS
$name = $_GET['name'] ?? '';
$date = $_GET['date'] ?? '';

// =======================
// FETCH RESIDENTS
// =======================
$query = "
SELECT DISTINCT r.residentSIN, r.fname, r.lname, r.phone, r.profilePhoto
FROM resident r
JOIN assignment a ON r.residentSIN = a.residentSIN
LEFT JOIN healthreport hr ON r.residentSIN = hr.residentSIN
WHERE a.empID = ?
";

$params = [$empID];

if (!empty($name)) {
    $query .= " AND (r.fname LIKE ? OR r.lname LIKE ?)";
    $params[] = "%$name%";
    $params[] = "%$name%";
}

if (!empty($date)) {
    $query .= " AND DATE(hr.dateOfCreation) = ?";
    $params[] = $date;
}

$query .= " ORDER BY r.fname ASC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$residents = $stmt->fetchAll();
?>

<div class="container mt-5">

<h2 class="mb-4">My Assigned Residents</h2>

<!-- SEARCH -->
<form method="GET" class="row g-3 mb-4">

    <div class="col-md-4">
        <label>Resident Name</label>
        <input type="text" name="name" class="form-control"
               value="<?= htmlspecialchars($name) ?>" placeholder="Enter name">
    </div>

    <div class="col-md-4">
        <label>Date</label>
        <input type="date" name="date" class="form-control"
               value="<?= htmlspecialchars($date) ?>">
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary w-100">Search</button>
    </div>

</form>

<!-- LIST -->
<div class="card shadow-sm">
<div class="card-header bg-dark text-white">
    <strong>Residents List</strong>
</div>

<div class="card-body p-0" style="max-height:400px; overflow-y:auto;">

<table class="table table-bordered text-center align-middle mb-0">
<thead class="table-light sticky-top">
<tr>
    <th>Profile</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php if (count($residents) > 0): ?>
<?php foreach ($residents as $r): ?>

<?php
$imgPath = "../uploads/residents/" . $r['profilePhoto'];
?>

<tr>

<td>
<?php if (!empty($r['profilePhoto']) && file_exists($imgPath)): ?>
    <img src="<?= $imgPath ?>"
         style="width:50px; height:50px; object-fit:cover; border-radius:50%;">
<?php else: ?>
    <div style="width:50px;height:50px;border-radius:50%;
                display:flex;align-items:center;justify-content:center;
                background:#f1f1f1;">
        <i class="bi bi-person-exclamation" style="font-size:24px;"></i>
    </div>
<?php endif; ?>
</td>

<td><?= htmlspecialchars($r['fname'] . " " . $r['lname']) ?></td>
<td><?= htmlspecialchars($r['phone'] ?? 'N/A') ?></td>

<td>
<a href="view_resident.php?sin=<?= $r['residentSIN'] ?>" 
   class="btn btn-sm btn-primary">
   View Details
</a>
</td>

</tr>

<?php endforeach; ?>
<?php else: ?>
<tr>
<td colspan="4">No residents found</td>
</tr>
<?php endif; ?>
</tbody>

</table>

</div>
</div>

</div>

<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>