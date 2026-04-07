<?php
include '../includes/header.php';
require_once '../includes/db_connection.php';



// ===============================
// FETCH ASSIGNED RESIDENTS NAMES
// ===============================
$stmt = $conn->prepare("SELECT empID FROM caregiver WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cgRow = $stmt->fetch();

if (!$cgRow) {
    die("Caregiver not found.");
}
$empID = $cgRow['empID'];

$stmt = $conn->prepare("SELECT CONCAT(r.fname + ' ' + r.lname) as rname
                        FROM resident r
                        LEFT JOIN assignment a ON r.residentSIN = a.residentSIN
                        WHERE a.empID = ?");
$stmt->execute([$empID]);
$rName = $stmt->fetchAll();

// ===============================
// GET MEDICATIONS FOR RESIDENTS
// ===============================
$stmt = $conn->prepare("SELECT m.residentSIN as rSIN, m.medName, m.timeScheduled,
                            r.fname, r.lname, m.dose, m.medID
                        FROM medication m
                        JOIN assignment a ON m.residentSIN = a.residentSIN 
                        JOIN resident r ON a.residentSIN = r.residentSIN 
                        WHERE a.empID = ?");
$stmt->execute([$empID]);
$rmeds = $stmt->fetchAll();
?>


<div class="container mt-5">

<h2 class="mb-4">Medication Status</h2>

<div class="card shadow p-4">

<table class="table table-bordered">
<thead>
<tr>
    <th>Resident</th>
    <th>Medicine</th>
    <th>Dose</th>
    <th>Time Scheduled</th>
    <th>Status</th>
    <th>Edit</th>
</tr>
</thead>
<tbody>


<!-- Example -->
<?php foreach ($rmeds as $r): ?>
            <tr class="">
                <td><?= htmlspecialchars($r['fname'] . ' ' . $r['lname']) ?></td>
                <td><?= htmlspecialchars($r['medName']) ?></td>
                <td><?= htmlspecialchars($r['dose']) ?></td>
                <td><?= htmlspecialchars($r['timeScheduled']) ?></td>
                <form method="POST">
                <td>
                    <select class="form-select">
                        <option value="" selected>Pending</option>
                        <option value="">Given</option>
                        <option value="">Delayed</option>
                        <option value="">Missed</option>
                    </select>
                </td>
                <td>
                        <input type="hidden" name="medID" value="<?= $r['medID'] ?>">
                        <input type="hidden" name="current_status" value="<?= $r['dose'] ?>">
                        <!-- Button to delete account-->
                        <button type="submit" name="delete_acct" 
                                class="btn btn-sm btn-danger">
                            Delete
                        </button>
                </td>
            </form>
            </tr>
<?php endforeach; ?>

</tbody>
</table>

</div>

</div>

<?php include '../includes/footer.php'; ?>
