<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Approve Family Requests";

session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';


// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Flash message
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// Handle approve
if (isset($_POST['approve'])) {
    $linkID = $_POST['linkID'];
    $stmt = $conn->prepare("UPDATE link SET status='approved' WHERE linkID=?");
    $stmt->execute([$linkID]);
    $_SESSION['flash_message'] = "Request approved!";
    header("Location: approve_family_request.php");
    exit;
}

// Handle reject (optional)
if (isset($_POST['reject'])) {
    $linkID = $_POST['linkID'];
    $stmt = $conn->prepare("DELETE FROM link WHERE linkID=?");
    $stmt->execute([$linkID]);
    $_SESSION['flash_message'] = "Request rejected!";
    header("Location: approve_family_request.php");
    exit;
}

// Fetch pending requests
$stmt = $conn->query("
    SELECT l.linkID, f.fname AS family_fname, f.lname AS family_lname,
           r.fname AS resident_fname, r.lname AS resident_lname
    FROM link l
    JOIN familymember f ON l.fmID = f.fmID
    JOIN resident r ON l.residentSIN = r.residentSIN
    WHERE l.status='pending'
");
$pending_links = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="text-center mb-4">Approve Family Requests</h2>

    <table class="table table-bordered text-center">
        <thead class="table-primary">
            <tr>
                <th>Family Name</th>
                <th>Resident Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_links as $link): ?>
                <tr>
                    <td><?= htmlspecialchars($link['family_fname'] . ' ' . $link['family_lname']) ?></td>
                    <td><?= htmlspecialchars($link['resident_fname'] . ' ' . $link['resident_lname']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="linkID" value="<?= $link['linkID'] ?>">
                            <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                            <button type="submit" name="reject" class="btn btn-danger btn-sm">Reject</button>
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

<?php include '../includes/footer.php'; ?>