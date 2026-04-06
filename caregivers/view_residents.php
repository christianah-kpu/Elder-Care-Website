<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "My Residents";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// Ensure caregiver
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'caregiver') {
    header("Location: ../login.php");
    exit;
}

// ===============================
// GET empID
// ===============================
$stmt = $conn->prepare("SELECT empID FROM caregiver WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$caregiver = $stmt->fetch();

if (!$caregiver) {
    die("Caregiver profile not found.");
}

$empID = $caregiver['empID'];

// ===============================
// FETCH ASSIGNED RESIDENTS
// ===============================
$stmt = $conn->prepare("
    SELECT 
        r.residentSIN,
        r.fname,
        r.lname,
        r.phone,
        u.email
    FROM assignment a
    JOIN resident r ON a.residentSIN = r.residentSIN
    JOIN users u ON r.user_id = u.user_id
    WHERE a.empID = ?
");
$stmt->execute([$empID]);
$residents = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="text-center mb-4">My Assigned Residents</h2>

    <?php if (empty($residents)): ?>
        <p class="text-center">No residents assigned yet.</p>
    <?php else: ?>
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $r): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars(($r['fname'] ?? '') . ' ' . ($r['lname'] ?? '')) ?>
                        </td>
                        <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
                        <td>
                            <a href="view_resident.php?id=<?= $r['residentSIN'] ?>" 
                               class="btn btn-primary btn-sm">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">← Back</a>
</div>

<?php include '../includes/footer.php'; ?>