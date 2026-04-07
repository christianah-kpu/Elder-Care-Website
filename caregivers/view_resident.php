<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Resident Details";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// Ensure caregiver
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

// ===============================
// GET SIN FROM URL
// ===============================
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger text-center'>No resident selected.</div>";
    include '../includes/footer.php';
    exit;
}

$residentSIN = $_GET['id'];

// ===============================
// GET empID
// ===============================
$stmt = $conn->prepare("SELECT empID FROM caregiver WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$caregiver = $stmt->fetch();

if (!$caregiver) {
    echo "<div class='alert alert-danger text-center'>Caregiver profile not found.</div>";
    include '../includes/footer.php';
    exit;
}

$empID = $caregiver['empID'];

// ===============================
// SECURITY CHECK
// ===============================
$stmt = $conn->prepare("
    SELECT * FROM assignment 
    WHERE residentSIN = ? AND empID = ?
");
$stmt->execute([$residentSIN, $empID]);

if ($stmt->rowCount() === 0) {
    echo "<div class='alert alert-danger text-center'>Access denied.</div>";
    include '../includes/footer.php';
    exit;
}

// ===============================
// FETCH RESIDENT INFO
// ===============================
$stmt = $conn->prepare("
    SELECT r.*, u.email
    FROM resident r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.residentSIN = ?
");
$stmt->execute([$residentSIN]);
$resident = $stmt->fetch();

if (!$resident) {
    echo "<div class='alert alert-danger text-center'>Resident not found.</div>";
    include '../includes/footer.php';
    exit;
}

// ===============================
// FETCH HEALTH DATA
// ===============================
$stmt = $conn->prepare("
    SELECT *
    FROM healthreport
    WHERE residentSIN = ?
    ORDER BY dateOfCreation DESC
");
$stmt->execute([$residentSIN]);
$health = $stmt->fetchAll();
?>

<div class="container py-4">

    <h2 class="text-center mb-4">Resident Details</h2>

    <!-- BASIC INFO -->
    <div class="card mb-4 shadow">
        <div class="card-body">

            <h4>Basic Information</h4>

            <p><strong>SIN:</strong> <?= htmlspecialchars($resident['residentSIN']) ?></p>

            <p><strong>Name:</strong>
                <?= htmlspecialchars(($resident['fname'] ?? '') . ' ' . ($resident['lname'] ?? '')) ?>
            </p>

            <p><strong>Email:</strong> <?= htmlspecialchars($resident['email'] ?? '') ?></p>

            <p><strong>Phone:</strong> <?= htmlspecialchars($resident['phone'] ?? '') ?></p>

            <hr>

            <h5>Emergency Contact</h5>

            <p><strong>Name:</strong> <?= htmlspecialchars($resident['ECname'] ?? '') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($resident['ECphone'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($resident['ECemail'] ?? '') ?></p>

        </div>
    </div>

    <!-- HEALTH DATA -->
    <div class="card shadow">
        <div class="card-body">

            <h4>Health Records</h4>

            <?php if (empty($health)): ?>
                <p>No health records yet.</p>
            <?php else: ?>

                <table class="table table-bordered text-center">
                    <thead class="table-success">
                        <tr>
                            <th>Date</th>
                            <th>Blood Pressure</th>
                            <th>Blood Sugar</th>
                            <th>Heart Rate</th>
                            <th>Temperature</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($health as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['dateOfCreation'] ?? '') ?></td>
                                <td><?= htmlspecialchars($h['bloodPressure'] ?? '') ?></td>
                                <td><?= htmlspecialchars($h['bloodSugar'] ?? '') ?></td>
                                <td><?= htmlspecialchars($h['heartRate'] ?? '') ?></td>
                                <td><?= htmlspecialchars($h['temperature'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>

            <?php endif; ?>

        </div>
    </div>

</div>

<div class="text-center mt-4">
    <a href="view_residents.php" class="btn btn-secondary">← Back</a>
</div>

<?php include '../includes/footer.php'; ?>