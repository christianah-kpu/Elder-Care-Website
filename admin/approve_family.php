<?php
// approve_family.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Approve Family Members";
session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// Only admin can access
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
    $request_id = $_POST['request_id'];

    try {
        // Fetch the request
        $stmt = $conn->prepare("SELECT * FROM family_requests WHERE request_id=?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        if ($request) {
            // Move to familymember table
            $stmt2 = $conn->prepare("INSERT INTO familymember (user_id, fname, lname, phone)
                                     VALUES (?, ?, ?, ?)
                                     ON DUPLICATE KEY UPDATE fname=?, lname=?, phone=?");
            $stmt2->execute([
                $request['user_id'], $request['fname'], $request['lname'], $request['phone'],
                $request['fname'], $request['lname'], $request['phone']
            ]);

            // Delete from family_requests
            $stmt3 = $conn->prepare("DELETE FROM family_requests WHERE request_id=?");
            $stmt3->execute([$request_id]);

            $_SESSION['flash_message'] = "Family request approved and added to family members!";
        }

        header("Location: approve_family.php");
        exit;

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
    }
}

// Handle reject
if (isset($_POST['reject'])) {
    $request_id = $_POST['request_id'];
    $stmt = $conn->prepare("DELETE FROM family_requests WHERE request_id=?");
    $stmt->execute([$request_id]);
    $_SESSION['flash_message'] = "Family request rejected!";
    header("Location: approve_family.php");
    exit;
}

// Fetch pending requests
$stmt = $conn->query("SELECT fr.request_id, u.username AS family_username, fr.fname, fr.lname, fr.phone
                      FROM family_requests fr
                      JOIN users u ON fr.user_id = u.user_id");
$pending_requests = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="text-center mb-4">Approve Family Requests</h2>

    <table class="table table-bordered text-center">
        <thead class="table-primary">
            <tr>
                <th>Family Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['family_username']) ?></td>
                    <td><?= htmlspecialchars($req['fname']) ?></td>
                    <td><?= htmlspecialchars($req['lname']) ?></td>
                    <td><?= htmlspecialchars($req['phone']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
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