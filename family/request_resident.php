<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Request Resident Access";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';


// Ensure user is family
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'family') {
    header("Location: ../login.php");
    exit;
}

// get fmID from logged in user_id session.
$stmt = $conn->prepare("SELECT fmID FROM familymember WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$family = $stmt->fetch();

if (!$family) {
    echo "<div class='alert alert-warning text-center'>
            Your account is awaiting admin approval.
          </div>
          <div class='text-center mt-4'>
            <a href='dashboard.php' class='btn btn-secondary'>← Back to Dashboard</a>
           </div>";
          
    include '../includes/footer.php';
    exit;
}

$fmID = $family['fmID'];

// Flash message
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// Handle request submission
if (isset($_POST['request_access'])) {
    $residentSIN = $_POST['residentSIN'];

    // Prevent duplicate requests
    $stmtCheck = $conn->prepare("SELECT * FROM link WHERE fmID=? AND residentSIN=?");
    $stmtCheck->execute([$fmID, $residentSIN]);

    if ($stmtCheck->rowCount() === 0) {
        $stmt = $conn->prepare("INSERT INTO link (residentSIN, fmID, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$residentSIN, $fmID]);
        $_SESSION['flash_message'] = "Request sent! Waiting for admin approval.";
    } else {
        $_SESSION['flash_message'] = "You have already requested access to this resident.";
    }

    header("Location: request_resident.php");
    exit;
}

// Fetch residents not linked yet
$stmt = $conn->prepare("
    SELECT r.residentSIN, r.fname, r.lname
    FROM resident r
    WHERE r.residentSIN NOT IN (
        SELECT residentSIN FROM link WHERE fmID=?
    )
");
$stmt->execute([$fmID]);
$residents = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="text-center mb-4">Request Resident Access</h2>

    <?php if (empty($residents)): ?>
        <p class="text-center">No residents available for request.</p>
    <?php else: ?>
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Resident Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['fname'] . ' ' . $r['lname']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="residentSIN" value="<?= $r['residentSIN'] ?>">
                                <button type="submit" name="request_access" class="btn btn-primary btn-sm">
                                    Request Access
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <h4 class="mt-5">My Requests</h4>

    <?php
    $stmt = $conn->prepare("
        SELECT r.fname, r.lname, l.status
        FROM link l
        JOIN resident r ON l.residentSIN = r.residentSIN
        WHERE l.fmID = ?
    ");
    $stmt->execute([$fmID]);
    $requests = $stmt->fetchAll();
    ?>

    <?php if (empty($requests)): ?>
        <p>No requests made yet.</p>
    <?php else: ?>
        <table class="table table-bordered text-center">
            <thead class="table-secondary">
                <tr>
                    <th>Resident Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?= htmlspecialchars($req['fname'] . ' ' . $req['lname']) ?></td>
                        <td>
                            <span class="badge 
                                <?= $req['status']=='approved' ? 'bg-success' : 'bg-warning' ?>">
                                <?= ucfirst($req['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    </div>

<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>