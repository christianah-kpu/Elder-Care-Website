<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Assign Caregivers";
session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// ===============================
// ONLY ADMIN
// ===============================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ===============================
// FLASH MESSAGE
// ===============================
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// ===============================
// AUTO REMOVE ASSIGNMENTS FOR SUSPENDED USERS
// (runs every time page loads)
// ===============================
$conn->prepare("
    DELETE a FROM assignment a
    JOIN resident r ON a.residentSIN = r.residentSIN
    JOIN users u ON r.user_id = u.user_id
    JOIN user_status us ON u.user_id = us.user_id
    WHERE us.status = 'suspended'
")->execute();

$conn->prepare("
    DELETE a FROM assignment a
    JOIN caregiver c ON a.empID = c.empID
    JOIN users u ON c.user_id = u.user_id
    JOIN user_status us ON u.user_id = us.user_id
    WHERE us.status = 'suspended'
")->execute();

// ===============================
// HANDLE ASSIGN
// ===============================
if (isset($_POST['assign'])) {

    $residentSIN = $_POST['residentSIN'];
    $empID = $_POST['empID'];

    try {
        $check = $conn->prepare("
            SELECT * FROM assignment 
            WHERE residentSIN=? AND empID=?
        ");
        $check->execute([$residentSIN, $empID]);

        if ($check->rowCount() === 0) {
            $stmt = $conn->prepare("
                INSERT INTO assignment (residentSIN, empID) 
                VALUES (?, ?)
            ");
            $stmt->execute([$residentSIN, $empID]);

            $_SESSION['flash_message'] = "Caregiver assigned successfully!";
        } else {
            $_SESSION['flash_message'] = "This caregiver is already assigned.";
        }

        header("Location: assign_caregivers.php");
        exit;

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
    }
}

// ===============================
// HANDLE REMOVE
// ===============================
if (isset($_POST['remove'])) {

    $residentSIN = $_POST['residentSIN'];
    $empID = $_POST['empID'];

    try {
        $stmt = $conn->prepare("
            DELETE FROM assignment 
            WHERE residentSIN=? AND empID=?
        ");
        $stmt->execute([$residentSIN, $empID]);

        $_SESSION['flash_message'] = "Assignment removed successfully!";
        header("Location: assign_caregivers.php");
        exit;

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
    }
}

// ===============================
// FETCH ONLY ACTIVE RESIDENTS
// ===============================
$residents = $conn->query("
    SELECT r.residentSIN, r.fname, r.lname
    FROM resident r
    JOIN users u ON r.user_id = u.user_id
    LEFT JOIN user_status us ON u.user_id = us.user_id
    WHERE COALESCE(us.status, 'active') = 'active'
    ORDER BY r.fname
")->fetchAll();

// ===============================
// FETCH ONLY ACTIVE CAREGIVERS
// ===============================
$caregivers = $conn->query("
    SELECT c.empID, c.fname, c.lname
    FROM caregiver c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN user_status us ON u.user_id = us.user_id
    WHERE COALESCE(us.status, 'active') = 'active'
    ORDER BY c.fname
")->fetchAll();

// ===============================
// FETCH ASSIGNMENTS (ACTIVE ONLY)
// ===============================
$assignments = $conn->query("
    SELECT 
        a.residentSIN, 
        a.empID, 
        r.fname AS r_fname, 
        r.lname AS r_lname,
        c.fname AS c_fname, 
        c.lname AS c_lname
    FROM assignment a
    JOIN resident r ON a.residentSIN = r.residentSIN
    JOIN caregiver c ON a.empID = c.empID

    JOIN users ur ON r.user_id = ur.user_id
    LEFT JOIN user_status usr ON ur.user_id = usr.user_id

    JOIN users uc ON c.user_id = uc.user_id
    LEFT JOIN user_status usc ON uc.user_id = usc.user_id

    WHERE COALESCE(usr.status,'active') = 'active'
      AND COALESCE(usc.status,'active') = 'active'

    ORDER BY r.fname
")->fetchAll();
?>

<div class="container py-4">
    <h2 class="mb-4 text-center">Assign Caregivers to Residents</h2>

    <!-- FORM -->
    <form method="POST" class="row g-2 mb-4">

        <div class="col-md-5">
            <select name="residentSIN" class="form-select" required>
                <option value="">Select Resident</option>
                <?php foreach ($residents as $r): ?>
                    <option value="<?= $r['residentSIN'] ?>">
                        <?= htmlspecialchars($r['fname'].' '.$r['lname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-5">
            <select name="empID" class="form-select" required>
                <option value="">Select Caregiver</option>
                <?php foreach ($caregivers as $c): ?>
                    <option value="<?= $c['empID'] ?>">
                        <?= htmlspecialchars($c['fname'].' '.$c['lname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" name="assign" class="btn btn-success w-100">
                Assign
            </button>
        </div>

    </form>

    <!-- TABLE -->
    <h4 class="mb-3">Current Assignments</h4>

    <table class="table table-bordered text-center">
        <thead class="table-primary">
            <tr>
                <th>Resident</th>
                <th>Caregiver</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php if (count($assignments) === 0): ?>
            <tr><td colspan="3">No assignments yet.</td></tr>
        <?php else: ?>
            <?php foreach ($assignments as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['r_fname'].' '.$a['r_lname']) ?></td>
                <td><?= htmlspecialchars($a['c_fname'].' '.$a['c_lname']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="residentSIN" value="<?= $a['residentSIN'] ?>">
                        <input type="hidden" name="empID" value="<?= $a['empID'] ?>">
                        <button type="submit" name="remove" class="btn btn-danger btn-sm">
                            Remove
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary">← Back</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>