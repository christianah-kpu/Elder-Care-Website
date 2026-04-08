<?php
$page_title = "Medication Status";
include '../includes/header.php';
require_once '../includes/db_connection.php';
//require_once __DIR__ . '/../includes/medication_alert.php';
//checkMissedMedications($conn);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Manage Medications";

// ===============================
// TEMPORARY - CHECKS SCHEDULED MEDICATIONS AND IF A DAILY ENTRY DOES NOT EXIST, CREATES ONE
// TEMPORARY - USES A PLACEHOLDER HEALTH REPORT. NEEDS TO USE THE HEALTH REPORT FOR THAT DAY/SEGMENT
// ===============================
$today = date('Y-m-d');
$error = '';
date_default_timezone_set("America/Vancouver");
$time = date('H:i');
$stmt = $conn->prepare("SELECT m.medID
                        FROM medication m
                        LEFT JOIN medication_entry me on m.medID = me.medID
                        WHERE me.date is NULL
                        ");
$stmt->execute();
$unlisted_medications = $stmt->fetchAll();
foreach($unlisted_medications as $um) {
    $stmt = $conn->prepare("INSERT INTO medication_entry
                            (medID, reportID)
                            VALUES (?, 2)");
    $stmt->execute([$um['medID']]);
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
                            r.fname, r.lname, m.dose, m.medID
                        FROM medication m
                        JOIN assignment a ON m.residentSIN = a.residentSIN 
                        JOIN resident r ON a.residentSIN = r.residentSIN 
                        WHERE a.empID = ?");
$stmt->execute([$empID]);
$rmeds = $stmt->fetchAll();

// ===============================
// HANDLES FORMS
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ===============================
        // MARK MEDICATION AS TAKEN
        // ===============================
        if(isset($_POST['mark_taken'])) {
            
            $stmt = $conn->prepare("SELECT me.entryID, m.timeScheduled as ts
                                    FROM medication m
                                    JOIN medication_entry me ON m.medID = me.medID 
                                    WHERE m.medID = ? AND me.date = ?");
            $stmt->execute([$_POST['medID'], (string) $today]);
            $updtEntry = $stmt->fetchAll();
            foreach($updtEntry as $ue) {
                echo $ue['entryID'] . '<br>' . $ue['ts'];
                if (strtotime($time)-strtotime($ue['ts']) >= 900) {
                $stmt = $conn->prepare("UPDATE medication_entry me
                                        SET me.status = 'delayed', me.timeTaken = ?
                                        WHERE me.entryID = ?");
                $stmt->execute([(string) $time, $ue['entryID']]);
                }
                else {
                $stmt = $conn->prepare("UPDATE medication_entry me
                                        SET me.status = 'taken', me.timeTaken = ?
                                        WHERE me.entryID = ?");
                $stmt->execute([(string) $time, $ue['entryID']]);
                }
            }
            
            
        }

    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }

        try {
        // ===============================
        // DELETE MEDICATION COMPLETELY
        // ===============================
        if (isset($_POST['delete'])) {
            
            $medStmt = $conn->prepare("DELETE FROM medication
                                    WHERE medID = ?");
            $medStmt->execute([$_POST['medID']]);

            }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
echo $error;
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
                
                <td>
                <form method="POST">
                    <input type="hidden" name="medID" value="<?= $r['medID'] ?>">
                    <input type="hidden" name="current_status" value="<?= $r['rSIN'] ?>">
                <!-- Mark a medication as taken. Will mark it as late automatically -->
                    <button type="submit" name="mark_taken" value="mark_taken"
                                class="btn btn-sm btn-success">
                            Mark Taken
                    </button>
                </form>
                </td>
                <td>
                <form method="POST">
                    <input type="hidden" name="medID" value="<?= $r['medID'] ?>">
                    <input type="hidden" name="current_status" value="<?= $r['rSIN'] ?>">
                <!-- Currently does nothing. Intent is to allow the schedule to be edited
                    <button type="submit" name="update" 
                            class="btn btn-sm btn-success" style="margin-bottom:.2rem">
                        Edit Medication
                    </button><br> -->
                <!-- Deletes the medication for resident - not just todays entry. -->
                    <button type="submit" name="delete" value="delete"
                            class="btn btn-sm btn-danger">
                        Remove From Schedule
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
