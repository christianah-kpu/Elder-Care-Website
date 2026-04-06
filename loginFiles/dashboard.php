<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

try {
    // Fetch user status from the database
    $stmt = $conn->prepare("SELECT status FROM user_status WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row    = $stmt->fetch();
    $status = $row['status'] ?? null; // null if user has no status row yet

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} finally {
    $stmt = null;
    $conn = null;
}

if ($status === 'suspended') {
    echo "<p style='color:red; text-align:center;'>Your account is suspended. Please contact admin.</p>";
    session_unset();
    session_destroy();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        h1 { color: #333; }
        p  { margin: 10px 0; }
        a  { color: #007BFF; text-decoration: none; margin: 5px; }
        a:hover { text-decoration: underline; }
        .admin-link {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }
        .logout-link {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Welcome to the Dashboard</h1>
        <?php if ($user_role === 'admin'): ?>
            <p>You are logged in as an Admin.</p>
            <a href="Admin_panel.php" class="admin-link">Go to Admin Panel</a>
        <?php else: ?>
            <p>You are logged in as a User.</p>
        <?php endif; ?>
        <p><a href="logout.php" class="logout-link">Logout</a></p>
    </div>
</body>
</html>