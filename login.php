<!-- Sonny -->
<?php 
$page_title="Log In";

session_start();
require_once './includes/db_connection.php';
include 'includes/header.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {

        // ===============================
        // GET USER
        // ===============================
        $stmt = $conn->prepare("
            SELECT user_id, password_hash, role, is_verified, username 
            FROM users 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // ===============================
        // VERIFY PASSWORD
        // ===============================
        if ($user && password_verify($password, $user['password_hash'])) {

            // ===============================
            // CHECK IF USER IS SUSPENDED
            // ===============================
            $statusStmt = $conn->prepare("
                SELECT status FROM user_status WHERE user_id = ?
            ");
            $statusStmt->execute([$user['user_id']]);
            $statusRow = $statusStmt->fetch();

            if ($statusRow && $statusRow['status'] === 'suspended') {
                $error_message = "Your account has been suspended. Please contact the administrator.";
            }

            // ===============================
            // CHECK EMAIL VERIFICATION (FAMILY ONLY)
            // ===============================
            elseif ($user['role'] === 'family' && !$user['is_verified']) {
                $error_message = "Your account is not verified. Please check your email.";
            }

            // ===============================
            // LOGIN SUCCESS
            // ===============================
            else {

                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['username'] = $user['username'];

                //---------------------------------------------------------------------
                //Runs the check for health reports and medications.
                //Located in login.php to guarantee it will be run if any users are online.
                date_default_timezone_set("America/Vancouver");
                $today = date('Y-m-d');
                //checks if a health report exists. if not, one is made.
                //specifically: finds any residents who do not have health reports made yet today
                //and thne makes a health report
                $stmt = $conn->prepare("SELECT r.residentSIN
                                        FROM resident r
                                        WHERE NOT EXISTS 
                                            (select *
                                            from healthreport hr
                                            where date(hr.dateOfCreation) = date(now()) AND r.residentSIN = hr.residentSIN)");
                $stmt->execute();
                $reportsList = $stmt->fetchAll();
                foreach($reportsList as $rl) {
                            $stmt = $conn->prepare("
                                    INSERT INTO healthreport
                                    (residentSIN, empID)
                                    VALUES (?, 2)");
                            $stmt->execute([$rl['residentSIN']]);
                }
                //checks if a medication entry exists. if not, one is made. entry is linked to daily report
                //currently report is 2 by default. working on it.
                $stmt = $conn->prepare("SELECT DISTINCT m.medID,r.fname, max(hr.reportID) AS repID
                                        FROM medication m
                                        LEFT JOIN resident r ON m.residentSIN = r.residentSIN
                                        LEFT JOIN healthreport hr on r.residentSIN = hr.residentSIN
                                        WHERE NOT EXISTS (SELECT * FROM medication_entry me
                                                                    where me.date = date(now()) AND m.medID = me.medID)
                                        GROUP BY m.medID");
                $stmt->execute();
                $medsList = $stmt->fetchAll();
                foreach($medsList as $ml) {
                            $stmt = $conn->prepare("
                                    INSERT INTO medication_entry
                                    (medID, reportID, date)
                                    VALUES (?, ?, ?)");
                            $stmt->execute([$ml['medID'],$ml['repID'],$today]);
                }
                //-------------------------------------------------------------------

                switch($user['role']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        exit;

                    case 'family':
                        header("Location: family/dashboard.php");
                        exit;

                    case 'caregiver':
                        header("Location: caregivers/dashboard.php");
                        exit;

                    case 'resident':
                        header("Location: residents/dashboard.php");
                        exit;

                    default:
                        header("Location: login.php");
                        exit;
                }
            }

        } else {
            $error_message = "Invalid username or password.";
        }

    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    } finally {
        $stmt = null;
        $conn = null;
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Login</h3>

        <?php if (!empty($error_message)): ?>
            <p class="text-danger text-center">
                <?= htmlspecialchars($error_message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">
            Don't have an account? <a href="register.php">Sign Up</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
