<?php
$page_title = "Medication Status";
include '../includes/header.php';
require_once '../includes/db_connection.php';
require_once '../includes/medication_alert.php';
//require_once __DIR__ . '/../includes/medication_alert.php';
//checkMissedMedications($conn);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Manage Medications";
date_default_timezone_set("America/Vancouver");
$today = date('Y-m-d');

// FLASH MESSAGE
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success text-center'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}


try {
    checkMissedMeds($conn);
} catch (Throwable $t) {
    $_SESSION['flash_message'] =  "AI ERROR: " . htmlspecialchars($t->getMessage()) . " in " . htmlspecialchars($t->getFile()) . " line " . $t->getLine();
}

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
// GET MEDICATIONS FOR ASSIGNED RESIDENTS
// ===============================
$stmt = $conn->prepare("SELECT m.residentSIN as rSIN, m.medName, m.timeScheduled,
                            r.fname, r.lname, m.dose, me.entryID, me.status, m.medID
                        FROM medication_entry me
                        LEFT JOIN medication m ON me.medID = m.medID
                        JOIN assignment a ON m.residentSIN = a.residentSIN 
                        JOIN resident r ON a.residentSIN = r.residentSIN 
                        WHERE a.empID = ? AND me.date = ?");
$stmt->execute([$empID, $today]);
$rmeds = $stmt->fetchAll();

// ===============================
// HANDLES FORMS
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ===============================
    // MARK MEDICATION AS TAKEN
    // ===============================
    if(isset($_POST['mark'])) {
        $status = 'pending';
        date_default_timezone_set("America/Vancouver");
        $time = date('H:i');
            //recieves either missed or taken as input:
            //is also passed by default:entryID, timing,
            //and mark_taken OR mark_missed
        if ($_POST['mark'] == 'mark_taken') {
            if(strtotime($time)-strtotime($_POST['timing']) >= 900) {$status = 'delayed';}
            else {$status = 'taken';}
                $stmt = $conn->prepare("UPDATE medication_entry
                                    SET status = ?, timeTaken = ?
                                    WHERE entryID = ?");
                $stmt->execute([$status, (string) $time, $_POST['entry']]);
        }
        else {
            $status = 'missed';
            $stmt = $conn->prepare("UPDATE medication_entry
                                    SET status = ?
                                    WHERE entryID = ?");
            $stmt->execute([$status, $_POST['entry']]);
        }

        $_SESSION['flash_message'] = "Medication has been marked as " .
        $status . "! ";
        header("Location: medication_status.php");
        exit;
    }

    // ===============================
    // DELETE MEDICATION
    // ===============================
    if (isset($_POST['delete'])) {
        $medStmt = $conn->prepare("DELETE FROM medication
                                WHERE medID = ?");
        $medStmt->execute([$_POST['medID']]);
        $_SESSION['flash_message'] = "Medication successfully deleted.";
        header("Location: medication_status.php");
        exit;
        }
}
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
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td>
                <form method="POST">
                    <input type="hidden" name="entry" value="<?= $r['entryID'] ?>">
                    <input type="hidden" name="timing" value="<?= $r['timeScheduled'] ?>">
                <!-- Mark a medication as taken. Will mark it as late automatically -->
                    <button type="submit" name="mark" value="mark_taken"
                                class="btn btn-sm btn-success">
                            Taken
                    </button>
                    <button type="submit" name="mark" value="mark_missed"
                                class="btn btn-sm btn-danger">
                            Missed
                    </button>
                </form>
                <form method="POST">
                    <input type="hidden" name="medID" value="<?= $r['medID'] ?>">
                <!-- Removes the medication from the residents
                 schedule - not just todays entry. -->
                    <button type="submit" name="delete" value="delete"
                            class="btn btn-sm btn-danger">
                        Delete
                    </button>
                </form>
                </td>
            </tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>
</div>

<?php include '../includes/footer.php'; ?>
