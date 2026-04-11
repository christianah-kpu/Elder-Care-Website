<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Residents";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// CHECK FAMILY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'family') {
    header("Location: ../login.php");
    exit;
}

// GET fmID
$stmt = $conn->prepare("SELECT fmID FROM familymember WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$family = $stmt->fetch();

if (!$family) {
    echo "<div class='alert alert-danger'>Family member not found.</div>";
    exit;
}

$fmID = $family['fmID'];

// =======================
// FETCH LINKED RESIDENTS
// =======================
$stmt = $conn->prepare("
SELECT r.residentSIN, r.fname, r.lname, r.phone, r.profilePhoto
FROM resident r
JOIN link l ON r.residentSIN = l.residentSIN
WHERE l.fmID = ? AND l.status = 'approved'
ORDER BY r.fname
");
$stmt->execute([$fmID]);
$residents = $stmt->fetchAll();
?>

<div class="container mt-5">

<h2 class="mb-4">My Linked Residents</h2>

<div class="card shadow-sm">
<div class="card-header bg-dark text-white">
    <strong>Residents</strong>
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
<?php if ($residents): ?>
<?php foreach ($residents as $r): ?>
<tr>

<td>
<?php if (!empty($r['profilePhoto'])): ?>
    <img src="../uploads/<?= htmlspecialchars($r['profilePhoto']) ?>"
         style="width:50px; height:50px; object-fit:cover; border-radius:50%;">
<?php else: ?>
    <div style="font-size:40px; color:#6c757d;">
        <i class="bi bi-person-circle"></i>
    </div>
<?php endif; ?>
</td>

<td><?= htmlspecialchars($r['fname']." ".$r['lname']) ?></td>
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
<td colspan="4">No linked residents</td>
</tr>
<?php endif; ?>
</tbody>

</table>

</div>
</div>

<!-- BACK -->
<div class="text-center mt-4">
<a href="dashboard.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Dashboard
</a>
</div>

</div>

<?php include '../includes/footer.php'; ?>