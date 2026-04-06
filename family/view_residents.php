<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Family Dashboard";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// Ensure user is family
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'family') {
    header("Location: ../login.php");
    exit;
}

// Get fmID
$stmt = $conn->prepare("SELECT fmID FROM familymember WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$family = $stmt->fetch();

if (!$family) {
    echo "<div class='alert alert-warning text-center'>
            Your account is awaiting admin approval.
          </div>";
    include '../includes/footer.php';
    exit;
}

$fmID = $family['fmID'];

// ===============================
// FETCH APPROVED RESIDENTS
// ===============================
$stmt = $conn->prepare("
    SELECT 
        r.residentSIN,
        r.fname,
        r.lname,
        r.phone,
        u.email
    FROM link l
    JOIN resident r ON l.residentSIN = r.residentSIN
    JOIN users u ON r.user_id = u.user_id
    WHERE l.fmID = ? AND l.status = 'approved'
");
$stmt->execute([$fmID]);
$residents = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="text-center mb-4">My Residents</h2>

    <?php if (empty($residents)): ?>
        <div class="alert alert-info text-center">
            You are not linked to any residents yet.
        </div>
    <?php else: ?>
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(($r['fname'] ?? '') . ' ' . ($r['lname'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
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
    <a href="./dashboard.php" class="btn btn-danger">Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>