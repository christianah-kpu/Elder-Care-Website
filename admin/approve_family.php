
<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Approve Family Members";
session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin'){
    header("Location: ../login.php"); exit;
}

// Approve action
if (isset($_POST['approve'])) {
    $user_id = $_POST['user_id'];

    try {
        // Set status to active
        $stmt = $conn->prepare("INSERT INTO user_status (user_id,status) VALUES (?, 'active')
                                ON DUPLICATE KEY UPDATE status='active'");
        $stmt->execute([$user_id]);

        // Create familymember record if not exists
        $stmt2 = $conn->prepare("SELECT * FROM familymember WHERE user_id=?");
        $stmt2->execute([$user_id]);
        if ($stmt2->rowCount()===0){
            $stmt3 = $conn->prepare("INSERT INTO familymember (user_id, fname, lname, phone)
                                     SELECT user_id, NULL, NULL, NULL FROM users WHERE user_id=?");
            $stmt3->execute([$user_id]);
        }

        // Add a flash message
        $_SESSION['flash_message'] = "Family member approved successfully!";

        // Redirect back to refresh page and avoid resubmission
        header("Location: approve_family.php");
        exit;

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
    }
}

// Fetch pending family users
$stmt = $conn->query("SELECT u.user_id, u.username, u.email, COALESCE(us.status,'pending') as status
                      FROM users u
                      LEFT JOIN user_status us ON u.user_id=us.user_id
                      WHERE u.role='family' AND COALESCE(us.status,'pending')='pending'");
$pending = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="mb-4 text-center">Approve Family Members</h2>

    <table class="table table-bordered text-center">
        <thead class="table-primary">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pending as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['username']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $p['user_id'] ?>">
                        <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
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