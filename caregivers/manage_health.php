<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Manage Health Data";

session_start();
require_once '../includes/db_connection.php';
require_once __DIR__ . '/../includes/ai_health_alert.php';
require_once '../includes/medication_alert.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

$success = '';
$error   = '';

$stmt = $conn->prepare("SELECT empID FROM caregiver WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cgRow = $stmt->fetch();

if (!$cgRow) {
    die("Caregiver profile not found.");
}
$empID = $cgRow['empID'];

$resStmt = $conn->prepare("
    SELECT r.residentSIN, r.fname, r.lname
    FROM assignment a
    JOIN resident r ON a.residentSIN = r.residentSIN
    WHERE a.empID = ?
");
$resStmt->execute([$empID]);
$residents = $resStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['report'])) {
        try {
            date_default_timezone_set("America/Vancouver");
            $today = date('Y-m-d');
            $residentSIN   = $_POST['resident_id'];
            $bloodPressure = (int)   $_POST['blood_pressure'];
            $bloodSugar    = (float) $_POST['blood_sugar'];
            $temperature   = (float) $_POST['temperature'];
            $heartRate     = (int)   $_POST['heart_rate'];

            $stmt = $conn->prepare("SELECT reportID
                                  FROM healthreport
                                  WHERE residentSin = ? AND DATE(dateOfCreation) = ?");
            $stmt->execute([$residentSIN, $today]);
            $hrrow = $stmt->fetch();
            if(!$hrrow) {
                //if a health report with todays date is NOT found, create a
                //new health report for today
                $stmt = $conn->prepare("
                    INSERT INTO healthreport
                    (residentSIN, empID, heartRate, bloodPressure, bloodSugar, temperature,
                     dateOfCreation, dateEdited)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$residentSIN, $empID, $heartRate, $bloodPressure, $bloodSugar, $temperature]);

            }
            else {
                //if a health report with todays date IS found, overwrite
                //it with the new info
                $reportID = $hrrow['reportID'];
                $stmt = $conn->prepare("UPDATE healthreport
                                        SET bloodPressure = ?, bloodSugar = ?,
                                            temperature = ?, heartRate = ?,
                                            dateEdited = NOW(), empID = ?
                                        WHERE reportID = ?");
                $stmt->execute([$heartRate, $bloodPressure, $bloodSugar, $temperature, $empID, $reportID]);
            }
            
            $success = "Health data saved successfully. AI check ran for SIN: " . htmlspecialchars($residentSIN);

            // RUN AI HEALTH TREND CHECK
            try {
                checkHealthTrend($conn, $residentSIN);
            } catch (Throwable $t) {
                $error = "AI ERROR: " . htmlspecialchars($t->getMessage()) . " in " . htmlspecialchars($t->getFile()) . " line " . $t->getLine();
            }
            
            try {
                checkMissedMeds($conn);
            } catch (Throwable $t) {
                echo "AI ERROR: " . htmlspecialchars($t->getMessage()) . " in " . htmlspecialchars($t->getFile()) . " line " . $t->getLine();
            }
        } catch (PDOException $e) {
            $error = "Error saving health report: " . $e->getMessage();
        }
    }

    if (isset($_POST['newMed'])) {
        try {
            $residentSIN   = $_POST['resident_id'];
            $medicineName  = trim($_POST['medicine_name']);
            $dose          = trim($_POST['dose']);
            $scheduledTime = $_POST['time'];
            $timeOnly      = date('H:i:s', strtotime($scheduledTime));

            $medStmt = $conn->prepare("
                INSERT INTO medication
                    (residentSIN, empID, medName, dose, timeScheduled)
                VALUES (?, ?, ?, ?, ?)
            ");
            $medStmt->execute([$residentSIN, $empID, $medicineName, $dose, $timeOnly]);

            $success = "Medication scheduled successfully.";

        } catch (PDOException $e) {
            $error = "Error saving medication: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Manage Health Data</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow p-4 mb-4">
        <h5>Today's Health Report</h5>
        <p class="text-muted small">
            After 7 days of records, the AI system will automatically calculate
            7-day averages and email caregivers and family if any vital is outside
            the normal range.
        </p>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Resident</label>
                <select class="form-select" name="resident_id" required>
                    <option value="" selected disabled>-- Select Resident --</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?= htmlspecialchars($r['residentSIN']) ?>">
                            <?= htmlspecialchars($r['fname'] . ' ' . $r['lname'] . ' (' . $r['residentSIN'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label class="form-label">Vitals</label>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Blood Pressure (mmHg)<br>Normal: 90–135</label>
                    <input type="number" name="blood_pressure" class="form-control mb-3"
                           placeholder="e.g. 120" min="0" max="300" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Blood Sugar (mmol/L)<br>Normal: 3.9–7.0</label>
                    <input type="number" step="0.1" name="blood_sugar" class="form-control mb-3"
                           placeholder="e.g. 5.5" min="0" max="30" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Temperature (°C)<br>Normal: 36.0–37.5</label>
                    <input type="number" step="0.1" name="temperature" class="form-control mb-3"
                           placeholder="e.g. 36.8" min="30" max="45" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Heart Rate (bpm)<br>Normal: 60–100</label>
                    <input type="number" name="heart_rate" class="form-control mb-3"
                           placeholder="e.g. 72" min="0" max="300" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100" name="report" value="reportSave">
                Save Health Report
            </button>
        </form>
    </div>

    <div class="card shadow p-4 mb-5">
        <h5>Schedule New Medication</h5>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Resident</label>
                <select class="form-select" name="resident_id" required>
                    <option value="" selected disabled>-- Select Resident --</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?= htmlspecialchars($r['residentSIN']) ?>">
                            <?= htmlspecialchars($r['fname'] . ' ' . $r['lname'] . ' (' . $r['residentSIN'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="text" name="medicine_name" class="form-control mb-2"
                   placeholder="Medicine Name" required>
            <input type="text" name="dose" class="form-control mb-2"
                   placeholder="Dose (e.g. 10mg)" required>
            <input type="datetime-local" name="time" class="form-control mb-3" required>

            <button type="submit" class="btn btn-success w-100" name="newMed" value="medicationSave">
                Save New Medication
            </button>
        </form>
    </div>
</div>

<div class="text-center mb-5">
    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
