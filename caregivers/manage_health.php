<?php
// -----------------
// caregivers/manage_health.php - This page allows a caregiver to enter health data for their assigned residents. 
// After saving, it automatically runs the AI trend detection to check if the resident's health is declining and sends alerts if needed.
// Only users with role = 'caregiver' can access this page.
// -----------------

$page_title = "Manage Health Data";

// Start session to check who is logged in
session_start();

// Load database connection from includes folder
// We use ../ because this file is inside caregivers/ subfolder
require_once '../includes/db_connection.php';

// Load the AI health trend detection function
require_once __DIR__ . '/../includes/ai_health_alert.php';

// ---------------------------------------------------
// SECURITY CHECK
// If user is not logged in or is not a caregiver, redirect them to login page
// ---------------------------------------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

$success = ''; // stores success message to show on page
$error   = ''; // stores error message to show on page

// ---------------------------------------------------
// Here we try to find the caregiver's empID using their session user_id.
// We need empID to:
//   1. Find which residents are assigned to them
//   2. Save health data linked to this caregiver
// Flow: user_id - users.username - caregiver.empID
// ---------------------------------------------------
$userStmt = $conn->prepare("
    SELECT username FROM users WHERE user_id = ?
");
$userStmt->execute([$_SESSION['user_id']]);
$userRow = $userStmt->fetch();

$cgStmt = $conn->prepare("
    SELECT empID FROM caregiver WHERE username = ?
");
$cgStmt->execute([$userRow['username']]);
$cgRow = $cgStmt->fetch();
$empID = $cgRow['empID'];

// ---------------------------------------------------
// Here we will get only residents assigned to THIS caregiver
// We join assignment table with resident table using empID, meaning:
// assignment columns: assignmentID, residentSIN, empID
// resident columns: residentSIN, username, name, DoB...
// ---------------------------------------------------
$resStmt = $conn->prepare("
    SELECT r.residentSIN, r.name
    FROM assignment a
    JOIN resident r ON a.residentSIN = r.residentSIN
    WHERE a.empID = ?
");
$resStmt->execute([$empID]);
$residents = $resStmt->fetchAll();


// This if command will handle form submission when caregiver clicks the Save Health Data button
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get all form values and cast to correct types to match database column types
    $residentSIN   = (int)   $_POST['resident_id'];
    $bloodPressure = (int)   $_POST['blood_pressure']; // stored as INT in DB
    $bloodSugar    = (float) $_POST['blood_sugar'];     // now DECIMAL after Step 1
    $temperature   = (float) $_POST['temperature'];     // now DECIMAL after Step 1
    $heartRate     = (int)   $_POST['heart_rate'];
    $note          = trim($_POST['note']);

    // Medicine fields (optional - caregiver may not enter medicine every time)
    $medicineName  = trim($_POST['medicine_name']);
    $dose          = trim($_POST['dose']);
    $scheduledTime = $_POST['time']; // comes as "YYYY-MM-DDTHH:MM" from datetime-local input

    try {
        // -----------------------------------------------
        // Insert health data into healthreport table
        // Columns: reportID (auto), residentSIN, empID,
        //          heartRate, bloodPressure, bloodSugar,
        //          temperature, dateOfCreation, dateEdited, note
        // -----------------------------------------------
        $stmt = $conn->prepare("
            INSERT INTO healthreport 
                (residentSIN, empID, heartRate, bloodPressure, 
                 bloodSugar, temperature, dateOfCreation, dateEdited, note)
            VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURDATE(), ?)
        ");
        $stmt->execute([
            $residentSIN,
            $empID,
            $heartRate,
            $bloodPressure,
            $bloodSugar,
            $temperature,
            $note
        ]);

        // -----------------------------------------------
        // If caregiver entered a medicine name, insert it into the medication table.
        // Columns: medID (auto), medicine_name, residentSIN, scheduledTime, dose
        // scheduledTime from datetime-local input is "YYYY-MM-DDTHH:MM" as I mentioned before,therefore we can extract the time part
        // -----------------------------------------------
        if (!empty($medicineName)) {
            // Convert "2026-04-05T08:00" to "08:00:00"
            $timeOnly = date('H:i:s', strtotime($scheduledTime));

            $medStmt = $conn->prepare("
                INSERT INTO medication 
                    (medicine_name, residentSIN, scheduledTime, dose)
                VALUES (?, ?, ?, ?)
            ");
            $medStmt->execute([
                $medicineName,
                $residentSIN,
                $timeOnly,
                $dose
            ]);
        }

        $success = "Health data saved successfully.";

        // -----------------------------------------------
        // Run AI trend detection after saving. Function is loaded from includes/ai_health_alert.php
        // This checks the last 3 records for this resident and sends alerts if a declining trend is detected.
        // -----------------------------------------------
        checkHealthTrend($conn, $residentSIN);

    } catch (PDOException $e) {
        // If anything goes wrong with the database, show error
        $error = "Error saving data: " . $e->getMessage();
    }
}

// Load the header template (navigation bar etc.)
include '../includes/header.php';
?>



<div class="container mt-5">
    <h2 class="mb-4">Manage Health Data</h2>

    <!-- Show success message if data was saved -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Show error message if something went wrong -->
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow p-4">
        <form method="POST" action="">

            <!-- Select Resident (only shows residents assigned to this caregiver) -->
            <div class="mb-3">
                <label class="form-label">Resident</label>
                <select class="form-select" name="resident_id" required>
                    <option value="">-- Select Resident --</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?= $r['residentSIN'] ?>">
                            <?= htmlspecialchars($r['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Vital Signs -->
            <div class="row">
                <div class="col-md-3">
                    <!-- bloodPressure stored as INT (systolic only e.g. 135) -->
                    <input type="number" name="blood_pressure"
                           class="form-control mb-2"
                           placeholder="Blood Pressure (e.g. 135)"
                           required>
                </div>
                <div class="col-md-3">
                    <!-- bloodSugar stored as DECIMAL e.g. 7.2 -->
                    <input type="number" step="0.1" name="blood_sugar"
                           class="form-control mb-2"
                           placeholder="Blood Sugar (mmol/L)"
                           required>
                </div>
                <div class="col-md-3">
                    <!-- temperature stored as DECIMAL e.g. 37.8 -->
                    <input type="number" step="0.1" name="temperature"
                           class="form-control mb-2"
                           placeholder="Temperature (°C)"
                           required>
                </div>
                <div class="col-md-3">
                    <!-- heartRate stored as INT e.g. 72 -->
                    <input type="number" name="heart_rate"
                           class="form-control mb-2"
                           placeholder="Heart Rate (bpm)"
                           required>
                </div>
            </div>

            <!-- Notes -->
            <textarea name="note" class="form-control mb-3"
                      placeholder="Notes (optional)"></textarea>

            <!-- Medication section (optional) -->
            <h5>Medication (optional)</h5>
            <input type="text" name="medicine_name"
                   class="form-control mb-2"
                   placeholder="Medicine Name">
            <input type="text" name="dose"
                   class="form-control mb-2"
                   placeholder="Dose (e.g. 500mg)">
            <!-- datetime-local gives us date + time together -->
            <input type="datetime-local" name="time"
                   class="form-control mb-3">

            <button type="submit" class="btn btn-success w-100">
                Save Health Data
            </button>

        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>