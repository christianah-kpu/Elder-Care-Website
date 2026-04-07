<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Manage Health Data";

session_start();
require_once '../includes/db_connection.php';
require_once __DIR__ . '/../includes/ai_health_alert.php';

// ===============================
// SECURITY CHECK
// ===============================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

$success = '';
$error   = '';

// ===============================
// GET empID (FIXED)
// ===============================
$stmt = $conn->prepare("SELECT empID FROM caregiver WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cgRow = $stmt->fetch();

if (!$cgRow) {
    die("Caregiver not found.");
}
$empID = $cgRow['empID'];

// ===============================
// FETCH ASSIGNED RESIDENTS (FIXED)
// ===============================
$resStmt = $conn->prepare("
    SELECT r.residentSIN, r.fname, r.lname
    FROM assignment a
    JOIN resident r ON a.residentSIN = r.residentSIN
    WHERE a.empID = ?
");
$resStmt->execute([$empID]);
$residents = $resStmt->fetchAll();

// ===============================
// HANDLE FORM
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    try {
        // ===============================
        // INSERT HEALTH DATA (FIXED)
        // ===============================
        if(isset($_POST['report'])) {
            $residentSIN   = $_POST['resident_id']; // VARCHAR, no casting
            $bloodPressure = (int) $_POST['blood_pressure'];
            $bloodSugar    = (float) $_POST['blood_sugar'];
            $temperature   = (float) $_POST['temperature'];
            $heartRate     = (int) $_POST['heart_rate'];
            $stmt = $conn->prepare("
                INSERT INTO healthreport 
                (residentSIN, empID, heartRate, bloodPressure, bloodSugar, temperature, dateOfCreation, dateEdited)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([
                $residentSIN,
                $empID,
                $heartRate,
                $bloodPressure,
                $bloodSugar,
                $temperature
            ]);
            $success = "Health data saved successfully.";

            // ===============================
            // RUN AI CHECK
            // ===============================
            checkHealthTrend($conn, $residentSIN);
        }

    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }

        try {
        // ===============================
        // INSERT MEDICATION (OPTIONAL)
        // ===============================
        if (isset($_POST['newMed'])) {
            $residentSIN   = $_POST['resident_id']; // VARCHAR, no casting
            $medicineName  = trim($_POST['medicine_name']);
            $dose          = trim($_POST['dose']);
            $scheduledTime = $_POST['time'];
            $timeOnly = date('H:i:s', strtotime($scheduledTime));

            $medStmt = $conn->prepare("
                INSERT INTO medication 
                (residentSIN, empID, medName, dose, timeScheduled)
                VALUES (?, ?, ?, ?, ?)
            ");

            $medStmt->execute([
                $residentSIN, 
                $empID,   
                $medicineName,
                $dose,
                $timeOnly
            ]);
            $success = "New Medication Saved Successfully.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
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

    <div class="card shadow p-4">
        <form method="POST">
        <h5>Todays Health Report</h5>
            <!-- RESIDENT SELECT -->
            <div class="mb-3">
                <label class="form-label">Resident</label>
                <select class="form-select" name="resident_id" required>
                    <option value="" selected disabled>-- Select Resident --</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?= $r['residentSIN'] ?>">
                            <?= htmlspecialchars($r['fname'] . ' ' . $r['lname'] . ' (' . $r['residentSIN'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- VITALS -->
            <label class="form-label">Vitals</label>
            <div class="row">
                <div class="col-md-3">
                    <input type="number" name="blood_pressure" class="form-control mb-2" placeholder="Blood Pressure" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.1" name="blood_sugar" class="form-control mb-2" placeholder="Blood Sugar" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.1" name="temperature" class="form-control mb-2" placeholder="Temperature" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="heart_rate" class="form-control mb-2" placeholder="Heart Rate" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100" name="report" value="reportSave">
                Save Health Report
            </button>
        </form>
    </div>
    
    <div class="card shadow p-4" style="margin-top: 1rem">
        <form method="POST">
            <!-- MEDICATION -->
            <h5>Schedule New Medication</h5>
                        <!-- RESIDENT SELECT -->
            <div class="mb-3">
                <label class="form-label">Resident</label>
                <select class="form-select" name="resident_id" required>
                    <option value="" selected disabled>-- Select Resident --</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?= $r['residentSIN'] ?>">
                            <?= htmlspecialchars($r['fname'] . ' ' . $r['lname'] . ' (' . $r['residentSIN'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="text" name="medicine_name" class="form-control mb-2" placeholder="Medicine Name">
            <input type="text" name="dose" class="form-control mb-2" placeholder="Dose">

            <input type="datetime-local" name="time" class="form-control mb-3">

            <button type="submit" class="btn btn-success w-100" name="newMed" value="medicationSave">
                Save New Medication
            </button>

        </form>
    </div>
</div>

<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
